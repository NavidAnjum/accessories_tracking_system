<?php
/**
 * db.php — PDO singleton for ed_module database
 */

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $isLive = ($_SERVER['HTTP_HOST'] ?? '') !== 'localhost';
        $dbName = $isLive ? 'talhagr1_atx' : 'ed_module';
        $dbUser = $isLive ? 'talhagr1_sim' : 'root';
        $dbPass = $isLive ? 'DDiiUU@@10ng' : '';

        $dsn = "mysql:host=localhost;dbname={$dbName};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}
