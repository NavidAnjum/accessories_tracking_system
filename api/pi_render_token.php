<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/pi_render_tokens.php';
requireLogin();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

try {
    $userId = (int)(currentUser()['id'] ?? 0);
    if (!$userId) throw new RuntimeException('No authenticated user was found.');
    echo json_encode(['token' => issuePiRenderToken(getDB(), $userId), 'expires_in' => 120]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not prepare the secure PI email link.']);
}

