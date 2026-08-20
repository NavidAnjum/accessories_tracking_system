<?php
/**
 * setup/migrate_erp_creation_dates.php
 *
 * One-time migration: ensure the erp_sale_orders_cache table has the
 * header_creation_date / line_creation_date columns, then populate them for
 * rows that were backfilled before those columns existed by extracting the
 * values from the stored raw_json.
 *
 * Run (CLI):  php setup/migrate_erp_creation_dates.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'CLI only']);
    exit;
}

// Force the local ed_module database in CLI mode.
$_SERVER['HTTP_HOST'] = 'localhost';

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_sale_orders_cache.php';

try {
    $db = getDB();
    // This also adds the new columns if they are missing.
    ensureErpSaleOrdersCacheTable($db);

    // Pull the ISO timestamps out of raw_json for rows that don't have them yet.
    $sql = "
        UPDATE erp_sale_orders_cache
        SET
            header_creation_date = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(raw_json, '$.header_creation_date')), 'null'),
            line_creation_date   = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(raw_json, '$.line_creation_date')),   'null')
        WHERE raw_json IS NOT NULL
          AND (
              header_creation_date IS NULL OR header_creation_date = ''
              OR line_creation_date IS NULL OR line_creation_date = ''
          )
    ";
    $affected = $db->exec($sql);

    $stats = erpSaleOrdersCacheStats($db);
    echo json_encode([
        'ok' => true,
        'rows_updated' => (int) $affected,
        'cache_total_rows' => (int) ($stats['total_rows'] ?? 0),
    ], JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]) . PHP_EOL;
    exit(1);
}
