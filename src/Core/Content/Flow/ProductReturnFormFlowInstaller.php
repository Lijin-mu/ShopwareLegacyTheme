<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Content\Flow;

use ShopwareLegacyTheme\Core\Content\Flow\ProductReturnFormEmailTemplate;

class ProductReturnFormFlowInstaller extends AbstractFormFlowInstaller
{
    protected function getTemplateTechnicalName(): string
    {
        return 'product_return_form_email';
    }

    protected function getAvailableEntities(): array
    {
        return [
            'customerNumber' => 'customerNumber',
            'email' => 'email',
            'invoiceNumber' => 'invoiceNumber',
            'itemNumbers' => 'itemNumbers',
            'comment' => 'comment',
            'productName' => 'productName',
            'salesChannel' => 'sales_channel',
        ];
    }

    protected function getMailTemplateTypeTranslations(): array
    {
        return [
            'en-GB' => 'Product Return Form Email',
            'de-DE' => 'Retourenformular E-Mail',
        ];
    }

    protected function getMailTemplateTranslations(): array
    {
        return [
            'en-GB' => [
                'sender_name' => '{{ salesChannel.name }}',
                'subject' => 'New Product Return Request - Customer: {{ customerNumber }}',
                'description' => 'Product Return Form Notification',
                'content_html' => ProductReturnFormEmailTemplate::getContentHtmlEn(),
                'content_plain' => ProductReturnFormEmailTemplate::getContentPlainEn(),
            ],
            'de-DE' => [
                'sender_name' => '{{ salesChannel.name }}',
                'subject' => 'Neue Retourenanfrage - Kunde: {{ customerNumber }}',
                'description' => 'Retourenformular Benachrichtigung',
                'content_html' => ProductReturnFormEmailTemplate::getContentHtmlDe(),
                'content_plain' => ProductReturnFormEmailTemplate::getContentPlainDe(),
            ],
        ];
    }

    protected function getFlowName(): string
    {
        return 'Product Return Form Email Notification';
    }

    protected function getEventName(): string
    {
        return 'product.return.form.submitted';
    }

    protected function getFlowDescription(): string
    {
        return 'Send email notification when product return form is submitted';
    }

    protected function getFlowTemplateDescription(): string
    {
        return 'Send email notification when product return form is submitted';
    }
}

