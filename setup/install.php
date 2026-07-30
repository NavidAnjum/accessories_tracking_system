<?php
/**
 * install.php — Run the schema.sql to set up the ed_module database.
 * Access at: http://localhost/ed_module/setup/install.php
 */

$isLive = ($_SERVER['HTTP_HOST'] ?? '') !== 'localhost';
$dbUser = $isLive ? 'talhagr1_sim' : 'root';
$dbPass = $isLive ? 'DDiiUU@@10ng' : '';

$output  = [];
$success = true;

try {
    $pdo = new PDO(
        'mysql:host=localhost;charset=utf8mb4',
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $sqlFile = __DIR__ . '/schema.sql';
    if (!file_exists($sqlFile)) {
        throw new RuntimeException('schema.sql not found at: ' . $sqlFile);
    }

    $sql = file_get_contents($sqlFile);

    // Split on semicolons (simple approach — avoids multi-statement PDO issue)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($s) => $s !== '' && !preg_match('/^--/', $s)
    );

    foreach ($statements as $stmt) {
        if (trim($stmt) === '') continue;
        $pdo->exec($stmt);
        $output[] = htmlspecialchars(substr($stmt, 0, 80)) . '…  <span style="color:#16a34a">OK</span>';
    }

    $output[] = '';
    $output[] = '<strong style="color:#16a34a;font-size:1.1em;">Installation complete. Database ed_module is ready.</strong>';

} catch (Throwable $e) {
    $success = false;
    $output[] = '<strong style="color:#dc2626">Error:</strong> ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ED Module — Install</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f1f5f9; margin: 0; padding: 40px; }
        h1   { color: #1e3a5f; margin-bottom: 8px; }
        p    { color: #475569; margin-top: 0; }
        pre  {
            background: #0f172a; color: #e2e8f0;
            padding: 24px; border-radius: 8px;
            font-size: 13px; line-height: 1.7;
            overflow-x: auto; white-space: pre-wrap; word-break: break-word;
        }
        a    { color: #2563eb; }
    </style>
</head>
<body>
    <h1>ED Module — Database Installer</h1>
    <p>Runs <code>setup/schema.sql</code> against <code>localhost</code> (user: root, no password).</p>
    <pre><?= implode("\n", $output) ?></pre>
    <?php if ($success): ?>
    <p><a href="../pages/dashboard.php">&larr; Go to Dashboard</a></p>
    <?php endif; ?>
</body>
</html>
