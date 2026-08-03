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

requireLogin();

$db = getDB();
ensureNotificationsTable($db);
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

        $stepMatch = "BINARY COALESCE(o.current_step, '') = BINARY COALESCE(n.step_name, '')";

        $orderJoin = "BINARY o.order_id = BINARY n.order_id";

        $countStmt = $db->prepare("
            SELECT COUNT(*)
            FROM notifications n
            INNER JOIN orders o ON $orderJoin
            WHERE $where
              AND $stepMatch
        ");
        $countStmt->execute($params);
        $activeCount = (int)$countStmt->fetchColumn();

        $sql = "SELECT n.id, n.user_id, n.order_id, n.step_name, n.target_role, n.title, n.message, n.is_read, n.created_at
                FROM notifications n
                INNER JOIN orders o ON $orderJoin
                WHERE $where
                  AND $stepMatch
                ORDER BY n.created_at DESC";
        if (!$full) $sql .= ' LIMIT ' . $limit;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        echo json_encode([
            'ok' => true,
            'unreadCount' => $activeCount,
            'activeCount' => $activeCount,
            'items' => $stmt->fetchAll(),
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
