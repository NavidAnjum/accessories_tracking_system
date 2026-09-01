<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_order_inbox.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$me = currentUser();
$role = (string)($me['role'] ?? '');
if (!canManageErpOrderInbox($role)) {
    http_response_code(403);
    echo json_encode(['error' => 'Commercial access is required.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$erpOrderNo = trim((string)($body['sale_order_no'] ?? ''));
$requestedWorkOrderId = trim((string)($body['work_order_id'] ?? ''));
if ($erpOrderNo === '' || !preg_match('/^\d+$/', $erpOrderNo)) {
    http_response_code(400);
    echo json_encode(['error' => 'A valid ERP sales order number is required.']);
    exit;
}

try {
    $db = getDB();
    ensureErpOrderInboxTable($db);
    $db->beginTransaction();

    $db->prepare('INSERT IGNORE INTO erp_order_inbox (sale_order_no) VALUES (?)')->execute([$erpOrderNo]);
    $lock = $db->prepare('SELECT * FROM erp_order_inbox WHERE sale_order_no = ? FOR UPDATE');
    $lock->execute([$erpOrderNo]);
    $inbox = $lock->fetch();

    if (!empty($inbox['work_order_id'])) {
        if ($requestedWorkOrderId !== '' && $requestedWorkOrderId !== (string)$inbox['work_order_id']) {
            $db->commit();
            http_response_code(409);
            echo json_encode([
                'error' => 'ERP sales order ' . $erpOrderNo . ' already belongs to ' . $inbox['work_order_id'] . '.',
                'order_id' => $inbox['work_order_id'],
            ]);
            exit;
        }
        $db->commit();
        echo json_encode([
            'ok' => true,
            'created' => false,
            'order_id' => $inbox['work_order_id'],
            'sale_order_no' => $erpOrderNo,
            'redirect' => '../pages/sales.php?erp_order=' . rawurlencode($erpOrderNo),
        ]);
        exit;
    }

    if ($requestedWorkOrderId !== '') {
        $exists = $db->prepare('SELECT COUNT(*) FROM orders WHERE order_id = ?');
        $exists->execute([$requestedWorkOrderId]);
        if (!(int)$exists->fetchColumn()) {
            throw new RuntimeException('The selected internal work order does not exist.');
        }

        $update = $db->prepare('UPDATE erp_order_inbox SET work_order_id = ?, converted_by_id = ?, converted_at = NOW() WHERE sale_order_no = ? AND work_order_id IS NULL');
        $update->execute([$requestedWorkOrderId, $me['id'] ?? null, $erpOrderNo]);
        if ($update->rowCount() !== 1) {
            throw new RuntimeException('This ERP order was already linked by another user.');
        }
        $db->commit();
        echo json_encode([
            'ok' => true,
            'created' => false,
            'linked' => true,
            'order_id' => $requestedWorkOrderId,
            'sale_order_no' => $erpOrderNo,
        ]);
        exit;
    }

    $columns = [];
    foreach ($db->query('SHOW COLUMNS FROM orders')->fetchAll() as $column) {
        $columns[(string)$column['Field']] = true;
    }

    $yearMonth = date('Y-m');
    $prefix = 'ORD-' . $yearMonth . '-';
    // The generated id must be free in BOTH the orders table and the inbox
    // (work_order_id is unique there). Taking the max across both avoids reusing
    // an id already claimed by a prior conversion, which caused a 1062 duplicate.
    $lastStmt = $db->prepare('SELECT order_id FROM orders WHERE order_id LIKE ? ORDER BY order_id DESC LIMIT 1 FOR UPDATE');
    $lastStmt->execute([$prefix . '%']);
    $lastOrderNum = (int)substr((string)($lastStmt->fetchColumn() ?: ''), strlen($prefix));

    $lastInboxStmt = $db->prepare('SELECT work_order_id FROM erp_order_inbox WHERE work_order_id LIKE ? ORDER BY work_order_id DESC LIMIT 1');
    $lastInboxStmt->execute([$prefix . '%']);
    $lastInboxNum = (int)substr((string)($lastInboxStmt->fetchColumn() ?: ''), strlen($prefix));

    $nextNumber = max($lastOrderNum, $lastInboxNum) + 1;
    $orderId = sprintf('%s%05d', $prefix, $nextNumber);

    $values = [
        'order_id' => $orderId,
        'current_step' => 'sales',
        'customer_name' => (string)($inbox['customer_name'] ?? ''),
        'po_number' => (string)($inbox['customer_po_no'] ?? ''),
        'order_no' => $erpOrderNo,
        'to_buyer' => (string)($inbox['buyer'] ?? ''),
        'created_by_id' => $me['id'] ?? null,
        'created_by_name' => $me['name'] ?? null,
    ];
    $insertValues = array_intersect_key($values, $columns);
    $insertColumns = array_keys($insertValues);
    $marks = implode(',', array_fill(0, count($insertColumns), '?'));
    $db->prepare('INSERT INTO orders (' . implode(',', $insertColumns) . ') VALUES (' . $marks . ')')
        ->execute(array_values($insertValues));

    $update = $db->prepare('UPDATE erp_order_inbox SET work_order_id = ?, converted_by_id = ?, converted_at = NOW() WHERE sale_order_no = ? AND work_order_id IS NULL');
    $update->execute([$orderId, $me['id'] ?? null, $erpOrderNo]);
    if ($update->rowCount() !== 1) {
        throw new RuntimeException('This ERP order was already converted by another user.');
    }

    $db->commit();
    echo json_encode([
        'ok' => true,
        'created' => true,
        'order_id' => $orderId,
        'sale_order_no' => $erpOrderNo,
        'redirect' => '../pages/sales.php?erp_order=' . rawurlencode($erpOrderNo),
    ]);
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
