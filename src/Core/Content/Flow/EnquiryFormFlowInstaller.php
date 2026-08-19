<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Content\Flow;

use ShopwareLegacyTheme\Core\Content\Flow\EmailTemplate;

class EnquiryFormFlowInstaller extends AbstractFormFlowInstaller
{
    protected function getTemplateTechnicalName(): string
    {
        return 'enquiry_form_email';
    }

    protected function getAvailableEntities(): array
    {
        return [
            'salutation' => 'salutation',
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'email',
            'phone' => 'phone',
            'description' => 'description',
            'productName' => 'productName',
            'salesChannel' => 'sales_channel',
        ];
    }

    protected function getMailTemplateTypeTranslations(): array
    {
        return [
            'en-GB' => 'Enquiry Form Email',
            'de-DE' => 'Anfrageformular E-Mail',
        ];
    }

    protected function getMailTemplateTranslations(): array
    {
        return [
            'en-GB' => [
                'sender_name' => '{{ salesChannel.name }}',
                'subject' => 'New Enquiry from {{ firstName }} {{ lastName }}',
                'description' => 'Enquiry Form Notification',
                'content_html' => EmailTemplate::getContentHtmlEn(),
                'content_plain' => EmailTemplate::getContentPlainEn(),
            ],
            'de-DE' => [
                'sender_name' => '{{ salesChannel.name }}',
                'subject' => 'Neue Anfrage von {{ firstName }} {{ lastName }}',
                'description' => 'Anfrageformular Benachrichtigung',
                'content_html' => EmailTemplate::getContentHtmlDe(),
                'content_plain' => EmailTemplate::getContentPlainDe(),
            ],
        ];
    }

    protected function getFlowName(): string
    {
        return 'Enquiry Form Email Notification';
    }

    protected function getEventName(): string
    {
        return 'enquiry.form.submitted';
    }

    protected function getFlowDescription(): string
    {
        return 'Send email notification when enquiry form is submitted';
    }

    protected function getFlowTemplateDescription(): string
    {
        return 'Send email notification when enquiry form is submitted';
    }
}

