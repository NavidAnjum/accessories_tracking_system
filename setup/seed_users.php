<?php
require_once __DIR__ . '/../includes/db.php';

$db = getDB();

$users = [
    ['Sales Person',              'sales@ed.local',       'sales_person'],
    ['Commercial Officer',        'commercial@ed.local',   'commercial'],
    ['Team Leader',               'teamleader@ed.local',   'team_leader'],
    ['Sales Coordinator',         'coordinator@ed.local',  'sales_coordinator'],
    ['Head of Business',          'head@ed.local',         'head_of_business'],
];

$password = password_hash('Pass@1234', PASSWORD_DEFAULT);

foreach ($users as [$name, $email, $role]) {
    try {
        $db->prepare("INSERT INTO users (name, email, password, role, is_active, created_by) VALUES (?, ?, ?, ?, 1, 1)")
           ->execute([$name, $email, $password, $role]);
        echo "Created: $name ($email) — $role\n";
    } catch (Exception $e) {
        echo "Skip $email: " . $e->getMessage() . "\n";
    }
}

echo "\nDone. Password for all: Pass@1234\n";
