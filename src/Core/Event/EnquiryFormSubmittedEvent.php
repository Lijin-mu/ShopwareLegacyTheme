<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Event;

use Shopware\Core\Content\Flow\Dispatching\Aware\ScalarValuesAware;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\EventData\ScalarValueType;
use Shopware\Core\Framework\Event\MailAware;
use Shopware\Core\Framework\Event\SalesChannelAware;
use Shopware\Core\Framework\Event\FlowEventAware;
use Symfony\Contracts\EventDispatcher\Event;

class EnquiryFormSubmittedEvent extends Event implements SalesChannelAware, MailAware, ScalarValuesAware, FlowEventAware
{
    public const EVENT_NAME = 'enquiry.form.submitted';

    private ?MailRecipientStruct $mailRecipientStruct = null;

    public function __construct(
        private readonly string $salesChannelId,
        private readonly array $enquiryData,
        private readonly Context $context
    ) {
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('salutationDisplay', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('firstName', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('lastName', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('email', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('phone', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('description', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('productName', new ScalarValueType(ScalarValueType::TYPE_STRING));
    }

    /**
     * @return array<string, scalar|array<mixed>|null>
     */
    public function getValues(): array
    {
        return $this->enquiryData;
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getEnquiryData(): array
    {
        return $this->enquiryData;
    }

    public function getMailStruct(): MailRecipientStruct
    {
        if (!$this->mailRecipientStruct instanceof MailRecipientStruct) {
            $fullName = trim(($this->enquiryData['firstName'] ?? '') . ' ' . ($this->enquiryData['lastName'] ?? ''));
            $this->mailRecipientStruct = new MailRecipientStruct([
                $this->enquiryData['email'] => $fullName
            ]);
        }

        return $this->mailRecipientStruct;
    }

    public function getSalesChannelContext(): ?\Shopware\Core\System\SalesChannel\SalesChannelContext
    {
        return null;
    }
}

