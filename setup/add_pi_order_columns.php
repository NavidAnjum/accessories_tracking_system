<?php
/**
 * Migration: add order_id, is_master, included_pis to pis table
 * Run once: /accessories_tracking_system/setup/add_pi_order_columns.php
 */
require_once __DIR__ . '/../includes/db.php';

$db = getDB();
$msgs = [];

$cols = [
    "ALTER TABLE `pis` ADD COLUMN `order_id`     VARCHAR(20)  NULL AFTER `id`",
    "ALTER TABLE `pis` ADD COLUMN `is_master`     TINYINT(1)   NOT NULL DEFAULT 0 AFTER `order_id`",
    "ALTER TABLE `pis` ADD COLUMN `included_pis`  JSON         NULL AFTER `is_master`",
    "ALTER TABLE `pis` ADD INDEX `idx_pis_order_id` (`order_id`)",
];

foreach ($cols as $sql) {
    try {
        $db->exec($sql);
        $msgs[] = "✓ " . substr($sql, 0, 80);
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'Duplicate column') || str_contains($e->getMessage(), 'already exists')) {
            $msgs[] = "— already exists: " . substr($sql, 20, 40);
        } else {
            $msgs[] = "✗ ERROR: " . $e->getMessage();
        }
    }
}

echo implode("\n", $msgs) . "\nDone.\n";
