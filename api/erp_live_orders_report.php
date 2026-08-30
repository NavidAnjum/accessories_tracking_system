<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_order_inbox.php';
require_once __DIR__ . '/../includes/erp_sale_orders_cache.php';
requireLogin();

// This endpoint is read-only. Release PHP's session lock before the slow ERP
// request so paginated report calls from the same browser can run concurrently.
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

const ERP_LIVE_ORDERS_BASE = 'https://ebs.talhagroup.com:8080/ords/xxapi/ebs/sale-orders';
const ERP_LIVE_ORDERS_LIMIT = 1000;

function erpLiveReportValidDate(string $value): bool
{
    return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $value);
}

function erpLiveReportErpDate(string $value): string
{
    $dt = DateTime::createFromFormat('Y-m-d', $value);
    if (!$dt) {
        throw new RuntimeException('Invalid date: ' . $value);
    }
    return $dt->format('d/m/Y');
}

function erpLiveReportFetchPage(string $fromDate, string $toDate, int $offset = 0, int $limit = ERP_LIVE_ORDERS_LIMIT): array
{
    $url = ERP_LIVE_ORDERS_BASE
        . '?p_from_date=' . rawurlencode($fromDate)
        . '&p_to_date=' . rawurlencode($toDate)
        . '&offset=' . max(0, $offset)
        . '&limit=' . max(1, $limit);

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
            return ['ok' => false, 'error' => $error ?: 'Unknown cURL error', 'url' => $url];
        }
        return ['ok' => true, 'body' => $body, 'url' => $url];
    }

    $ctx = stream_context_create([
        'http' => ['method' => 'GET', 'timeout' => 60, 'header' => "Accept: application/json\r\n"],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false) {
        $lastError = error_get_last();
        return ['ok' => false, 'error' => $lastError['message'] ?? 'file_get_contents failed', 'url' => $url];
    }
    return ['ok' => true, 'body' => $body, 'url' => $url];
}

function erpLiveReportDecode(array $response): array
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
        'limit' => (int) ($decoded['limit'] ?? ERP_LIVE_ORDERS_LIMIT),
        'offset' => (int) ($decoded['offset'] ?? 0),
    ];
}

function erpLiveReportFetchRangeItems(string $fromYmd, string $toYmd, bool $singlePage = false, int $startOffset = 0): array
{
    $erpFromDate = erpLiveReportErpDate($fromYmd);
    $erpToDate = erpLiveReportErpDate($toYmd);
    $offset = max(0, $startOffset);
    $all = [];
    $calls = 0;
    $urls = [];

    while (true) {
        $calls++;
        $response = erpLiveReportFetchPage($erpFromDate, $erpToDate, $offset, ERP_LIVE_ORDERS_LIMIT);
        $urls[] = (string) ($response['url'] ?? '');
        $page = erpLiveReportDecode($response);
        if (empty($page['ok'])) {
            return ['ok' => false, 'error' => $page['error'] ?? 'Could not load ERP data'];
        }

        foreach ($page['items'] as $item) {
            if (is_array($item)) {
                $all[] = $item;
            }
        }

        if ($singlePage) {
            return [
                'ok' => true,
                'items' => $all,
                'erpFromDate' => $erpFromDate,
                'erpToDate' => $erpToDate,
                'pageCount' => 1,
                'urls' => $urls,
                'hasMore' => !empty($page['hasMore']),
                'nextOffset' => $offset + max(1, (int) ($page['limit'] ?? ERP_LIVE_ORDERS_LIMIT)),
            ];
        }

        if (empty($page['hasMore'])) {
            break;
        }

        $nextOffset = $offset + max(1, (int) ($page['limit'] ?? ERP_LIVE_ORDERS_LIMIT));
        if ($nextOffset === $offset) {
            break;
        }
        $offset = $nextOffset;

        if ($calls > 200) {
            break;
        }
    }

    return [
        'ok' => true,
        'items' => $all,
        'erpFromDate' => $erpFromDate,
        'erpToDate' => $erpToDate,
        'pageCount' => $calls,
        'urls' => $urls,
    ];
}

function erpLiveReportRowDate(array $row): string
{
    foreach (['header_creation_date', 'line_creation_date', 'ordered_date', 'header_request_date', 'request_date'] as $field) {
        $value = trim((string) ($row[$field] ?? ''));
        if ($value !== '') {
            return substr($value, 0, 10);
        }
    }
    return '';
}

function erpLiveReportFilterRowsByDate(array $items, string $fromYmd, string $toYmd): array
{
    return array_values(array_filter($items, static function ($row) use ($fromYmd, $toYmd): bool {
        if (!is_array($row)) {
            return false;
        }
        $rowDate = erpLiveReportRowDate($row);
        return $rowDate !== '' && $rowDate >= $fromYmd && $rowDate <= $toYmd;
    }));
}

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$to = trim((string) ($_GET['to'] ?? $today));
$from = trim((string) ($_GET['from'] ?? $yesterday));
$paged = (string) ($_GET['paged'] ?? '') === '1';
$cached = (string) ($_GET['cached'] ?? '') === '1'; // read saved rows from the local cache (fast, no ERP call)
$requestedOffset = max(0, (int) ($_GET['offset'] ?? 0));

if (!erpLiveReportValidDate($from) || !erpLiveReportValidDate($to)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format. Use YYYY-MM-DD for from/to.']);
    exit;
}

if ($from > $to) {
    [$from, $to] = [$to, $from];
}

$rangeDays = (int) ((new DateTime($from))->diff(new DateTime($to))->days) + 1;
if ($rangeDays > 31) {
    http_response_code(400);
    echo json_encode(['error' => 'Please select a range of 31 days or less.']);
    exit;
}

try {
    $db = getDB();
    ensureErpSaleOrdersCacheTable($db);
    $savedNew = 0; // rows this load persisted that were NOT already in the local cache

    if ($cached) {
        // Fast path: read previously-saved rows from the local cache (no ERP call).
        $stmt = $db->prepare("
            SELECT raw_json FROM erp_sale_orders_cache
            WHERE header_creation_date IS NOT NULL
              AND LEFT(header_creation_date, 10) BETWEEN :from AND :to
        ");
        $stmt->execute([':from' => $from, ':to' => $to]);
        $rawItems = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $json) {
            $row = json_decode((string) $json, true);
            if (is_array($row)) $rawItems[] = $row;
        }
        $rawItems = erpLiveReportFilterRowsByDate($rawItems, $from, $to);
        $result = ['erpFromDate' => '', 'erpToDate' => '', 'pageCount' => 0, 'urls' => [], 'hasMore' => false, 'nextOffset' => 0];
    } else {
        $result = erpLiveReportFetchRangeItems($from, $to, $paged, $requestedOffset);
        if (empty($result['ok'])) {
            throw new RuntimeException($result['error'] ?? 'Could not load ERP data');
        }
        $rawItems = erpLiveReportFilterRowsByDate($result['items'] ?? [], $from, $to);
        // Save everything we fetched so the next load can serve it fast from the DB,
        // and count how many rows were genuinely NEW (not already cached).
        if ($rawItems) {
            $keys = array_values(array_unique(erpSaleOrdersItemKeys($rawItems)));
            $existingBefore = $keys ? erpSaleOrdersCountExistingKeys($db, $keys) : 0;
            erpSaleOrdersUpsertItems($db, $rawItems, 0, count($rawItems) ?: ERP_LIVE_ORDERS_LIMIT);
            $savedNew = max(0, count($keys) - $existingBefore);
        }
        syncErpOrderInbox($db, $rawItems, false);
    }

    $orders = [];
    $totalQty = 0.0;
    $totalValue = 0.0;
    $lineCount = 0;

    foreach ($rawItems as $row) {
        $orderNo = trim((string) ($row['sale_order_no'] ?? ''));
        if ($orderNo === '') {
            $orderNo = 'UNKNOWN';
        }

        if (!isset($orders[$orderNo])) {
            $orders[$orderNo] = [
                'saleOrderNo' => $orderNo,
                'customerPo' => (string) ($row['customer_po_no'] ?? ''),
                'customerName' => (string) ($row['customer_name'] ?? ''),
                'buyer' => (string) ($row['buyer'] ?? ''),
                'salesPerson' => (string) ($row['csm'] ?? ($row['salesperson'] ?? '')),
                'status' => (string) ($row['header_status'] ?? ''),
                'currency' => (string) ($row['currency_code'] ?? 'USD'),
                'createdDate' => substr((string) ($row['header_creation_date'] ?? ''), 0, 10),
                'orderedDate' => substr((string) ($row['ordered_date'] ?? ''), 0, 10),
                'bookedDate' => substr((string) ($row['booked_date'] ?? ''), 0, 10),
                'requestDate' => substr((string) ($row['header_request_date'] ?? ''), 0, 10),
                'shipDate' => substr((string) ($row['schedule_ship_date'] ?? ''), 0, 10),
                'lineCount' => 0,
                'totalQty' => 0.0,
                'totalValue' => 0.0,
                'items' => [],
            ];
        }

        $qty = (float) ($row['ordered_qty'] ?? 0);
        $price = (float) ($row['unit_selling_price'] ?? ($row['price'] ?? 0));
        $value = (float) ($row['line_order_value'] ?? ($row['amount'] ?? ($qty * $price)));
        $itemName = trim((string) ($row['item_description'] ?? ($row['ordered_item'] ?? '')));
        $itemCode = trim((string) ($row['item_code'] ?? ''));
        $uom = trim((string) ($row['order_uom'] ?? ($row['wdd_uom'] ?? '')));
        $lineStatus = trim((string) ($row['line_status'] ?? ''));
        $remarks = trim((string) ($row['remarks'] ?? ''));

        $itemKey = implode('|', [$itemName, $itemCode, $price, $uom, $lineStatus, $remarks]);
        if (!isset($orders[$orderNo]['items'][$itemKey])) {
            $orders[$orderNo]['items'][$itemKey] = [
                'itemName' => $itemName,
                'itemCode' => $itemCode,
                'uom' => $uom,
                'lineStatus' => $lineStatus,
                'remarks' => $remarks,
                'qty' => 0.0,
                'price' => $price,
                'value' => 0.0,
                'lines' => 0,
            ];
        }

        $orders[$orderNo]['items'][$itemKey]['qty'] += $qty;
        $orders[$orderNo]['items'][$itemKey]['value'] += $value;
        $orders[$orderNo]['items'][$itemKey]['lines'] += 1;

        $orders[$orderNo]['lineCount'] += 1;
        $orders[$orderNo]['totalQty'] += $qty;
        $orders[$orderNo]['totalValue'] += $value;

        $lineCount++;
        $totalQty += $qty;
        $totalValue += $value;
    }

    $orderRows = array_values(array_map(static function (array $order): array {
        $order['items'] = array_values($order['items']);
        usort($order['items'], static function (array $a, array $b): int {
            return strcmp(($a['itemName'] ?? '') . '|' . ($a['itemCode'] ?? ''), ($b['itemName'] ?? '') . '|' . ($b['itemCode'] ?? ''));
        });
        return $order;
    }, $orders));

    usort($orderRows, static function (array $a, array $b): int {
        $dateA = (string) ($a['createdDate'] ?: $a['orderedDate'] ?: $a['requestDate'] ?: '');
        $dateB = (string) ($b['createdDate'] ?: $b['orderedDate'] ?: $b['requestDate'] ?: '');
        if ($dateA === $dateB) {
            return strcmp((string) ($a['saleOrderNo'] ?? ''), (string) ($b['saleOrderNo'] ?? ''));
        }
        return strcmp($dateB, $dateA);
    });

    $orderNumbers = array_column($orderRows, 'saleOrderNo');
    $workOrderMap = erpOrderInboxMappings($db, $orderNumbers);
    $readMap = erpOrderInboxReadMap($db, $orderNumbers);
    foreach ($orderRows as &$orderRow) {
        $erpOrderNo = (string)($orderRow['saleOrderNo'] ?? '');
        $workOrderId = (string)($workOrderMap[$erpOrderNo] ?? '');
        $orderRow['workOrderId'] = $workOrderId;
        $orderRow['conversionStatus'] = $workOrderId !== '' ? 'created' : 'new';
        $orderRow['readStatus'] = $readMap[$erpOrderNo] ?? 'unread';
    }
    unset($orderRow);

    echo json_encode([
        'from' => $from,
        'to' => $to,
        'erpFromDate' => $result['erpFromDate'] ?? '',
        'erpToDate' => $result['erpToDate'] ?? '',
        'pageCount' => (int) ($result['pageCount'] ?? 0),
        'requestUrls' => $result['urls'] ?? [],
        'hasMore' => !empty($result['hasMore']),
        'nextOffset' => (int) ($result['nextOffset'] ?? 0),
        'orderCount' => count($orderRows),
        'lineCount' => $lineCount,
        'totalQty' => $totalQty,
        'totalValue' => $totalValue,
        'orders' => $orderRows,
        'savedNew' => $savedNew,
        'source' => $cached ? 'cache' : 'live_api',
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
