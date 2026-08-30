<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'CLI only']);
    exit;
}

$_SERVER['HTTP_HOST'] = 'localhost';

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_sale_orders_cache.php';

const ERP_LOCAL_BACKFILL_START_DATE = '10/07/2026';

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

function normalizeLocalBackfillDate(string $value): ?DateTimeImmutable
{
    $date = DateTimeImmutable::createFromFormat('d/m/Y', trim($value));
    return $date instanceof DateTimeImmutable ? $date : null;
}

try {
    $startDate = (string) backfillArg('start-date', ERP_LOCAL_BACKFILL_START_DATE);
    $days = max(1, (int) backfillArg('days', 1));
    $sleepMs = max(0, (int) backfillArg('sleep', 0));

    $fromDate = normalizeLocalBackfillDate($startDate);
    if (!$fromDate) {
        throw new RuntimeException('Invalid start date. Use dd/mm/YYYY.');
    }

    $db = getDB();
    ensureErpSaleOrdersCacheTable($db);

    $day = 0;
    $savedTotal = 0;
    $rowCountTotal = 0;
    $startedAt = microtime(true);

    backfillOut([
        'ok' => true,
        'stage' => 'start',
        'db' => 'ed_module',
        'start_date' => $fromDate->format('d/m/Y'),
        'days' => $days,
        'sleep_ms' => $sleepMs,
    ]);

    while ($day < $days) {
        $queryDate = $fromDate->modify('+' . $day . ' day')->format('d/m/Y');
        $offset = 0;
        $limit = ERP_SALE_ORDERS_LIMIT;
        $rowCount = 0;
        $daySaved = 0;
        $lastSource = null;

        while (true) {
            $response = erpSaleOrdersFetchToDate($queryDate, $offset, $limit);
            $decoded = erpSaleOrdersDecodeResponse($response);

            if (empty($decoded['ok'])) {
                backfillOut([
                    'ok' => false,
                    'stage' => 'fetch_error',
                    'day' => $day + 1,
                    'p_to_date' => $queryDate,
                    'offset' => $offset,
                    'error' => $decoded['error'] ?? 'Unknown ERP connection error',
                    'source' => $response['url'] ?? null,
                ]);
                exit(1);
            }

            $items = $decoded['items'] ?? [];
            $pageCount = count($items);
            $rowCount += $pageCount;
            $lastSource = $response['url'] ?? null;

            if ($pageCount > 0) {
                $saved = erpSaleOrdersUpsertItems(
                    $db,
                    $items,
                    $offset,
                    (int) ($decoded['limit'] ?? $limit)
                );
                $daySaved += (int) ($saved['saved'] ?? 0);
            }

            if (empty($decoded['hasMore'])) {
                break;
            }

            $offset += (int) ($decoded['limit'] ?? $limit);
        }

        $savedTotal += $daySaved;
        $rowCountTotal += $rowCount;
        $day++;

        backfillOut([
            'ok' => true,
            'stage' => 'date',
            'day' => $day,
            'p_to_date' => $queryDate,
            'rows' => $rowCount,
            'saved' => $daySaved,
            'source' => $lastSource,
        ]);

        if ($sleepMs > 0 && $day < $days) {
            usleep($sleepMs * 1000);
        }
    }

    $stats = erpSaleOrdersCacheStats($db);
    backfillOut([
        'ok' => true,
        'stage' => 'done',
        'days_processed' => $day,
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
