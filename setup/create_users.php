<?php
require_once __DIR__ . '/../includes/db.php';
try {
    $db = getDB();
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name       VARCHAR(100) NOT NULL,
        email      VARCHAR(150) NOT NULL UNIQUE,
        password   VARCHAR(255) NOT NULL,
        role       ENUM('admin','manager','staff') NOT NULL DEFAULT 'staff',
        is_active  TINYINT(1) DEFAULT 1,
        created_by INT UNSIGNED NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $exists = $db->query("SELECT COUNT(*) FROM users WHERE email='admin@ed.local'")->fetchColumn();
    if (!$exists) {
        $hash = password_hash('Admin@1234', PASSWORD_DEFAULT);
        $db->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)")
           ->execute(['Administrator','admin@ed.local',$hash,'admin']);
        echo "Done. Default admin created: admin@ed.local / Admin@1234";
    } else {
        echo "Done. Users table ready. Admin already exists.";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
