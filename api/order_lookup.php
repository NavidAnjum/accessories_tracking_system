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
require_once __DIR__ . '/../includes/erp_order_inbox.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    $orderColumns = null;
    $getOrderColumns = static function () use ($db, &$orderColumns): array {
        if ($orderColumns !== null) {
            return $orderColumns;
        }
        $orderColumns = [];
        foreach ($db->query("SHOW COLUMNS FROM orders")->fetchAll() as $col) {
            if (!empty($col['Field'])) {
                $orderColumns[$col['Field']] = true;
            }
        }
        return $orderColumns;
    };

    // ── POST — create new order ───────────────────────────────────────────────
    if ($method === 'POST') {
        // Optional starting step (e.g. 'sales' to start directly from PI)
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $startStep = $input['step'] ?? ($_GET['step'] ?? 'marketing-intake');
        if (!in_array($startStep, ['marketing-intake', 'sales'], true)) {
            $startStep = 'marketing-intake';
        }

        // Generate the next monthly ID across both real orders and IDs already
        // claimed in the ERP inbox. COUNT(*) can reuse a number after gaps and
        // does not see inbox claims, causing uq_erp_order_inbox_work_order 1062.
        $yearMonth = date('Y-m');
        $prefix = "ORD-{$yearMonth}-";
        ensureErpOrderInboxTable($db);

        $stmt = $db->prepare('SELECT order_id FROM orders WHERE order_id LIKE ? ORDER BY order_id DESC LIMIT 1');
        $stmt->execute([$prefix . '%']);
        $lastOrderNum = (int)substr((string)($stmt->fetchColumn() ?: ''), strlen($prefix));

        $stmt = $db->prepare('SELECT work_order_id FROM erp_order_inbox WHERE work_order_id LIKE ? ORDER BY work_order_id DESC LIMIT 1');
        $stmt->execute([$prefix . '%']);
        $lastInboxNum = (int)substr((string)($stmt->fetchColumn() ?: ''), strlen($prefix));

        $orderId = sprintf('%s%05d', $prefix, max($lastOrderNum, $lastInboxNum) + 1);

        // Stamp who created the order
        $me     = currentUser();
        $byId   = $me['id']   ?? null;
        $byName = $me['name'] ?? null;

        $cols = $getOrderColumns();
        $insertCols = ['order_id', 'current_step'];
        $insertVals = [$orderId, $startStep];
        if (isset($cols['created_by_id'])) {
            $insertCols[] = 'created_by_id';
            $insertVals[] = $byId;
        }
        if (isset($cols['created_by_name'])) {
            $insertCols[] = 'created_by_name';
            $insertVals[] = $byName;
        }
        $placeholders = implode(', ', array_fill(0, count($insertCols), '?'));
        $sql = 'INSERT INTO orders (' . implode(', ', $insertCols) . ') VALUES (' . $placeholders . ')';
        $db->prepare($sql)->execute($insertVals);

        echo json_encode(['ok' => true, 'order_id' => $orderId, 'current_step' => $startStep, 'created_by' => $byName]);
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
