<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
requireLogin();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_sale_orders_cache.php';

$po = trim((string) ($_GET['po'] ?? ''));
if ($po === '') {
    http_response_code(400);
    echo json_encode(['error' => 'po parameter required']);
    exit;
}

define('ERP_PROXY_BASE', 'https://ebs.talhagroup.com:8080/ords/xxapi/ebs/sale-orders');

function erpProxyFetchUrl(string $url): array
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
        $err = curl_error($ch);
        curl_close($ch);
        if ($body !== false) {
            return ['ok' => true, 'body' => $body];
        }
        return ['ok' => false, 'error' => $err ?: 'Unknown cURL error'];
    }

    $ctx = stream_context_create([
        'http' => ['method' => 'GET', 'timeout' => 20, 'header' => "Accept: application/json\r\n"],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body !== false) {
        return ['ok' => true, 'body' => $body];
    }

    $lastError = error_get_last();
    return ['ok' => false, 'error' => $lastError['message'] ?? 'file_get_contents failed'];
}

function erpProxyNormalizeText(string $value): string
{
    return strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $value));
}

function erpProxyRowCompletenessScore(array $line): int
{
    $fields = [
        'customer_po_no',
        'sale_order_no',
        'line_id',
        'line_number',
        'shipment_number',
        'item_description',
        'ordered_item',
        'item_code',
        'order_uom',
        'wdd_uom',
        'delivery_name',
        'ar_invoice_no',
        'invoice_number',
        'line_status',
    ];

    $score = 0;
    foreach ($fields as $field) {
        if (trim((string) ($line[$field] ?? '')) !== '') {
            $score++;
        }
    }

    if (is_numeric($line['ordered_qty'] ?? null)) {
        $score++;
    }
    if (is_numeric($line['shipped_qty'] ?? null)) {
        $score++;
    }
    if (is_numeric($line['unit_selling_price'] ?? ($line['price'] ?? null))) {
        $score++;
    }
    if (is_numeric($line['line_order_value'] ?? ($line['amount'] ?? null))) {
        $score++;
    }

    return $score;
}

function erpProxyPickBetterRow(array $current, array $candidate): array
{
    return erpProxyRowCompletenessScore($candidate) > erpProxyRowCompletenessScore($current)
        ? $candidate
        : $current;
}

function erpProxyFieldContains(string $fieldValue, string $query): bool
{
    $fieldValue = trim($fieldValue);
    $query = trim($query);
    if ($fieldValue === '' || $query === '') {
        return false;
    }

    if (stripos($fieldValue, $query) !== false) {
        return true;
    }

    return strpos(erpProxyNormalizeText($fieldValue), erpProxyNormalizeText($query)) !== false;
}

function erpProxyCollectMatchSummary(array $rows, string $query): array
{
    $matches = [
        'customer_po_no' => [],
        'remarks' => [],
        'item_description' => [],
        'item_code' => [],
        'ordered_item' => [],
    ];

    foreach ($rows as $row) {
        foreach ($matches as $field => $_) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value === '' || !erpProxyFieldContains($value, $query)) {
                continue;
            }
            $matches[$field][$value] = true;
        }
    }

    $result = [];
    foreach ($matches as $field => $values) {
        if (!$values) {
            continue;
        }
        $result[$field] = array_values(array_keys($values));
    }

    return $result;
}

function normalizeErpProxyRows(array $items, string $po, ?string $source = null): array
{
    if (!$items) {
        return ['found' => false, 'po' => $po, 'source' => $source];
    }

    $groups = [];
    foreach ($items as $line) {
        $key = (string) ($line['sale_order_no'] ?? '');
        if ($key === '') {
            $key = 'UNKNOWN';
        }

        if (!isset($groups[$key])) {
            $shipAddr = trim((string) (($line['ship_to_address1'] ?? '') . ', ' . ($line['ship_to_city'] ?? '')), ', ');
            $billAddr = trim((string) (($line['bill_to_address1'] ?? '') . ', ' . ($line['bill_to_city'] ?? '')), ', ');

            $groups[$key] = [
                'salesOrderNo'  => (string) ($line['sale_order_no'] ?? ''),
                'customerPo'    => (string) ($line['customer_po_no'] ?? $po),
                'customerName'  => (string) ($line['customer_name'] ?? ''),
                'operatingUnit' => (string) ($line['operating_unit'] ?? ''),
                'orderType'     => (string) ($line['order_type'] ?? ''),
                'currency'      => (string) ($line['currency_code'] ?? 'BDT'),
                'status'        => (string) ($line['header_status'] ?? ''),
                'shipToAddress' => $shipAddr,
                'billToAddress' => $billAddr,
                'requestDate'   => !empty($line['header_request_date']) ? substr((string) $line['header_request_date'], 0, 10) : '',
                'shipDate'      => !empty($line['schedule_ship_date']) ? substr((string) $line['schedule_ship_date'], 0, 10) : '',
                'orderedDate'   => !empty($line['ordered_date']) ? substr((string) $line['ordered_date'], 0, 10) : '',
                'bookedDate'    => !empty($line['booked_date']) ? substr((string) $line['booked_date'], 0, 10) : '',
                'lines'         => [],
            ];
        }

        $itemName = (string) ($line['item_description'] ?? ($line['ordered_item'] ?? ''));

        $groups[$key]['lines'][] = [
            'lineId'     => (string) ($line['line_id'] ?? ''),
            'lineNo'     => (string) ($line['line_number'] ?? ''),
            'shipNo'     => (string) ($line['shipment_number'] ?? ''),
            'item'       => $itemName,
            'itemCode'   => (string) ($line['item_code'] ?? ''),
            'qty'        => (float) ($line['ordered_qty'] ?? 0),
            'shipped'    => (float) ($line['shipped_qty'] ?? 0),
            'price'      => (float) ($line['unit_selling_price'] ?? ($line['price'] ?? 0)),
            'uom'        => (string) ($line['order_uom'] ?? ($line['wdd_uom'] ?? '')),
            'value'      => (float) ($line['line_order_value'] ?? ($line['amount'] ?? 0)),
            'lineStatus' => (string) ($line['line_status'] ?? ''),
            'arInvoice'  => (string) ($line['ar_invoice_no'] ?? ($line['invoice_number'] ?? '')),
            'delivery'   => (string) ($line['delivery_name'] ?? ''),
        ];
    }

    return [
        'found' => true,
        'po' => $po,
        'groups' => array_values($groups),
        'total' => count($items),
        'source' => $source,
    ];
}

function erpProxyGroupOptions(array $items, string $query, ?string $source = null): array
{
    $byPo = [];
    foreach ($items as $line) {
        $poNo = trim((string) ($line['customer_po_no'] ?? ''));
        if ($poNo === '') {
            continue;
        }
        $saleOrderNo = trim((string) ($line['sale_order_no'] ?? ''));
        $groupKey = $poNo . '||' . ($saleOrderNo !== '' ? $saleOrderNo : 'UNKNOWN');
        if (!isset($byPo[$groupKey])) {
            $byPo[$groupKey] = [
                'po' => $poNo,
                'rows' => [],
            ];
        }
        $byPo[$groupKey]['rows'][] = $line;
    }

    $options = [];
    foreach ($byPo as $groupEntry) {
        $poNo = (string) ($groupEntry['po'] ?? '');
        $rows = (array) ($groupEntry['rows'] ?? []);
        $normalized = normalizeErpProxyRows($rows, $poNo, $source);
        $firstGroup = $normalized['groups'][0] ?? [];
        $lineCount = 0;
        foreach (($normalized['groups'] ?? []) as $group) {
            $lineCount += count($group['lines'] ?? []);
        }
        $matchSummary = erpProxyCollectMatchSummary($rows, $query);

        $options[] = [
            'po' => $poNo,
            'label' => $poNo,
            'customerName' => (string) ($firstGroup['customerName'] ?? ''),
            'salesOrders' => array_values(array_filter(array_map(static function ($group) {
                return (string) ($group['salesOrderNo'] ?? '');
            }, $normalized['groups'] ?? []))),
            'groupCount' => count($normalized['groups'] ?? []),
            'lineCount' => $lineCount,
            'matchSummary' => $matchSummary,
            'data' => $normalized,
        ];
    }

    usort($options, static function (array $a, array $b) use ($query) {
        $normalizedQuery = erpProxyNormalizeText($query);
        $aNormalized = erpProxyNormalizeText($a['po']);
        $bNormalized = erpProxyNormalizeText($b['po']);

        $aExact = $aNormalized === $normalizedQuery;
        $bExact = $bNormalized === $normalizedQuery;
        if ($aExact !== $bExact) {
            return $aExact ? -1 : 1;
        }

        $aStarts = strpos($aNormalized, $normalizedQuery) === 0;
        $bStarts = strpos($bNormalized, $normalizedQuery) === 0;
        if ($aStarts !== $bStarts) {
            return $aStarts ? -1 : 1;
        }

        return strcmp($a['po'], $b['po']);
    });

    return $options;
}

function erpProxyMergeItemsByKey(array ...$lists): array
{
    $merged = [];
    foreach ($lists as $items) {
        foreach ($items as $line) {
            if (!is_array($line)) {
                continue;
            }

            $customerPo = (string) ($line['customer_po_no'] ?? '');
            $saleOrderNo = (string) ($line['sale_order_no'] ?? '');
            $lineId = trim((string) ($line['line_id'] ?? ''));
            $lineNo = trim((string) ($line['line_number'] ?? ''));
            $shipNo = trim((string) ($line['shipment_number'] ?? ''));
            $orderedItem = trim((string) ($line['ordered_item'] ?? ''));

            if ($saleOrderNo !== '' && $lineId !== '') {
                $key = 'sale-line-id|' . sha1($customerPo . '|' . $saleOrderNo . '|' . $lineId);
            } elseif ($saleOrderNo !== '' && $lineNo !== '' && $shipNo !== '') {
                $key = 'sale-line-ship|' . sha1($customerPo . '|' . $saleOrderNo . '|' . $lineNo . '|' . $shipNo);
            } else {
                $key = 'fallback|' . sha1(implode('|', [
                    $customerPo,
                    $saleOrderNo,
                    $lineId,
                    $lineNo,
                    $shipNo,
                    $orderedItem,
                    (string) ($line['item_description'] ?? ''),
                    (string) ($line['ordered_qty'] ?? ''),
                ]));
            }

            if (isset($merged[$key])) {
                $merged[$key] = erpProxyPickBetterRow($merged[$key], $line);
            } else {
                $merged[$key] = $line;
            }
        }
    }
    return array_values($merged);
}

function erpProxyPickBestOption(array $options, string $query): ?array
{
    if (!$options) {
        return null;
    }

    $normalizedQuery = erpProxyNormalizeText($query);
    $exact = array_values(array_filter($options, static function ($option) use ($normalizedQuery) {
        return erpProxyNormalizeText((string) ($option['po'] ?? '')) === $normalizedQuery;
    }));

    if (count($exact) === 1) {
        return $exact[0];
    }

    if (count($options) === 1) {
        return $options[0];
    }

    return null;
}

try {
    $db = getDB();
    ensureErpSaleOrdersCacheTable($db);
    $localItems = erpSaleOrdersSearchForPiLookup($db, $po, 500);
} catch (Throwable $e) {
    $localItems = [];
}

$url = ERP_PROXY_BASE . '?po=' . rawurlencode($po);
$erpResponse = erpProxyFetchUrl($url);
$liveItems = [];
$liveError = null;

if (!empty($erpResponse['ok'])) {
    $data = json_decode((string) ($erpResponse['body'] ?? ''), true);
    if (is_array($data)) {
        $liveItems = is_array($data['items'] ?? null) ? $data['items'] : [];
    } else {
        $liveError = 'ERP returned invalid JSON.';
    }
} else {
    $liveError = $erpResponse['error'] ?? 'Unknown ERP connection error';
}

$mergedItems = erpProxyMergeItemsByKey($localItems, $liveItems);

if (!$mergedItems) {
    if ($liveError) {
        http_response_code(502);
        echo json_encode([
            'error' => 'Could not reach ERP server. Check network connectivity.',
            'detail' => $liveError,
        ]);
        exit;
    }

    echo json_encode(['found' => false, 'po' => $po, 'source' => 'none']);
    exit;
}

$options = erpProxyGroupOptions($mergedItems, $po, 'merged');
$exactOptions = array_values(array_filter($options, static function (array $option) use ($po) {
    return strcasecmp((string) ($option['po'] ?? ''), $po) === 0;
}));

if (count($exactOptions) > 1) {
    $payload = [
        'found' => false,
        'po' => $po,
        'source' => 'merged',
        'multiple' => true,
        'options' => $exactOptions,
    ];
    if ($liveError) {
        $payload['warning'] = $liveError;
    }
    echo json_encode($payload);
    exit;
}

if (count($options) > 1) {
    $payload = [
        'found' => false,
        'po' => $po,
        'source' => 'merged',
        'multiple' => true,
        'options' => $options,
    ];
    if ($liveError) {
        $payload['warning'] = $liveError;
    }
    echo json_encode($payload);
    exit;
}

$best = $exactOptions[0] ?? ($options[0] ?? null);
if ($best && !empty($best['data'])) {
    $payload = $best['data'];
    if ($liveError) {
        $payload['warning'] = $liveError;
    }
    echo json_encode($payload);
    exit;
}

echo json_encode(['found' => false, 'po' => $po, 'source' => 'merged']);
