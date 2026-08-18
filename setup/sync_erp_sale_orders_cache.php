<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'CLI only']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_sale_orders_cache.php';

function syncArgValue(string $name, $default = null)
{
    global $argv;
    foreach ((array) $argv as $arg) {
        if (strpos($arg, '--' . $name . '=') === 0) {
            return substr($arg, strlen($name) + 3);
        }
    }
    return $default;
}

try {
    $offset = max(0, (int) syncArgValue('offset', 0));
    $limit = max(1, (int) syncArgValue('limit', ERP_SALE_ORDERS_LIMIT));

    $db = getDB();
    ensureErpSaleOrdersCacheTable($db);

    $response = erpSaleOrdersFetchPage($offset, $limit);
    $decoded = erpSaleOrdersDecodeResponse($response);
    if (empty($decoded['ok'])) {
        echo json_encode([
            'ok' => false,
            'offset' => $offset,
            'limit' => $limit,
            'error' => $decoded['error'] ?? 'Unknown ERP connection error',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        exit(1);
    }

    $saved = erpSaleOrdersUpsertItems(
        $db,
        $decoded['items'] ?? [],
        (int) ($decoded['offset'] ?? $offset),
        (int) ($decoded['limit'] ?? $limit)
    );
    $stats = erpSaleOrdersCacheStats($db);

    echo json_encode([
        'ok' => true,
        'offset' => (int) ($decoded['offset'] ?? $offset),
        'limit' => (int) ($decoded['limit'] ?? $limit),
        'hasMore' => !empty($decoded['hasMore']),
        'count' => (int) ($decoded['count'] ?? 0),
        'saved' => (int) ($saved['saved'] ?? 0),
        'cache' => [
            'totalRows' => (int) ($stats['total_rows'] ?? 0),
            'totalPos' => (int) ($stats['total_pos'] ?? 0),
            'lastSyncedAt' => $stats['last_synced_at'] ?? null,
        ],
        'source' => $response['url'] ?? null,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (Throwable $e) {
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit(1);
}
