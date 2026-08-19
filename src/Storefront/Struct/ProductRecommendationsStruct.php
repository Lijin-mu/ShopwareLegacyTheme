<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Storefront\Struct;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Framework\Struct\Struct;

class ProductRecommendationsStruct extends Struct
{
    public const EXTENSION_NAME = 'legacyRecommendations';

    private ?SalesChannelProductCollection $customersBought = null;

    private ?SalesChannelProductCollection $customersAlsoViewed = null;

    public function getCustomersBought(): ?SalesChannelProductCollection
    {
        return $this->customersBought;
    }

    public function setCustomersBought(?SalesChannelProductCollection $customersBought): void
    {
        $this->customersBought = $customersBought;
    }

    public function getCustomersAlsoViewed(): ?SalesChannelProductCollection
    {
        return $this->customersAlsoViewed;
    }

    public function setCustomersAlsoViewed(?SalesChannelProductCollection $customersAlsoViewed): void
    {
        $this->customersAlsoViewed = $customersAlsoViewed;
    }
}

