<?php
/**
 * Migration: create pis table
 * Run once: http://localhost/ed_module/setup/add_pis_table.php
 */
require_once __DIR__ . '/../includes/db.php';

$db = getDB();

$db->exec("
    CREATE TABLE IF NOT EXISTS `pis` (
        `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `pi_number`    VARCHAR(100) NOT NULL,
        `customer`     VARCHAR(255),
        `product_line` VARCHAR(255),
        `pi_date`      DATE,
        `status`       VARCHAR(50)  DEFAULT 'Saved',
        `grand_qty`    DECIMAL(14,3) DEFAULT 0,
        `grand_val`    DECIMAL(14,2) DEFAULT 0,
        `pos`          JSON,
        `created_by`   INT UNSIGNED,
        `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at`   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_pi_number` (`pi_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "✓ pis table created (or already exists).\n";
