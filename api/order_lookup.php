<?php
/**
 * api/order_lookup.php
 *
 * GET  ?id=ORD-2026-0001  → full order snapshot (orders + page_data + pis)
 * POST (no body)           → create new order, returns { order_id }
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    // ── POST — create new order ───────────────────────────────────────────────
    if ($method === 'POST') {
        // Generate next order_id: ORD-YYYY-NNNN
        $year  = date('Y');
        $stmt  = $db->prepare("SELECT COUNT(*) FROM orders WHERE order_id LIKE ?");
        $stmt->execute(["ORD-{$year}-%"]);
        $count = (int)$stmt->fetchColumn();
        $orderId = sprintf('ORD-%s-%04d', $year, $count + 1);

        $db->prepare("INSERT INTO orders (order_id, current_step) VALUES (?, 'marketing-intake')")
           ->execute([$orderId]);

        echo json_encode(['ok' => true, 'order_id' => $orderId]);
        exit;
    }

    // ── GET — look up full order ─────────────────────────────────────────────
    if ($method === 'GET') {
        $id = trim($_GET['id'] ?? '');
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'id required']); exit; }

        // Search by full match first, then partial
        $stmt = $db->prepare("SELECT * FROM orders WHERE order_id = ? LIMIT 1");
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        if (!$order) {
            $stmt = $db->prepare("SELECT * FROM orders WHERE order_id LIKE ? LIMIT 1");
            $stmt->execute(['%' . $id . '%']);
            $order = $stmt->fetch();
        }

        if (!$order) {
            echo json_encode(['found' => false]);
            exit;
        }

        // Fetch all page_data blobs for this order
        $stmt = $db->prepare("SELECT page_name, data FROM page_data WHERE order_id = ?");
        $stmt->execute([$order['order_id']]);
        $pages = [];
        foreach ($stmt->fetchAll() as $row) {
            $pages[$row['page_name']] = json_decode($row['data'], true) ?? [];
        }

        // Detect migration columns
        $hasMigCols = false;
        try { $db->query('SELECT order_id FROM pis LIMIT 0'); $hasMigCols = true; } catch (PDOException $_) {}

        // Fetch linked PIs
        $pis = [];
        if ($hasMigCols) {
            $stmt = $db->prepare("SELECT * FROM pis WHERE order_id = ? ORDER BY is_master ASC, created_at DESC");
            $stmt->execute([$order['order_id']]);
            $pis = $stmt->fetchAll();
        }
        if (!$pis) {
            $stmt = $db->prepare("SELECT * FROM pis WHERE pi_number LIKE ? ORDER BY created_at DESC");
            $stmt->execute(['%' . $order['order_id'] . '%']);
            $pis = $stmt->fetchAll();
        }
        foreach ($pis as &$pi) {
            $pi['pos']          = json_decode($pi['pos'],          true) ?? [];
            $pi['included_pis'] = json_decode($pi['included_pis'] ?? 'null', true) ?? [];
        }

        echo json_encode([
            'found'   => true,
            'order'   => $order,
            'pages'   => $pages,
            'pis'     => $pis,
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
