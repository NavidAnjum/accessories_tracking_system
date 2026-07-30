<?php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
header("Location: {$base}/pages/dashboard.php");
exit;
?>
