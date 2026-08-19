<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Content\Cms\SalesChannel\Struct;

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Struct\Struct;

class ProductReturnFormStruct extends Struct
{
    protected string $title = '';
    protected string $subtitle = '';
    protected bool $showLabel = true;
    protected bool $showSalutation = false;
    protected bool $showPhone = true;
    protected bool $showDescription = true;
    protected bool $showProductNumber = true;
    protected bool $showReason = true;
    /** @var string Fallback default - actual value comes from CMS element configuration (can be translated) */
    protected string $buttonText = 'Submit Return Request';
    /** @var string Fallback default - actual value comes from CMS element configuration (can be translated) */
    protected string $successMessage = 'Your return request has been submitted successfully!';
    protected ?EntitySearchResult $salutations = null;
    protected ?string $productName = null;
    protected ?string $productNumber = null;
    /**
     * @var list<string>
     */
    protected array $reasonOptions = [];

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function setSubtitle(string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function getShowLabel(): bool
    {
        return $this->showLabel;
    }

    public function setShowLabel(bool $showLabel): void
    {
        $this->showLabel = $showLabel;
    }

    public function getShowSalutation(): bool
    {
        return $this->showSalutation;
    }

    public function setShowSalutation(bool $showSalutation): void
    {
        $this->showSalutation = $showSalutation;
    }

    public function getShowPhone(): bool
    {
        return $this->showPhone;
    }

    public function setShowPhone(bool $showPhone): void
    {
        $this->showPhone = $showPhone;
    }

    public function getShowDescription(): bool
    {
        return $this->showDescription;
    }

    public function setShowDescription(bool $showDescription): void
    {
        $this->showDescription = $showDescription;
    }

    public function getShowProductNumber(): bool
    {
        return $this->showProductNumber;
    }

    public function setShowProductNumber(bool $showProductNumber): void
    {
        $this->showProductNumber = $showProductNumber;
    }

    public function getShowReason(): bool
    {
        return $this->showReason;
    }

    public function setShowReason(bool $showReason): void
    {
        $this->showReason = $showReason;
    }

    public function getButtonText(): string
    {
        return $this->buttonText;
    }

    public function setButtonText(string $buttonText): void
    {
        $this->buttonText = $buttonText;
    }

    public function getSuccessMessage(): string
    {
        return $this->successMessage;
    }

    public function setSuccessMessage(string $successMessage): void
    {
        $this->successMessage = $successMessage;
    }

    public function getSalutations(): ?EntitySearchResult
    {
        return $this->salutations;
    }

    public function setSalutations(?EntitySearchResult $salutations): void
    {
        $this->salutations = $salutations;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(?string $productName): void
    {
        $this->productName = $productName;
    }

    public function getProductNumber(): ?string
    {
        return $this->productNumber;
    }

    public function setProductNumber(?string $productNumber): void
    {
        $this->productNumber = $productNumber;
    }

    /**
     * @return list<string>
     */
    public function getReasonOptions(): array
    {
        return $this->reasonOptions;
    }

    /**
     * @param list<string> $reasonOptions
     */
    public function setReasonOptions(array $reasonOptions): void
    {
        $this->reasonOptions = $reasonOptions;
    }
}

