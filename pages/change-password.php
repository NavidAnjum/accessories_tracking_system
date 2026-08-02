<?php
// change-password.php — any logged-in user can change their own password.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$db      = getDB();
$me      = currentUser();
$message = '';
$msgType = 'err';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
    $stmt->execute([(int)$me['id']]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password'])) {
        $message = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $message = 'New password must be at least 6 characters.';
    } elseif ($new !== $confirm) {
        $message = 'New passwords do not match.';
    } else {
        $db->prepare('UPDATE users SET password = ? WHERE id = ?')
           ->execute([password_hash($new, PASSWORD_DEFAULT), (int)$me['id']]);
        $message = 'Your password has been changed successfully.';
        $msgType = 'ok';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/styles.css">
    <style>
        body { background: linear-gradient(135deg,#f0f0ff 0%,#e8f0fe 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; margin:0; }
        .cpw-card { background:#fff; border-radius:16px; padding:40px 36px; width:100%; max-width:420px; box-shadow:0 20px 60px rgba(99,102,241,.18); }
        .cpw-card h1 { font-size:1.35rem; font-weight:800; color:#1e1e2e; margin:0 0 4px; }
        .cpw-card .sub { color:#888; font-size:13px; margin-bottom:22px; }
        .cpw-field { margin-bottom:16px; }
        .cpw-field label { display:block; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin-bottom:6px; }
        .cpw-field input { width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:14px; outline:none; box-sizing:border-box; }
        .cpw-field input:focus { border-color:#6366f1; }
        .cpw-btn { width:100%; padding:12px; background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; border:none; border-radius:9px; font-size:15px; font-weight:700; cursor:pointer; margin-top:6px; }
        .cpw-btn:hover { opacity:.92; }
        .cpw-msg { border-radius:8px; padding:10px 14px; font-size:13px; margin-bottom:16px; font-weight:600; }
        .cpw-msg.ok  { background:#dcfce7; color:#166534; border:1.5px solid #86efac; }
        .cpw-msg.err { background:#fee2e2; color:#c0392b; border:1.5px solid #fca5a5; }
        .cpw-links { text-align:center; margin-top:18px; font-size:13px; }
        .cpw-links a { color:#6366f1; text-decoration:none; font-weight:600; }
    </style>
</head>
<body>
<div class="cpw-card">
    <h1>Change Password</h1>
    <div class="sub">Signed in as <strong><?= htmlspecialchars($me['email'] ?? '') ?></strong></div>

    <?php if ($message): ?>
        <div class="cpw-msg <?= $msgType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <div class="cpw-field">
            <label>Current Password</label>
            <input type="password" name="current_password" required autofocus>
        </div>
        <div class="cpw-field">
            <label>New Password</label>
            <input type="password" name="new_password" required minlength="6" placeholder="At least 6 characters">
        </div>
        <div class="cpw-field">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required minlength="6">
        </div>
        <button type="submit" class="cpw-btn">Update Password</button>
    </form>

    <div class="cpw-links">
        <a href="<?= BASE_PATH ?>/pages/dashboard.php">&#8592; Back to Dashboard</a>
    </div>
</div>
</body>
</html>
