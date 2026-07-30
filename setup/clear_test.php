<?php
require_once __DIR__ . '/../includes/db.php';
try {
    $db = getDB();
    $deleted = $db->exec("DELETE FROM customers WHERE company_name = 'Test company'");
    echo "Deleted $deleted record(s). <a href='../pages/customer-profile.php'>Go back</a>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
