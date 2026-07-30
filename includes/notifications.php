<?php

require_once __DIR__ . '/db.php';

function ensureNotificationsTable(PDO $db): void {
    static $done = false;
    if ($done) return;

    $db->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            order_id VARCHAR(80) NOT NULL,
            step_name VARCHAR(80) NOT NULL,
            target_role VARCHAR(80) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NULL,
            source_user_id INT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            read_at DATETIME NULL,
            INDEX idx_notifications_user_read (user_id, is_read, created_at),
            INDEX idx_notifications_order_step (order_id, step_name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    $done = true;
}

function notificationStepRoles(string $step): array {
    $map = [
        'marketing-intake' => ['marketing'],
        'costing-review'   => ['costing'],
        'production'       => ['production'],
        'sales'            => ['commercial', 'commercial_dept'],
        'marketing'        => ['marketing'],
        'lc'               => ['commercial', 'commercial_dept'],
        'exchange'         => ['commercial', 'commercial_dept'],
        'commercial'       => ['commercial', 'commercial_dept'],
        'packing'          => ['commercial', 'commercial_dept'],
        'delivery'         => ['commercial', 'commercial_dept'],
        'truck'            => ['commercial', 'commercial_dept'],
        'origin'           => ['commercial', 'commercial_dept'],
        'beneficiary'      => ['commercial', 'commercial_dept'],
        'forwarding'       => ['commercial', 'commercial_dept'],
        'bank-forwarding'  => ['commercial', 'commercial_dept'],
        'po-status'        => ['commercial', 'commercial_dept'],
    ];

    return $map[$step] ?? [];
}

function notificationTargetUsers(PDO $db, string $step): array {
    $roles = notificationStepRoles($step);
    $roles[] = 'admin';
    $roles = array_values(array_unique(array_filter($roles)));
    if (empty($roles)) return [];

    $placeholders = implode(',', array_fill(0, count($roles), '?'));
    $sql = "SELECT id, role FROM users WHERE role IN ($placeholders) AND COALESCE(is_active, 1) = 1";
    $stmt = $db->prepare($sql);
    $stmt->execute($roles);
    return $stmt->fetchAll();
}

function notificationStepLabel(string $step): string {
    $map = [
        'marketing-intake' => 'Marketing Intake',
        'costing-review'   => 'Costing Review',
        'production'       => 'Production',
        'sales'            => 'PI',
        'marketing'        => 'Marketing',
        'lc'               => 'LC',
        'exchange'         => 'Bill of Exchange',
        'commercial'       => 'Commercial Invoice',
        'packing'          => 'Packing List',
        'delivery'         => 'Delivery Challan',
        'truck'            => 'Truck Challan',
        'origin'           => 'Certificate of Origin',
        'beneficiary'      => "Beneficiary's Certificate",
        'forwarding'       => 'Forwarding',
        'bank-forwarding'  => 'Bank Forwarding',
        'po-status'        => 'Challan Sheet',
    ];

    return $map[$step] ?? ucwords(str_replace('-', ' ', $step));
}

function createStepNotifications(PDO $db, string $orderId, string $step, ?int $sourceUserId = null): void {
    ensureNotificationsTable($db);

    $users = notificationTargetUsers($db, $step);
    if (empty($users)) return;

    $stmt = $db->prepare("SELECT order_id, customer_name, salesperson FROM orders WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if (!$order) return;

    $stepLabel = notificationStepLabel($step);
    $customer  = trim((string)($order['customer_name'] ?? ''));
    $sales     = trim((string)($order['salesperson'] ?? ''));
    $title     = $orderId . ' moved to ' . $stepLabel;
    $message   = trim(($customer !== '' ? $customer : 'No customer yet') . ($sales !== '' ? ' · Sales: ' . $sales : ''));

    $insert = $db->prepare("
        INSERT INTO notifications
            (user_id, order_id, step_name, target_role, title, message, source_user_id, is_read)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, 0)
    ");

    foreach ($users as $user) {
        $insert->execute([
            (int)$user['id'],
            $orderId,
            $step,
            (string)$user['role'],
            $title,
            $message,
            $sourceUserId,
        ]);
    }
}

function backfillCurrentStepNotifications(PDO $db): void {
    ensureNotificationsTable($db);

    $orders = $db->query("SELECT order_id, current_step FROM orders WHERE COALESCE(current_step, '') <> ''")->fetchAll();
    if (empty($orders)) return;

    $existsStmt = $db->prepare("
        SELECT id
        FROM notifications
        WHERE user_id = ? AND order_id = ? AND step_name = ?
        LIMIT 1
    ");

    $insertStmt = $db->prepare("
        INSERT INTO notifications
            (user_id, order_id, step_name, target_role, title, message, source_user_id, is_read)
        VALUES
            (?, ?, ?, ?, ?, ?, NULL, 0)
    ");

    $orderInfoStmt = $db->prepare("SELECT customer_name, salesperson FROM orders WHERE order_id = ?");

    foreach ($orders as $order) {
        $orderId = (string)($order['order_id'] ?? '');
        $step    = trim((string)($order['current_step'] ?? ''));
        if ($orderId === '' || $step === '') continue;

        $users = notificationTargetUsers($db, $step);
        if (empty($users)) continue;

        $orderInfoStmt->execute([$orderId]);
        $info = $orderInfoStmt->fetch() ?: [];
        $stepLabel = notificationStepLabel($step);
        $customer  = trim((string)($info['customer_name'] ?? ''));
        $sales     = trim((string)($info['salesperson'] ?? ''));
        $title     = $orderId . ' waiting at ' . $stepLabel;
        $message   = trim(($customer !== '' ? $customer : 'No customer yet') . ($sales !== '' ? ' · Sales: ' . $sales : ''));

        foreach ($users as $user) {
            $uid = (int)$user['id'];
            $existsStmt->execute([$uid, $orderId, $step]);
            if ($existsStmt->fetch()) continue;

            $insertStmt->execute([
                $uid,
                $orderId,
                $step,
                (string)$user['role'],
                $title,
                $message,
            ]);
        }
    }
}
