<?php

require_once __DIR__ . '/db.php';

function ensureErpOrderInboxTable(PDO $db): void
{
    static $done = false;
    if ($done) return;

    $db->exec("
        CREATE TABLE IF NOT EXISTS erp_order_inbox (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            sale_order_no VARCHAR(80) NULL,
            customer_po_no VARCHAR(255) NULL,
            customer_name VARCHAR(255) NULL,
            buyer VARCHAR(255) NULL,
            header_status VARCHAR(80) NULL,
            header_creation_date DATETIME NULL,
            line_count INT UNSIGNED NOT NULL DEFAULT 0,
            snapshot_json LONGTEXT NULL,
            work_order_id VARCHAR(30) NULL,
            converted_by_id INT NULL,
            converted_at DATETIME NULL,
            first_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uq_erp_order_inbox_sale_order (sale_order_no),
            UNIQUE KEY uq_erp_order_inbox_work_order (work_order_id),
            KEY idx_erp_order_inbox_pending (work_order_id, header_creation_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Upgrade installations that received an earlier inbox-table draft.
    $columns = [];
    $legacyErpOrderRequired = false;
    foreach ($db->query('SHOW COLUMNS FROM erp_order_inbox')->fetchAll() as $column) {
        $columns[(string)$column['Field']] = true;
        if ((string)$column['Field'] === 'erp_order_no' && strtoupper((string)($column['Null'] ?? '')) === 'NO') {
            $legacyErpOrderRequired = true;
        }
    }
    $required = [
        'sale_order_no' => 'VARCHAR(80) NULL',
        'customer_po_no' => 'VARCHAR(255) NULL',
        'customer_name' => 'VARCHAR(255) NULL',
        'buyer' => 'VARCHAR(255) NULL',
        'header_status' => 'VARCHAR(80) NULL',
        'header_creation_date' => 'DATETIME NULL',
        'line_count' => 'INT UNSIGNED NOT NULL DEFAULT 0',
        'snapshot_json' => 'LONGTEXT NULL',
        'work_order_id' => 'VARCHAR(30) NULL',
        'converted_by_id' => 'INT NULL',
        'converted_at' => 'DATETIME NULL',
        'read_at' => 'DATETIME NULL',
        'read_by_id' => 'INT NULL',
        'first_seen_at' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'last_seen_at' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
    ];
    foreach ($required as $name => $definition) {
        if (!isset($columns[$name])) {
            $db->exec("ALTER TABLE erp_order_inbox ADD COLUMN `$name` $definition");
            $columns[$name] = true;
        }
    }

    if (isset($columns['erp_order_no'])) {
        $db->exec("UPDATE erp_order_inbox SET sale_order_no = erp_order_no WHERE (sale_order_no IS NULL OR sale_order_no = '') AND erp_order_no IS NOT NULL");
        // Earlier drafts made this legacy column required. Keep it only for
        // compatibility, but allow new code to write the canonical column.
        if ($legacyErpOrderRequired) {
            $db->exec("ALTER TABLE erp_order_inbox MODIFY COLUMN erp_order_no VARCHAR(80) NULL");
        }
    }
    if (isset($columns['internal_order_id'])) {
        $db->exec("UPDATE erp_order_inbox SET work_order_id = internal_order_id WHERE (work_order_id IS NULL OR work_order_id = '') AND internal_order_id IS NOT NULL");
    }

    $indexes = [];
    foreach ($db->query('SHOW INDEX FROM erp_order_inbox')->fetchAll() as $index) {
        $indexes[(string)$index['Key_name']] = true;
    }
    if (!isset($indexes['uq_erp_order_inbox_sale_order'])) {
        $db->exec('ALTER TABLE erp_order_inbox ADD UNIQUE KEY uq_erp_order_inbox_sale_order (sale_order_no)');
    }
    if (!isset($indexes['uq_erp_order_inbox_work_order'])) {
        $db->exec('ALTER TABLE erp_order_inbox ADD UNIQUE KEY uq_erp_order_inbox_work_order (work_order_id)');
    }

    $done = true;
}

function canManageErpOrderInbox(string $role): bool
{
    return in_array(strtolower(trim($role)), ['admin', 'commercial', 'commercial_dept'], true);
}

function erpInboxSqlDate(string $value): ?string
{
    $value = trim($value);
    if ($value === '') return null;
    $time = strtotime($value);
    return $time === false ? null : date('Y-m-d H:i:s', $time);
}

function syncErpOrderInbox(PDO $db, array $items, bool $notifyCommercial = false): array
{
    ensureErpOrderInboxTable($db);
    if (!$items) return ['orders' => 0, 'new_orders' => 0, 'notified' => 0];

    $groups = [];
    foreach ($items as $row) {
        if (!is_array($row)) continue;
        $orderNo = trim((string)($row['sale_order_no'] ?? ''));
        if ($orderNo === '') continue;
        $groups[$orderNo][] = $row;
    }

    $exists = $db->prepare('SELECT 1 FROM erp_order_inbox WHERE sale_order_no = ? LIMIT 1');
    $upsert = $db->prepare("
        INSERT INTO erp_order_inbox
            (sale_order_no, customer_po_no, customer_name, buyer, header_status, header_creation_date, line_count, snapshot_json)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            customer_po_no = VALUES(customer_po_no),
            customer_name = VALUES(customer_name),
            buyer = VALUES(buyer),
            header_status = VALUES(header_status),
            header_creation_date = COALESCE(VALUES(header_creation_date), header_creation_date),
            line_count = VALUES(line_count),
            snapshot_json = VALUES(snapshot_json),
            last_seen_at = CURRENT_TIMESTAMP
    ");

    $newOrders = 0;
    foreach ($groups as $orderNo => $rows) {
        $exists->execute([$orderNo]);
        $wasPresent = (bool)$exists->fetchColumn();
        $first = $rows[0];
        $upsert->execute([
            $orderNo,
            trim((string)($first['customer_po_no'] ?? '')),
            trim((string)($first['customer_name'] ?? '')),
            trim((string)($first['buyer'] ?? '')),
            trim((string)($first['header_status'] ?? '')),
            erpInboxSqlDate((string)($first['header_creation_date'] ?? $first['ordered_date'] ?? '')),
            count($rows),
            json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
        if (!$wasPresent) $newOrders++;
    }

    // Notifications are generated from today's unresolved inbox rows by notifications.php.
    return ['orders' => count($groups), 'new_orders' => $newOrders, 'notified' => 0];
}

function erpOrderInboxMappings(PDO $db, array $orderNumbers): array
{
    ensureErpOrderInboxTable($db);
    $orderNumbers = array_values(array_unique(array_filter(array_map('strval', $orderNumbers))));
    if (!$orderNumbers) return [];
    $marks = implode(',', array_fill(0, count($orderNumbers), '?'));
    $stmt = $db->prepare("SELECT sale_order_no, work_order_id FROM erp_order_inbox WHERE sale_order_no IN ($marks)");
    $stmt->execute($orderNumbers);
    $result = [];
    foreach ($stmt->fetchAll() as $row) {
        $result[(string)$row['sale_order_no']] = (string)($row['work_order_id'] ?? '');
    }
    return $result;
}

function erpInboxStatuses(PDO $db, array $orderNumbers): array
{
    $mappings = erpOrderInboxMappings($db, $orderNumbers);
    $result = [];
    foreach ($orderNumbers as $orderNo) {
        $workOrderId = (string)($mappings[(string)$orderNo] ?? '');
        $result[(string)$orderNo] = [
            'workOrderStatus' => $workOrderId === '' ? 'new' : 'created',
            'internalOrderId' => $workOrderId,
        ];
    }
    return $result;
}

// Map ERP order numbers → 'read' | 'unread'. A converted order counts as read.
function erpOrderInboxReadMap(PDO $db, array $orderNumbers): array
{
    ensureErpOrderInboxTable($db);
    $orderNumbers = array_values(array_unique(array_filter(array_map('strval', $orderNumbers))));
    if (!$orderNumbers) return [];
    $marks = implode(',', array_fill(0, count($orderNumbers), '?'));
    $stmt = $db->prepare("SELECT sale_order_no, read_at, work_order_id FROM erp_order_inbox WHERE sale_order_no IN ($marks)");
    $stmt->execute($orderNumbers);
    $result = [];
    foreach ($stmt->fetchAll() as $row) {
        $isRead = !empty($row['read_at']) || !empty($row['work_order_id']);
        $result[(string)$row['sale_order_no']] = $isRead ? 'read' : 'unread';
    }
    return $result;
}

// Mark an ERP order read (default) or unread. Converting an order also marks it read.
function markErpOrderRead(PDO $db, string $orderNo, ?int $userId, bool $read = true): void
{
    ensureErpOrderInboxTable($db);
    $orderNo = trim($orderNo);
    if ($orderNo === '') return;
    $db->prepare('INSERT IGNORE INTO erp_order_inbox (sale_order_no) VALUES (?)')->execute([$orderNo]);
    if ($read) {
        $db->prepare('UPDATE erp_order_inbox SET read_at = COALESCE(read_at, NOW()), read_by_id = COALESCE(read_by_id, ?) WHERE sale_order_no = ?')
           ->execute([$userId, $orderNo]);
    } else {
        $db->prepare('UPDATE erp_order_inbox SET read_at = NULL, read_by_id = NULL WHERE sale_order_no = ?')->execute([$orderNo]);
    }
}
