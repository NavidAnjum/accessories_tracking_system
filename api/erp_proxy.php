<?php
/**
 * api/erp_proxy.php
 * GET ?po=Po-30203  → fetches from ERP and returns normalised JSON
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$po = trim($_GET['po'] ?? '');
if (!$po) {
    http_response_code(400);
    echo json_encode(['error' => 'po parameter required']);
    exit;
}

define('ERP_BASE', 'https://ebs.talhagroup.com:8080/ords/xxapi/ebs/sale-orders');

$ctx = stream_context_create([
    'http' => ['method' => 'GET', 'timeout' => 15, 'header' => "Accept: application/json\r\n"],
    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
]);

$url = ERP_BASE . '?po=' . rawurlencode($po);
$raw = @file_get_contents($url, false, $ctx);

if ($raw === false) {
    http_response_code(502);
    echo json_encode(['error' => 'Could not reach ERP server. Check network connectivity.']);
    exit;
}

$data = json_decode($raw, true);

if (empty($data['items'])) {
    echo json_encode(['found' => false, 'po' => $po]);
    exit;
}

// Filter to only lines matching the requested PO (actual field: customer_po_no)
$items = array_values(array_filter($data['items'], function($line) use ($po) {
    return strcasecmp((string)($line['customer_po_no'] ?? ''), $po) === 0;
}));

if (empty($items)) {
    echo json_encode(['found' => false, 'po' => $po]);
    exit;
}

// Group lines by sale_order_no
$groups = [];
foreach ($items as $line) {
    $key = $line['sale_order_no'];
    if (!isset($groups[$key])) {
        $shipAddr = trim(($line['ship_to_address1'] ?? '') . ', ' . ($line['ship_to_city'] ?? ''), ', ');
        $billAddr = trim(($line['bill_to_address1'] ?? '') . ', ' . ($line['bill_to_city'] ?? ''), ', ');

        $groups[$key] = [
            'salesOrderNo'  => (string)($line['sale_order_no']       ?? ''),
            'customerPo'    => $line['customer_po_no']               ?? $po,
            'customerName'  => $line['customer_name']                ?? '',
            'operatingUnit' => $line['operating_unit']               ?? '',
            'orderType'     => $line['order_type']                   ?? '',
            'currency'      => $line['currency_code']                ?? 'BDT',
            'status'        => $line['header_status']                ?? '',
            'shipToAddress' => $shipAddr,
            'billToAddress' => $billAddr,
            'requestDate'   => isset($line['header_request_date'])
                                   ? substr($line['header_request_date'], 0, 10) : '',
            'shipDate'      => isset($line['schedule_ship_date'])
                                   ? substr($line['schedule_ship_date'], 0, 10) : '',
            'orderedDate'   => isset($line['ordered_date'])
                                   ? substr($line['ordered_date'], 0, 10) : '',
            'bookedDate'    => isset($line['booked_date'])
                                   ? substr($line['booked_date'], 0, 10) : '',
            'lines'         => [],
        ];
    }

    $itemName = $line['item_description'] ?? $line['ordered_item'] ?? '';

    $groups[$key]['lines'][] = [
        'lineId'    => $line['line_id']            ?? '',
        'lineNo'    => $line['line_number']         ?? '',
        'shipNo'    => $line['shipment_number']     ?? '',
        'item'      => $itemName,
        'itemCode'  => $line['item_code']           ?? '',
        'qty'       => (float)($line['ordered_qty']        ?? 0),
        'shipped'   => (float)($line['shipped_qty']        ?? 0),
        'price'     => (float)($line['unit_selling_price'] ?? 0),
        'uom'       => $line['order_uom']           ?? '',
        'value'     => (float)($line['line_order_value']   ?? 0),
        'lineStatus'=> $line['line_status']         ?? '',
        'arInvoice' => $line['ar_invoice_no']       ?? '',
        'delivery'  => $line['delivery_name']       ?? '',
    ];
}

echo json_encode([
    'found'  => true,
    'po'     => $po,
    'groups' => array_values($groups),
    'total'  => $data['count'] ?? count($items),
]);
