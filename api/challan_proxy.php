<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$pi = trim($_GET['pi'] ?? $_GET['P_PI_NUM'] ?? '');
if ($pi === '') {
    http_response_code(400);
    echo json_encode(['error' => 'pi parameter required']);
    exit;
}

define('ERP_CHALLAN_BASE', 'https://ebs.talhagroup.com:8080/ords/xxapi/ebs/sales-order-invoice');

function fetchChallanUrl(string $url): array
{
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $body = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if ($body !== false) {
            return ['ok' => true, 'body' => $body];
        }
        return ['ok' => false, 'error' => $error ?: 'Unknown cURL error'];
    }

    $ctx = stream_context_create([
        'http' => ['method' => 'GET', 'timeout' => 20, 'header' => "Accept: application/json\r\n"],
        'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body !== false) {
        return ['ok' => true, 'body' => $body];
    }

    $lastError = error_get_last();
    return ['ok' => false, 'error' => $lastError['message'] ?? 'file_get_contents failed'];
}

function pickField(array $row, array $keys, $default = '')
{
    foreach ($keys as $key) {
        if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
            return $row[$key];
        }
    }
    return $default;
}

function piCandidates(string $pi): array
{
    $variants = [];
    $base = trim($pi);
    if ($base === '') {
        return [];
    }
    $variants[] = $base;

    if (stripos($base, 'PI-') !== 0) {
        $variants[] = 'PI-' . $base;
    }

    if (stripos($base, 'PI/') !== 0 && stripos($base, 'PI-') !== 0) {
        $variants[] = 'PI/' . $base;
    }

    return array_values(array_unique($variants));
}

function decodeChallanResponse(string $piCandidate, array $erpResponse): array
{
    if (empty($erpResponse['ok'])) {
        return [
            'ok' => false,
            'error' => 'Could not reach ERP challan endpoint.',
            'detail' => $erpResponse['error'] ?? 'Unknown ERP connection error',
            'pi' => $piCandidate,
        ];
    }

    $decoded = json_decode($erpResponse['body'], true);
    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'error' => 'ERP returned invalid JSON.',
            'pi' => $piCandidate,
            'preview' => substr((string) $erpResponse['body'], 0, 500),
        ];
    }

    $items = $decoded['items'] ?? $decoded['rows'] ?? $decoded['data'] ?? $decoded;
    if (!is_array($items)) {
        $items = [];
    }

    return ['ok' => true, 'items' => $items];
}

$rows = [];
$customer = '';
$sheetDate = '';
$totalQty = 0;
$matchedPi = '';
$lastError = null;

foreach (piCandidates($pi) as $piCandidate) {
    $url = ERP_CHALLAN_BASE . '?P_PI_NUM=' . rawurlencode($piCandidate);
    $erpResponse = fetchChallanUrl($url);
    $decodedResult = decodeChallanResponse($piCandidate, $erpResponse);
    if (empty($decodedResult['ok'])) {
        $lastError = $decodedResult;
        continue;
    }

    $items = $decodedResult['items'];
    $rows = [];
    $customer = '';
    $sheetDate = '';
    $totalQty = 0;

    foreach ($items as $row) {
        if (!is_array($row)) {
            continue;
        }

        $piNo = (string) pickField($row, ['pi_no', 'pi_number', 'pi_num', 'p_pi_num', 'pi'], '');
        if ($piNo !== '' && strcasecmp($piNo, $piCandidate) !== 0 && strcasecmp($piNo, $pi) !== 0) {
            continue;
        }

        $qty = (float) pickField($row, ['shipped_qty', 'ordered_qty', 'qty', 'quantity', 'delivered_qty', 'delivery_qty'], 0);
        $deliveryDateRaw = (string) pickField($row, ['dispatch_date', 'sch_ship_date', 'invoice_date', 'delivery_date', 'challan_date', 'delivery_dt', 'date'], '');
        $deliveryDate = $deliveryDateRaw !== '' ? substr($deliveryDateRaw, 0, 11) : '';

        if ($customer === '') {
            $customer = (string) pickField($row, ['customer_name', 'customer', 'buyer_name', 'party_name', 'supplier_name'], '');
        }
        if ($sheetDate === '' && $deliveryDate !== '') {
            $sheetDate = $deliveryDate;
        }

        $rows[] = [
            'piNo' => $piNo !== '' ? $piNo : $piCandidate,
            'orderRef' => (string) pickField($row, ['order_number', 'sale_order_no', 'sales_order_no', 'order_ref', 'order_ref_no', 'order_no'], ''),
            'description' => (string) pickField($row, ['item_description', 'description', 'remarks', 'ordered_item', 'item_name', 'item_code'], ''),
            'deliveryDate' => $deliveryDate,
            'qty' => $qty,
            'challanNo' => (string) pickField($row, ['dc_number', 'challan_no', 'delivery_name', 'delivery_no', 'delivery_number', 'invoice_number', 'invoice_no', 'ar_invoice_no'], ''),
            'inspectionResult' => (string) pickField($row, ['shipping_status', 'inspection_result', 'inspection_status', 'status', 'line_status'], ''),
        ];
        $totalQty += $qty;
    }

    if ($rows) {
        $matchedPi = $piCandidate;
        break;
    }
}

if (!$rows) {
    if ($lastError && !empty($lastError['error'])) {
        http_response_code(502);
        echo json_encode($lastError);
        exit;
    }
    echo json_encode(['found' => false, 'pi' => $pi]);
    exit;
}

echo json_encode([
    'found' => true,
    'pi' => $matchedPi !== '' ? $matchedPi : $pi,
    'searchPi' => $pi,
    'customer' => $customer,
    'sheetDate' => $sheetDate,
    'totalQty' => $totalQty,
    'rows' => $rows,
]);
