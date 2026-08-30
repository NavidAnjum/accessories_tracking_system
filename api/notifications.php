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

try {
    if ($method === 'GET') {
        $limit = max(1, min(200, (int)($_GET['limit'] ?? 8)));
        $full  = !empty($_GET['full']);
        // Everyone (including admin) sees only their own worklist — one row per order/step.
        $where = 'n.user_id = ?';
        $params = [$userId];

        // ERP inbox alerts are generated below from unresolved inbox rows.
        // Exclude old persisted erp-order alerts so the same ERP order is not shown twice.
        $stepMatch = "(n.step_name <> 'erp-order'
                       AND BINARY COALESCE(o.current_step, '') = BINARY COALESCE(n.step_name, ''))";

        $orderJoin = "BINARY o.order_id = BINARY n.order_id";

        $countStmt = $db->prepare("
            SELECT COUNT(*)
            FROM notifications n
            LEFT JOIN orders o ON $orderJoin
            WHERE $where
              AND $stepMatch
        ");
        $countStmt->execute($params);
        $activeCount = (int)$countStmt->fetchColumn();

        $sql = "SELECT n.id, n.user_id, n.order_id, n.step_name, n.target_role, n.title, n.message, n.is_read, n.created_at
                FROM notifications n
                LEFT JOIN orders o ON $orderJoin
                WHERE $where
                  AND $stepMatch
                ORDER BY n.created_at DESC";
        if (!$full) $sql .= ' LIMIT ' . $limit;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $items = $stmt->fetchAll();
        $erpItems = [];
        if (canManageErpOrderInbox($userRole)) {
            ensureErpOrderInboxTable($db);
            // Show the newest batch of new (unconverted) ERP orders. Use the latest
            // available created date rather than the server's "today" so orders still
            // appear when the ERP/backfill date lags the server date by a day.
            $erpDate = (string) $db->query(
                "SELECT MAX(LEFT(COALESCE(header_creation_date, ''), 10))
                 FROM erp_order_inbox
                 WHERE work_order_id IS NULL AND header_creation_date IS NOT NULL"
            )->fetchColumn();
            $erpStmt = $db->prepare("SELECT sale_order_no, customer_po_no, customer_name, buyer, header_status,
                                            header_creation_date, line_count, first_seen_at
                                     FROM erp_order_inbox
                                     WHERE work_order_id IS NULL
                                       AND LEFT(COALESCE(header_creation_date, ''), 10) = ?
                                     ORDER BY header_creation_date DESC, sale_order_no DESC");
            $erpStmt->execute([$erpDate !== '' ? $erpDate : date('Y-m-d')]);
            foreach ($erpStmt->fetchAll() as $erpRow) {
                $erpOrderNo = (string)$erpRow['sale_order_no'];
                $po = trim((string)($erpRow['customer_po_no'] ?? ''));
                $customer = trim((string)($erpRow['customer_name'] ?? ''));
                $erpItems[] = [
                    'id' => 'erp:' . $erpOrderNo,
                    'type' => 'erp_order',
                    'erp_order_no' => $erpOrderNo,
                    'order_id' => '',
                    'step_name' => 'sales',
                    'title' => 'New ERP sales order ' . $erpOrderNo,
                    'message' => trim(($po !== '' ? 'PO ' . $po : 'No PO') . ($customer !== '' ? ' - ' . $customer : '')),
                    'is_read' => 0,
                    'created_at' => $erpRow['header_creation_date'] ?: $erpRow['first_seen_at'],
                ];
            }
        }

        $items = array_merge($erpItems, $items);
        usort($items, static function (array $a, array $b): int {
            return strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? ''));
        });
        if (!$full) $items = array_slice($items, 0, $limit);

        echo json_encode([
            'ok' => true,
            'unreadCount' => $activeCount + count($erpItems),
            'activeCount' => $activeCount + count($erpItems),
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
