<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db      = getDB();
$message = '';
$msgType = 'ok';

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = $_POST['role'] ?? 'staff';
    $password = $_POST['password'] ?? '';
    if ($name && $email && $password) {
        try {
            $db->prepare("INSERT INTO users (name,email,password,role,created_by) VALUES (?,?,?,?,?)")
               ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role, currentUser()['id']]);
            $message = "User '$name' created successfully.";
        } catch (Exception $e) {
            $message = 'Error: ' . (str_contains($e->getMessage(),'Duplicate') ? 'Email already exists.' : $e->getMessage());
            $msgType = 'err';
        }
    } else { $message = 'All fields are required.'; $msgType = 'err'; }
}

if ($action === 'toggle' && isset($_POST['uid'])) {
    $uid = (int)$_POST['uid'];
    if ($uid !== (int)currentUser()['id']) {
        $db->prepare("UPDATE users SET is_active = 1 - is_active WHERE id = ?")->execute([$uid]);
        $message = 'User status updated.';
    }
}

if ($action === 'change_role' && isset($_POST['uid'], $_POST['role'])) {
    $uid = (int)$_POST['uid'];
    if ($uid !== (int)currentUser()['id']) {
        $db->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$_POST['role'], $uid]);
        $message = 'Role updated.';
    }
}

if ($action === 'reset_password' && isset($_POST['uid'], $_POST['new_password'])) {
    $uid = (int)$_POST['uid'];
    $pw  = $_POST['new_password'];
    if (strlen($pw) >= 6) {
        $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([password_hash($pw, PASSWORD_DEFAULT), $uid]);
        $message = 'Password reset successfully.';
    } else { $message = 'Password must be at least 6 characters.'; $msgType = 'err'; }
}

if ($action === 'change_own_password' && isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
    $me = $db->prepare("SELECT password FROM users WHERE id = ?");
    $me->execute([(int)currentUser()['id']]);
    $row = $me->fetch();
    if (!password_verify($_POST['current_password'], $row['password'])) {
        $message = 'Current password is incorrect.'; $msgType = 'err';
    } elseif (strlen($_POST['new_password']) < 6) {
        $message = 'New password must be at least 6 characters.'; $msgType = 'err';
    } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
        $message = 'New passwords do not match.'; $msgType = 'err';
    } else {
        $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([password_hash($_POST['new_password'], PASSWORD_DEFAULT), (int)currentUser()['id']]);
        $message = 'Your password has been changed successfully.';
    }
}

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

$totalUsers  = count($users);
$totalAdmins = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
$totalActive = count(array_filter($users, fn($u) => $u['is_active']));

$pageTitle  = 'User Management';
$activePage = 'users';
$navSection = 'master';
include __DIR__ . '/../includes/header.php';
?>

<style>
/* ── User Management ── */
.um-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 28px;
}
.um-title-block h2 { margin: 0; font-size: 20px; font-weight: 800; color: #1e1e2e; }
.um-title-block p  { margin: 3px 0 0; font-size: 12px; color: #94a3b8; }

.um-stats-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 32px;
}
.um-stat {
    flex: 1; min-width: 110px;
    background: #fff;
    border: 1.5px solid #e8eaff;
    border-radius: 12px;
    padding: 14px 18px;
    text-align: center;
}
.um-stat-n { font-size: 28px; font-weight: 800; color: #4f46e5; line-height: 1; }
.um-stat-l { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; margin-top: 4px; }

/* ── Signature-style user grid ── */
.um-sig-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 24px;
}

.um-sig-card {
    background: #fff;
    border: 1.5px solid #e8eaff;
    border-radius: 16px;
    padding: 28px 20px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
    position: relative;
    transition: box-shadow .18s, border-color .18s;
}
.um-sig-card:hover { box-shadow: 0 6px 24px rgba(99,102,241,.13); border-color: #c7d2fe; }
.um-sig-card.inactive { opacity: .55; }

/* Status dot top-right */
.um-sig-status {
    position: absolute;
    top: 14px; right: 14px;
    width: 10px; height: 10px;
    border-radius: 50%;
}
.um-sig-status.on  { background: #22c55e; box-shadow: 0 0 0 3px #dcfce7; }
.um-sig-status.off { background: #f87171; box-shadow: 0 0 0 3px #fee2e2; }

/* "You" badge */
.um-you {
    position: absolute;
    top: 12px; left: 12px;
    font-size: 9px; font-weight: 800;
    background: #ede9fe; color: #6366f1;
    border-radius: 999px; padding: 2px 8px;
    letter-spacing: .04em; text-transform: uppercase;
}

/* Avatar circle — like signature area */
.um-avatar-wrap {
    width: 76px; height: 76px;
    margin-bottom: 16px;
    position: relative;
}
.um-avatar-circle {
    width: 76px; height: 76px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; font-weight: 800; color: #fff;
    border: 3px solid #fff;
    box-shadow: 0 4px 16px rgba(99,102,241,.22);
}
.um-avatar-circle.admin            { background: linear-gradient(135deg,#6366f1,#4f46e5); }
.um-avatar-circle.team_leader      { background: linear-gradient(135deg,#0ea5e9,#0284c7); }
.um-avatar-circle.sales_person     { background: linear-gradient(135deg,#10b981,#059669); }
.um-avatar-circle.commercial       { background: linear-gradient(135deg,#f59e0b,#d97706); }
.um-avatar-circle.sales_coordinator{ background: linear-gradient(135deg,#ec4899,#db2777); }
.um-avatar-circle.head_of_business { background: linear-gradient(135deg,#8b5cf6,#7c3aed); }

/* Signature line — the key visual from the screenshot */
.um-sig-line {
    width: 100%;
    border: none;
    border-top: 1.5px solid #94a3b8;
    margin: 0 0 10px;
}

/* Name + role below the line — exactly like the signature block */
.um-sig-name {
    font-size: 14px;
    font-weight: 700;
    color: #1e1e2e;
    text-align: center;
    line-height: 1.3;
    margin-bottom: 4px;
}
.um-sig-email {
    font-size: 10px;
    color: #94a3b8;
    text-align: center;
    margin-bottom: 10px;
    word-break: break-all;
}

/* Role selector */
.um-sig-role-wrap { margin-bottom: 14px; width: 100%; display: flex; justify-content: center; }
.um-role-select {
    padding: 4px 12px;
    border: 1.5px solid #e2e8f0;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    background: #f8fafc;
    color: #475569;
    outline: none;
    text-align: center;
    transition: border-color .15s;
    appearance: none;
    -webkit-appearance: none;
}
.um-role-select:focus { border-color: #6366f1; }
.um-role-pill {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
}
.um-role-pill.admin            { background: #ede9fe; color: #5b21b6; }
.um-role-pill.team_leader      { background: #dbeafe; color: #1d4ed8; }
.um-role-pill.sales_person     { background: #dcfce7; color: #166534; }
.um-role-pill.commercial       { background: #fef3c7; color: #92400e; }
.um-role-pill.sales_coordinator{ background: #fce7f3; color: #9d174d; }
.um-role-pill.head_of_business { background: #f3e8ff; color: #6b21a8; }

/* Action buttons row */
.um-sig-actions { display: flex; gap: 6px; width: 100%; }
.um-act-btn {
    flex: 1;
    padding: 6px 8px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
    border: 1.5px solid #e2e8f0;
    background: #f8fafc;
    color: #64748b;
    cursor: pointer;
    text-align: center;
    transition: all .15s;
    white-space: nowrap;
}
.um-act-btn:hover            { background: #f0f0ff; border-color: #c7d2fe; color: #4f46e5; }
.um-act-btn.danger:hover     { background: #fee2e2; border-color: #fca5a5; color: #c0392b; }
.um-act-btn.activate:hover   { background: #dcfce7; border-color: #86efac; color: #166534; }

/* Flash */
.um-flash {
    padding: 12px 18px; border-radius: 10px;
    margin-bottom: 24px; font-size: 13px; font-weight: 600;
    display: flex; align-items: center; gap: 10px;
}
.um-flash.ok  { background: #dcfce7; color: #166534; }
.um-flash.err { background: #fee2e2; color: #c0392b; }

/* Modals */
.um-modal-shell {
    display: none; position: fixed; inset: 0;
    background: rgba(15,15,30,.45); backdrop-filter: blur(4px);
    z-index: 9999; align-items: center; justify-content: center;
}
.um-modal-shell.open { display: flex; }
.um-modal-card {
    background: #fff; border-radius: 18px; padding: 32px;
    width: 100%; max-width: 480px;
    box-shadow: 0 24px 80px rgba(0,0,0,.22); position: relative;
}
.um-modal-sm { max-width: 360px; }
.um-modal-title { font-size: 18px; font-weight: 800; color: #1e1e2e; margin: 0 0 22px; }
.um-modal-close {
    position: absolute; top: 16px; right: 16px;
    background: #f1f5f9; border: none; border-radius: 8px;
    width: 30px; height: 30px; font-size: 14px; cursor: pointer;
    color: #64748b; display: flex; align-items: center; justify-content: center;
}
.um-modal-close:hover { background: #e2e8f0; }
.um-field { margin-bottom: 16px; }
.um-label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #64748b; margin-bottom: 5px; }
.um-input { width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 9px; font-size: 13px; outline: none; box-sizing: border-box; transition: border-color .15s; }
.um-input:focus { border-color: #6366f1; }
.um-modal-actions { display: flex; gap: 10px; margin-top: 22px; }
</style>

<div style="padding: 4px 0 8px;">

    <!-- Top bar -->
    <div class="um-topbar">
        <div class="um-title-block">
            <div class="eyebrow">Admin</div>
            <h2>User Management</h2>
            <p>Manage team access, roles and permissions</p>
        </div>
        <button class="primary-btn" onclick="openCreateUser()">+ New User</button>
    </div>

    <?php if ($message): ?>
    <div class="um-flash <?= $msgType ?>">
        <span><?= $msgType === 'ok' ? '✓' : '✕' ?></span>
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="um-stats-row">
        <div class="um-stat">
            <div class="um-stat-n"><?= $totalUsers ?></div>
            <div class="um-stat-l">Total Users</div>
        </div>
        <div class="um-stat">
            <div class="um-stat-n" style="color:#22c55e;"><?= $totalActive ?></div>
            <div class="um-stat-l">Active</div>
        </div>
        <div class="um-stat">
            <div class="um-stat-n" style="color:#0ea5e9;"><?= $totalAdmins ?></div>
            <div class="um-stat-l">Admins</div>
        </div>
        <div class="um-stat">
            <div class="um-stat-n" style="color:#f59e0b;"><?= $totalUsers - $totalActive ?></div>
            <div class="um-stat-l">Inactive</div>
        </div>
    </div>

    <!-- Signature-style user grid -->
    <div class="um-sig-grid">
        <?php foreach ($users as $u):
            $isSelf   = (int)$u['id'] === (int)currentUser()['id'];
            $initials = strtoupper(implode('', array_map(fn($w) => $w[0], array_slice(explode(' ', trim($u['name'])), 0, 2))));
            $role     = $u['role'];
        ?>
        <div class="um-sig-card<?= $u['is_active'] ? '' : ' inactive' ?>">

            <!-- Status dot -->
            <span class="um-sig-status <?= $u['is_active'] ? 'on' : 'off' ?>" title="<?= $u['is_active'] ? 'Active' : 'Inactive' ?>"></span>

            <?php if ($isSelf): ?><span class="um-you">You</span><?php endif; ?>

            <!-- Avatar -->
            <div class="um-avatar-wrap">
                <div class="um-avatar-circle <?= $role ?>"><?= htmlspecialchars($initials) ?></div>
            </div>

            <!-- Signature line -->
            <hr class="um-sig-line">

            <!-- Name & email below line — like signature block -->
            <div class="um-sig-name"><?= htmlspecialchars($u['name']) ?></div>
            <div class="um-sig-email"><?= htmlspecialchars($u['email']) ?></div>

            <!-- Role -->
            <div class="um-sig-role-wrap">
                <?php if ($isSelf): ?>
                    <span class="um-role-pill <?= $role ?>"><?= ucfirst($role) ?></span>
                <?php else: ?>
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="action" value="change_role">
                        <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                        <select name="role" class="um-role-select" onchange="this.form.submit()" title="Change role">
                            <option value="sales_person"    <?= $role==='sales_person'    ?'selected':'' ?>>Sales Person</option>
                            <option value="commercial"      <?= $role==='commercial'      ?'selected':'' ?>>Commercial</option>
                            <option value="team_leader"     <?= $role==='team_leader'     ?'selected':'' ?>>Team Leader</option>
                            <option value="sales_coordinator" <?= $role==='sales_coordinator'?'selected':'' ?>>Sales Coordinator</option>
                            <option value="head_of_business" <?= $role==='head_of_business'?'selected':'' ?>>Head of Business/Dept</option>
                            <option value="admin"           <?= $role==='admin'           ?'selected':'' ?>>Admin</option>
                        </select>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <?php if ($isSelf): ?>
            <div class="um-sig-actions">
                <button class="um-act-btn" onclick="openChangeOwnPw()">🔑 Change Password</button>
            </div>
            <?php else: ?>
            <div class="um-sig-actions">
                <form method="POST" style="flex:1;display:contents;">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                    <button type="submit" class="um-act-btn <?= $u['is_active'] ? 'danger' : 'activate' ?>">
                        <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
                    </button>
                </form>
                <button class="um-act-btn"
                        onclick="openResetPw(<?= $u['id'] ?>,'<?= htmlspecialchars(addslashes($u['name'])) ?>')">
                    Reset PW
                </button>
            </div>
            <?php endif; ?>

        </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- Create User Modal -->
<div class="um-modal-shell" id="createUserModal">
    <div class="um-modal-card">
        <button class="um-modal-close" onclick="closeCreateUser()">✕</button>
        <div class="um-modal-title">Create New User</div>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="um-field">
                <label class="um-label">Full Name</label>
                <input type="text" name="name" class="um-input" placeholder="e.g. John Smith" required autofocus>
            </div>
            <div class="um-field">
                <label class="um-label">Email Address</label>
                <input type="email" name="email" class="um-input" placeholder="email@company.com" required>
            </div>
            <div class="um-field">
                <label class="um-label">Role</label>
                <select name="role" class="um-input">
                    <option value="sales_person">Sales Person</option>
                    <option value="commercial">Commercial</option>
                    <option value="team_leader">Team Leader</option>
                    <option value="sales_coordinator">Sales Coordinator</option>
                    <option value="head_of_business">Head of Business/Department</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="um-field">
                <label class="um-label">Password</label>
                <input type="password" name="password" class="um-input" placeholder="Minimum 6 characters" required>
            </div>
            <div class="um-modal-actions">
                <button type="submit" class="primary-btn" style="flex:1;">Create User</button>
                <button type="button" class="ghost-btn" onclick="closeCreateUser()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="um-modal-shell" id="resetPwModal">
    <div class="um-modal-card um-modal-sm">
        <button class="um-modal-close" onclick="closeResetPw()">✕</button>
        <div class="um-modal-title" id="resetPwTitle">Reset Password</div>
        <form method="POST">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="uid" id="resetPwUid">
            <div class="um-field">
                <label class="um-label">New Password</label>
                <input type="password" name="new_password" class="um-input" placeholder="Minimum 6 characters" required>
            </div>
            <div class="um-modal-actions">
                <button type="submit" class="primary-btn" style="flex:1;">Reset Password</button>
                <button type="button" class="ghost-btn" onclick="closeResetPw()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Change Own Password Modal -->
<div class="um-modal-shell" id="changeOwnPwModal">
    <div class="um-modal-card um-modal-sm">
        <button class="um-modal-close" onclick="closeChangeOwnPw()">✕</button>
        <div class="um-modal-title">Change My Password</div>
        <form method="POST">
            <input type="hidden" name="action" value="change_own_password">
            <div class="um-field">
                <label class="um-label">Current Password</label>
                <input type="password" name="current_password" class="um-input" placeholder="Enter current password" required autofocus>
            </div>
            <div class="um-field">
                <label class="um-label">New Password</label>
                <input type="password" name="new_password" class="um-input" placeholder="Minimum 6 characters" required>
            </div>
            <div class="um-field">
                <label class="um-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="um-input" placeholder="Repeat new password" required>
            </div>
            <div class="um-modal-actions">
                <button type="submit" class="primary-btn" style="flex:1;">Save Password</button>
                <button type="button" class="ghost-btn" onclick="closeChangeOwnPw()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateUser() { document.getElementById('createUserModal').classList.add('open'); }
function closeCreateUser() { document.getElementById('createUserModal').classList.remove('open'); }
function openResetPw(uid, name) {
    document.getElementById('resetPwUid').value = uid;
    document.getElementById('resetPwTitle').textContent = 'Reset Password — ' + name;
    document.getElementById('resetPwModal').classList.add('open');
}
function closeResetPw() { document.getElementById('resetPwModal').classList.remove('open'); }
function openChangeOwnPw()  { document.getElementById('changeOwnPwModal').classList.add('open'); }
function closeChangeOwnPw() { document.getElementById('changeOwnPwModal').classList.remove('open'); }
document.getElementById('createUserModal').addEventListener('click', function(e){ if(e.target===this) closeCreateUser(); });
document.getElementById('resetPwModal').addEventListener('click', function(e){ if(e.target===this) closeResetPw(); });
document.getElementById('changeOwnPwModal').addEventListener('click', function(e){ if(e.target===this) closeChangeOwnPw(); });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
