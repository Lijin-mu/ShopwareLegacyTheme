<?php

declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Content\Cms\DataResolver\Element;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayStruct;

class CtaResolver extends AbstractCmsElementResolver
{

    private const MEDIA_FIELDS = ['mediaDesktop', 'mediaTablet', 'mediaMobile'];

    public function getType(): string
    {
        return 'cta';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        $config = $slot->getFieldConfig();
        
        $criteriaCollection = new CriteriaCollection();

        foreach (self::MEDIA_FIELDS as $mediaKey) {

            $mediaConfig = $config->get($mediaKey);

            if ($mediaConfig && !$mediaConfig->isMapped() && $mediaConfig->getValue()) {
                $criteria = new Criteria([$mediaConfig->getValue()]);

                $criteriaKey = $this->buildCriteriaKey($mediaKey, $slot);

                $criteriaCollection->add(
                    $criteriaKey,
                    MediaDefinition::class,
                    $criteria
                );
            }
        }
        return $criteriaCollection->all() !== [] ? $criteriaCollection : null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $config = $slot->getFieldConfig();
        $slotData = new ArrayStruct();

        foreach (self::MEDIA_FIELDS as $mediaKey) {
            $mediaConfig = $config->get($mediaKey);

            if (!$mediaConfig || !$mediaConfig->getValue()) {
                continue;
            }

            $criteriaKey = $this->buildCriteriaKey($mediaKey, $slot);
            $searchResult = $result->get($criteriaKey);
            
            if ($searchResult) {
                $mediaEntity = $searchResult->get($mediaConfig->getValue());

                if ($mediaEntity) {
                    $slotData->set($mediaKey, $mediaEntity);
                }
            }
        }
        $slot->setData($slotData);
    }

    private function buildCriteriaKey(string $mediaKey, CmsSlotEntity $slot): string
    {
        return sprintf('%s_%s', $mediaKey, $slot->getUniqueIdentifier());
    }
}