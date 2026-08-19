<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Content\Product\CustomersAlsoViewed;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class CustomersAlsoViewedEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $targetProductId = null;

    protected ?string $viewedProductId = null;

    protected ?int $hits = null;

    public function getTargetProductId(): ?string
    {
        return $this->targetProductId;
    }

    public function setTargetProductId(?string $targetProductId): void
    {
        $this->targetProductId = $targetProductId;
    }

    public function getViewedProductId(): ?string
    {
        return $this->viewedProductId;
    }

    public function setViewedProductId(?string $viewedProductId): void
    {
        $this->viewedProductId = $viewedProductId;
    }

    public function getHits(): ?int
    {
        return $this->hits;
    }

    public function setHits(?int $hits): void
    {
        $this->hits = $hits;
    }
}

