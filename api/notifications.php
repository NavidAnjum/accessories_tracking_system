<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/notifications.php';
require_once __DIR__ . '/../includes/erp_order_inbox.php';

requireLogin();

$db = getDB();
ensureNotificationsTable($db);
ensureErpOrderInboxTable($db);
backfillCurrentStepNotifications($db);
$userId = (int)(currentUser()['id'] ?? 0);
$userRole = (string)(currentUser()['role'] ?? '');
$method = $_SERVER['REQUEST_METHOD'];

function erpNotificationFloorDate(): string
{
    if (defined('ERP_ORDER_NOTIFY_FLOOR_DATE') && ERP_ORDER_NOTIFY_FLOOR_DATE !== '') {
        return ERP_ORDER_NOTIFY_FLOOR_DATE;
    }
    return (new DateTime('now', new DateTimeZone('Asia/Dhaka')))->format('Y-m-d');
}

function erpNotificationDateExpr(string $tableAlias = ''): string
{
    $prefix = $tableAlias !== '' ? $tableAlias . '.' : '';
    return "LEFT(COALESCE(NULLIF({$prefix}header_creation_date, ''), {$prefix}first_seen_at), 10)";
}

function summarizeErpNotificationItems(?string $snapshotJson): array
{
    $summary = [
        'item_name' => '',
        'total_qty' => 0,
        'price' => '',
        'total_value' => 0,
    ];
    $rows = json_decode((string)$snapshotJson, true);
    if (!is_array($rows)) return $summary;

    $priceSeen = null;
    $mixedPrice = false;
    foreach ($rows as $row) {
        if (!is_array($row)) continue;
        $status = strtoupper((string)($row['line_status'] ?? $row['header_status'] ?? ''));
        $cancelled = strtoupper((string)($row['line_cancelled_flag'] ?? $row['header_cancelled_flag'] ?? ''));
        if ($cancelled === 'Y' || strpos($status, 'CANCEL') !== false) continue;

        if ($summary['item_name'] === '') {
            $summary['item_name'] = trim((string)($row['item_description'] ?? ''));
        }
        $qty = (float)($row['ordered_qty'] ?? $row['wdd_requested_qty'] ?? $row['shipped_qty'] ?? 0);
        $price = (float)($row['unit_selling_price'] ?? $row['price'] ?? 0);
        $value = (float)($row['line_order_value'] ?? $row['amount'] ?? ($qty * $price));
        $summary['total_qty'] += $qty;
        $summary['total_value'] += $value;

        $priceKey = number_format($price, 6, '.', '');
        if ($priceSeen === null) {
            $priceSeen = $priceKey;
        } elseif ($priceSeen !== $priceKey) {
            $mixedPrice = true;
        }
    }

    $summary['price'] = $mixedPrice ? 'Mixed' : ($priceSeen !== null ? rtrim(rtrim($priceSeen, '0'), '.') : '');
    return $summary;
}

function canSeeCommercialPiWorklist(string $role): bool
{
    return in_array($role, ['admin', 'commercial', 'commercial_dept'], true);
}

function normalizeNotificationCustomer(string $name): string
{
    return strtolower(trim(preg_replace('/\s+/', ' ', (string)$name)));
}

function isBlockedNotificationCustomer(string $name): bool
{
    static $blocked = null;
    if ($blocked === null) {
        $blocked = array_map(
            static fn($value) => normalizeNotificationCustomer($value),
            [
                'Noman Terry Towel Mills Limited',
                'R S TRADERS',
                'Zaber & Zubair Fabrics Ltd. - Home',
                'Zakaria Enterprise',
                'Nice Denim Mills Ltd. ( Solid Dyeing Fabrics )',
                'Sufia Cotton Mills Limited-(Spinning) (Saad Group)',
                'Noman Fashion Fabrics Limited',
                'Noman Textile Mills Limited',
                'Noman Fabrics Limited - Unit-2',
                'Nice Fabrics Processing Ltd. - 2'
            ]
        );
    }

    return in_array(normalizeNotificationCustomer($name), $blocked, true);
}

function summarizeSalesPageForCommercial(?string $salesJson, array $order = []): array
{
    $summary = [
        'customer' => trim((string)($order['customer_name'] ?? '')),
        'buyer' => trim((string)($order['to_buyer'] ?? '')),
        'sales_person' => trim((string)($order['salesperson'] ?? '')),
        'po_numbers' => [],
        'pi_numbers' => [],
        'item_name' => '',
        'total_qty' => 0,
        'total_value' => 0,
    ];

    $sales = json_decode((string)$salesJson, true);
    if (!is_array($sales)) {
        $po = trim((string)($order['po_number'] ?? ''));
        if ($po !== '') $summary['po_numbers'][$po] = true;
        return $summary;
    }

    foreach (['customer' => 'customer', 'buyer' => 'buyer'] as $src => $dst) {
        $val = trim((string)($sales[$src] ?? ''));
        if ($val !== '') $summary[$dst] = $val;
    }

    $pos = $sales['pos'] ?? [];
    if (!is_array($pos)) $pos = [];
    foreach ($pos as $po) {
        if (!is_array($po)) continue;
        $poNum = trim((string)($po['poNum'] ?? $po['po_number'] ?? ''));
        $piNum = trim((string)($po['piNum'] ?? $po['pi_number'] ?? ''));
        if ($poNum !== '') $summary['po_numbers'][$poNum] = true;
        if ($piNum !== '') $summary['pi_numbers'][$piNum] = true;

        $summary['total_qty'] += (float)($po['qty'] ?? $po['grandQty'] ?? 0);
        $summary['total_value'] += (float)($po['val'] ?? $po['grandVal'] ?? 0);

        $items = $po['items'] ?? [];
        if (!is_array($items)) $items = [];
        foreach ($items as $line) {
            if (!is_array($line)) continue;
            if ($summary['item_name'] === '') {
                $summary['item_name'] = trim((string)($line['desc'] ?? $line['description'] ?? $line['item_description'] ?? ''));
            }
            if (empty($po['qty'])) $summary['total_qty'] += (float)($line['qty'] ?? $line['quantity'] ?? 0);
            if (empty($po['val'])) $summary['total_value'] += (float)($line['amount'] ?? $line['value'] ?? 0);
        }
    }

    if (empty($summary['po_numbers'])) {
        $po = trim((string)($order['po_number'] ?? ''));
        if ($po !== '') $summary['po_numbers'][$po] = true;
    }

    return $summary;
}

try {
    if ($method === 'GET') {
        $limit = max(1, min(200, (int)($_GET['limit'] ?? 8)));
        $full  = !empty($_GET['full']);

        // ERP inbox orders are the only bell/worklist notifications. Opening an
        // item or creating its work order must not clear it. It remains visible
        // while Commercial is working in Sales and clears after PI submission
        // moves the linked work order to Marketing.
        $items = [];
        if (canManageErpOrderInbox($userRole)) {
            $erpStmt = $db->prepare("SELECT e.sale_order_no, e.customer_po_no, e.customer_name, e.buyer, e.sales_person,
                                              e.header_status, e.line_count, e.snapshot_json, e.header_creation_date, e.first_seen_at, e.read_at
                                       FROM erp_order_inbox e
                                       LEFT JOIN orders o ON BINARY o.order_id = BINARY e.work_order_id
                                       WHERE e.work_order_id IS NULL
                                          OR e.work_order_id = ''
                                          OR LOWER(COALESCE(o.current_step, 'sales')) = 'sales'
                                       ORDER BY COALESCE(NULLIF(e.header_creation_date, ''), e.first_seen_at) DESC, e.sale_order_no DESC");
            $erpStmt->execute();

            foreach ($erpStmt->fetchAll() as $erpRow) {
                $erpOrderNo = (string)$erpRow['sale_order_no'];
                $po = trim((string)($erpRow['customer_po_no'] ?? ''));
                $customer = trim((string)($erpRow['customer_name'] ?? ''));
                if (isBlockedNotificationCustomer($customer)) {
                    continue;
                }
                $itemSummary = summarizeErpNotificationItems($erpRow['snapshot_json'] ?? '');
                $items[] = [
                    'id' => 'erp:' . $erpOrderNo,
                    'type' => 'erp_order',
                    'erp_order_no' => $erpOrderNo,
                    'order_id' => '',
                    'step_name' => 'sales',
                    'title' => 'New ERP sales order ' . $erpOrderNo,
                    'message' => trim(($po !== '' ? 'PO ' . $po : 'No PO') . ($customer !== '' ? ' - ' . $customer : '')),
                    'customer_po_no' => $po,
                    'customer_name' => $customer,
                    'buyer' => trim((string)($erpRow['buyer'] ?? '')),
                    'sales_person' => trim((string)($erpRow['sales_person'] ?? '')),
                    'header_status' => trim((string)($erpRow['header_status'] ?? '')),
                    'line_count' => (int)($erpRow['line_count'] ?? 0),
                    'item_name' => $itemSummary['item_name'],
                    'total_qty' => $itemSummary['total_qty'],
                    'price' => $itemSummary['price'],
                    'total_value' => $itemSummary['total_value'],
                    'erp_created_at' => $erpRow['header_creation_date'] ?: '',
                    'first_seen_at' => $erpRow['first_seen_at'] ?: '',
                    'is_read' => empty($erpRow['read_at']) ? 0 : 1,
                    'created_at' => $erpRow['header_creation_date'] ?: $erpRow['first_seen_at'],
                ];
            }
        }

        $activeCount = count($items);
        if (!$full) {
            $items = array_slice($items, 0, $limit);
        }

        echo json_encode([
            'ok' => true,
            'unreadCount' => $activeCount,
            'activeCount' => $activeCount,
            'items' => $items,
        ]);
        exit;

        // Only surface notifications from the fixed floor date onward — older
        // alerts are hidden so the bell starts fresh (no clearing of old rows).
        $notifyFloor = (defined('NOTIFY_FLOOR_DATE') ? NOTIFY_FLOOR_DATE : '2026-09-01') . ' 00:00:00';
        // Everyone (including admin) sees only their own worklist — one row per order/step.
        // The bell and worklist are reserved for new ERP sales orders only.
        $where = "n.user_id = ? AND n.created_at >= ? AND n.step_name = 'erp-order'";
        $params = [$userId, $notifyFloor];

        // PI follow-up rows belong to the Commercial user who submitted that
        // PI. Older broadcasts to other Commercial users must not remain in
        // their personal worklists.
        $commercialPiScope = '';
        if (canSeeCommercialPiWorklist($userRole)) {
            $commercialPiScope = " AND (n.step_name <> 'commercial-pi' OR n.source_user_id = ?)";
            $params[] = $userId;
        }

        // Persisted workflow alerts must match the order's current step so old
        // step alerts disappear. Old persisted 'erp-order' rows are excluded here
        // because unconverted ERP orders are injected fresh from the inbox below.
        $stepMatch = "(n.step_name = 'erp-order'
                       OR (n.step_name = 'commercial-pi' AND o.current_step IN ('sales', 'marketing'))
                       OR BINARY COALESCE(o.current_step, '') = BINARY COALESCE(n.step_name, ''))";

        $orderJoin = "BINARY o.order_id = BINARY n.order_id";
        $erpJoin = "BINARY e.sale_order_no = BINARY n.order_id";

        $countStmt = $db->prepare("
            SELECT COUNT(*)
            FROM notifications n
            LEFT JOIN orders o ON $orderJoin
            LEFT JOIN erp_order_inbox e ON $erpJoin
            LEFT JOIN orders erp_work_order ON BINARY erp_work_order.order_id = BINARY e.work_order_id
            WHERE $where
              AND n.is_read = 0
               AND (e.work_order_id IS NULL
                    OR e.work_order_id = ''
                    OR LOWER(COALESCE(erp_work_order.current_step, 'sales')) = 'sales')
              AND " . erpNotificationDateExpr('e') . " >= ?
        ");
        $countStmt->execute(array_merge($params, [erpNotificationFloorDate()]));
        $activeCount = (int)$countStmt->fetchColumn();

        $sql = "SELECT n.id, n.user_id, n.order_id, n.step_name, n.target_role, n.title, n.message, n.is_read, n.created_at,
                       CASE WHEN n.step_name = 'erp-order' THEN 'erp_order'
                            WHEN n.step_name = 'commercial-pi' THEN 'commercial_pi'
                            ELSE 'workflow' END AS type,
                       CASE WHEN n.step_name = 'erp-order' THEN n.order_id ELSE '' END AS erp_order_no,
                       e.customer_po_no, e.customer_name, e.buyer, e.sales_person, e.header_status, e.line_count,
                       e.snapshot_json, e.header_creation_date AS erp_created_at, e.first_seen_at AS erp_first_seen_at,
                       o.customer_name AS order_customer_name, o.to_buyer AS order_buyer,
                       o.salesperson AS order_sales_person, o.po_number AS order_po_number
                FROM notifications n
                LEFT JOIN orders o ON $orderJoin
                LEFT JOIN erp_order_inbox e ON $erpJoin
                LEFT JOIN orders erp_work_order ON BINARY erp_work_order.order_id = BINARY e.work_order_id
                WHERE $where
                   AND (e.work_order_id IS NULL
                        OR e.work_order_id = ''
                        OR LOWER(COALESCE(erp_work_order.current_step, 'sales')) = 'sales')
                  AND " . erpNotificationDateExpr('e') . " >= ?
                ORDER BY n.created_at DESC";
        if (!$full) $sql .= ' LIMIT ' . $limit;

        $stmt = $db->prepare($sql);
        $stmt->execute(array_merge($params, [erpNotificationFloorDate()]));
        $items = $stmt->fetchAll();
        foreach ($items as &$item) {
            if (($item['type'] ?? '') === 'commercial_pi') {
                $item['customer_po_no'] = trim((string)($item['order_po_number'] ?? ''));
                $item['customer_name'] = trim((string)($item['order_customer_name'] ?? ''));
                $item['buyer'] = trim((string)($item['order_buyer'] ?? ''));
                $item['sales_person'] = trim((string)($item['order_sales_person'] ?? ''));
                $item['item_name'] = 'PI submitted - create another PI';
                $item['total_qty'] = 0;
                $item['price'] = '';
                $item['total_value'] = 0;
                unset($item['snapshot_json']);
                continue;
            }
            if (($item['type'] ?? '') !== 'erp_order') {
                continue;
            }
            $itemSummary = summarizeErpNotificationItems($item['snapshot_json'] ?? '');
            $item['erp_order_no'] = (string)($item['erp_order_no'] ?? $item['order_id'] ?? '');
            $item['customer_po_no'] = trim((string)($item['customer_po_no'] ?? ''));
            $item['customer_name'] = trim((string)($item['customer_name'] ?? ''));
            $item['buyer'] = trim((string)($item['buyer'] ?? ''));
            $item['sales_person'] = trim((string)($item['sales_person'] ?? ''));
            $item['header_status'] = trim((string)($item['header_status'] ?? ''));
            $item['line_count'] = (int)($item['line_count'] ?? 0);
            $item['item_name'] = $itemSummary['item_name'];
            $item['total_qty'] = $itemSummary['total_qty'];
            $item['price'] = $itemSummary['price'];
            $item['total_value'] = $itemSummary['total_value'];
            $item['created_at'] = $item['erp_created_at'] ?: ($item['erp_first_seen_at'] ?: $item['created_at']);
            unset($item['snapshot_json']);
        }
        unset($item);

        // Inject unconverted ERP sales orders created today/on the floor date
        // as notifications for roles that manage the ERP inbox. Each stays until a
        // work order is created from it. ERP notifications use header_creation_date
        // everywhere so "today" and "Sent" both mean ERP order creation time.
        $erpItems = [];
        $erpPendingCount = 0;
        $seenErpOrders = [];
            foreach ($items as $item) {
                if (($item['type'] ?? '') === 'erp_order') {
                    $seenErpOrders[(string)($item['erp_order_no'] ?? $item['order_id'] ?? '')] = true;
            }
        }
        if (canManageErpOrderInbox($userRole)) {
            $erpFloorDate = erpNotificationFloorDate();
            $erpStmt = $db->prepare("SELECT e.sale_order_no, e.customer_po_no, e.customer_name, e.buyer, e.sales_person,
                                             header_status, line_count, snapshot_json, header_creation_date, first_seen_at, read_at
                                     FROM erp_order_inbox e
                                     LEFT JOIN orders o ON BINARY o.order_id = BINARY e.work_order_id
                                     WHERE (e.work_order_id IS NULL
                                            OR e.work_order_id = ''
                                            OR LOWER(COALESCE(o.current_step, 'sales')) = 'sales')
                                         AND " . erpNotificationDateExpr() . " >= ?
                                      ORDER BY COALESCE(NULLIF(header_creation_date, ''), first_seen_at) DESC, sale_order_no DESC");
            $erpStmt->execute([$erpFloorDate]);
            foreach ($erpStmt->fetchAll() as $erpRow) {
                $erpOrderNo = (string)$erpRow['sale_order_no'];
                if (isset($seenErpOrders[$erpOrderNo])) continue;
                $po = trim((string)($erpRow['customer_po_no'] ?? ''));
                $customer = trim((string)($erpRow['customer_name'] ?? ''));
                if (isBlockedNotificationCustomer($customer)) {
                    continue;
                }
                $itemSummary = summarizeErpNotificationItems($erpRow['snapshot_json'] ?? '');
                $erpItems[] = [
                    'id' => 'erp:' . $erpOrderNo,
                    'type' => 'erp_order',
                    'erp_order_no' => $erpOrderNo,
                    'order_id' => '',
                    'step_name' => 'sales',
                    'title' => 'New ERP sales order ' . $erpOrderNo,
                    'message' => trim(($po !== '' ? 'PO ' . $po : 'No PO') . ($customer !== '' ? ' - ' . $customer : '')),
                    'customer_po_no' => $po,
                    'customer_name' => $customer,
                    'buyer' => trim((string)($erpRow['buyer'] ?? '')),
                    'sales_person' => trim((string)($erpRow['sales_person'] ?? '')),
                    'header_status' => trim((string)($erpRow['header_status'] ?? '')),
                    'line_count' => (int)($erpRow['line_count'] ?? 0),
                    'item_name' => $itemSummary['item_name'],
                    'total_qty' => $itemSummary['total_qty'],
                    'price' => $itemSummary['price'],
                    'total_value' => $itemSummary['total_value'],
                    'erp_created_at' => $erpRow['header_creation_date'] ?: '',
                    'first_seen_at' => $erpRow['first_seen_at'] ?: '',
                    // Read ERP orders stay visible until a PI/work order is created.
                    'is_read' => empty($erpRow['read_at']) ? 0 : 1,
                    'created_at' => $erpRow['header_creation_date'] ?: $erpRow['first_seen_at'],
                ];
                // Keep the ERP alert in the badge until PI submission advances it.
                // Opening/reading the notification must not make it disappear.
                $erpPendingCount++;
            }
        }

        $items = array_merge($erpItems, $items);

        usort($items, static function (array $a, array $b): int {
            return strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? ''));
        });
        $items = array_values(array_filter($items, static function (array $item): bool {
            return !isBlockedNotificationCustomer((string)($item['customer_name'] ?? ''));
        }));
        $unreadCount = 0;
        foreach ($items as $item) {
            if (empty($item['is_read'])) {
                $unreadCount++;
            }
        }
        if (!$full) $items = array_slice($items, 0, $limit);

        echo json_encode([
            'ok' => true,
            'unreadCount' => $unreadCount,
            'activeCount' => count($items),
            'items' => $items,
        ]);
        exit;
    }

    if ($method === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true) ?: [];
        $action = $body['action'] ?? '';

        if ($action === 'mark_read') {
            $id = (int)($body['id'] ?? 0);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'id required']);
                exit;
            }
            $stmt = $db->prepare('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);
            echo json_encode(['ok' => true]);
            exit;
        }

        if ($action === 'mark_all_read') {
            $stmt = $db->prepare('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0');
            $stmt->execute([$userId]);
            if (canManageErpOrderInbox($userRole)) {
                $erpStmt = $db->prepare("UPDATE erp_order_inbox
                                          SET read_at = COALESCE(read_at, NOW()), read_by_id = COALESCE(read_by_id, ?)
                                          WHERE (work_order_id IS NULL OR work_order_id = '')
                                            AND read_at IS NULL");
                $erpStmt->execute([$userId]);
            }
            echo json_encode(['ok' => true]);
            exit;
        }

        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
