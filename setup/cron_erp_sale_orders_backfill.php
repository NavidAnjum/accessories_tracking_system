<?php
header('Content-Type: application/json; charset=utf-8');

const ERP_BACKFILL_ACCESS_KEY = '123';
const ERP_BACKFILL_BATCH_PAGES = 10;

$isCli = (PHP_SAPI === 'cli');
if ($isCli) {
    // Force local DB in CLI mode.
    $_SERVER['HTTP_HOST'] = 'localhost';
} else {
    $providedKey = (string) ($_GET['key'] ?? '');
    if (!hash_equals(ERP_BACKFILL_ACCESS_KEY, $providedKey)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid key']);
        exit;
    }
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_sale_orders_cache.php';

const ERP_BACKFILL_JOB_NAME = 'sale_orders_full_backfill';

function cronBackfillOut(array $payload): void
{
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

function ensureErpBackfillStateTable(PDO $db): void
{
    $db->exec("
        CREATE TABLE IF NOT EXISTS erp_backfill_state (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            job_name VARCHAR(100) NOT NULL,
            next_offset INT NOT NULL DEFAULT 0,
            page_limit INT NOT NULL DEFAULT 1000,
            pages_completed INT NOT NULL DEFAULT 0,
            rows_seen BIGINT NOT NULL DEFAULT 0,
            rows_saved BIGINT NOT NULL DEFAULT 0,
            last_batch_count INT NOT NULL DEFAULT 0,
            last_source_url VARCHAR(500) NULL,
            completed TINYINT(1) NOT NULL DEFAULT 0,
            last_error TEXT NULL,
            last_run_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_erp_backfill_job_name (job_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

function getErpBackfillState(PDO $db): array
{
    ensureErpBackfillStateTable($db);

    $stmt = $db->prepare("SELECT * FROM erp_backfill_state WHERE job_name = :job_name LIMIT 1");
    $stmt->execute([':job_name' => ERP_BACKFILL_JOB_NAME]);
    $row = $stmt->fetch();

    if ($row) {
        return $row;
    }

    $insert = $db->prepare("
        INSERT INTO erp_backfill_state (job_name, next_offset, page_limit, completed)
        VALUES (:job_name, 0, :page_limit, 0)
    ");
    $insert->execute([
        ':job_name' => ERP_BACKFILL_JOB_NAME,
        ':page_limit' => ERP_SALE_ORDERS_LIMIT,
    ]);

    $stmt->execute([':job_name' => ERP_BACKFILL_JOB_NAME]);
    return $stmt->fetch() ?: [];
}

function updateErpBackfillState(PDO $db, array $values): void
{
    $sets = [];
    $params = [':job_name' => ERP_BACKFILL_JOB_NAME];

    foreach ($values as $column => $value) {
        $sets[] = $column . ' = :' . $column;
        $params[':' . $column] = $value;
    }

    if (!$sets) {
        return;
    }

    $sql = "UPDATE erp_backfill_state SET " . implode(', ', $sets) . " WHERE job_name = :job_name";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
}

try {
    $db = getDB();
    ensureErpSaleOrdersCacheTable($db);
    $state = getErpBackfillState($db);

    // NOTE: the job never permanently stops. The ERP returns orders oldest→newest
    // across the offset range, so brand-new orders appear at the tail (highest
    // offset). Once the full crawl has caught up (completed=1), each run re-polls
    // from next_offset (the tail) forward so newly-created orders keep syncing.
    $caughtUp = !empty($state['completed']);

    $offset = max(0, (int) ($state['next_offset'] ?? 0));
    $limit = max(1, (int) ($state['page_limit'] ?? ERP_SALE_ORDERS_LIMIT));
    $pagesThisRun = 0;
    $rowsSeenThisRun = 0;
    $rowsSavedThisRun = 0;
    $hasMore = true;
    $lastSource = null;
    $lastBatchCount = 0;
    $processedOffsets = [];

    while ($pagesThisRun < ERP_BACKFILL_BATCH_PAGES && $hasMore) {
        $response = erpSaleOrdersFetchPage($offset, $limit);
        $decoded = erpSaleOrdersDecodeResponse($response);

        if (empty($decoded['ok'])) {
            updateErpBackfillState($db, [
                'last_error' => (string) ($decoded['error'] ?? 'Unknown ERP connection error'),
                'last_run_at' => date('Y-m-d H:i:s'),
                'last_source_url' => (string) ($response['url'] ?? ''),
            ]);

            cronBackfillOut([
                'ok' => false,
                'stage' => 'fetch_error',
                'job' => ERP_BACKFILL_JOB_NAME,
                'offset' => $offset,
                'limit' => $limit,
                'pages_this_run' => $pagesThisRun,
                'error' => $decoded['error'] ?? 'Unknown ERP connection error',
                'source' => $response['url'] ?? null,
            ]);
            exit(1);
        }

        $items = $decoded['items'] ?? [];
        $batchCount = count($items);
        $lastBatchCount = $batchCount;
        $lastSource = (string) ($response['url'] ?? '');
        $processedOffsets[] = $offset;

        if ($batchCount > 0) {
            $saved = erpSaleOrdersUpsertItems(
                $db,
                $items,
                (int) ($decoded['offset'] ?? $offset),
                (int) ($decoded['limit'] ?? $limit)
            );
            $rowsSavedThisRun += (int) ($saved['saved'] ?? 0);
            $rowsSeenThisRun += $batchCount;
        }

        $pagesThisRun++;
        $hasMore = !empty($decoded['hasMore']);
        if (!$hasMore) {
            break;
        }

        $offset += $limit;
    }

    $pagesCompleted = (int) ($state['pages_completed'] ?? 0) + $pagesThisRun;
    $rowsSeen = (int) ($state['rows_seen'] ?? 0) + $rowsSeenThisRun;
    $rowsSaved = (int) ($state['rows_saved'] ?? 0) + $rowsSavedThisRun;
    $nextOffset = $hasMore ? $offset : (($processedOffsets[count($processedOffsets) - 1] ?? 0));

    updateErpBackfillState($db, [
        'next_offset' => $nextOffset,
        'pages_completed' => $pagesCompleted,
        'rows_seen' => $rowsSeen,
        'rows_saved' => $rowsSaved,
        'last_batch_count' => $lastBatchCount,
        'last_source_url' => $lastSource,
        'completed' => $hasMore ? 0 : 1,
        'last_error' => null,
        'last_run_at' => date('Y-m-d H:i:s'),
    ]);

    // Stage: still crawling → batch_saved; reached the end → either the first
    // completion, or a tail re-poll that looked for newly-added orders.
    $stage = $hasMore ? 'batch_saved' : ($caughtUp ? 'tail_repolled' : 'completed');

    $stats = erpSaleOrdersCacheStats($db);
    cronBackfillOut([
        'ok' => true,
        'stage' => $stage,
        'caught_up' => !$hasMore,
        'job' => ERP_BACKFILL_JOB_NAME,
        'pages_this_run' => $pagesThisRun,
        'processed_offsets' => $processedOffsets,
        'limit_per_page' => $limit,
        'rows_seen_this_run' => $rowsSeenThisRun,
        'rows_saved_this_run' => $rowsSavedThisRun,
        'has_more' => $hasMore,
        'next_offset' => $nextOffset,
        'pages_completed' => $pagesCompleted,
        'rows_seen' => $rowsSeen,
        'rows_saved' => $rowsSaved,
        'cache_total_rows' => (int) ($stats['total_rows'] ?? 0),
        'cache_total_pos' => (int) ($stats['total_pos'] ?? 0),
        'last_synced_at' => $stats['last_synced_at'] ?? null,
        'source' => $lastSource,
    ]);
} catch (Throwable $e) {
    cronBackfillOut([
        'ok' => false,
        'stage' => 'exception',
        'job' => ERP_BACKFILL_JOB_NAME,
        'error' => $e->getMessage(),
    ]);
    exit(1);
}
