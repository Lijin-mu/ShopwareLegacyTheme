<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Event;

use Shopware\Core\Content\Flow\Dispatching\Aware\ScalarValuesAware;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\EventData\ScalarValueType;
use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Event\MailAware;
use Shopware\Core\Framework\Event\SalesChannelAware;
use Symfony\Contracts\EventDispatcher\Event;

class ProductReturnFormSubmittedEvent extends Event implements
    SalesChannelAware,
    MailAware,
    ScalarValuesAware,
    FlowEventAware
{
    public const EVENT_NAME = 'product.return.form.submitted';

    private ?MailRecipientStruct $mailRecipientStruct = null;

    public function __construct(
        private readonly string $salesChannelId,
        private readonly array $returnData,
        private readonly Context $context
    ) {
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('customerNumber', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('email', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('invoiceNumber', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('itemNumbers', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('comment', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('privacyCheck', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('productName', new ScalarValueType(ScalarValueType::TYPE_STRING));
    }

    /**
     * @return array<string, scalar|array<mixed>|null>
     */
    public function getValues(): array
    {
        return $this->returnData;
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

    public function getReturnData(): array
    {
        return $this->returnData;
    }

    public function getMailStruct(): MailRecipientStruct
    {
        if (!$this->mailRecipientStruct instanceof MailRecipientStruct) {
            $email = $this->returnData['email'] ?? 'customer@example.com';
            $customerNumber = $this->returnData['customerNumber'] ?? '';

            $this->mailRecipientStruct = new MailRecipientStruct([
                $email => $customerNumber ?: $email,
            ]);
        }

        return $this->mailRecipientStruct;
    }

    public function getSalesChannelContext(): ?\Shopware\Core\System\SalesChannel\SalesChannelContext
    {
        return null;
    }
}

