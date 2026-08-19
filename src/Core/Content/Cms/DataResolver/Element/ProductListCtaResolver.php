<?php

declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Content\Cms\DataResolver\Element;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayStruct;


class ProductListCtaResolver extends AbstractCmsElementResolver
{
    
    public function getType(): string
    {
        return 'product-list-cta';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $config = $slot->getFieldConfig();
        
        $productsConfig = $config->get('products');

        if (!$productsConfig || $productsConfig->isMapped() || !$productsConfig->getValue()) {
            return null;
        }

        $productIds = $productsConfig->getValue();
        
        if (!\is_array($productIds) || empty($productIds)) {
            return null;
        }

        $criteria = new Criteria($productIds);
        
        $criteria->addAssociation('cover');
        $criteria->addAssociation('options.group');

        $criteriaCollection = new CriteriaCollection();
        
        $criteriaKey = $this->buildCriteriaKey($slot);
        
        $criteriaCollection->add(
            $criteriaKey,
            ProductDefinition::class,
            $criteria
        );

        return $criteriaCollection;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $config = $slot->getFieldConfig();
        
        $productsConfig = $config->get('products');

        if (!$productsConfig || !$productsConfig->getValue()) {
            return;
        }

        $criteriaKey = $this->buildCriteriaKey($slot);
        
        $searchResult = $result->get($criteriaKey);

        if ($searchResult && $searchResult->getTotal() > 0) {

            $slotData = new ArrayStruct([
                'products' => $searchResult,
                'productIds' => $productsConfig->getValue(),
            ]);
            
            $slot->setData($slotData);
        }
    }

    private function buildCriteriaKey(CmsSlotEntity $slot): string
    {
        return sprintf('product_list_cta_%s', $slot->getUniqueIdentifier());
    }
}