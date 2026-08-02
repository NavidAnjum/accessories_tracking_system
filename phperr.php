<?php
// Temporary debug helper — shows PHP settings and recent errors.
// DELETE THIS FILE from live after you're done.
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "<h3>PHP version: " . phpversion() . "</h3>";
echo "<h3>short_open_tag: " . (ini_get('short_open_tag') ? 'ON  (this was the footer bug!)' : 'off') . "</h3>";

echo "<hr><h3>Recent PHP errors (last 30 lines of error_log):</h3><pre>";
$found = false;
foreach ([__DIR__.'/error_log', __DIR__.'/pages/error_log', __DIR__.'/includes/error_log'] as $log) {
    if (is_file($log)) {
        $found = true;
        echo "===== $log =====\n";
        $lines = file($log);
        echo htmlspecialchars(implode('', array_slice($lines, -30))) . "\n\n";
    }
}
if (!$found) echo "(no error_log file found in app/pages/includes)";
echo "</pre>";

echo "<hr><h3>footer.php check:</h3>";
$footer = __DIR__ . '/includes/footer.php';
echo file_exists($footer)
    ? "footer.php exists (" . number_format(filesize($footer)) . " bytes)<br>"
    : "footer.php MISSING at $footer<br>";
