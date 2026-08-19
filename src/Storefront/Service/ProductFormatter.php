<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Storefront\Service;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProductFormatter
{
    /**
     * Format the product entity into the payload expected by the storefront widgets.
     *
     * @param SalesChannelProductEntity $product
     * @param SalesChannelContext $context
     *
     * @return array<string, mixed>
     */
    public function format(SalesChannelProductEntity $product, SalesChannelContext $context): array
    {
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'translated' => [
                'name' => $product->getTranslated()['name'] ?? $product->getName(),
            ],
            'cover' => $this->formatCover($product),
            'calculatedPrice' => $this->formatCalculatedPrice($product, $context),
            'calculatedPrices' => $this->formatCalculatedPrices($product, $context),
            'seoUrls' => $this->formatSeoUrls($product),
        ];
    }

    private function formatCover(SalesChannelProductEntity $product): ?array
    {
        $cover = $product->getCover();
        if ($cover === null) {
            return null;
        }

        $media = $cover->getMedia();
        if ($media === null) {
            return null;
        }

        $coverUrl = $media->getUrl();

        return [
            'url' => $coverUrl,
            'media' => ['url' => $coverUrl],
        ];
    }

    private function formatCalculatedPrice(SalesChannelProductEntity $product, SalesChannelContext $context): ?array
    {
        try {
            $price = $product->getCalculatedPrice();
            if ($price === null) {
                return null;
            }

            return [
                'unitPrice' => $price->getUnitPrice(),
                'currency' => [
                    'symbol' => $context->getCurrency()->getSymbol() ?? '€',
                ],
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function formatCalculatedPrices(SalesChannelProductEntity $product, SalesChannelContext $context): array
    {
        $formattedPrices = [];

        try {
            $prices = $product->getCalculatedPrices();
            if ($prices === null) {
                return [];
            }

            foreach ($prices as $price) {
                $formattedPrices[] = [
                    'unitPrice' => $price->getUnitPrice(),
                    'currency' => [
                        'symbol' => $context->getCurrency()->getSymbol() ?? '€',
                    ],
                ];
            }
        } catch (\Throwable) {
            return [];
        }

        return $formattedPrices;
    }

    private function formatSeoUrls(SalesChannelProductEntity $product): array
    {
        $seoUrls = [];

        if ($product->getSeoUrls() === null) {
            return $seoUrls;
        }

        foreach ($product->getSeoUrls() as $seoUrl) {
            $seoUrls[] = [
                'seoPathInfo' => $seoUrl->getSeoPathInfo(),
            ];
        }

        return $seoUrls;
    }
}


