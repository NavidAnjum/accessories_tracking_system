<?php
/**
 * api/page_data.php
 *
 * GET  ?order_id=ZNZ000001&page=costing-review  → saved JSON for that page
 * POST JSON { "orderId":"...", "page":"...", "data":{} }  → upsert
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    // ── GET ───────────────────────────────────────────────────────────────────
    if ($method === 'GET') {
        $orderId = $_GET['order_id'] ?? null;
        $page    = $_GET['page']     ?? null;

        if (!$orderId || !$page) {
            http_response_code(400);
            echo json_encode(['error' => 'order_id and page are required']);
            exit;
        }

        $stmt = $db->prepare(
            'SELECT data, updated_at FROM page_data WHERE order_id = ? AND page_name = ?'
        );
        $stmt->execute([$orderId, $page]);
        $row = $stmt->fetch();

        if (!$row) {
            echo json_encode(['data' => null]);
            exit;
        }

        $data = json_decode($row['data'], true);
        echo json_encode(['data' => $data, 'updated_at' => $row['updated_at']]);
        exit;
    }

    // ── POST — upsert ─────────────────────────────────────────────────────────
    if ($method === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON body']);
            exit;
        }

        $orderId = trim($body['orderId'] ?? '');
        $page    = trim($body['page']    ?? '');
        $data    = $body['data']          ?? [];

        if (!$orderId || !$page) {
            http_response_code(400);
            echo json_encode(['error' => 'orderId and page are required']);
            exit;
        }

        // Ensure the order exists before inserting page_data (FK constraint)
        $check = $db->prepare('SELECT id FROM orders WHERE order_id = ?');
        $check->execute([$orderId]);
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found: ' . $orderId]);
            exit;
        }

        $sql = '
            INSERT INTO page_data (order_id, page_name, data)
            VALUES (:order_id, :page_name, :data)
            ON DUPLICATE KEY UPDATE
                data       = VALUES(data),
                updated_at = CURRENT_TIMESTAMP
        ';

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':order_id'  => $orderId,
            ':page_name' => $page,
            ':data'      => json_encode($data),
        ]);

        echo json_encode(['ok' => true, 'order_id' => $orderId, 'page' => $page]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
