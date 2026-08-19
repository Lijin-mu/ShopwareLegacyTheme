<?php

declare(strict_types=1);

namespace ShopwareLegacyTheme\Storefront\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Shopware\Core\Content\Product\SalesChannel\AbstractProductListRoute;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CustomersAlsoViewedService
{
    public function __construct(
        private readonly Connection $connection,
        private readonly AbstractProductListRoute $productListRoute,
        private readonly ProductFormatter $productFormatter
    ) {}

    /**
     * Load products that customers viewed along with the given product.
     *
     * @param string $productId
     * @param int $limit
     * @param SalesChannelContext $context
     *
     * @return array<int, array<string, mixed>>
     */
    public function load(string $productId, int $limit, SalesChannelContext $context): array
    {
        if ($limit <= 0) {
            return [];
        }

        $viewedProductIds = $this->fetchViewedProductIds($productId, $limit);
        if (empty($viewedProductIds)) {
            return [];
        }

        $criteria = $this->createCriteria($viewedProductIds, $limit);
        $products = $this->productListRoute->load($criteria, $context)->getProducts();

        if ($products->count() === 0) {
            return [];
        }

        $orderedProducts = $this->orderProductsByIds($products, $viewedProductIds);

        $formatted = [];
        foreach ($orderedProducts as $product) {
            $formatted[] = $this->productFormatter->format($product, $context);
        }

        return $formatted;
    }

    public function loadEntities(
        string $productId,
        int $limit,
        SalesChannelContext $context
    ): SalesChannelProductCollection {
        
        if ($limit <= 0) {
            return new SalesChannelProductCollection([]);
        }

        $viewedProductIds = $this->fetchViewedProductIds($productId, $limit);
        if (empty($viewedProductIds)) {
            return new SalesChannelProductCollection([]);
        }

        $criteria = $this->createCriteria($viewedProductIds, $limit);
        $products = $this->productListRoute->load($criteria, $context)->getProducts();

        if ($products->count() === 0) {
            return new SalesChannelProductCollection([]);
        }

        return $this->orderProductsByIds($products, $viewedProductIds);
    }

    /**
     * @return string[]
     */
    private function fetchViewedProductIds(string $productId, int $limit): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('viewed_product_id', 'hits')
            ->from('s_customers_also_viewed', 'cav')
            ->where('cav.target_product_id = :productId')
            ->orderBy('cav.hits', 'DESC')
            ->setMaxResults($limit)
            ->setParameter('productId', $this->uuidToBinary($productId), ParameterType::BINARY);

        $result = $qb->executeQuery()->fetchAllAssociative();
        if (empty($result)) {
            return [];
        }

        $ids = [];
        foreach ($result as $row) {
            $ids[] = Uuid::fromBytesToHex($row['viewed_product_id']);
        }

        return $ids;
    }

    private function createCriteria(array $viewedProductIds, int $limit): Criteria
    {
        $criteria = new Criteria($viewedProductIds);
        $criteria->addAssociation('cover.media');
        $criteria->addAssociation('options.group');
        $criteria->addAssociation('seoUrls');
        $criteria->addAssociation('calculatedPrices');
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->setLimit($limit);

        return $criteria;
    }

    private function orderProductsByIds(
        SalesChannelProductCollection $products,
        array $orderedIds
    ): SalesChannelProductCollection {

        $ordered = [];
        foreach ($orderedIds as $productId) {
            $product = $products->get($productId);
            if ($product instanceof SalesChannelProductEntity) {
                $ordered[] = $product;
            }
        }

        return new SalesChannelProductCollection($ordered);
    }

    private function uuidToBinary(string $uuid): string
    {
        $hex = str_replace('-', '', $uuid);
        if (!Uuid::isValid($hex)) {
            throw new \InvalidArgumentException('Invalid UUID format: ' . $uuid);
        }

        return Uuid::fromHexToBytes($hex);
    }
}
