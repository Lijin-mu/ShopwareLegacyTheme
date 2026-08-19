<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1764159142CreateCustomersAlsoViewedTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1764159142;
    }

    public function update(Connection $connection): void
    {
        // INT(10) for `hits` provides room for large counters while keeping storage small.
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `s_customers_also_viewed` (
    `id` BINARY(16) NOT NULL,
    `target_product_id` BINARY(16) NOT NULL,
    `viewed_product_id` BINARY(16) NOT NULL,
    `hits` INT(10) NULL DEFAULT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`id`),
    KEY `target_product_id` (`target_product_id`),
    KEY `viewed_product_id` (`viewed_product_id`),
    KEY `idx_target_viewed` (`target_product_id`, `viewed_product_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
        $connection->executeStatement('DROP TABLE IF EXISTS `s_customers_also_viewed`');
    }
}

