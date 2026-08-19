<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Content\Flow;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Flow\Aggregate\FlowTemplate\FlowTemplateDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

abstract class AbstractFormFlowInstaller
{
    public function __construct(protected Connection $connection)
    {
    }

    final public function install(): void
    {
        $mailTemplateTypeId = $this->createMailTemplateType();
        $mailTemplateId = $this->createMailTemplate($mailTemplateTypeId);
        $this->createFlow($mailTemplateId);
        $this->createFlowTemplate($mailTemplateId);
    }

    final public function uninstall(): void
    {
        $this->deleteFlowTemplate();
        $this->deleteFlow();
        $this->deleteMailTemplateAndType();
    }

    abstract protected function getTemplateTechnicalName(): string;

    abstract protected function getAvailableEntities(): array;

    /**
     * @return array<string, string>
     */
    abstract protected function getMailTemplateTypeTranslations(): array;

    /**
     * @return array<string, array<string, string>>
     */
    abstract protected function getMailTemplateTranslations(): array;

    abstract protected function getFlowName(): string;

    abstract protected function getEventName(): string;

    abstract protected function getFlowDescription(): string;

    abstract protected function getFlowTemplateDescription(): string;

    private function createMailTemplateType(): string
    {
        $templateTypeId = $this->connection->fetchOne(
            'SELECT id FROM mail_template_type WHERE technical_name = :technicalName',
            ['technicalName' => $this->getTemplateTechnicalName()]
        );

        if ($templateTypeId) {
            return Uuid::fromBytesToHex($templateTypeId);
        }

        $mailTemplateTypeId = Uuid::randomHex();

        $this->connection->insert('mail_template_type', [
            'id' => Uuid::fromHexToBytes($mailTemplateTypeId),
            'technical_name' => $this->getTemplateTechnicalName(),
            'available_entities' => json_encode($this->getAvailableEntities(), JSON_THROW_ON_ERROR),
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $this->insertTemplateTypeTranslations($mailTemplateTypeId);

        return $mailTemplateTypeId;
    }

    private function createMailTemplate(string $mailTemplateTypeId): string
    {
        $templateId = $this->connection->fetchOne(
            'SELECT id FROM mail_template WHERE mail_template_type_id = :typeId',
            ['typeId' => Uuid::fromHexToBytes($mailTemplateTypeId)]
        );

        if ($templateId) {
            return Uuid::fromBytesToHex($templateId);
        }

        $mailTemplateId = Uuid::randomHex();

        $this->connection->insert('mail_template', [
            'id' => Uuid::fromHexToBytes($mailTemplateId),
            'mail_template_type_id' => Uuid::fromHexToBytes($mailTemplateTypeId),
            'system_default' => true,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $this->insertTemplateTranslations($mailTemplateId);

        return $mailTemplateId;
    }

    private function createFlow(string $mailTemplateId): void
    {
        $flowId = $this->connection->fetchOne(
            'SELECT id FROM flow WHERE name = :name',
            ['name' => $this->getFlowName()]
        );

        if ($flowId) {
            return;
        }

        $flowBytes = Uuid::randomBytes();

        $this->connection->insert('flow', [
            'id' => $flowBytes,
            'name' => $this->getFlowName(),
            'event_name' => $this->getEventName(),
            'active' => true,
            'payload' => null,
            'invalid' => 0,
            'custom_fields' => null,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $this->connection->insert('flow_sequence', [
            'id' => Uuid::randomBytes(),
            'flow_id' => $flowBytes,
            'rule_id' => null,
            'parent_id' => null,
            'action_name' => 'action.mail.send',
            'position' => 1,
            'true_case' => 1,
            'display_group' => 1,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            'config' => json_encode([
                'recipient' => [
                    'data' => [],
                    'type' => 'default',
                ],
                'mailTemplateId' => $mailTemplateId,
                'documentTypeIds' => [],
            ], JSON_THROW_ON_ERROR),
        ]);
    }

    private function createFlowTemplate(string $mailTemplateId): void
    {
        $flowTemplateId = $this->connection->fetchOne(
            'SELECT id FROM flow_template WHERE name = :name',
            ['name' => $this->getFlowName()]
        );

        if ($flowTemplateId) {
            return;
        }

        $sequenceConfig = [
            [
                'id' => Uuid::randomHex(),
                'actionName' => 'action.mail.send',
                'config' => [
                    'recipient' => [
                        'data' => [],
                        'type' => 'default',
                    ],
                    'mailTemplateId' => $mailTemplateId,
                    'documentTypeIds' => [],
                ],
                'parentId' => null,
                'ruleId' => null,
                'position' => 1,
                'trueCase' => 0,
                'displayGroup' => 1,
            ],
        ];

        $this->connection->insert(FlowTemplateDefinition::ENTITY_NAME, [
            'id' => Uuid::randomBytes(),
            'name' => $this->getFlowName(),
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            'config' => json_encode([
                'eventName' => $this->getEventName(),
                'description' => $this->getFlowTemplateDescription(),
                'customFields' => null,
                'sequences' => $sequenceConfig,
            ], JSON_THROW_ON_ERROR),
        ]);
    }

    private function deleteFlowTemplate(): void
    {
        $flowTemplateId = $this->connection->fetchOne(
            'SELECT id FROM flow_template WHERE name = :name',
            ['name' => $this->getFlowName()]
        );

        if ($flowTemplateId) {
            $this->connection->delete('flow_template', ['id' => $flowTemplateId]);
        }
    }

    private function deleteFlow(): void
    {
        $flowId = $this->connection->fetchOne(
            'SELECT id FROM flow WHERE name = :name',
            ['name' => $this->getFlowName()]
        );

        if ($flowId) {
            $this->connection->delete('flow_sequence', ['flow_id' => $flowId]);
            $this->connection->delete('flow', ['id' => $flowId]);
        }
    }

    private function deleteMailTemplateAndType(): void
    {
        $mailTemplateTypeId = $this->connection->fetchOne(
            'SELECT id FROM mail_template_type WHERE technical_name = :technicalName',
            ['technicalName' => $this->getTemplateTechnicalName()]
        );

        if ($mailTemplateTypeId) {
            $criteria = ['mail_template_type_id' => $mailTemplateTypeId];

            $this->connection->delete('mail_template_translation', $criteria);
            $this->connection->delete('mail_template', $criteria);
            $this->connection->delete('mail_template_type_translation', $criteria);
            $this->connection->delete('mail_template_type', ['id' => $mailTemplateTypeId]);
        }
    }

    private function insertTemplateTypeTranslations(string $mailTemplateTypeId): void
    {
        foreach ($this->getMailTemplateTypeTranslations() as $locale => $name) {
            $languageId = $this->getLanguageIdByLocale($locale);
            if ($languageId === null) {
                continue;
            }

            $this->connection->insert('mail_template_type_translation', [
                'mail_template_type_id' => Uuid::fromHexToBytes($mailTemplateTypeId),
                'language_id' => Uuid::fromHexToBytes($languageId),
                'name' => $name,
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }
    }

    private function insertTemplateTranslations(string $mailTemplateId): void
    {
        foreach ($this->getMailTemplateTranslations() as $locale => $translation) {
            $languageId = $this->getLanguageIdByLocale($locale);
            if ($languageId === null) {
                continue;
            }

            $this->connection->insert('mail_template_translation', [
                'mail_template_id' => Uuid::fromHexToBytes($mailTemplateId),
                'language_id' => Uuid::fromHexToBytes($languageId),
                'sender_name' => $translation['sender_name'] ?? '{{ salesChannel.name }}',
                'subject' => $translation['subject'] ?? '',
                'description' => $translation['description'] ?? '',
                'content_html' => $translation['content_html'] ?? '',
                'content_plain' => $translation['content_plain'] ?? '',
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }
    }

    private function getLanguageIdByLocale(string $locale): ?string
    {
        $languageId = $this->connection->fetchOne(
            'SELECT language.id 
             FROM language 
             INNER JOIN locale ON language.locale_id = locale.id 
             WHERE locale.code = :code',
            ['code' => $locale]
        );

        return $languageId ? Uuid::fromBytesToHex($languageId) : null;
    }
}


