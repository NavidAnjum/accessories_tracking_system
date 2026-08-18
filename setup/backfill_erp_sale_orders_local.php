<?php
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

function backfillArg(string $name, $default = null)
{
    global $argv;
    foreach ((array) $argv as $arg) {
        if (strpos($arg, '--' . $name . '=') === 0) {
            return substr($arg, strlen($name) + 3);
        }
    }
    return $default;
}

function backfillOut(array $payload): void
{
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

try {
    $startOffset = max(0, (int) backfillArg('start', 0));
    $limit = max(1, (int) backfillArg('limit', ERP_SALE_ORDERS_LIMIT));
    $maxPages = max(1, (int) backfillArg('pages', 500));
    $sleepMs = max(0, (int) backfillArg('sleep', 0));

    $db = getDB();
    ensureErpSaleOrdersCacheTable($db);

    $offset = $startOffset;
    $page = 0;
    $savedTotal = 0;
    $rowCountTotal = 0;
    $startedAt = microtime(true);

    backfillOut([
        'ok' => true,
        'stage' => 'start',
        'db' => 'ed_module',
        'start_offset' => $startOffset,
        'limit' => $limit,
        'max_pages' => $maxPages,
        'sleep_ms' => $sleepMs,
    ]);

    while ($page < $maxPages) {
        $response = erpSaleOrdersFetchPage($offset, $limit);
        $decoded = erpSaleOrdersDecodeResponse($response);

        if (empty($decoded['ok'])) {
            backfillOut([
                'ok' => false,
                'stage' => 'fetch_error',
                'page' => $page + 1,
                'offset' => $offset,
                'error' => $decoded['error'] ?? 'Unknown ERP connection error',
                'source' => $response['url'] ?? null,
            ]);
            exit(1);
        }

        $items = $decoded['items'] ?? [];
        $rowCount = count($items);
        if ($rowCount === 0) {
            break;
        }

        $saved = erpSaleOrdersUpsertItems(
            $db,
            $items,
            (int) ($decoded['offset'] ?? $offset),
            (int) ($decoded['limit'] ?? $limit)
        );

        $savedTotal += (int) ($saved['saved'] ?? 0);
        $rowCountTotal += $rowCount;
        $page++;

        backfillOut([
            'ok' => true,
            'stage' => 'page',
            'page' => $page,
            'offset' => (int) ($decoded['offset'] ?? $offset),
            'rows' => $rowCount,
            'saved' => (int) ($saved['saved'] ?? 0),
            'has_more' => !empty($decoded['hasMore']),
            'source' => $response['url'] ?? null,
        ]);

        if (empty($decoded['hasMore'])) {
            break;
        }

        $offset += $limit;
        if ($sleepMs > 0) {
            usleep($sleepMs * 1000);
        }
    }

    $stats = erpSaleOrdersCacheStats($db);
    backfillOut([
        'ok' => true,
        'stage' => 'done',
        'pages_processed' => $page,
        'rows_seen' => $rowCountTotal,
        'rows_saved' => $savedTotal,
        'cache_total_rows' => (int) ($stats['total_rows'] ?? 0),
        'cache_total_pos' => (int) ($stats['total_pos'] ?? 0),
        'last_synced_at' => $stats['last_synced_at'] ?? null,
        'elapsed_seconds' => round(microtime(true) - $startedAt, 2),
    ]);
} catch (Throwable $e) {
    backfillOut([
        'ok' => false,
        'stage' => 'exception',
        'error' => $e->getMessage(),
    ]);
    exit(1);
}
