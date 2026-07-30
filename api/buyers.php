<?php
/**
 * api/buyers.php
 *
 * GET  (optional ?customer=X)  → buyers list, filtered by customer if provided
 * POST (JSON body)              → upsert by buyer_code
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
        $customer = $_GET['customer'] ?? null;
        if ($customer !== null && $customer !== '') {
            $stmt = $db->prepare(
                'SELECT * FROM buyers WHERE customer_name = ? ORDER BY buyer_code'
            );
            $stmt->execute([$customer]);
        } else {
            $stmt = $db->query('SELECT * FROM buyers ORDER BY buyer_code');
        }
        echo json_encode($stmt->fetchAll());
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

        $buyerCode    = trim($body['buyerCode']    ?? '');
        $buyerName    = trim($body['buyerName']    ?? '');
        $customerName = trim($body['customer']     ?? '');
        $address      = trim($body['address']      ?? '');

        if (!$buyerCode || !$buyerName) {
            http_response_code(400);
            echo json_encode(['error' => 'buyerCode and buyerName are required']);
            exit;
        }

        $sql = '
            INSERT INTO buyers (buyer_code, buyer_name, customer_name, address)
            VALUES (:buyer_code, :buyer_name, :customer_name, :address)
            ON DUPLICATE KEY UPDATE
                buyer_name    = VALUES(buyer_name),
                customer_name = VALUES(customer_name),
                address       = VALUES(address)
        ';

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':buyer_code'    => $buyerCode,
            ':buyer_name'    => $buyerName,
            ':customer_name' => $customerName,
            ':address'       => $address,
        ]);

        echo json_encode(['ok' => true, 'buyer_code' => $buyerCode]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
