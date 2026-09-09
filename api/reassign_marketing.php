<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';
requireLogin();

try {
    $role = strtolower(trim((string)(currentUser()['role'] ?? '')));
    if (!in_array($role, ['commercial', 'commercial_dept'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Only Commercial can change the Marketing person.']);
        exit;
    }

    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $orderId = trim((string)($body['order_id'] ?? ''));
    $marketingUserId = (int)($body['marketing_user_id'] ?? 0);
    if ($orderId === '' || $marketingUserId < 1) {
        http_response_code(400);
        echo json_encode(['error' => 'Order and Marketing person are required.']);
        exit;
    }

    $db = getDB();
    $userStmt = $db->prepare("SELECT id, name, role FROM users
                              WHERE id = ? AND role IN ('marketing', 'team_leader')
                                AND COALESCE(is_active, 1) = 1 LIMIT 1");
    $userStmt->execute([$marketingUserId]);
    $marketingUser = $userStmt->fetch();
    if (!$marketingUser) {
        http_response_code(404);
        echo json_encode(['error' => 'Active Marketing person not found.']);
        exit;
    }

    $orderStmt = $db->prepare('SELECT current_step FROM orders WHERE order_id = ? LIMIT 1');
    $orderStmt->execute([$orderId]);
    $currentStep = $orderStmt->fetchColumn();
    if ($currentStep === false) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found.']);
        exit;
    }

    $salesStmt = $db->prepare("SELECT data FROM page_data WHERE order_id = ? AND page_name = 'sales' LIMIT 1");
    $salesStmt->execute([$orderId]);
    $salesData = json_decode((string)($salesStmt->fetchColumn() ?: '{}'), true);
    if (!is_array($salesData)) $salesData = [];
    $salesData['marketingUserId'] = (string)$marketingUser['id'];
    $salesData['marketingUserName'] = (string)$marketingUser['name'];

    ensureNotificationsTable($db);
    $db->beginTransaction();
    $save = $db->prepare("INSERT INTO page_data (order_id, page_name, data)
                          VALUES (?, 'sales', ?)
                          ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = CURRENT_TIMESTAMP");
    $save->execute([$orderId, json_encode($salesData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);

    // If approval is still pending, remove the old person's alert and create
    // the pending Marketing alert for the newly selected person.
    $approvalStmt = $db->prepare("SELECT data FROM page_data WHERE order_id = ? AND page_name = 'marketing' LIMIT 1");
    $approvalStmt->execute([$orderId]);
    $approvalData = json_decode((string)($approvalStmt->fetchColumn() ?: '{}'), true);
    $approvedValue = is_array($approvalData) ? ($approvalData['marketingApproved'] ?? false) : false;
    $isApproved = $approvedValue === true || $approvedValue === 'true'
        || (is_array($approvalData) && ($approvalData['piApprovalStatus'] ?? '') === 'approved');

    if ($isApproved) {
        $db->rollBack();
        http_response_code(409);
        echo json_encode(['error' => 'Marketing approval is already completed and cannot be reassigned.']);
        exit;
    }

    if ($currentStep === 'marketing') {
        $delete = $db->prepare("DELETE n FROM notifications n
                                INNER JOIN users u ON u.id = n.user_id
                                WHERE n.order_id = ? AND n.step_name = 'marketing' AND u.role <> 'admin'");
        $delete->execute([$orderId]);
        createStepNotifications($db, $orderId, 'marketing', (int)(currentUser()['id'] ?? 0) ?: null, $marketingUserId);
    }
    $db->commit();

    echo json_encode(['ok' => true, 'marketing_user_id' => $marketingUser['id'], 'marketing_user_name' => $marketingUser['name']]);
} catch (Throwable $e) {
    if (isset($db) && $db instanceof PDO && $db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
