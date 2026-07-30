<?php
require_once __DIR__ . '/../includes/db.php';
$db = getDB();
try {
    $db->exec("ALTER TABLE customers ADD COLUMN stage VARCHAR(30) NOT NULL DEFAULT 'completed' AFTER extra_data");
    echo "Added stage\n";
} catch (Exception $e) { echo "stage: " . $e->getMessage() . "\n"; }

try {
    $db->exec("ALTER TABLE customers ADD COLUMN signatures JSON NULL AFTER stage");
    echo "Added signatures\n";
} catch (Exception $e) { echo "signatures: " . $e->getMessage() . "\n"; }
echo "Done\n";
