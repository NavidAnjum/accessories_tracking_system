<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

try {
    $user = currentUser() ?? [];
    $role = strtolower(trim((string)($user['role'] ?? '')));
    if (!in_array($role, ['commercial', 'commercial_dept'], true)) {
        echo json_encode(['ok' => true, 'tracked' => false]);
        exit;
    }

    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    $orderId = trim((string)($body['order_id'] ?? ''));
    if ($orderId === '') {
        http_response_code(400);
        echo json_encode(['error' => 'order_id is required']);
        exit;
    }

    $db = getDB();
    $exists = $db->prepare('SELECT 1 FROM orders WHERE order_id = ? LIMIT 1');
    $exists->execute([$orderId]);
    if (!$exists->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        exit;
    }

    $createdAt = gmdate('c');
    $data = [
        'commercialPdfCreatedAt' => $createdAt,
        'commercialPdfCreatedBy' => (string)($user['name'] ?? ''),
        'commercialPdfCreatedById' => (int)($user['id'] ?? 0),
    ];
    $stmt = $db->prepare("INSERT INTO page_data (order_id, page_name, data)
                          VALUES (?, 'pi-pdf-log', ?)
                          ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = CURRENT_TIMESTAMP");
    $stmt->execute([$orderId, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);

    echo json_encode(['ok' => true, 'tracked' => true, 'created_at' => $createdAt]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
