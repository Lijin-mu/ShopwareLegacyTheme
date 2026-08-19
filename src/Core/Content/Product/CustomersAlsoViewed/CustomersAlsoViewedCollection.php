<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Content\Product\CustomersAlsoViewed;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                              add(CustomersAlsoViewedEntity $entity)
 * @method void                              set(string $key, CustomersAlsoViewedEntity $entity)
 * @method CustomersAlsoViewedEntity[]       getIterator()
 * @method CustomersAlsoViewedEntity[]       getElements()
 * @method CustomersAlsoViewedEntity|null    get(string $key)
 * @method CustomersAlsoViewedEntity|null    first()
 * @method CustomersAlsoViewedEntity|null    last()
 */
class CustomersAlsoViewedCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return CustomersAlsoViewedEntity::class;
    }
}

