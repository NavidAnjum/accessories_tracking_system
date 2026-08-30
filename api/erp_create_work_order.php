<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_order_inbox.php';

requireLogin();

if (!canManageErpOrderInbox((string)(currentUser()['role'] ?? ''))) {
    http_response_code(403);
    echo json_encode(['error' => 'Commercial access required.']);
    exit;
}

function nextErpWorkOrderId(PDO $db): string
{
    $prefix = 'ORD-' . date('Y-m') . '-';
    $stmt = $db->prepare("SELECT order_id FROM orders WHERE order_id LIKE ? ORDER BY order_id DESC LIMIT 1 FOR UPDATE");
    $stmt->execute([$prefix . '%']);
    $last = (string)($stmt->fetchColumn() ?: '');
    $sequence = 1;
    if (preg_match('/(\d+)$/', $last, $match)) $sequence = (int)$match[1] + 1;
    return $prefix . str_pad((string)$sequence, 5, '0', STR_PAD_LEFT);
}

function erpWorkOrderAddress(array $row, string $prefix): string
{
    return implode(', ', array_values(array_filter([
        trim((string)($row[$prefix . '_address1'] ?? '')),
        trim((string)($row[$prefix . '_address2'] ?? '')),
        trim((string)($row[$prefix . '_city'] ?? '')),
        trim((string)($row[$prefix . '_country'] ?? '')),
    ])));
}

function fetchExactErpOrderRows(string $erpOrderNo): array
{
    if (!function_exists('curl_init')) return [];
    $rows = [];
    $offset = 0;
    for ($page = 0; $page < 20; $page++) {
        $url = 'https://ebs.talhagroup.com:8080/ords/xxapi/ebs/sale-orders?p_order='
            . rawurlencode($erpOrderNo) . '&offset=' . $offset . '&limit=1000';
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        if (!is_string($response) || $response === '') return [];
        $json = json_decode($response, true);
        if (!is_array($json)) return [];
        foreach (($json['items'] ?? []) as $row) {
            if (is_array($row) && (string)($row['sale_order_no'] ?? '') === $erpOrderNo) {
                $rows[] = $row;
            }
        }
        if (empty($json['hasMore'])) break;
        $offset += max(1, (int)($json['limit'] ?? 1000));
    }
    return $rows;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'POST required']);
        exit;
    }

    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $erpOrderNo = trim((string)($body['erp_order_no'] ?? ''));
    if ($erpOrderNo === '' || !preg_match('/^\d+$/', $erpOrderNo)) {
        http_response_code(400);
        echo json_encode(['error' => 'A valid ERP sales order number is required.']);
        exit;
    }

    $db = getDB();
    ensureErpOrderInboxTable($db);

    // Avoid an unnecessary ERP request when this order was already converted.
    $existingStmt = $db->prepare('SELECT work_order_id FROM erp_order_inbox WHERE sale_order_no = ? LIMIT 1');
    $existingStmt->execute([$erpOrderNo]);
    $existingOrderId = trim((string)($existingStmt->fetchColumn() ?: ''));
    if ($existingOrderId !== '') {
        echo json_encode(['ok' => true, 'duplicate' => true, 'order_id' => $existingOrderId]);
        exit;
    }

    // Older/manual imports stored the ERP number directly on orders.order_no
    // before the inbox mapping existed. Adopt that order instead of duplicating it.
    $legacyOrderStmt = $db->prepare('SELECT order_id FROM orders WHERE order_no = ? ORDER BY id ASC LIMIT 1');
    $legacyOrderStmt->execute([$erpOrderNo]);
    $legacyOrderId = trim((string)($legacyOrderStmt->fetchColumn() ?: ''));
    if ($legacyOrderId !== '') {
        $db->prepare('INSERT IGNORE INTO erp_order_inbox (sale_order_no) VALUES (?)')->execute([$erpOrderNo]);
        $db->prepare('UPDATE erp_order_inbox SET work_order_id = ?, converted_by_id = ?, converted_at = COALESCE(converted_at, NOW()) WHERE sale_order_no = ? AND work_order_id IS NULL')
            ->execute([$legacyOrderId, currentUser()['id'] ?? null, $erpOrderNo]);
        echo json_encode(['ok' => true, 'duplicate' => true, 'order_id' => $legacyOrderId]);
        exit;
    }

    $freshRows = fetchExactErpOrderRows($erpOrderNo);
    if ($freshRows) syncErpOrderInbox($db, $freshRows, false);
    $db->prepare('INSERT IGNORE INTO erp_order_inbox (sale_order_no) VALUES (?)')->execute([$erpOrderNo]);

    $db->beginTransaction();
    $lock = $db->prepare('SELECT * FROM erp_order_inbox WHERE sale_order_no = ? FOR UPDATE');
    $lock->execute([$erpOrderNo]);
    $inbox = $lock->fetch();

    if (!empty($inbox['work_order_id'])) {
        $db->commit();
        echo json_encode([
            'ok' => true,
            'duplicate' => true,
            'order_id' => $inbox['work_order_id'],
        ]);
        exit;
    }

    $rows = json_decode((string)($inbox['snapshot_json'] ?? ''), true);
    if (!is_array($rows) || !$rows) {
        throw new RuntimeException('ERP order details are not cached yet. Reload the live ERP report and try again.');
    }

    $rows = array_values(array_filter($rows, static function ($row) use ($erpOrderNo): bool {
        if (!is_array($row) || (string)($row['sale_order_no'] ?? '') !== $erpOrderNo) return false;
        $cancelled = strtoupper(trim((string)($row['line_status'] ?? ''))) === 'CANCELLED'
            || strtoupper(trim((string)($row['line_cancelled_flag'] ?? ''))) === 'Y';
        return !$cancelled && (float)($row['ordered_qty'] ?? 0) > 0;
    }));
    if (!$rows) throw new RuntimeException('This ERP order has no active item lines.');

    $first = $rows[0];
    $orderId = nextErpWorkOrderId($db);
    $customer = trim((string)($first['customer_name'] ?? ''));
    $po = trim((string)($first['customer_po_no'] ?? ''));
    $buyer = trim((string)($first['buyer'] ?? ''));
    $requestDate = substr((string)($first['header_request_date'] ?? $first['ordered_date'] ?? ''), 0, 10) ?: null;
    $deliveryDate = substr((string)($first['sch_ship_date'] ?? ''), 0, 10) ?: null;
    $status = ucfirst(strtolower(trim((string)($first['header_status'] ?? ''))));

    $insertOrder = $db->prepare("
        INSERT INTO orders
            (order_id, customer_name, intake_date, po_number, to_buyer, order_no, order_type, delivery_date, current_step, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'sales', ?)
    ");
    $insertOrder->execute([
        $orderId,
        $customer,
        $requestDate,
        $po,
        $buyer,
        $erpOrderNo,
        trim((string)($first['order_type'] ?? '')),
        $deliveryDate,
        'Created automatically from ERP sales order ' . $erpOrderNo,
    ]);

    $itemStmt = $db->prepare("
        INSERT INTO order_items (order_id, sl_no, product_line, item_name, qty, unit, unit_price, amount)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $piItems = [];
    $marketingRows = [];
    $grandQty = 0.0;
    $grandVal = 0.0;
    foreach ($rows as $index => $row) {
        $qty = (float)($row['ordered_qty'] ?? 0);
        $price = (float)($row['unit_selling_price'] ?? 0);
        $amount = (float)($row['line_order_value'] ?? ($qty * $price));
        $description = trim((string)($row['item_description'] ?? $row['ordered_item'] ?? ''));
        $uom = trim((string)($row['order_uom'] ?? $row['wdd_uom'] ?? ''));
        $itemStmt->execute([
            $orderId, $index + 1, trim((string)($row['product_line'] ?? '')),
            $description, $qty, $uom, $price, $amount,
        ]);
        $piItems[] = ['desc' => $description, 'ply' => $uom, 'qty' => $qty, 'price' => $price, 'total' => number_format($amount, 2, '.', '')];
        $marketingRows[] = ['seg2' => $description, 'unit' => $uom, 'qty' => $qty, 'unitPrc' => $price];
        $grandQty += $qty;
        $grandVal += $amount;
    }

    $salesData = [
        'erpImported' => true,
        'piType' => 'single',
        'piNum' => '',
        'customer' => $customer,
        'buyer' => $buyer,
        'piDate' => $requestDate ?: date('Y-m-d'),
        'buyerAddress' => erpWorkOrderAddress($first, 'ship_to'),
        'pos' => [[
            'piNum' => '', 'poNum' => $po, 'qty' => $grandQty, 'val' => $grandVal,
            'items' => $piItems, 'buyer' => $buyer, 'salesOrder' => $erpOrderNo,
            'status' => $status, 'reqDate' => $requestDate,
        ]],
        'grandQty' => $grandQty,
        'grandVal' => number_format($grandVal, 2, '.', ''),
    ];
    $marketingData = [
        'customer' => $customer,
        'intakeDate' => $requestDate,
        'pos' => [[
            'poNum' => $po, 'endBuyer' => $buyer, 'orderNo' => $erpOrderNo,
            'delivery' => $deliveryDate ?: $requestDate, 'rows' => $marketingRows,
        ]],
    ];
    $pageStmt = $db->prepare("
        INSERT INTO page_data (order_id, page_name, data) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = CURRENT_TIMESTAMP
    ");
    $pageStmt->execute([$orderId, 'sales', json_encode($salesData, JSON_UNESCAPED_UNICODE)]);
    $pageStmt->execute([$orderId, 'marketing-intake', json_encode($marketingData, JSON_UNESCAPED_UNICODE)]);

    $db->prepare('UPDATE erp_order_inbox SET work_order_id = ?, converted_by_id = ?, converted_at = NOW() WHERE sale_order_no = ?')
        ->execute([$orderId, currentUser()['id'] ?? null, $erpOrderNo]);
    $db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE order_id = ? AND step_name = 'erp-order'")
        ->execute([$erpOrderNo]);
    $db->commit();

    echo json_encode(['ok' => true, 'duplicate' => false, 'order_id' => $orderId]);
} catch (Throwable $e) {
    if (isset($db) && $db instanceof PDO && $db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
