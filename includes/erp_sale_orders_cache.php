<?php

const ERP_SALE_ORDERS_BASE = 'https://ebs.talhagroup.com:8080/ords/xxapi/ebs/sale-orders';
const ERP_SALE_ORDERS_LIMIT = 1000;

function ensureErpSaleOrdersCacheTable(PDO $db): void
{
    $db->exec("
        CREATE TABLE IF NOT EXISTS erp_sale_orders_cache (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            cache_key VARCHAR(64) NOT NULL,
            org_id BIGINT NULL,
            operating_unit VARCHAR(255) NULL,
            header_id BIGINT NULL,
            line_id BIGINT NULL,
            sale_order_no VARCHAR(64) NULL,
            customer_po_no VARCHAR(255) NULL,
            customer_name VARCHAR(255) NULL,
            buyer VARCHAR(255) NULL,
            order_type VARCHAR(255) NULL,
            header_status VARCHAR(100) NULL,
            line_status VARCHAR(100) NULL,
            ordered_item VARCHAR(255) NULL,
            item_code VARCHAR(255) NULL,
            item_description TEXT NULL,
            remarks TEXT NULL,
            ordered_date VARCHAR(40) NULL,
            booked_date VARCHAR(40) NULL,
            header_request_date VARCHAR(40) NULL,
            schedule_ship_date VARCHAR(40) NULL,
            header_creation_date VARCHAR(40) NULL,
            line_creation_date VARCHAR(40) NULL,
            ordered_qty DECIMAL(18,4) NULL,
            shipped_qty DECIMAL(18,4) NULL,
            unit_selling_price DECIMAL(18,6) NULL,
            line_order_value DECIMAL(18,4) NULL,
            delivery_name VARCHAR(255) NULL,
            search_blob TEXT NULL,
            search_blob_normalized TEXT NULL,
            source_offset INT NOT NULL DEFAULT 0,
            source_page_size INT NOT NULL DEFAULT 1000,
            raw_json LONGTEXT NULL,
            synced_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_erp_sale_orders_cache_key (cache_key),
            KEY idx_erp_sale_orders_customer_po (customer_po_no),
            KEY idx_erp_sale_orders_sale_order (sale_order_no),
            KEY idx_erp_sale_orders_header_id (header_id),
            KEY idx_erp_sale_orders_line_id (line_id),
            KEY idx_erp_sale_orders_synced_at (synced_at),
            KEY idx_erp_sale_orders_header_created (header_creation_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    // Add columns to tables that were created before these fields existed.
    // (MySQL 5.7 has no "ADD COLUMN IF NOT EXISTS", so check information_schema.)
    ensureErpSaleOrdersCacheColumns($db);
}

function ensureErpSaleOrdersCacheColumns(PDO $db): void
{
    $wanted = [
        'header_creation_date' => "ADD COLUMN header_creation_date VARCHAR(40) NULL AFTER schedule_ship_date",
        'line_creation_date'   => "ADD COLUMN line_creation_date VARCHAR(40) NULL AFTER header_creation_date",
    ];

    $stmt = $db->prepare("
        SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'erp_sale_orders_cache'
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
        $db->exec('ALTER TABLE erp_sale_orders_cache ' . implode(', ', $alters));
    }
}

function erpSaleOrdersFetchPage(int $offset = 0, int $limit = ERP_SALE_ORDERS_LIMIT): array
{
    $url = ERP_SALE_ORDERS_BASE . '?offset=' . max(0, $offset) . '&limit=' . max(1, $limit);

    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $body = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($body === false) {
            return ['ok' => false, 'error' => $error ?: 'Unknown cURL error', 'url' => $url];
        }
        return ['ok' => true, 'body' => $body, 'url' => $url];
    }

    $ctx = stream_context_create([
        'http' => ['method' => 'GET', 'timeout' => 30, 'header' => "Accept: application/json\r\n"],
        'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false) {
        $lastError = error_get_last();
        return ['ok' => false, 'error' => $lastError['message'] ?? 'file_get_contents failed', 'url' => $url];
    }

    return ['ok' => true, 'body' => $body, 'url' => $url];
}

function erpSaleOrdersFetchExactPo(string $po): array
{
    $url = ERP_SALE_ORDERS_BASE . '?po=' . rawurlencode($po);

    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $body = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($body === false) {
            return ['ok' => false, 'error' => $error ?: 'Unknown cURL error', 'url' => $url];
        }
        return ['ok' => true, 'body' => $body, 'url' => $url];
    }

    $ctx = stream_context_create([
        'http' => ['method' => 'GET', 'timeout' => 25, 'header' => "Accept: application/json\r\n"],
        'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false) {
        $lastError = error_get_last();
        return ['ok' => false, 'error' => $lastError['message'] ?? 'file_get_contents failed', 'url' => $url];
    }

    return ['ok' => true, 'body' => $body, 'url' => $url];
}

function erpSaleOrdersDecodeResponse(array $response): array
{
    if (empty($response['ok'])) {
        return ['ok' => false, 'error' => $response['error'] ?? 'Unknown ERP connection error'];
    }

    $decoded = json_decode((string) ($response['body'] ?? ''), true);
    if (!is_array($decoded)) {
        return ['ok' => false, 'error' => 'ERP returned invalid JSON'];
    }

    $items = $decoded['items'] ?? [];
    if (!is_array($items)) {
        $items = [];
    }

    return [
        'ok' => true,
        'items' => $items,
        'hasMore' => !empty($decoded['hasMore']),
        'count' => (int) ($decoded['count'] ?? count($items)),
        'limit' => (int) ($decoded['limit'] ?? ERP_SALE_ORDERS_LIMIT),
        'offset' => (int) ($decoded['offset'] ?? 0),
    ];
}

function erpSaleOrdersCacheKey(array $row): string
{
    $parts = [
        (string) ($row['org_id'] ?? ''),
        (string) ($row['header_id'] ?? ''),
        (string) ($row['line_id'] ?? ''),
        (string) ($row['line_number'] ?? ''),
        (string) ($row['shipment_number'] ?? ''),
        (string) ($row['sale_order_no'] ?? ''),
        (string) ($row['customer_po_no'] ?? ''),
        (string) ($row['ordered_item'] ?? ''),
    ];
    return sha1(implode('|', $parts));
}

function erpSaleOrdersNormalize(string $value): string
{
    return strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $value));
}

function erpSaleOrdersShouldUseLooseTokens(string $query): bool
{
    $query = trim($query);
    if ($query === '') {
        return false;
    }

    $hasLetters = (bool) preg_match('/[a-z]/i', $query);
    $hasDigits = (bool) preg_match('/\d/', $query);
    $hasSeparators = (bool) preg_match('/[^a-z0-9]/i', $query);

    if ($hasLetters && $hasDigits && $hasSeparators) {
        return false;
    }

    return true;
}

function erpSaleOrdersSearchBlob(array $row): string
{
    $fields = [
        $row['customer_po_no'] ?? '',
        $row['sale_order_no'] ?? '',
        $row['customer_name'] ?? '',
        $row['buyer'] ?? '',
        $row['item_description'] ?? '',
        $row['ordered_item'] ?? '',
        $row['item_code'] ?? '',
        $row['remarks'] ?? '',
        $row['order_type'] ?? '',
        $row['operating_unit'] ?? '',
    ];
    return trim(implode(' | ', array_filter(array_map('strval', $fields), static function ($v) {
        return trim($v) !== '';
    })));
}

function erpSaleOrdersUpsertItems(PDO $db, array $items, int $offset = 0, int $pageSize = ERP_SALE_ORDERS_LIMIT): array
{
    ensureErpSaleOrdersCacheTable($db);
    if (!$items) {
        return ['saved' => 0];
    }

    $sql = "
        INSERT INTO erp_sale_orders_cache (
            cache_key, org_id, operating_unit, header_id, line_id, sale_order_no,
            customer_po_no, customer_name, buyer, order_type, header_status, line_status,
            ordered_item, item_code, item_description, remarks, ordered_date, booked_date,
            header_request_date, schedule_ship_date, header_creation_date, line_creation_date,
            ordered_qty, shipped_qty,
            unit_selling_price, line_order_value, delivery_name, search_blob,
            search_blob_normalized, source_offset, source_page_size, raw_json, synced_at
        ) VALUES (
            :cache_key, :org_id, :operating_unit, :header_id, :line_id, :sale_order_no,
            :customer_po_no, :customer_name, :buyer, :order_type, :header_status, :line_status,
            :ordered_item, :item_code, :item_description, :remarks, :ordered_date, :booked_date,
            :header_request_date, :schedule_ship_date, :header_creation_date, :line_creation_date,
            :ordered_qty, :shipped_qty,
            :unit_selling_price, :line_order_value, :delivery_name, :search_blob,
            :search_blob_normalized, :source_offset, :source_page_size, :raw_json, NOW()
        )
        ON DUPLICATE KEY UPDATE
            org_id = VALUES(org_id),
            operating_unit = VALUES(operating_unit),
            header_id = VALUES(header_id),
            line_id = VALUES(line_id),
            sale_order_no = VALUES(sale_order_no),
            customer_po_no = VALUES(customer_po_no),
            customer_name = VALUES(customer_name),
            buyer = VALUES(buyer),
            order_type = VALUES(order_type),
            header_status = VALUES(header_status),
            line_status = VALUES(line_status),
            ordered_item = VALUES(ordered_item),
            item_code = VALUES(item_code),
            item_description = VALUES(item_description),
            remarks = VALUES(remarks),
            ordered_date = VALUES(ordered_date),
            booked_date = VALUES(booked_date),
            header_request_date = VALUES(header_request_date),
            schedule_ship_date = VALUES(schedule_ship_date),
            header_creation_date = VALUES(header_creation_date),
            line_creation_date = VALUES(line_creation_date),
            ordered_qty = VALUES(ordered_qty),
            shipped_qty = VALUES(shipped_qty),
            unit_selling_price = VALUES(unit_selling_price),
            line_order_value = VALUES(line_order_value),
            delivery_name = VALUES(delivery_name),
            search_blob = VALUES(search_blob),
            search_blob_normalized = VALUES(search_blob_normalized),
            source_offset = VALUES(source_offset),
            source_page_size = VALUES(source_page_size),
            raw_json = VALUES(raw_json),
            synced_at = NOW()
    ";

    $stmt = $db->prepare($sql);
    $saved = 0;
    foreach ($items as $row) {
        if (!is_array($row)) {
            continue;
        }
        $searchBlob = erpSaleOrdersSearchBlob($row);
        $stmt->execute([
            ':cache_key' => erpSaleOrdersCacheKey($row),
            ':org_id' => $row['org_id'] ?? null,
            ':operating_unit' => $row['operating_unit'] ?? null,
            ':header_id' => $row['header_id'] ?? null,
            ':line_id' => $row['line_id'] ?? null,
            ':sale_order_no' => (string) ($row['sale_order_no'] ?? ''),
            ':customer_po_no' => (string) ($row['customer_po_no'] ?? ''),
            ':customer_name' => (string) ($row['customer_name'] ?? ''),
            ':buyer' => (string) ($row['buyer'] ?? ''),
            ':order_type' => (string) ($row['order_type'] ?? ''),
            ':header_status' => (string) ($row['header_status'] ?? ''),
            ':line_status' => (string) ($row['line_status'] ?? ''),
            ':ordered_item' => (string) ($row['ordered_item'] ?? ''),
            ':item_code' => (string) ($row['item_code'] ?? ''),
            ':item_description' => (string) ($row['item_description'] ?? ''),
            ':remarks' => (string) ($row['remarks'] ?? ''),
            ':ordered_date' => (string) ($row['ordered_date'] ?? ''),
            ':booked_date' => (string) ($row['booked_date'] ?? ''),
            ':header_request_date' => (string) ($row['header_request_date'] ?? ''),
            ':schedule_ship_date' => (string) ($row['schedule_ship_date'] ?? ''),
            ':header_creation_date' => (string) ($row['header_creation_date'] ?? ''),
            ':line_creation_date' => (string) ($row['line_creation_date'] ?? ''),
            ':ordered_qty' => is_numeric($row['ordered_qty'] ?? null) ? (float) $row['ordered_qty'] : null,
            ':shipped_qty' => is_numeric($row['shipped_qty'] ?? null) ? (float) $row['shipped_qty'] : null,
            ':unit_selling_price' => is_numeric($row['unit_selling_price'] ?? ($row['price'] ?? null)) ? (float) ($row['unit_selling_price'] ?? $row['price']) : null,
            ':line_order_value' => is_numeric($row['line_order_value'] ?? ($row['amount'] ?? null)) ? (float) ($row['line_order_value'] ?? $row['amount']) : null,
            ':delivery_name' => (string) ($row['delivery_name'] ?? ''),
            ':search_blob' => $searchBlob,
            ':search_blob_normalized' => erpSaleOrdersNormalize($searchBlob),
            ':source_offset' => $offset,
            ':source_page_size' => $pageSize,
            ':raw_json' => json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
        $saved++;
    }

    return ['saved' => $saved];
}

function erpSaleOrdersFindExactPo(PDO $db, string $po): array
{
    ensureErpSaleOrdersCacheTable($db);
    $stmt = $db->prepare("
        SELECT * FROM erp_sale_orders_cache
        WHERE customer_po_no = :po
        ORDER BY sale_order_no ASC, line_id ASC, id ASC
    ");
    $stmt->execute([':po' => $po]);
    return $stmt->fetchAll();
}

function erpSaleOrdersSearchCached(PDO $db, string $query, int $limit = 200): array
{
    ensureErpSaleOrdersCacheTable($db);
    $plain = '%' . $query . '%';
    $normalizedQuery = erpSaleOrdersNormalize($query);
    $normalized = '%' . $normalizedQuery . '%';

    $tokens = [];
    if (erpSaleOrdersShouldUseLooseTokens($query)) {
        $tokens = preg_split('/[^a-z0-9]+/i', (string) $query) ?: [];
        $tokens = array_values(array_filter(array_map('trim', $tokens), static function ($token) {
            return $token !== '';
        }));
    }

    $plainFields = [
        'customer_po_no',
        'sale_order_no',
        'customer_name',
        'buyer',
        'item_description',
        'ordered_item',
        'item_code',
        'remarks',
        'raw_json',
    ];

    $where = [];
    $params = [];

    foreach ($plainFields as $index => $field) {
        $param = ':plain_' . $index;
        $where[] = $field . ' LIKE ' . $param;
        $params[$param] = $plain;
    }

    $where[] = 'search_blob_normalized LIKE :normalized_main';
    $params[':normalized_main'] = $normalized;

    foreach ($tokens as $index => $token) {
        $normalizedToken = erpSaleOrdersNormalize($token);
        if ($normalizedToken === '') {
            continue;
        }
        $param = ':token_' . $index;
        $where[] = 'search_blob_normalized LIKE ' . $param;
        $params[$param] = '%' . $normalizedToken . '%';
    }

    $sql = "
        SELECT * FROM erp_sale_orders_cache
        WHERE " . implode("\n           OR ", $where) . "
        ORDER BY synced_at DESC, sale_order_no ASC, line_id ASC
        LIMIT " . max(1, (int) $limit);

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function erpSaleOrdersSearchForPiLookup(PDO $db, string $query, int $limit = 500): array
{
    ensureErpSaleOrdersCacheTable($db);

    $plain = '%' . $query . '%';
    $normalized = '%' . erpSaleOrdersNormalize($query) . '%';
    $tokens = [];
    if (erpSaleOrdersShouldUseLooseTokens($query)) {
        $tokens = preg_split('/[^a-z0-9]+/i', (string) $query) ?: [];
        $tokens = array_values(array_filter(array_map('trim', $tokens), static function ($token) {
            return $token !== '' && strlen($token) >= 3;
        }));
    }

    $scoreParts = [
        'CASE WHEN customer_po_no LIKE :score_plain_1 THEN 120 ELSE 0 END',
        'CASE WHEN remarks LIKE :score_plain_2 THEN 110 ELSE 0 END',
        'CASE WHEN item_description LIKE :score_plain_3 THEN 100 ELSE 0 END',
        'CASE WHEN item_code LIKE :score_plain_4 THEN 95 ELSE 0 END',
        'CASE WHEN ordered_item LIKE :score_plain_5 THEN 90 ELSE 0 END',
        'CASE WHEN raw_json LIKE :score_plain_6 THEN 70 ELSE 0 END',
        'CASE WHEN search_blob_normalized LIKE :score_normalized THEN 60 ELSE 0 END',
    ];

    $whereParts = [
        'customer_po_no LIKE :where_plain_1',
        'remarks LIKE :where_plain_2',
        'item_description LIKE :where_plain_3',
        'item_code LIKE :where_plain_4',
        'ordered_item LIKE :where_plain_5',
        'raw_json LIKE :where_plain_6',
        'search_blob_normalized LIKE :where_normalized',
    ];

    $params = [
        ':score_plain_1' => $plain,
        ':score_plain_2' => $plain,
        ':score_plain_3' => $plain,
        ':score_plain_4' => $plain,
        ':score_plain_5' => $plain,
        ':score_plain_6' => $plain,
        ':score_normalized' => $normalized,
        ':where_plain_1' => $plain,
        ':where_plain_2' => $plain,
        ':where_plain_3' => $plain,
        ':where_plain_4' => $plain,
        ':where_plain_5' => $plain,
        ':where_plain_6' => $plain,
        ':where_normalized' => $normalized,
    ];

    foreach ($tokens as $index => $token) {
        $plainToken = '%' . $token . '%';
        $normalizedToken = '%' . erpSaleOrdersNormalize($token) . '%';
        $scorePlainParams = [];
        $wherePlainParams = [];
        $fields = ['customer_po_no', 'remarks', 'item_description', 'item_code', 'ordered_item', 'raw_json'];

        foreach ($fields as $fieldIndex => $fieldName) {
            $scorePlainParam = ':score_token_' . $index . '_' . $fieldIndex;
            $wherePlainParam = ':where_token_' . $index . '_' . $fieldIndex;
            $scoreParts[] = 'CASE WHEN ' . $fieldName . ' LIKE ' . $scorePlainParam . ' THEN ' . max(25, 55 - ($fieldIndex * 5)) . ' ELSE 0 END';
            $whereParts[] = $fieldName . ' LIKE ' . $wherePlainParam;
            $params[$scorePlainParam] = $plainToken;
            $params[$wherePlainParam] = $plainToken;
        }

        $scoreNormalizedParam = ':score_token_normalized_' . $index;
        $whereNormalizedParam = ':where_token_normalized_' . $index;
        $scoreParts[] = 'CASE WHEN search_blob_normalized LIKE ' . $scoreNormalizedParam . ' THEN 20 ELSE 0 END';
        $whereParts[] = 'search_blob_normalized LIKE ' . $whereNormalizedParam;
        $params[$scoreNormalizedParam] = $normalizedToken;
        $params[$whereNormalizedParam] = $normalizedToken;
    }

    $sql = "
        SELECT *,
               (" . implode(" +\n                    ", $scoreParts) . ") AS match_score
        FROM erp_sale_orders_cache
        WHERE " . implode("\n           OR ", $whereParts) . "
        ORDER BY match_score DESC, synced_at DESC, sale_order_no ASC, line_id ASC
        LIMIT " . max(1, (int) $limit);

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

function erpSaleOrdersCacheStats(PDO $db): array
{
    ensureErpSaleOrdersCacheTable($db);
    $stmt = $db->query("
        SELECT COUNT(*) AS total_rows,
               COUNT(DISTINCT customer_po_no) AS total_pos,
               MAX(synced_at) AS last_synced_at
        FROM erp_sale_orders_cache
    ");
    return $stmt->fetch() ?: ['total_rows' => 0, 'total_pos' => 0, 'last_synced_at' => null];
}
