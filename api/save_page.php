<?php
/**
 * api/save_page.php
 *
 * POST JSON { order_id, page_name, ...data }
 * Upserts a row in page_data and optionally updates orders.current_step.
 * Also syncs top-level order fields (customer_name, salesperson, etc.) into orders table.
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

try {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) { http_response_code(400); echo json_encode(['error' => 'Invalid JSON']); exit; }

    $orderId  = trim($body['order_id']  ?? '');
    $pageName = trim($body['page_name'] ?? '');
    if (!$orderId || !$pageName) {
        http_response_code(400);
        echo json_encode(['error' => 'order_id and page_name required']);
        exit;
    }

    $db = getDB();

    // Ensure order exists
    $stmt = $db->prepare('SELECT id FROM orders WHERE order_id = ?');
    $stmt->execute([$orderId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found: ' . $orderId]);
        exit;
    }

    // Strip meta keys, save the rest as JSON blob
    $data = $body;
    unset($data['order_id'], $data['page_name']);
    $dataJson = json_encode($data);

    $db->prepare('
        INSERT INTO page_data (order_id, page_name, data)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = CURRENT_TIMESTAMP
    ')->execute([$orderId, $pageName, $dataJson]);

    // Sync customer/salesperson into orders table from marketing-intake page
    if ($pageName === 'marketing-intake') {
        $db->prepare('
            UPDATE orders SET
                customer_name   = COALESCE(NULLIF(:c,  ""), customer_name),
                salesperson     = COALESCE(NULLIF(:sp, ""), salesperson),
                intake_date     = COALESCE(NULLIF(:id, ""), intake_date),
                sub_description = COALESCE(NULLIF(:sd, ""), sub_description),
                current_step    = :step,
                updated_at      = CURRENT_TIMESTAMP
            WHERE order_id = :oid
        ')->execute([
            ':c'    => $data['customer']    ?? '',
            ':sp'   => $data['salesPerson'] ?? '',
            ':id'   => $data['intakeDate']  ?? '',
            ':sd'   => $data['subject']     ?? '',
            ':step' => 'marketing-intake',
            ':oid'  => $orderId,
        ]);
    }

    echo json_encode(['ok' => true, 'order_id' => $orderId, 'page' => $pageName]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
