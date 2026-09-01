<?php
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$db = getDB();
$results = [];

function addCustomerColumn(PDO $db, array &$results, string $name, string $sql): void {
    try {
        $db->exec($sql);
        $results[] = ['column' => $name, 'status' => 'added'];
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $results[] = [
            'column' => $name,
            'status' => stripos($message, 'Duplicate column') !== false ? 'already_exists' : 'error',
            'message' => $message,
        ];
    }
}

addCustomerColumn($db, $results, 'scorecard_total', "ALTER TABLE customers ADD COLUMN scorecard_total SMALLINT NULL AFTER extra_data");
addCustomerColumn($db, $results, 'scorecard_grade', "ALTER TABLE customers ADD COLUMN scorecard_grade VARCHAR(5) NULL AFTER scorecard_total");
addCustomerColumn($db, $results, 'scorecard_action', "ALTER TABLE customers ADD COLUMN scorecard_action TEXT NULL AFTER scorecard_grade");
addCustomerColumn($db, $results, 'scorecard_json', "ALTER TABLE customers ADD COLUMN scorecard_json JSON NULL AFTER scorecard_action");

$backfilled = 0;
$stmt = $db->query("SELECT id, extra_data FROM customers WHERE extra_data IS NOT NULL AND extra_data <> ''");
$update = $db->prepare("
    UPDATE customers
    SET scorecard_total = ?,
        scorecard_grade = ?,
        scorecard_action = ?,
        scorecard_json = ?
    WHERE id = ?
");

foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $extra = json_decode($row['extra_data'] ?? '', true);
    if (!is_array($extra) || empty($extra['scorecard']) || !is_array($extra['scorecard'])) {
        continue;
    }

    $scorecard = $extra['scorecard'];
    $update->execute([
        isset($scorecard['total']) ? (int) $scorecard['total'] : null,
        trim((string) ($scorecard['grade'] ?? '')) ?: null,
        trim((string) ($scorecard['action'] ?? '')) ?: null,
        json_encode($scorecard),
        (int) $row['id'],
    ]);
    $backfilled++;
}

echo json_encode(['ok' => true, 'results' => $results, 'backfilled' => $backfilled], JSON_PRETTY_PRINT);
