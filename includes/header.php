<?php
// header.php — shared across all ED Module pages
$navSection = $navSection ?? 'order';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
requireLogin();
$__user = currentUser();

// Enforce page-level tab access for restricted roles
if (!empty($activePage) && !in_array($activePage, ['dashboard', 'notifications'], true)) {
    $__allowed = allowedTabs();
    if (!empty($__allowed) && !in_array($activePage, $__allowed, true)) {
        // Redirect to first allowed page for this role
        $__first = $__allowed[0] ?? 'dashboard';
        $__map = [
            'marketing-intake' => 'marketing-intake.php',
            'marketing'        => 'marketing.php',
            'costing-review'   => 'costing-review.php',
            'production'       => 'production.php',
            'sales'            => 'sales.php',
            'erp-orders-report'=> 'erp-orders-report.php',
            'lc'               => 'lc.php',
            'exchange'         => 'exchange.php',
            'commercial'       => 'commercial.php',
            'packing'          => 'packing.php',
            'delivery'         => 'delivery.php',
            'truck'            => 'truck.php',
            'origin'           => 'origin.php',
            'beneficiary'      => 'beneficiary.php',
            'forwarding'       => 'forwarding.php',
            'bank-forwarding'  => 'bank-forwarding.php',
            'po-status'        => 'po-status.php',
        ];
        $__dest = $__map[$__first] ?? 'dashboard.php';
        header('Location: ' . BASE_PATH . '/pages/' . $__dest);
        exit;
    }
}
// Override any server/WordPress CSP that blocks inline scripts on this app
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval'; img-src * data: blob:; font-src * data:; connect-src *;");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Workspace') ?></title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/styles.css">
    <script>window.APP_BASE = '<?= BASE_PATH ?>';</script>
    <style>
        .nav-notify-wrap { position: relative; }
        .nav-notify-btn {
            width: 42px; height: 42px; border: 1.5px solid #dbe3ff; border-radius: 12px;
            background: #fff; color: #4f46e5; cursor: pointer; position: relative;
            display: inline-flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .nav-notify-count {
            position: absolute; top: -7px; right: -7px; min-width: 22px; height: 22px; padding: 0 6px;
            border-radius: 999px; background: #ef4444; color: #fff; font-size: 11px; font-weight: 800;
            display: none; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(239,68,68,.35);
        }
        .nav-notify-panel {
            position: absolute; top: calc(100% + 10px); right: 0; width: min(420px, 92vw);
            background: #fff; border: 1.5px solid #dbe3ff; border-radius: 18px; box-shadow: 0 22px 50px rgba(15,23,42,.18);
            overflow: hidden; z-index: 3000; display: none;
        }
        .nav-notify-panel.open { display: block; }
        .nav-notify-head {
            padding: 16px 18px; display: flex; align-items: center; justify-content: space-between;
            gap: 10px; border-bottom: 1px solid #eef2ff; background: #f8faff;
        }
        .nav-notify-list { max-height: 420px; overflow: auto; }
        .nav-notify-item {
            padding: 14px 18px; border-bottom: 1px solid #eef2ff; cursor: pointer; transition: background .15s;
        }
        .nav-notify-item:hover { background: #f8faff; }
        .nav-notify-item.unread { background: #eef4ff; }
        .nav-notify-item:last-child { border-bottom: none; }
        .nav-notify-title { font-size: 13px; font-weight: 800; color: #1e1e2e; margin-bottom: 4px; }
        .nav-notify-msg { font-size: 12px; color: #64748b; line-height: 1.45; }
        .nav-notify-meta { margin-top: 6px; font-size: 11px; color: #94a3b8; display: flex; gap: 8px; flex-wrap: wrap; }
        .nav-notify-empty { padding: 28px 18px; text-align: center; color: #94a3b8; font-size: 13px; }
        .nav-notify-foot {
            padding: 14px 18px; display: flex; justify-content: space-between; gap: 10px;
            border-top: 1px solid #eef2ff; background: #fff;
        }
        .nav-notify-link {
            font-size: 12px; font-weight: 700; color: #4f46e5; text-decoration: none; cursor: pointer;
        }
    </style>
</head>
<body data-page="<?= htmlspecialchars($activePage ?? '') ?>">
<div class="app-shell">

    <nav class="page-nav" aria-label="Form pages">
        <div class="nav-section-row">
            <a class="nav-section-btn<?= $navSection === 'master' ? ' active' : '' ?>"
               href="<?= BASE_PATH ?>/pages/customer-profile.php">Master Data</a>
            <span class="nav-flow-arrow">&#8594;</span>
            <a class="nav-section-btn<?= $navSection === 'order' ? ' active' : '' ?>"
               href="<?= BASE_PATH ?>/pages/marketing-intake.php">Order &amp; Documents</a>
            <span class="nav-flow-arrow">&#8594;</span>
            <a class="nav-home-btn<?= $activePage === 'dashboard' ? ' active' : '' ?>"
               href="<?= BASE_PATH ?>/pages/dashboard.php">&#9783; Dashboard</a>

            <div class="nav-user-bar">
                <div class="nav-notify-wrap">
                    <button type="button" class="nav-notify-btn" id="navNotifyBtn" title="Notifications">🔔
                        <span class="nav-notify-count" id="navNotifyCount">0</span>
                    </button>
                    <div class="nav-notify-panel" id="navNotifyPanel">
                        <div class="nav-notify-head">
                            <div>
                                <div style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#6366f1;">Notifications</div>
                                <div style="font-size:15px;font-weight:800;color:#1e1e2e;">My Worklist</div>
                            </div>
                            <button type="button" class="ghost-btn" id="navNotifyReadAll" style="padding:6px 12px;font-size:12px;">Mark all read</button>
                        </div>
                        <div class="nav-notify-list" id="navNotifyList">
                            <div class="nav-notify-empty">Loading notifications...</div>
                        </div>
                        <div class="nav-notify-foot">
                            <a class="nav-notify-link" href="<?= BASE_PATH ?>/pages/notifications.php">Go to Full Worklist</a>
                        </div>
                    </div>
                </div>
                <span class="nav-user-name"><?= htmlspecialchars($__user['name']) ?></span>
                <span class="nav-user-role"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $__user['role'] ?? ''))) ?></span>
                <a href="<?= BASE_PATH ?>/pages/change-password.php" class="nav-logout-btn" style="background:#eef2ff;color:#4f46e5;border-color:#c7d2fe;" title="Change your password">&#128273; Password</a>
                <a href="<?= BASE_PATH ?>/pages/logout.php" class="nav-logout-btn">Sign Out</a>
            </div>
        </div>
        <?php
        $orderTabs = [
            ['id' => 'marketing-intake', 'href' => 'marketing-intake.php',      'label' => 'Marketing Intake'],
            ['id' => 'costing-review',   'href' => 'costing-review.php',        'label' => 'Costing Review'],
            ['id' => 'production',       'href' => 'production.php',            'label' => 'Production'],
            ['id' => 'sales',            'href' => 'sales.php',                 'label' => 'PI'],
            ['id' => 'erp-orders-report','href' => 'erp-orders-report.php',     'label' => 'ERP Orders'],
            ['id' => 'marketing',        'href' => 'marketing.php',            'label' => 'Marketing'],
            ['id' => 'lc',               'href' => 'lc.php',                    'label' => 'LC'],
            ['id' => 'exchange',         'href' => 'exchange.php',              'label' => 'Bill of Exchange'],
            ['id' => 'commercial',       'href' => 'commercial.php',            'label' => 'Commercial Invoice'],
            ['id' => 'packing',          'href' => 'packing.php',               'label' => 'Packing List'],
            ['id' => 'delivery',         'href' => 'delivery.php',              'label' => 'Delivery Challan'],
            ['id' => 'truck',            'href' => 'truck.php',                 'label' => 'Truck Challan'],
            ['id' => 'origin',           'href' => 'origin.php',                'label' => 'Certificate of Origin'],
            ['id' => 'beneficiary',      'href' => 'beneficiary.php',           'label' => "Beneficiary's Certificate"],
            ['id' => 'forwarding',       'href' => 'forwarding.php',            'label' => 'Forwarding'],
            ['id' => 'bank-forwarding',  'href' => 'bank-forwarding.php',       'label' => 'Bank Forwarding'],
            ['id' => 'po-status',        'href' => 'po-status.php',             'label' => 'Challan Sheet'],
        ];
        // Filter tabs by role
        $visibleTabs = array_values(array_filter($orderTabs, fn($t) => canAccessTab($t['id'])));
        ?>
        <?php if ($navSection === 'order'): ?>
        <div class="nav-tab-group">
            <?php foreach ($visibleTabs as $i => $tab): ?>
                <?php if ($i > 0): ?><span class="tab-flow-arrow">&#8594;</span><?php endif; ?>
                <a class="page-tab<?= $activePage === $tab['id'] ? ' active' : '' ?>"
                   href="<?= BASE_PATH ?>/pages/<?= $tab['href'] ?>"><?= htmlspecialchars($tab['label']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php elseif ($navSection === 'master'): ?>
        <div class="nav-tab-group">
            <a class="page-tab<?= $activePage === 'customer-profile' ? ' active' : '' ?>" href="<?= BASE_PATH ?>/pages/customer-profile.php">Customer List</a>
            <span class="tab-flow-arrow">&#8594;</span>
            <a class="page-tab<?= $activePage === 'create-customer' ? ' active' : '' ?>" href="<?= BASE_PATH ?>/pages/create-customer.php">Create Profile</a>
            <span class="tab-flow-arrow">&#8594;</span>
            <a class="page-tab<?= $activePage === 'item-master' ? ' active' : '' ?>" href="<?= BASE_PATH ?>/pages/item-master.php">Item Master</a>
            <?php if (isAdmin()): ?>
            <span class="tab-flow-arrow">&#8594;</span>
            <a class="page-tab<?= $activePage === 'users' ? ' active' : '' ?>" href="<?= BASE_PATH ?>/pages/users.php">&#9881; Users</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </nav>

    <?php if ($navSection === 'order'): ?>
    <div class="order-id-bar" id="orderIdBar">
        <div class="oid-left">
            <span class="oid-icon">📋</span>
            <div>
                <div class="oid-label">WORK ORDER</div>
                <div class="oid-value" id="oidDisplay">No order loaded</div>
            </div>
        </div>
        <div class="oid-search-row">
            <input class="oid-input" id="oidInput" placeholder="Enter Order ID (e.g. ORD-2026-0001) or PI number…"
                   onkeydown="if(event.key==='Enter')oidSearch()">
            <button class="oid-btn-search" onclick="oidSearch()">🔍 Load Order</button>
            <button class="oid-btn-new" onclick="oidNewOrder()">+ New Order</button>
        </div>
        <div class="oid-status-row" id="oidStatusRow" style="display:none;">
            <span id="oidCustomer"></span>
            <span class="oid-sep">·</span>
            <span id="oidStep"></span>
            <span class="oid-sep">·</span>
            <span id="oidDate"></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Self-contained Work Order bar functions (do NOT depend on footer.php/script.js) -->
    <script>
    (function () {
        if (window.__oidBarReady) return;
        window.__oidBarReady = true;
        const OID_KEY = 'ats_current_order_id';
        const BASE = window.APP_BASE || ('/' + window.location.pathname.split('/')[1]);

        function setOrderDisplay(orderId, order) {
            const display = document.getElementById('oidDisplay');
            const statusRow = document.getElementById('oidStatusRow');
            if (!display) return;
            display.textContent = orderId;
            if (order && statusRow) {
                const cust = document.getElementById('oidCustomer');
                const step = document.getElementById('oidStep');
                const date = document.getElementById('oidDate');
                if (cust) cust.textContent = order.customer_name || 'No customer yet';
                if (step) step.textContent = 'Step: ' + (order.current_step || '—').replace(/-/g,' ').replace(/\b\w/g,c=>c.toUpperCase());
                if (date) date.textContent = order.created_at ? new Date(order.created_at).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : '';
                statusRow.style.display = 'flex';
            }
            sessionStorage.setItem(OID_KEY, orderId);
        }

        function loadOrderById(id, isManual) {
            fetch(BASE + '/api/order_lookup.php?id=' + encodeURIComponent(id))
                .then(r => r.json())
                .then(res => {
                    if (!res.found) {
                        sessionStorage.removeItem(OID_KEY);
                        const d = document.getElementById('oidDisplay');
                        if (d) d.textContent = 'No order loaded';
                        const sr = document.getElementById('oidStatusRow');
                        if (sr) sr.style.display = 'none';
                        if (isManual) alert('Order not found: ' + id);
                        return;
                    }
                    const displayOrder = {
                        ...res.order,
                        customer_name: res.order?.customer_name
                            || res.pages?.sales?.customer
                            || res.pages?.['marketing-intake']?.customer
                            || ''
                    };
                    setOrderDisplay(res.order.order_id, displayOrder);
                    const inp = document.getElementById('oidInput');
                    if (inp) inp.value = '';
                    if (typeof window.onOrderLoad === 'function') window.onOrderLoad(res);
                })
                .catch(() => alert('Could not reach server.'));
        }

        window.getCurrentOrderId = () => sessionStorage.getItem(OID_KEY) || '';
        window.loadOrderById = loadOrderById;

        window.oidSearch = function () {
            const q = (document.getElementById('oidInput')?.value || '').trim();
            if (q) loadOrderById(q, true);
        };

        // Show a blank, unsaved-order draft (no DB row is created yet).
        // The order is created only when the user Submits the Marketing Intake.
        function showNewOrderDraft() {
            sessionStorage.removeItem(OID_KEY);
            const display = document.getElementById('oidDisplay');
            if (display) display.textContent = 'New order (unsaved)';
            const sr = document.getElementById('oidStatusRow');
            if (sr) {
                const cust = document.getElementById('oidCustomer');
                const step = document.getElementById('oidStep');
                const date = document.getElementById('oidDate');
                if (cust) cust.textContent = 'Fill details and Submit to create the order';
                if (step) step.textContent = 'Step: Marketing Intake';
                if (date) date.textContent = '';
                sr.style.display = 'flex';
            }
            if (typeof window.onNewOrder === 'function') window.onNewOrder(null);
        }
        window.showNewOrderDraft = showNewOrderDraft;

        window.oidNewOrder = function () {
            if (sessionStorage.getItem(OID_KEY)) {
                if (!confirm('Start a new order? The current order will be cleared from this session.')) return;
            }
            sessionStorage.removeItem(OID_KEY);
            if (/(marketing-intake|sales)\.php/.test(window.location.pathname)) {
                showNewOrderDraft(); // page starts a blank draft in place — just reset the form
            } else {
                sessionStorage.setItem('ats_new_order', '1');
                window.location.href = BASE + '/pages/marketing-intake.php';
            }
        };

        // Auto-restore the current/most-recent order on page load
        document.addEventListener('DOMContentLoaded', function () {
            if (!document.getElementById('oidDisplay')) return;
            // New-order mode: show a blank draft, don't auto-load an existing order
            if (sessionStorage.getItem('ats_new_order') === '1') {
                sessionStorage.removeItem('ats_new_order');
                showNewOrderDraft();
                return;
            }
            const stored = sessionStorage.getItem(OID_KEY);
            if (stored) {
                loadOrderById(stored);
            } else {
                fetch(BASE + '/api/orders.php?last=1')
                    .then(r => r.json())
                    .then(row => { if (row?.order_id) loadOrderById(row.order_id); })
                    .catch(() => {});
            }
        });
    })();
    </script>

    <main class="form-stack">
<script>
(function () {
    const stepPageMap = {
        'marketing-intake': 'marketing-intake.php',
        'costing-review': 'costing-review.php',
        'production': 'production.php',
        'sales': 'sales.php',
        'marketing': 'marketing.php',
        'lc': 'lc.php',
        'exchange': 'exchange.php',
        'commercial': 'commercial.php',
        'packing': 'packing.php',
        'delivery': 'delivery.php',
        'truck': 'truck.php',
        'origin': 'origin.php',
        'beneficiary': 'beneficiary.php',
        'forwarding': 'forwarding.php',
        'bank-forwarding': 'bank-forwarding.php',
        'po-status': 'po-status.php'
    };

    function timeAgo(ts) {
        if (!ts) return '';
        const d = new Date(ts.replace(' ', 'T'));
        if (Number.isNaN(d.getTime())) return ts;
        const sec = Math.max(1, Math.floor((Date.now() - d.getTime()) / 1000));
        if (sec < 60) return sec + 's ago';
        if (sec < 3600) return Math.floor(sec / 60) + 'm ago';
        if (sec < 86400) return Math.floor(sec / 3600) + 'h ago';
        return Math.floor(sec / 86400) + 'd ago';
    }

    async function loadNotifications() {
        const countEl = document.getElementById('navNotifyCount');
        const listEl  = document.getElementById('navNotifyList');
        if (!countEl || !listEl) return;
        try {
            const res = await fetch(APP_BASE + '/api/notifications.php?limit=8');
            const json = await res.json();
            const items = json.items || [];
            const unread = Number(json.unreadCount || 0);

            countEl.textContent = unread > 99 ? '99+' : String(unread);
            countEl.style.display = unread > 0 ? 'inline-flex' : 'none';

            if (!items.length) {
                listEl.innerHTML = '<div class="nav-notify-empty">No notifications right now.</div>';
                return;
            }

            listEl.innerHTML = items.map(n => `
                <div class="nav-notify-item ${Number(n.is_read) ? '' : 'unread'}" data-id="${n.id}" data-order-id="${encodeURIComponent(n.order_id)}" data-step="${n.step_name}">
                    <div class="nav-notify-title">${escapeHtml(n.title || '')}</div>
                    <div class="nav-notify-msg">${escapeHtml(n.message || '')}</div>
                    <div class="nav-notify-meta">
                        <span>${escapeHtml(n.order_id || '')}</span>
                        <span>${escapeHtml((n.step_name || '').replace(/-/g, ' '))}</span>
                        <span>${escapeHtml(timeAgo(n.created_at))}</span>
                    </div>
                </div>
            `).join('');

            listEl.querySelectorAll('.nav-notify-item').forEach(item => {
                item.addEventListener('click', async function () {
                    const id = this.dataset.id;
                    const orderId = decodeURIComponent(this.dataset.orderId || '');
                    const step = this.dataset.step || '';
                    try {
                        await fetch(APP_BASE + '/api/notifications.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'mark_read', id })
                        });
                    } catch (_) {}

                    if (orderId) sessionStorage.setItem('ats_current_order_id', orderId);
                    const target = stepPageMap[step] || 'dashboard.php';
                    window.location.href = APP_BASE + '/pages/' + target;
                });
            });
        } catch (e) {
            listEl.innerHTML = '<div class="nav-notify-empty">Could not load notifications.</div>';
        }
    }

    function escapeHtml(s) {
        return String(s || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    window.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('navNotifyBtn');
        const panel = document.getElementById('navNotifyPanel');
        const markAllBtn = document.getElementById('navNotifyReadAll');
        if (!btn || !panel) return;

        loadNotifications();
        setInterval(loadNotifications, 30000);

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            panel.classList.toggle('open');
            if (panel.classList.contains('open')) loadNotifications();
        });

        document.addEventListener('click', function (e) {
            if (!panel.contains(e.target) && e.target !== btn) {
                panel.classList.remove('open');
            }
        });

        if (markAllBtn) {
            markAllBtn.addEventListener('click', async function (e) {
                e.preventDefault();
                try {
                    await fetch(APP_BASE + '/api/notifications.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'mark_all_read' })
                    });
                    loadNotifications();
                } catch (_) {}
            });
        }
    });
})();
</script>
