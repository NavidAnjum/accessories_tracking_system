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

function erpReportValidDate(string $value): bool
{
    return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $value);
}

// Date range on the ERP record creation date. Default = last 10 days (inclusive).
$to   = trim((string) ($_GET['to']   ?? date('Y-m-d')));
$from = trim((string) ($_GET['from'] ?? date('Y-m-d', strtotime('-9 days'))));

if (!erpReportValidDate($from) || !erpReportValidDate($to)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format. Use YYYY-MM-DD for from/to.']);
    exit;
}
if ($from > $to) {
    [$from, $to] = [$to, $from]; // swap if reversed
}

try {
    $db = getDB();
    ensureErpSaleOrdersCacheTable($db);

    // Filter on the date part of the ERP creation timestamp.
    $sql = "
        SELECT sale_order_no, customer_po_no, customer_name, buyer,
               item_code, ordered_item, item_description,
               ordered_qty, shipped_qty, unit_selling_price, line_order_value,
               line_status, header_creation_date
        FROM erp_sale_orders_cache
        WHERE header_creation_date IS NOT NULL
          AND LEFT(header_creation_date, 10) BETWEEN :from AND :to
        ORDER BY header_creation_date DESC, customer_po_no ASC, sale_order_no ASC, line_id ASC, id ASC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([':from' => $from, ':to' => $to]);
    $dbRows = $stmt->fetchAll();

    // Aggregate ERP line/shipment rows so the same order + item shows once,
    // with quantities and values summed (Total Qty). Rows stay grouped by order.
    $agg = [];
    $totalQty = 0.0;
    $totalValue = 0.0;

    foreach ($dbRows as $row) {
        $qty   = (float) ($row['ordered_qty'] ?? 0);
        $price = (float) ($row['unit_selling_price'] ?? 0);
        $value = (float) ($row['line_order_value'] ?? ($qty * $price));

        $saleOrderNo = (string) ($row['sale_order_no'] ?? '');
        $customerPo  = (string) ($row['customer_po_no'] ?? '');
        $itemName    = (string) ($row['item_description'] ?? ($row['ordered_item'] ?? ''));
        $key = $saleOrderNo . '|' . $customerPo . '|' . $itemName;

        if (!isset($agg[$key])) {
            $agg[$key] = [
                'createdDate' => substr((string) ($row['header_creation_date'] ?? ''), 0, 10),
                'saleOrderNo' => $saleOrderNo,
                'customerPo'  => $customerPo,
                'customerName'=> (string) ($row['customer_name'] ?? ''),
                'buyer'       => (string) ($row['buyer'] ?? ''),
                'itemName'    => $itemName,
                'itemCode'    => (string) ($row['item_code'] ?? ''),
                'qty'         => 0.0,
                'value'       => 0.0,
                'price'       => $price,
                'lines'       => 0,
            ];
        }
        $agg[$key]['qty']   += $qty;
        $agg[$key]['value'] += $value;
        $agg[$key]['lines'] += 1;

        $totalQty   += $qty;
        $totalValue += $value;
    }

    // Effective unit price = summed value / summed qty (falls back to line price).
    $rows = array_values($agg);
    foreach ($rows as &$r) {
        $r['price'] = $r['qty'] > 0 ? round($r['value'] / $r['qty'], 4) : $r['price'];
    }
    unset($r);

    echo json_encode([
        'from'       => $from,
        'to'         => $to,
        'lineCount'  => count($rows),
        'totalQty'   => $totalQty,
        'totalValue' => $totalValue,
        'rows'       => $rows,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
