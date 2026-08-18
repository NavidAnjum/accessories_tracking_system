<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/erp_sale_orders_cache.php';
requireLogin();

$query = trim((string) ($_GET['q'] ?? ''));
if ($query === '') {
    http_response_code(400);
    echo json_encode(['error' => 'q parameter required']);
    exit;
}

const ERP_PO_REPORT_MAX_RESULTS = 120;

function normalizedSearch(string $value): string
{
    return strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $value));
}

function fieldValue(array $row, string $key): string
{
    return trim((string) ($row[$key] ?? ''));
}

function fieldDate(array $row, string $key): string
{
    $raw = trim((string) ($row[$key] ?? ''));
    return $raw !== '' ? substr($raw, 0, 10) : '';
}

function matchScore(array $row, string $query): int
{
    $po = fieldValue($row, 'customer_po_no');
    $remarks = fieldValue($row, 'remarks');
    $itemCode = fieldValue($row, 'ordered_item');
    $itemDesc = fieldValue($row, 'item_description');
    $orderNo = fieldValue($row, 'sale_order_no');

    $score = 0;
    $normQuery = normalizedSearch($query);
    $normPo = normalizedSearch($po);
    $normRemarks = normalizedSearch($remarks);
    $normItemCode = normalizedSearch($itemCode);
    $normItemDesc = normalizedSearch($itemDesc);
    $normOrderNo = normalizedSearch($orderNo);

    if ($normPo === $normQuery) $score += 500;
    if ($normOrderNo === $normQuery) $score += 450;
    if ($normItemCode === $normQuery) $score += 430;
    if ($normRemarks === $normQuery) $score += 410;
    if ($normItemDesc === $normQuery) $score += 390;

    if (strpos(strtolower($po), strtolower($query)) !== false) $score += 240;
    if (strpos($normPo, $normQuery) !== false) $score += 200;
    if (strpos($normRemarks, $normQuery) !== false) $score += 160;
    if (strpos($normItemCode, $normQuery) !== false) $score += 150;
    if (strpos($normItemDesc, $normQuery) !== false) $score += 140;
    if (strpos($normOrderNo, $normQuery) !== false) $score += 120;

    return $score;
}

try {
    $db = getDB();
    ensureErpSaleOrdersCacheTable($db);

    $rows = erpSaleOrdersSearchCached($db, $query, 600);
    $stats = erpSaleOrdersCacheStats($db);
    $groups = [];

    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }

        $poNo = fieldValue($row, 'customer_po_no');
        if ($poNo === '') {
            continue;
        }

        $groupKey = $poNo;
        $score = matchScore($row, $query);

        if (!isset($groups[$groupKey])) {
            $groups[$groupKey] = [
                'customerPo' => $poNo,
                'customerName' => fieldValue($row, 'customer_name'),
                'buyer' => fieldValue($row, 'buyer'),
                'operatingUnit' => fieldValue($row, 'operating_unit'),
                'orderType' => fieldValue($row, 'order_type'),
                'status' => fieldValue($row, 'header_status'),
                'orderedDate' => fieldDate($row, 'ordered_date'),
                'bookedDate' => fieldDate($row, 'booked_date'),
                'requestDate' => fieldDate($row, 'header_request_date'),
                'salesOrders' => [],
                'items' => [],
                'remarks' => [],
                'score' => $score,
                'lastSyncedAt' => fieldValue($row, 'synced_at'),
            ];
        } else {
            $groups[$groupKey]['score'] = max($groups[$groupKey]['score'], $score);
            if ($groups[$groupKey]['orderedDate'] === '') $groups[$groupKey]['orderedDate'] = fieldDate($row, 'ordered_date');
            if ($groups[$groupKey]['bookedDate'] === '') $groups[$groupKey]['bookedDate'] = fieldDate($row, 'booked_date');
            if ($groups[$groupKey]['requestDate'] === '') $groups[$groupKey]['requestDate'] = fieldDate($row, 'header_request_date');
            if ($groups[$groupKey]['buyer'] === '') $groups[$groupKey]['buyer'] = fieldValue($row, 'buyer');
            if ($groups[$groupKey]['customerName'] === '') $groups[$groupKey]['customerName'] = fieldValue($row, 'customer_name');
            if ($groups[$groupKey]['status'] === '') $groups[$groupKey]['status'] = fieldValue($row, 'header_status');
            if ($groups[$groupKey]['lastSyncedAt'] === '') $groups[$groupKey]['lastSyncedAt'] = fieldValue($row, 'synced_at');
        }

        $saleOrderNo = fieldValue($row, 'sale_order_no');
        if ($saleOrderNo !== '' && !in_array($saleOrderNo, $groups[$groupKey]['salesOrders'], true)) {
            $groups[$groupKey]['salesOrders'][] = $saleOrderNo;
        }

        $itemDesc = fieldValue($row, 'item_description');
        if ($itemDesc !== '' && count($groups[$groupKey]['items']) < 4 && !in_array($itemDesc, $groups[$groupKey]['items'], true)) {
            $groups[$groupKey]['items'][] = $itemDesc;
        }

        $remark = fieldValue($row, 'remarks');
        if ($remark !== '' && count($groups[$groupKey]['remarks']) < 3 && !in_array($remark, $groups[$groupKey]['remarks'], true)) {
            $groups[$groupKey]['remarks'][] = $remark;
        }
    }

    $results = array_values($groups);
    usort($results, static function (array $a, array $b) {
        if ($a['score'] === $b['score']) {
            return strcmp($a['customerPo'], $b['customerPo']);
        }
        return $b['score'] <=> $a['score'];
    });
    $results = array_slice($results, 0, ERP_PO_REPORT_MAX_RESULTS);

    echo json_encode([
        'query' => $query,
        'results' => $results,
        'count' => count($results),
        'source' => 'cache',
        'cache' => [
            'totalRows' => (int) ($stats['total_rows'] ?? 0),
            'totalPos' => (int) ($stats['total_pos'] ?? 0),
            'lastSyncedAt' => $stats['last_synced_at'] ?? null,
        ],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
