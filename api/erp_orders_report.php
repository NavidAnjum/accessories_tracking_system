<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_sale_orders_cache.php';

$date = trim((string) ($_GET['date'] ?? date('Y-m-d')));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format. Use YYYY-MM-DD.']);
    exit;
}

$dateField = trim((string) ($_GET['date_field'] ?? 'ordered_date'));
$allowedDateFields = [
    'ordered_date' => 'ordered_date',
    'booked_date' => 'booked_date',
    'header_request_date' => 'header_request_date',
    'schedule_ship_date' => 'schedule_ship_date',
];
$column = $allowedDateFields[$dateField] ?? 'ordered_date';

try {
    $db = getDB();
    ensureErpSaleOrdersCacheTable($db);

    $sql = "
        SELECT *
        FROM erp_sale_orders_cache
        WHERE LEFT(COALESCE($column, ''), 10) = :report_date
        ORDER BY customer_po_no ASC, sale_order_no ASC, line_id ASC, id ASC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([':report_date' => $date]);
    $rows = $stmt->fetchAll();

    $groups = [];
    $totalQty = 0.0;
    $totalValue = 0.0;

    foreach ($rows as $row) {
        $groupKey = trim((string) ($row['customer_po_no'] ?? ''));
        if ($groupKey === '') {
            $groupKey = 'NO-PO';
        }

        if (!isset($groups[$groupKey])) {
            $groups[$groupKey] = [
                'customerPo' => (string) ($row['customer_po_no'] ?? ''),
                'customerName' => (string) ($row['customer_name'] ?? ''),
                'buyer' => (string) ($row['buyer'] ?? ''),
                'orderType' => (string) ($row['order_type'] ?? ''),
                'operatingUnit' => (string) ($row['operating_unit'] ?? ''),
                'status' => (string) ($row['header_status'] ?? ''),
                'orderedDate' => (string) ($row['ordered_date'] ?? ''),
                'bookedDate' => (string) ($row['booked_date'] ?? ''),
                'requestDate' => (string) ($row['header_request_date'] ?? ''),
                'shipDate' => (string) ($row['schedule_ship_date'] ?? ''),
                'salesOrders' => [],
                'items' => [],
                'lineCount' => 0,
                'totalQty' => 0.0,
                'totalValue' => 0.0,
            ];
        }

        $saleOrderNo = trim((string) ($row['sale_order_no'] ?? ''));
        if ($saleOrderNo !== '' && !in_array($saleOrderNo, $groups[$groupKey]['salesOrders'], true)) {
            $groups[$groupKey]['salesOrders'][] = $saleOrderNo;
        }

        $qty = (float) ($row['ordered_qty'] ?? 0);
        $value = (float) ($row['line_order_value'] ?? 0);

        $groups[$groupKey]['items'][] = [
            'saleOrderNo' => $saleOrderNo,
            'itemCode' => (string) ($row['item_code'] ?? ''),
            'orderedItem' => (string) ($row['ordered_item'] ?? ''),
            'description' => (string) ($row['item_description'] ?? ''),
            'remarks' => (string) ($row['remarks'] ?? ''),
            'qty' => $qty,
            'shippedQty' => (float) ($row['shipped_qty'] ?? 0),
            'price' => (float) ($row['unit_selling_price'] ?? 0),
            'value' => $value,
            'delivery' => (string) ($row['delivery_name'] ?? ''),
            'lineStatus' => (string) ($row['line_status'] ?? ''),
        ];
        $groups[$groupKey]['lineCount']++;
        $groups[$groupKey]['totalQty'] += $qty;
        $groups[$groupKey]['totalValue'] += $value;

        $totalQty += $qty;
        $totalValue += $value;
    }

    echo json_encode([
        'date' => $date,
        'dateField' => $dateField,
        'groupCount' => count($groups),
        'lineCount' => count($rows),
        'totalQty' => $totalQty,
        'totalValue' => $totalValue,
        'results' => array_values($groups),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
