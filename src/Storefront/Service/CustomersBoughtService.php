<?php

declare(strict_types=1);

namespace ShopwareLegacyTheme\Storefront\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Shopware\Core\Content\Product\SalesChannel\AbstractProductListRoute;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CustomersBoughtService
{
    public function __construct(
        private readonly Connection $connection,
        private readonly AbstractProductListRoute $productListRoute,
        private readonly ProductFormatter $productFormatter
    ) {}

    /**
     * Load products frequently bought together with the provided product.
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

        $productIds = $this->fetchProductIds($productId, $context, $limit);
        if (empty($productIds)) {
            return [];
        }

        $criteria = $this->createCriteria($productIds, $limit);
        $products = $this->productListRoute->load($criteria, $context)->getProducts();

        $formatted = [];
        foreach ($products as $product) {
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

        $productIds = $this->fetchProductIds($productId, $context, $limit);
        if (empty($productIds)) {
            return new SalesChannelProductCollection([]);
        }

        $criteria = $this->createCriteria($productIds, $limit);
        $products = $this->productListRoute->load($criteria, $context)->getProducts();

        return $this->orderProductsByIds($products, $productIds);
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

    /**
     * @return string[]
     */
    private function fetchProductIds(string $productId, SalesChannelContext $context, int $limit): array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('ol.product_id', 'COUNT(DISTINCT ol.order_id) AS order_count')
            ->from('order_line_item', 'ol')
            ->innerJoin('ol', 'product', 'p', 'p.id = ol.product_id AND p.version_id = ol.version_id')
            ->innerJoin(
                'ol',
                'product_visibility',
                'pv',
                'pv.product_id = p.id AND pv.product_version_id = p.version_id ' .
                    'AND pv.sales_channel_id = :salesChannelId'
            )
            ->where('ol.product_id != :productId')
            ->andWhere('ol.type = :orderLineItemType')
            ->andWhere('ol.version_id = :versionId')
            ->andWhere('p.active = 1')
            ->groupBy('ol.product_id')
            ->orderBy('order_count', 'DESC')
            ->setMaxResults($limit)
            ->setParameter('productId', $this->uuidToBinary($productId), ParameterType::BINARY)
            ->setParameter('salesChannelId', $this->uuidToBinary($context->getSalesChannelId()), ParameterType::BINARY)
            ->setParameter('versionId', $this->uuidToBinary($context->getVersionId()), ParameterType::BINARY)
            ->setParameter('orderLineItemType', 'product', ParameterType::STRING);

        $result = $qb->executeQuery()->fetchAllAssociative();
        if (empty($result)) {
            return [];
        }

        return array_map(
            static fn(array $row) => Uuid::fromBytesToHex($row['product_id']),
            $result
        );
    }

    private function createCriteria(array $productIds, int $limit): Criteria
    {
        $criteria = new Criteria($productIds);
        $criteria->addAssociation('cover.media');
        $criteria->addAssociation('options.group');
        $criteria->addAssociation('seoUrls');
        $criteria->addAssociation('calculatedPrices');
        $criteria->setLimit($limit);

        return $criteria;
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
