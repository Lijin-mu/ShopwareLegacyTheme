<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Content\Cms\SalesChannel\Block;

use Shopware\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockEntity;
use Shopware\Core\Content\Cms\DataResolver\CmsSlotsDataResolver;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\Struct\EnquiryFormStruct;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class EnquiryFormBlock
{
    private CmsSlotsDataResolver $slotResolver;

    public function __construct(CmsSlotsDataResolver $slotResolver)
    {
        $this->slotResolver = $slotResolver;
    }

    public function enrich(CmsBlockEntity $block, ResolverContext $context): void
    {
        $slots = $this->slotResolver->resolve(
            $block->getSlots(),
            $context
        );

        $block->setSlots($slots);
    }
}

