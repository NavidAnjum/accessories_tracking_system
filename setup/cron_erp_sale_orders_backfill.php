<?php
header('Content-Type: application/json; charset=utf-8');

const ERP_BACKFILL_ACCESS_KEY = '123';
const ERP_BACKFILL_START_DATE = '10/07/2026';
const ERP_BACKFILL_JOB_NAME = 'sale_orders_full_backfill';

$isCli = (PHP_SAPI === 'cli');
if ($isCli) {
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
require_once __DIR__ . '/../includes/erp_order_inbox.php';
require_once __DIR__ . '/../includes/erp_order_inbox.php';

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
            last_live_unique_rows INT NOT NULL DEFAULT 0,
            last_cached_before INT NOT NULL DEFAULT 0,
            last_new_rows INT NOT NULL DEFAULT 0,
            last_cached_after INT NOT NULL DEFAULT 0,
            last_missing_after INT NOT NULL DEFAULT 0,
            last_verified_from_date VARCHAR(10) NULL,
            last_verified_to_date VARCHAR(10) NULL,
            last_source_url VARCHAR(500) NULL,
            completed TINYINT(1) NOT NULL DEFAULT 0,
            last_error TEXT NULL,
            last_run_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_erp_backfill_job_name (job_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $wanted = [
        'next_to_date' => "ADD COLUMN next_to_date VARCHAR(10) NULL AFTER page_limit",
        'last_completed_to_date' => "ADD COLUMN last_completed_to_date VARCHAR(10) NULL AFTER next_to_date",
        'last_live_unique_rows' => "ADD COLUMN last_live_unique_rows INT NOT NULL DEFAULT 0 AFTER last_batch_count",
        'last_cached_before' => "ADD COLUMN last_cached_before INT NOT NULL DEFAULT 0 AFTER last_live_unique_rows",
        'last_new_rows' => "ADD COLUMN last_new_rows INT NOT NULL DEFAULT 0 AFTER last_cached_before",
        'last_cached_after' => "ADD COLUMN last_cached_after INT NOT NULL DEFAULT 0 AFTER last_new_rows",
        'last_missing_after' => "ADD COLUMN last_missing_after INT NOT NULL DEFAULT 0 AFTER last_cached_after",
        'last_verified_from_date' => "ADD COLUMN last_verified_from_date VARCHAR(10) NULL AFTER last_missing_after",
        'last_verified_to_date' => "ADD COLUMN last_verified_to_date VARCHAR(10) NULL AFTER last_verified_from_date",
    ];

    $stmt = $db->prepare("
        SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'erp_backfill_state'
    ");
    $stmt->execute();
    $existing = array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));

    $alters = [];
    foreach ($wanted as $col => $clause) {
        if (!in_array($col, $existing, true)) {
            $alters[] = $clause;
        }
    }
    if ($alters) {
        $db->exec('ALTER TABLE erp_backfill_state ' . implode(', ', $alters));
    }
}

function getErpBackfillState(PDO $db): array
{
    ensureErpBackfillStateTable($db);

    $stmt = $db->prepare("SELECT * FROM erp_backfill_state WHERE job_name = :job_name LIMIT 1");
    $stmt->execute([':job_name' => ERP_BACKFILL_JOB_NAME]);
    $row = $stmt->fetch();

    if ($row) {
        $needsReconciliationStart = empty($row['last_verified_from_date']);
        if (empty($row['next_to_date']) || $needsReconciliationStart) {
            $update = $db->prepare("
                UPDATE erp_backfill_state
                SET next_to_date = :next_to_date,
                    completed = 0,
                    last_error = NULL
                WHERE job_name = :job_name
            ");
            $update->execute([
                ':job_name' => ERP_BACKFILL_JOB_NAME,
                ':next_to_date' => ERP_BACKFILL_START_DATE,
            ]);
            $stmt->execute([':job_name' => ERP_BACKFILL_JOB_NAME]);
            $row = $stmt->fetch();
        }
        return $row ?: [];
    }

    $insert = $db->prepare("
        INSERT INTO erp_backfill_state (job_name, next_offset, page_limit, next_to_date, completed)
        VALUES (:job_name, 0, :page_limit, :next_to_date, 0)
    ");
    $insert->execute([
        ':job_name' => ERP_BACKFILL_JOB_NAME,
        ':page_limit' => ERP_SALE_ORDERS_LIMIT,
        ':next_to_date' => ERP_BACKFILL_START_DATE,
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

function normalizeBackfillDate(string $value): ?DateTimeImmutable
{
    $date = DateTimeImmutable::createFromFormat('d/m/Y', trim($value));
    return $date instanceof DateTimeImmutable ? $date : null;
}

try {
    $db = getDB();
    ensureErpSaleOrdersCacheTable($db);
    $state = getErpBackfillState($db);

    $nextDate = normalizeBackfillDate((string) ($state['next_to_date'] ?? ERP_BACKFILL_START_DATE));
    if (!$nextDate) {
        $nextDate = normalizeBackfillDate(ERP_BACKFILL_START_DATE);
    }
    if (!$nextDate) {
        throw new RuntimeException('Invalid ERP backfill start date configuration.');
    }

    $today = new DateTimeImmutable(date('Y-m-d'));
    if ($nextDate > $today) {
        $stats = erpSaleOrdersCacheStats($db);
        cronBackfillOut([
            'ok' => true,
            'stage' => 'already_complete',
            'job' => ERP_BACKFILL_JOB_NAME,
            'next_to_date' => $nextDate->format('d/m/Y'),
            'last_completed_to_date' => $state['last_completed_to_date'] ?? null,
            'rows_seen' => (int) ($state['rows_seen'] ?? 0),
            'rows_saved' => (int) ($state['rows_saved'] ?? 0),
            'cache_total_rows' => (int) ($stats['total_rows'] ?? 0),
            'cache_total_pos' => (int) ($stats['total_pos'] ?? 0),
            'last_synced_at' => $stats['last_synced_at'] ?? null,
        ]);
        exit(0);
    }

    $queryDate = $nextDate->format('d/m/Y');
    $offset = 0;
    $limit = ERP_SALE_ORDERS_LIMIT;
    $batchCount = 0;
    $batchSaved = 0;
    $lastSource = null;
    $liveKeys = [];
    $liveOrderNumbers = [];
    $missingBefore = 0;
    $inboxItems = [];

    while (true) {
        $response = erpSaleOrdersFetchDateRange($queryDate, $queryDate, $offset, $limit);
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
                'p_to_date' => $queryDate,
                'offset' => $offset,
                'error' => $decoded['error'] ?? 'Unknown ERP connection error',
                'source' => $response['url'] ?? null,
            ]);
            exit(1);
        }

        $items = $decoded['items'] ?? [];
        $pageCount = count($items);
        $batchCount += $pageCount;
        $lastSource = (string) ($response['url'] ?? '');

        if ($pageCount > 0) {
            foreach ($items as $item) {
                if (is_array($item)) $inboxItems[] = $item;
            }
            $pageKeys = erpSaleOrdersItemKeys($items);
            foreach (erpSaleOrdersItemOrderNumbers($items) as $number) {
                $liveOrderNumbers[$number] = true;
            }
            $unseenKeys = [];
            foreach ($pageKeys as $key) {
                if (!isset($liveKeys[$key])) {
                    $liveKeys[$key] = true;
                    $unseenKeys[] = $key;
                }
            }
            $missingBefore += count($unseenKeys) - erpSaleOrdersCountExistingKeys($db, $unseenKeys);

            $saved = erpSaleOrdersUpsertItems($db, $items, $offset, (int) ($decoded['limit'] ?? $limit));
            $batchSaved += (int) ($saved['saved'] ?? 0);
            syncErpOrderInbox($db, $items);
        }

        if (empty($decoded['hasMore'])) {
            break;
        }

        $offset += (int) ($decoded['limit'] ?? $limit);
    }

    $liveUniqueRows = count($liveKeys);
    $cachedBefore = max(0, $liveUniqueRows - $missingBefore);
    $cachedAfter = erpSaleOrdersCountExistingKeys($db, array_keys($liveKeys));
    $missingAfter = max(0, $liveUniqueRows - $cachedAfter);
    $liveOrderNumbers = array_keys($liveOrderNumbers);
    $cachedOrderNumbers = erpSaleOrdersExistingOrderNumbers($db, $liveOrderNumbers);
    $missingOrderNumbers = array_values(array_diff($liveOrderNumbers, $cachedOrderNumbers));

    $rowsSeen = (int) ($state['rows_seen'] ?? 0) + $batchCount;
    $rowsSaved = (int) ($state['rows_saved'] ?? 0) + $batchSaved;
    $pagesCompleted = (int) ($state['pages_completed'] ?? 0) + 1;
    $isCurrentDateRun = $nextDate->format('Y-m-d') === $today->format('Y-m-d');
    $inboxSync = syncErpOrderInbox($db, $inboxItems, $isCurrentDateRun);
    $verified = $missingAfter === 0 && !$missingOrderNumbers;
    $nextRunDate = !$verified ? $nextDate : ($isCurrentDateRun ? $today : $nextDate->modify('+1 day'));
    $completed = $nextRunDate > $today ? 1 : 0;
    $lastCompletedDate = $verified ? $queryDate : ($state['last_completed_to_date'] ?? null);

    updateErpBackfillState($db, [
        'next_to_date' => $nextRunDate->format('d/m/Y'),
        'last_completed_to_date' => $lastCompletedDate,
        'pages_completed' => $pagesCompleted,
        'rows_seen' => $rowsSeen,
        'rows_saved' => $rowsSaved,
        'last_batch_count' => $batchCount,
        'last_live_unique_rows' => $liveUniqueRows,
        'last_cached_before' => $cachedBefore,
        'last_new_rows' => $missingBefore,
        'last_cached_after' => $cachedAfter,
        'last_missing_after' => $missingAfter,
        'last_verified_from_date' => $queryDate,
        'last_verified_to_date' => $queryDate,
        'last_source_url' => $lastSource,
        'completed' => $completed,
        'last_error' => null,
        'last_run_at' => date('Y-m-d H:i:s'),
    ]);

    $stats = erpSaleOrdersCacheStats($db);
    cronBackfillOut([
        'ok' => $verified,
        'stage' => !$verified ? 'cache_verification_failed' : ($isCurrentDateRun ? 'current_date_resynced' : ($completed ? 'completed' : 'date_saved')),
        'job' => ERP_BACKFILL_JOB_NAME,
        'p_to_date' => $queryDate,
        'batch_count' => $batchCount,
        'batch_saved' => $batchSaved,
        'live_unique_rows' => $liveUniqueRows,
        'cached_before' => $cachedBefore,
        'new_rows_added' => $missingBefore,
        'cached_after' => $cachedAfter,
        'missing_after' => $missingAfter,
        'live_order_numbers' => count($liveOrderNumbers),
        'cached_order_numbers' => count($cachedOrderNumbers),
        'missing_order_numbers' => $missingOrderNumbers,
        'erp_inbox_orders' => (int)($inboxSync['orders'] ?? 0),
        'erp_inbox_new_orders' => (int)($inboxSync['new_orders'] ?? 0),
        'commercial_notifications_created' => (int)($inboxSync['notified'] ?? 0),
        'verified_range' => [
            'from' => $queryDate,
            'to' => $queryDate,
        ],
        'next_to_date' => $nextRunDate->format('d/m/Y'),
        'last_completed_to_date' => $lastCompletedDate,
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
