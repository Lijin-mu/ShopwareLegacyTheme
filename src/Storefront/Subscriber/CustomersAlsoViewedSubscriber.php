<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Storefront\Subscriber;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CustomersAlsoViewedSubscriber implements EventSubscriberInterface
{
    private Connection $connection;
    private RequestStack $requestStack;

    public function __construct(
        Connection $connection,
        RequestStack $requestStack
    ) {
        $this->connection = $connection;
        $this->requestStack = $requestStack;
    }

    /**
     * @return array<class-string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
        ];
    }

    /**
     * Persist product relations when a storefront product page is viewed.
     *
     * @param ProductPageLoadedEvent $event
     *
     * @return void
     */
    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $mainRequest = $this->requestStack->getMainRequest();
        if (!$mainRequest || !$mainRequest->hasSession()) {
            return;
        }

        $session = $mainRequest->getSession();
        $product = $event->getPage()->getProduct();
        $productId = $product->getId();

        if ($this->isNewProduct($session, $productId)) {
            $this->addOrUpdateProductView($session, $productId);
        }

        $this->setLastSeenProduct($session, $productId);
    }

    private function isNewProduct(SessionInterface $session, string $productId): bool
    {
        $lastSeenProductId = $session->get('last_seen_product');

        return $lastSeenProductId !== null && $lastSeenProductId !== $productId;
    }

    private function setLastSeenProduct(SessionInterface $session, string $productId): void
    {
        $session->set('last_seen_product', $productId);
    }

    private function addOrUpdateProductView(SessionInterface $session, string $productId): void
    {
        $lastSeenProductId = $session->get('last_seen_product');

        if ($lastSeenProductId === null) {
            return;
        }

        $productIdBinary = $this->uuidToBinary($productId);
        $lastSeenProductIdBinary = $this->uuidToBinary($lastSeenProductId);

        $existingHits = $this->fetchExistingHits($productIdBinary, $lastSeenProductIdBinary);

        if ($existingHits !== null) {
            $this->incrementProductHit($productIdBinary, $lastSeenProductIdBinary, $existingHits);

            return;
        }

        if ($this->productExists($lastSeenProductIdBinary)) {
            $this->insertProductView($productIdBinary, $lastSeenProductIdBinary);
        }
    }

    private function fetchExistingHits(string $productId, string $lastSeenProductId): ?int
    {
        $result = $this->connection->executeQuery(
            'SELECT hits FROM s_customers_also_viewed 
             WHERE target_product_id = :targetProductId 
             AND viewed_product_id = :viewedProductId',
            [
                'targetProductId' => $productId,
                'viewedProductId' => $lastSeenProductId,
            ],
            [
                'targetProductId' => ParameterType::BINARY,
                'viewedProductId' => ParameterType::BINARY,
            ]
        )->fetchAssociative();

        if ($result === false || !isset($result['hits'])) {
            return null;
        }

        return (int) $result['hits'];
    }

    private function incrementProductHit(string $productId, string $lastSeenProductId, int $currentHits): void
    {
        $this->connection->update(
            's_customers_also_viewed',
            [
                'hits' => $currentHits + 1,
                'updated_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ],
            [
                'target_product_id' => $productId,
                'viewed_product_id' => $lastSeenProductId,
            ],
            [
                'target_product_id' => ParameterType::BINARY,
                'viewed_product_id' => ParameterType::BINARY,
            ]
        );
    }

    private function productExists(string $productId): bool
    {
        $result = $this->connection->fetchOne(
            'SELECT 1 FROM product WHERE id = :id',
            ['id' => $productId],
            ['id' => ParameterType::BINARY]
        );

        return (bool) $result;
    }

    private function insertProductView(string $productId, string $lastSeenProductId): void
    {
        $this->connection->insert('s_customers_also_viewed', [
            'id' => Uuid::randomBytes(),
            'target_product_id' => $productId,
            'viewed_product_id' => $lastSeenProductId,
            'hits' => 1,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ], [
            'id' => ParameterType::BINARY,
            'target_product_id' => ParameterType::BINARY,
            'viewed_product_id' => ParameterType::BINARY,
            'hits' => ParameterType::INTEGER,
            'created_at' => ParameterType::STRING,
        ]);
    }

    /**
     * Convert UUID string (with or without dashes) to binary format
     */
    private function uuidToBinary(string $uuid): string
    {
        // Remove dashes if present
        $hex = str_replace('-', '', $uuid);

        // Validate and convert to binary
        if (!Uuid::isValid($hex)) {
            throw new \InvalidArgumentException('Invalid UUID format: ' . $uuid);
        }

        return Uuid::fromHexToBytes($hex);
    }
}

