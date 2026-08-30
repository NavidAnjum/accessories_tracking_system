<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
requireLogin();

const ERP_ORDER_BASE = 'https://ebs.talhagroup.com:8080/ords/xxapi/ebs/sale-orders';
const ERP_ORDER_LIMIT = 1000;

$order = trim((string) ($_GET['order'] ?? ''));
if ($order === '' || !preg_match('/^\d+$/', $order)) {
    http_response_code(400);
    echo json_encode(['error' => 'A valid numeric sales order number is required.']);
    exit;
}

function fetchErpOrderPage(string $order, int $offset): array
{
    $url = ERP_ORDER_BASE
        . '?p_order=' . rawurlencode($order)
        . '&offset=' . max(0, $offset)
        . '&limit=' . ERP_ORDER_LIMIT;

    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $body = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($body === false) {
            throw new RuntimeException($error ?: 'Could not reach ERP server.');
        }
        return ['body' => $body, 'url' => $url];
    }

    $context = stream_context_create([
        'http' => ['method' => 'GET', 'timeout' => 60, 'header' => "Accept: application/json\r\n"],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    $body = @file_get_contents($url, false, $context);
    if ($body === false) {
        $lastError = error_get_last();
        throw new RuntimeException($lastError['message'] ?? 'Could not reach ERP server.');
    }
    return ['body' => $body, 'url' => $url];
}

try {
    $items = [];
    $offset = 0;
    $pageCount = 0;

    do {
        $pageCount++;
        $response = fetchErpOrderPage($order, $offset);
        $decoded = json_decode((string) $response['body'], true);
        if (!is_array($decoded)) {
            throw new RuntimeException('ERP returned invalid JSON.');
        }

        foreach (($decoded['items'] ?? []) as $row) {
            if (is_array($row) && (string) ($row['sale_order_no'] ?? '') === $order) {
                $items[] = $row;
            }
        }

        $hasMore = !empty($decoded['hasMore']);
        $offset += max(1, (int) ($decoded['limit'] ?? ERP_ORDER_LIMIT));
    } while ($hasMore && $pageCount < 200);

    if (!$items) {
        echo json_encode([
            'found' => false,
            'order' => $order,
            'source' => 'live_order_api',
        ]);
        exit;
    }

    $first = $items[0];
    $shipAddress = trim(implode(', ', array_filter([
        trim((string) ($first['ship_to_address1'] ?? '')),
        trim((string) ($first['ship_to_address2'] ?? '')),
        trim((string) ($first['ship_to_city'] ?? '')),
        trim((string) ($first['ship_to_country'] ?? '')),
    ])));
    $billAddress = trim(implode(', ', array_filter([
        trim((string) ($first['bill_to_address1'] ?? '')),
        trim((string) ($first['bill_to_address2'] ?? '')),
        trim((string) ($first['bill_to_city'] ?? '')),
        trim((string) ($first['bill_to_country'] ?? '')),
    ])));

    $lines = [];
    foreach ($items as $row) {
        $lines[] = [
            'lineId' => (string) ($row['line_id'] ?? ''),
            'lineNo' => (string) ($row['line_number'] ?? ''),
            'shipNo' => (string) ($row['shipment_number'] ?? ''),
            'item' => (string) ($row['item_description'] ?? ($row['ordered_item'] ?? '')),
            'itemCode' => (string) ($row['item_code'] ?? ''),
            'qty' => (float) ($row['ordered_qty'] ?? 0),
            'shipped' => (float) ($row['shipped_qty'] ?? 0),
            'price' => (float) ($row['unit_selling_price'] ?? 0),
            'uom' => (string) ($row['order_uom'] ?? ($row['wdd_uom'] ?? '')),
            'value' => (float) ($row['line_order_value'] ?? 0),
            'lineStatus' => (string) ($row['line_status'] ?? ''),
            'arInvoice' => (string) ($row['ar_invoice_no'] ?? ($row['invoice_number'] ?? '')),
            'delivery' => (string) ($row['delivery_name'] ?? ''),
        ];
    }

    $customerPo = (string) ($first['customer_po_no'] ?? '');
    $status = strtolower(trim((string) ($first['header_status'] ?? '')));
    $status = $status !== '' ? ucfirst($status) : '';
    echo json_encode([
        'found' => true,
        'order' => $order,
        'po' => $customerPo,
        'source' => 'live_order_api',
        'total' => count($items),
        'groups' => [[
            'salesOrderNo' => (string) ($first['sale_order_no'] ?? $order),
            'customerPo' => $customerPo,
            'customerName' => (string) ($first['customer_name'] ?? ''),
            'buyer' => (string) ($first['buyer'] ?? ''),
            'operatingUnit' => (string) ($first['operating_unit'] ?? ''),
            'orderType' => (string) ($first['order_type'] ?? ''),
            'currency' => (string) ($first['currency_code'] ?? 'USD'),
            'status' => $status,
            'shipToAddress' => $shipAddress,
            'billToAddress' => $billAddress,
            'requestDate' => substr((string) ($first['header_request_date'] ?? ''), 0, 10),
            'shipDate' => substr((string) ($first['sch_ship_date'] ?? ''), 0, 10),
            'orderedDate' => substr((string) ($first['ordered_date'] ?? ''), 0, 10),
            'bookedDate' => substr((string) ($first['booked_date'] ?? ''), 0, 10),
            'lines' => $lines,
        ]],
    ]);
} catch (Throwable $e) {
    http_response_code(502);
    echo json_encode([
        'error' => 'Could not load the sales order from ERP.',
        'detail' => $e->getMessage(),
    ]);
}
