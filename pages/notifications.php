<?php
$pageTitle = 'Notifications';
$activePage = 'notifications';
$navSection = 'order';
include __DIR__ . '/../includes/header.php';
?>

<section class="form-card page-screen active">
    <div class="section-head">
        <div class="section-title">
            <span class="section-tag">Workflow</span>
            <h2>Notification Worklist</h2>
        </div>
        <div class="page-actions-right" style="display:flex;gap:10px;align-items:center;">
            <button type="button" class="ghost-btn" id="notifRefreshBtn">Refresh</button>
            <button type="button" class="primary-btn" id="notifMarkAllBtn">Mark All Read</button>
        </div>
    </div>

    <div style="margin-bottom:16px;padding:14px 16px;border:1.5px solid #e0e7ff;border-radius:14px;background:#f8faff;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
        <div>
            <div style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#6366f1;">My Notifications</div>
            <div style="font-size:15px;font-weight:800;color:#1e1e2e;">Role-wise worklist for your current step access</div>
        </div>
        <div style="font-size:14px;color:#475569;">
            Active: <strong id="notifUnreadCount">0</strong>
        </div>
    </div>

    <div class="packing-items-wrap">
        <table class="packing-items-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Order ID</th>
                    <th>Step</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Sent</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="notifTableBody">
                <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:28px;">Loading notifications...</td></tr>
            </tbody>
        </table>
    </div>
</section>

<script>
const NOTIF_STEP_PAGE_MAP = {
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

function notifEscape(s) {
    return String(s || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function notifFormatDate(ts) {
    if (!ts) return '—';
    const d = new Date(ts.replace(' ', 'T'));
    return Number.isNaN(d.getTime()) ? ts : d.toLocaleString('en-GB', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
}

async function loadNotificationWorklist() {
    const body = document.getElementById('notifTableBody');
    const unreadEl = document.getElementById('notifUnreadCount');
    try {
        const res = await fetch(APP_BASE + '/api/notifications.php?full=1');
        const json = await res.json();
        const items = json.items || [];
        unreadEl.textContent = String(json.unreadCount || 0);

        if (!items.length) {
            body.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:28px;">No notifications found.</td></tr>';
            return;
        }

        body.innerHTML = items.map(n => `
            <tr>
                <td>
                    <span style="display:inline-block;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:800;${Number(n.is_read) ? 'background:#e2e8f0;color:#475569;' : 'background:#dbeafe;color:#1d4ed8;'}">
                        ${Number(n.is_read) ? 'Read' : 'Unread'}
                    </span>
                </td>
                <td><strong>${notifEscape(n.erp_order_no ? 'ERP ' + n.erp_order_no : n.order_id)}</strong></td>
                <td>${notifEscape((n.step_name || '').replace(/-/g, ' '))}</td>
                <td>${notifEscape(n.title)}</td>
                <td>${notifEscape(n.message)}</td>
                <td>${notifFormatDate(n.created_at)}</td>
                <td>
                    <button type="button" class="primary-btn" style="padding:6px 12px;font-size:12px;" onclick="openNotificationStep('${notifEscape(n.id)}', '${encodeURIComponent(n.order_id || '')}', '${notifEscape(n.step_name || '')}', '${notifEscape(n.type || 'workflow')}', '${encodeURIComponent(n.erp_order_no || '')}')">
                        ${n.type === 'erp_order' ? 'Create Work Order' : 'Open'}
                    </button>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        body.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#dc2626;padding:28px;">Could not load notifications.</td></tr>';
    }
}

async function openNotificationStep(id, encodedOrderId, step, type, encodedErpOrder) {
    if (type === 'erp_order') {
        const erpOrder = decodeURIComponent(encodedErpOrder || '');
        try {
            const response = await fetch(APP_BASE + '/api/erp_create_work_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ erp_order_no: erpOrder })
            });
            const result = await response.json();
            if (!response.ok || result.error) throw new Error(result.error || 'Could not create work order.');
            sessionStorage.setItem('ats_current_order_id', result.order_id);
            window.location.href = APP_BASE + '/pages/sales.php?erp_order=' + encodeURIComponent(erpOrder);
        } catch (error) {
            alert(error.message || 'Could not create work order.');
        }
        return;
    }
    try {
        await fetch(APP_BASE + '/api/notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'mark_read', id })
        });
    } catch (_) {}

    const orderId = decodeURIComponent(encodedOrderId || '');
    if (step === 'erp-order') {
        window.location.href = APP_BASE + '/pages/erp-live-orders-report.php?create_erp_order=' + encodeURIComponent(orderId);
        return;
    }
    if (orderId) sessionStorage.setItem('ats_current_order_id', orderId);
    window.location.href = APP_BASE + '/pages/' + (NOTIF_STEP_PAGE_MAP[step] || 'dashboard.php');
}

document.addEventListener('DOMContentLoaded', function () {
    loadNotificationWorklist();
    document.getElementById('notifRefreshBtn')?.addEventListener('click', loadNotificationWorklist);
    document.getElementById('notifMarkAllBtn')?.addEventListener('click', async function () {
        try {
            await fetch(APP_BASE + '/api/notifications.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'mark_all_read' })
            });
        } catch (_) {}
        loadNotificationWorklist();
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
