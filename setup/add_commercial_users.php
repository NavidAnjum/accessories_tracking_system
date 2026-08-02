<?php
// One-time script: add 2 Commercial users. DELETE from live after running.
// Must be logged in as admin to run.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

header('Content-Type: text/plain; charset=utf-8');

$tempPassword = 'Commercial@2026'; // users should change this after first login

$newUsers = [
    ['name' => 'Md. Nazmul Islam',   'email' => 'nazmul.islam@znzal.com'],
    ['name' => 'Mahabub Alam Rakib',  'email' => 'mahabub@znzal.com'],
];

try {
    $db = getDB();
    foreach ($newUsers as $u) {
        $exists = $db->prepare('SELECT id FROM users WHERE email = ?');
        $exists->execute([$u['email']]);
        if ($exists->fetchColumn()) {
            // Already exists — just make sure the role is commercial
            $db->prepare('UPDATE users SET role = ? WHERE email = ?')
               ->execute(['commercial', $u['email']]);
            echo "UPDATED (role -> commercial): {$u['email']}\n";
        } else {
            $hash = password_hash($tempPassword, PASSWORD_DEFAULT);
            $db->prepare('INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)')
               ->execute([$u['name'], $u['email'], $hash, 'commercial']);
            echo "CREATED: {$u['name']} <{$u['email']}>  role=commercial\n";
        }
    }
    echo "\nDone. Temporary password for new users: {$tempPassword}\n";
    echo "Ask them to log in and change it. DELETE this file from the server now.\n";
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage();
}
