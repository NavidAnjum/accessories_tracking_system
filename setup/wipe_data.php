<?php
// ONE-TIME DB WIPE — clears all data for a fresh go-live, KEEPS the `users` table.
// Must be logged in as admin. Requires ?confirm=YES to actually run.
// DELETE THIS FILE from the server immediately after use.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

header('Content-Type: text/plain; charset=utf-8');

// Tables to PRESERVE (data kept intact)
$keep = ['users', 'items'];

if (($_GET['confirm'] ?? '') !== 'YES') {
    echo "This will DELETE ALL DATA except the `users` table.\n\n";
    echo "To proceed, add ?confirm=YES to the URL:\n";
    echo "   " . htmlspecialchars($_SERVER['REQUEST_URI']) . (strpos($_SERVER['REQUEST_URI'], '?') === false ? '?' : '&') . "confirm=YES\n";
    exit;
}

try {
    $db = getDB();
    $dbName = $db->query('SELECT DATABASE()')->fetchColumn();

    $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    $db->exec('SET FOREIGN_KEY_CHECKS = 0');
    $cleared = [];
    $skipped = [];
    foreach ($tables as $t) {
        if (in_array($t, $keep, true)) { $skipped[] = $t; continue; }
        $db->exec("TRUNCATE TABLE `$t`");
        $cleared[] = $t;
    }
    $db->exec('SET FOREIGN_KEY_CHECKS = 1');

    echo "Database: {$dbName}\n\n";
    echo "CLEARED (" . count($cleared) . "): " . implode(', ', $cleared) . "\n";
    echo "KEPT    (" . count($skipped) . "): " . implode(', ', $skipped) . "\n\n";

    $userCount = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    echo "Users remaining: {$userCount}\n\n";
    echo "Done. You are now live from scratch. DELETE this file from the server.\n";
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage();
}
