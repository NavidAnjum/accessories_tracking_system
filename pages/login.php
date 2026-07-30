<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (currentUser()) {
    header('Location: ' . BASE_PATH . '/pages/dashboard.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    try {
        $db   = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['ed_user'] = [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ];
            header('Location: ' . BASE_PATH . '/pages/dashboard.php'); exit;
        } else {
            $error = 'Invalid email or password.';
        }
    } catch (Exception $e) {
        $error = 'Login error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>ATS — Login</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/styles.css">
    <style>
        body { background: linear-gradient(135deg,#f0f0ff 0%,#e8f0fe 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .login-card { background:#fff; border-radius:16px; padding:48px 40px; width:100%; max-width:400px; box-shadow:0 20px 60px rgba(99,102,241,.18); }
        .login-logo { text-align:center; margin-bottom:28px; }
        .login-logo h1 { font-size:1.5rem; font-weight:800; color:#1e1e2e; margin:0; }
        .login-logo p  { color:#888; font-size:13px; margin:4px 0 0; }
        .login-field { margin-bottom:18px; }
        .login-field label { display:block; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#64748b; margin-bottom:6px; }
        .login-field input { width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:9px; font-size:14px; outline:none; box-sizing:border-box; transition:border-color .15s; }
        .login-field input:focus { border-color:#6366f1; }
        .login-btn { width:100%; padding:12px; background:linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; border:none; border-radius:9px; font-size:15px; font-weight:700; cursor:pointer; margin-top:8px; transition:opacity .15s; }
        .login-btn:hover { opacity:.9; }
        .login-error { background:#fee2e2; color:#c0392b; border-radius:8px; padding:10px 14px; font-size:13px; margin-bottom:16px; }
        .login-hint { text-align:center; margin-top:20px; font-size:12px; color:#aaa; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-logo">
        <h1>Accessories Tracking System</h1>
    </div>
    <?php if ($error): ?>
        <div class="login-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="login-field">
            <label>Email</label>
            <input type="email" name="email" required autofocus placeholder="your@email.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="login-field">
            <label>Password</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="login-btn">Sign In</button>
    </form>
    <div class="login-hint">Accessories Tracking System v1.0</div>
</div>
</body>
</html>
