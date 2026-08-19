<?php

declare(strict_types=1);

namespace ShopwareLegacyTheme\Storefront\Subscriber;

use Psr\Log\LoggerInterface;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use ShopwareLegacyTheme\Storefront\Service\CustomersAlsoViewedService;
use ShopwareLegacyTheme\Storefront\Service\CustomersBoughtService;
use ShopwareLegacyTheme\Storefront\Struct\ProductRecommendationsStruct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductRecommendationPageSubscriber implements EventSubscriberInterface
{
    private const DEFAULT_LIMIT = 10;

    public function __construct(
        private readonly CustomersBoughtService $customersBoughtService,
        private readonly CustomersAlsoViewedService $customersAlsoViewedService,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @return array<class-string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
        ];
    }

    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $product = $event->getPage()->getProduct();
        if ($product === null) {
            return;
        }

        $productId = $product->getId();
        $context = $event->getSalesChannelContext();
        
        try {
            $recommendations = new ProductRecommendationsStruct();
            $recommendations->setCustomersBought(
                $this->customersBoughtService->loadEntities($productId, self::DEFAULT_LIMIT, $context)
            );
            $recommendations->setCustomersAlsoViewed(
                $this->customersAlsoViewedService->loadEntities($productId, self::DEFAULT_LIMIT, $context)
            );

            $event->getPage()->addExtension(ProductRecommendationsStruct::EXTENSION_NAME, $recommendations);
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to load product recommendations', [
                'productId' => $productId,
                'exception' => $exception,
            ]);
        }
    }
}
