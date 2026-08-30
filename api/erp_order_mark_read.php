<?php
/**
 * api/erp_order_mark_read.php — mark an ERP sales order read/unread.
 * POST JSON { erp_order_no, read: true|false }
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_order_inbox.php';
requireLogin();

if (!canManageErpOrderInbox((string)(currentUser()['role'] ?? ''))) {
    http_response_code(403);
    echo json_encode(['error' => 'Commercial access required.']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'POST required']);
        exit;
    }
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $orderNo = trim((string)($body['erp_order_no'] ?? ''));
    if ($orderNo === '' || !preg_match('/^\d+$/', $orderNo)) {
        http_response_code(400);
        echo json_encode(['error' => 'A valid ERP sales order number is required.']);
        exit;
    }
    $read = !array_key_exists('read', $body) || !empty($body['read']);

    $db = getDB();
    markErpOrderRead($db, $orderNo, (int)(currentUser()['id'] ?? 0) ?: null, $read);
    echo json_encode(['ok' => true, 'erp_order_no' => $orderNo, 'readStatus' => $read ? 'read' : 'unread']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
