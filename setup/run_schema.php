<?php
$isLive = ($_SERVER['HTTP_HOST'] ?? '') !== 'localhost';
$dbName = $isLive ? 'talhagr1_atx' : 'ed_module';
$dbUser = $isLive ? 'talhagr1_sim' : 'root';
$dbPass = $isLive ? 'DDiiUU@@10ng' : '';

$sql = file_get_contents(__DIR__ . '/schema.sql');

// On live, strip CREATE DATABASE and USE statements — the DB already exists
if ($isLive) {
    $sql = preg_replace('/CREATE\s+DATABASE\b[^;]+;/i', '', $sql);
    $sql = preg_replace('/USE\s+`?[\w]+`?\s*;/i', '', $sql);
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if ($stmt) {
            $pdo->exec($stmt);
            echo "OK: " . substr($stmt, 0, 80) . "\n";
        }
    }
    echo "\nALL TABLES CREATED SUCCESSFULLY\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
