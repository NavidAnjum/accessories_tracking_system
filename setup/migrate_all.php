<?php
/**
 * migrate_all.php — Idempotent full migration for ATS / ED Module
 * Run once locally and once on live after uploading.
 * Safe to re-run: all steps use IF NOT EXISTS / try-catch on duplicates.
 */
require_once __DIR__ . '/../includes/db.php';

$db = getDB();
$ok = [];
$err = [];

function run(PDO $db, string $label, string $sql, array &$ok, array &$err): void {
    try {
        $db->exec($sql);
        $ok[] = "✓ $label";
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        // Treat "already exists" / "Duplicate column" as non-fatal
        if (stripos($msg, 'already exists') !== false
         || stripos($msg, 'Duplicate column') !== false
         || stripos($msg, 'Duplicate key name') !== false) {
            $ok[] = "– $label (already exists)";
        } else {
            $err[] = "✗ $label — $msg";
        }
    }
}

// ── 1. customers ──────────────────────────────────────────────────────────────
run($db, 'customers table', "
    CREATE TABLE IF NOT EXISTS `customers` (
        `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `company_name`        VARCHAR(255) NOT NULL,
        `address_head_office` TEXT,
        `factory_address`     TEXT,
        `chairman_name`       VARCHAR(255),
        `chairman_mobile`     VARCHAR(50),
        `customer_type`       VARCHAR(20) DEFAULT 'Regular',
        `date_form`           DATE,
        `politics_yes`        TINYINT(1) DEFAULT 0,
        `politics_party`      VARCHAR(100),
        `extra_data`          JSON,
        `stage`               VARCHAR(30) NOT NULL DEFAULT 'completed',
        `signatures`          JSON,
        `created_at`          DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at`          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_company_name` (`company_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $ok, $err);

run($db, 'customers.stage column', "ALTER TABLE `customers` ADD COLUMN `stage` VARCHAR(30) NOT NULL DEFAULT 'completed' AFTER `extra_data`", $ok, $err);
run($db, 'customers.signatures column', "ALTER TABLE `customers` ADD COLUMN `signatures` JSON NULL AFTER `stage`", $ok, $err);

// ── 2. buyers ────────────────────────────────────────────────────────────────
run($db, 'buyers table', "
    CREATE TABLE IF NOT EXISTS `buyers` (
        `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `buyer_code`    VARCHAR(50)  NOT NULL,
        `buyer_name`    VARCHAR(255) NOT NULL,
        `customer_name` VARCHAR(255),
        `address`       TEXT,
        `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_buyer_code` (`buyer_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $ok, $err);

// ── 3. users ─────────────────────────────────────────────────────────────────
run($db, 'users table', "
    CREATE TABLE IF NOT EXISTS `users` (
        `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `name`       VARCHAR(100) NOT NULL,
        `email`      VARCHAR(150) NOT NULL,
        `password`   VARCHAR(255) NOT NULL,
        `role`       VARCHAR(50)  NOT NULL DEFAULT 'staff',
        `is_active`  TINYINT(1) DEFAULT 1,
        `created_by` INT UNSIGNED NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $ok, $err);

// Seed admin if missing
try {
    $exists = $db->query("SELECT COUNT(*) FROM users WHERE email='admin@ed.local'")->fetchColumn();
    if (!$exists) {
        $hash = password_hash('Admin@1234', PASSWORD_DEFAULT);
        $db->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)")
           ->execute(['Administrator','admin@ed.local',$hash,'admin']);
        $ok[] = "✓ Admin user seeded (admin@ed.local / Admin@1234)";
    } else {
        $ok[] = "– Admin user already exists";
    }
} catch (PDOException $e) { $err[] = "✗ Admin seed — " . $e->getMessage(); }

// ── 4. orders ────────────────────────────────────────────────────────────────
run($db, 'orders table', "
    CREATE TABLE IF NOT EXISTS `orders` (
        `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `order_id`        VARCHAR(20)  NOT NULL,
        `customer_name`   VARCHAR(255),
        `salesperson`     VARCHAR(255),
        `intake_date`     DATE,
        `po_number`       VARCHAR(100),
        `trims_ipo`       VARCHAR(100),
        `without_arl`     TINYINT(1) DEFAULT 0,
        `to_buyer`        VARCHAR(255),
        `sub_description` TEXT,
        `paper_quality`   VARCHAR(255),
        `buyer_end_buyer` VARCHAR(255),
        `design`          VARCHAR(255),
        `order_no`        VARCHAR(100),
        `order_type`      VARCHAR(100),
        `delivery_date`   DATE,
        `buyer_address`   TEXT,
        `consignee_bank`  TEXT,
        `notes`           TEXT,
        `current_step`    VARCHAR(50) DEFAULT 'marketing-intake',
        `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at`      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_order_id` (`order_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $ok, $err);

run($db, 'orders.buyer_address column',  "ALTER TABLE `orders` ADD COLUMN `buyer_address`  TEXT NULL AFTER `delivery_date`", $ok, $err);
run($db, 'orders.consignee_bank column', "ALTER TABLE `orders` ADD COLUMN `consignee_bank` TEXT NULL AFTER `buyer_address`", $ok, $err);

// ── 5. order_items ───────────────────────────────────────────────────────────
run($db, 'order_items table', "
    CREATE TABLE IF NOT EXISTS `order_items` (
        `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `order_id`          VARCHAR(20)  NOT NULL,
        `sl_no`             INT          NOT NULL DEFAULT 1,
        `product_line`      VARCHAR(100),
        `item_name`         VARCHAR(255),
        `art_size`          VARCHAR(100),
        `grade`             VARCHAR(100),
        `paper_combination` VARCHAR(255),
        `qty`               DECIMAL(12,3) DEFAULT 0,
        `unit`              VARCHAR(20),
        `unit_price`        DECIMAL(12,4) DEFAULT 0,
        `amount`            DECIMAL(14,2) DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `idx_order_items_order_id` (`order_id`),
        CONSTRAINT `fk_order_items_order_id`
            FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $ok, $err);

// ── 6. page_data ─────────────────────────────────────────────────────────────
run($db, 'page_data table', "
    CREATE TABLE IF NOT EXISTS `page_data` (
        `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `order_id`   VARCHAR(20)  NOT NULL,
        `page_name`  VARCHAR(50)  NOT NULL,
        `data`       JSON,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_page_data` (`order_id`, `page_name`),
        CONSTRAINT `fk_page_data_order_id`
            FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $ok, $err);

// ── 7. pis ───────────────────────────────────────────────────────────────────
run($db, 'pis table', "
    CREATE TABLE IF NOT EXISTS `pis` (
        `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `order_id`     VARCHAR(20)  NULL,
        `is_master`    TINYINT(1)   NOT NULL DEFAULT 0,
        `included_pis` JSON         NULL,
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
        UNIQUE KEY `uq_pi_number` (`pi_number`),
        KEY `idx_pis_order_id` (`order_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $ok, $err);

// Migration columns on existing pis table
run($db, 'pis.order_id column',     "ALTER TABLE `pis` ADD COLUMN `order_id`     VARCHAR(20) NULL AFTER `id`", $ok, $err);
run($db, 'pis.is_master column',    "ALTER TABLE `pis` ADD COLUMN `is_master`    TINYINT(1) NOT NULL DEFAULT 0 AFTER `order_id`", $ok, $err);
run($db, 'pis.included_pis column', "ALTER TABLE `pis` ADD COLUMN `included_pis` JSON NULL AFTER `is_master`", $ok, $err);
run($db, 'pis order_id index',      "ALTER TABLE `pis` ADD INDEX `idx_pis_order_id` (`order_id`)", $ok, $err);

// ── 8. items (item master) ───────────────────────────────────────────────────
run($db, 'items table', "
    CREATE TABLE IF NOT EXISTS `items` (
        `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `product_line`      VARCHAR(100) NOT NULL,
        `item_name`         VARCHAR(255) NOT NULL,
        `grade`             VARCHAR(50)  DEFAULT 'N/A',
        `paper_combination` VARCHAR(255) DEFAULT 'N/A',
        `base_price`        DECIMAL(12,4) DEFAULT 0,
        `created_at`        DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at`        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
", $ok, $err);

// ── Output ───────────────────────────────────────────────────────────────────
header('Content-Type: text/plain; charset=utf-8');
echo "=== ATS Migration Results ===\n\n";
foreach ($ok  as $m) echo $m . "\n";
if ($err) {
    echo "\n--- ERRORS ---\n";
    foreach ($err as $m) echo $m . "\n";
}
echo "\n" . count($ok) . " OK, " . count($err) . " errors.\n";
echo "\nDone. Safe to run again.\n";
