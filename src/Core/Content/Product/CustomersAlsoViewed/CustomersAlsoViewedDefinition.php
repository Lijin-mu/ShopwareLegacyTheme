<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Content\Product\CustomersAlsoViewed;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class CustomersAlsoViewedDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 's_customers_also_viewed';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return CustomersAlsoViewedEntity::class;
    }

    public function getCollectionClass(): string
    {
        return CustomersAlsoViewedCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('target_product_id', 'targetProductId', ProductDefinition::class))->addFlags(new Required()),
            (new FkField('viewed_product_id', 'viewedProductId', ProductDefinition::class))->addFlags(new Required()),
            new IntField('hits', 'hits'),
        ]);
    }
}

