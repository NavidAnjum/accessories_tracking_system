<?php
/**
 * api/users.php — minimal user lookup for assignment dropdowns.
 *
 * GET ?role=marketing → active users with that role: [{id, name, team}]
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

try {
    $db = getDB();
    $role = trim((string)($_GET['role'] ?? ''));

    if ($role !== '') {
        $stmt = $db->prepare("SELECT id, name, team FROM users WHERE role = ? AND COALESCE(is_active, 1) = 1 ORDER BY name ASC");
        $stmt->execute([$role]);
    } else {
        $stmt = $db->query("SELECT id, name, role, team FROM users WHERE COALESCE(is_active, 1) = 1 ORDER BY name ASC");
    }
    echo json_encode($stmt->fetchAll());
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
