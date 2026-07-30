<?php
// Direct DB connection to local MySQL (bypasses HTTP_HOST check)
$pdo = new PDO('mysql:host=localhost;dbname=ed_module;charset=utf8mb4', 'root', '');
$hash = password_hash('admin@', PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password=? WHERE email='admin@ed.local'")->execute([$hash]);
echo "Done. Local password set to: admin@\n";
