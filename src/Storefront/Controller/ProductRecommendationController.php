<?php

declare(strict_types=1);

namespace ShopwareLegacyTheme\Storefront\Controller;

use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Psr\Log\LoggerInterface;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class ProductRecommendationController extends StorefrontController
{
    private const ALLOWED_BOX_LAYOUTS = ['standard', 'image', 'minimal'];
    private const ALLOWED_DISPLAY_MODES = ['standard', 'cover', 'contain'];
    private const DEFAULT_BOX_LAYOUT = 'standard';
    private const DEFAULT_DISPLAY_MODE = 'standard';
    private const MAX_PRODUCT_IDS = 100;
    private const TEMPLATE_SLIDER = '@ShopwareLegacyTheme/storefront/component/recently-viewed-product/slider.html.twig';

    public function __construct(
        private readonly SalesChannelRepository $productRepository,
        private readonly LoggerInterface $logger
    ) {}

    #[Route(
        path: '/recently-viewed-product-slider',
        name: 'frontend.recently-viewed-product.slider',
        methods: ['POST'],
        defaults: ['XmlHttpRequest' => true, 'csrf_protected' => true]
    )]
    /**
     * Load recently viewed products for slider display.
     *
     * @param Request $request
     * @param SalesChannelContext $context
     * @return Response
     */
    public function loadProductSlider(Request $request, SalesChannelContext $context): Response
    {
        try {
            $boxLayout = $this->validateBoxLayout($request->get('boxLayout'));
            $displayMode = $this->validateDisplayMode($request->get('displayMode'));
            $validProductIds = $this->validateProductIdsInput($request->get('productIds'));

            $criteria = new Criteria($validProductIds);
            $criteria
                ->addAssociation('cover')
                ->addAssociation('cover.media');

            $products = $this->productRepository->search($criteria, $context);

            if ($products->count() === 0) {
                $this->logger->info('No products found for valid IDs', [
                    'product_ids' => $validProductIds,
                    'sales_channel_id' => $context->getSalesChannelId(),
                ]);
                return new Response('No products found', Response::HTTP_NOT_FOUND);
            }

            return $this->renderStorefront(
                self::TEMPLATE_SLIDER,
                [
                    'products' => $products,
                    'boxLayout' => $boxLayout,
                    'displayMode' => $displayMode,
                ]
            );
        } catch (\InvalidArgumentException $e) {
            $this->logger->error('Validation error in product slider', [
                'exception' => $e->getMessage(),
            ]);
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            $this->logger->critical('Unexpected error in product slider', [
                'message' => $e->getMessage()
            ]);
            return new Response('An unexpected error occurred', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function validateBoxLayout($boxLayout): string
    {
        if (!is_string($boxLayout) || !in_array($boxLayout, self::ALLOWED_BOX_LAYOUTS, true)) {
            if ($boxLayout !== null) {
                $this->logger->info('Invalid boxLayout provided, using default', [
                    'provided' => $boxLayout,
                    'default' => self::DEFAULT_BOX_LAYOUT,
                ]);
            }
            return self::DEFAULT_BOX_LAYOUT;
        }

        return $boxLayout;
    }

    private function validateDisplayMode($displayMode): string
    {
        if (!is_string($displayMode) || !in_array($displayMode, self::ALLOWED_DISPLAY_MODES, true)) {
            if ($displayMode !== null) {
                $this->logger->info('Invalid displayMode provided, using default', [
                    'provided' => $displayMode,
                    'default' => self::DEFAULT_DISPLAY_MODE,
                ]);
            }
            return self::DEFAULT_DISPLAY_MODE;
        }

        return $displayMode;
    }

    private function validateProductIdsInput($productIds): array
    {
        if (!is_array($productIds)) {
            $this->logger->warning('Invalid productIds type provided', [
                'type' => gettype($productIds),
            ]);
            throw new \InvalidArgumentException('Product IDs must be an array');
        }

        if (empty($productIds)) {
            $this->logger->warning('Empty productIds array provided');
            throw new \InvalidArgumentException('Product IDs cannot be empty');
        }

        $originalCount = count($productIds);
        if ($originalCount > self::MAX_PRODUCT_IDS) {
            $this->logger->warning('Product IDs limit exceeded', [
                'count' => $originalCount,
                'limit' => self::MAX_PRODUCT_IDS,
            ]);
            $productIds = array_slice($productIds, 0, self::MAX_PRODUCT_IDS);
        }

        $validProductIds = $this->filterValidUuids($productIds);

        if (empty($validProductIds)) {
            $this->logger->warning('No valid product IDs after validation', [
                'original_count' => $originalCount,
            ]);
            throw new \InvalidArgumentException('No valid product IDs provided');
        }

        return $validProductIds;
    }

    private function filterValidUuids(array $productIds): array
    {
        $validProductIds = [];
        $invalidCount = 0;

        foreach ($productIds as $productId) {
            if (!is_string($productId)) {
                $invalidCount++;
                continue;
            }

            if (!Uuid::isValid($productId)) {
                $invalidCount++;
                continue;
            }

            $validProductIds[] = $productId;
        }

        if ($invalidCount > 0) {
            $this->logger->info('Invalid product IDs filtered out', [
                'invalid_count' => $invalidCount,
                'valid_count' => count($validProductIds),
            ]);
        }

        return $validProductIds;
    }
}
