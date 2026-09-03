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
            <span id="notifLiveStatus" style="display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border:1.5px solid #dbeafe;border-radius:999px;background:#eff6ff;color:#1d4ed8;font-size:13px;font-weight:800;">
                <span style="width:8px;height:8px;border-radius:999px;background:#22c55e;"></span>
                Live
            </span>
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

    <div style="margin-bottom:16px;padding:14px 16px;border:1.5px solid #e0e7ff;border-radius:14px;background:#fff;display:grid;grid-template-columns:repeat(5,minmax(160px,1fr));gap:12px;">
        <label style="display:block;">
            <span style="display:block;margin-bottom:6px;font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;">Order</span>
            <input type="search" id="notifSearchOrder" class="form-control" placeholder="Search order no">
        </label>
        <label style="display:block;">
            <span style="display:block;margin-bottom:6px;font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;">PO</span>
            <input type="search" id="notifSearchPo" class="form-control" placeholder="Search PO">
        </label>
        <label style="display:block;">
            <span style="display:block;margin-bottom:6px;font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;">Customer</span>
            <input type="search" id="notifSearchCustomer" class="form-control" placeholder="Search customer">
        </label>
        <label style="display:block;">
            <span style="display:block;margin-bottom:6px;font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;">Buyer</span>
            <input type="search" id="notifSearchBuyer" class="form-control" placeholder="Search buyer">
        </label>
        <label style="display:block;">
            <span style="display:block;margin-bottom:6px;font-size:11px;font-weight:800;text-transform:uppercase;color:#64748b;">Sales Person</span>
            <input type="search" id="notifSearchSalesPerson" class="form-control" placeholder="Search sales person">
        </label>
    </div>

    <div id="notifBlockedCustomerPanel" style="display:none;margin-bottom:16px;padding:14px 16px;border:1.5px solid #e0e7ff;border-radius:14px;background:#fff;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
            <div>
                <div style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#6366f1;">Hidden Customers</div>
                <div style="font-size:13px;color:#64748b;margin-top:2px;">ERP notifications for these customers are hidden from Commercial worklists.</div>
            </div>
            <button type="button" class="ghost-btn" id="notifBlockedRefreshBtn" style="padding:7px 12px;font-size:12px;">Refresh</button>
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:12px;">
            <input type="text" id="notifBlockedCustomerInput" class="form-control" placeholder="Customer name to hide" style="flex:1;min-width:240px;">
            <button type="button" class="primary-btn" id="notifBlockedAddBtn">Add Customer</button>
        </div>
        <div id="notifBlockedMsg" style="min-height:16px;font-size:12px;color:#64748b;margin-bottom:8px;"></div>
        <div id="notifBlockedCustomerList" style="display:flex;gap:8px;flex-wrap:wrap;"></div>
    </div>

    <div class="packing-items-wrap">
        <table class="packing-items-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Order No</th>
                    <th>Customer</th>
                    <th>Buyer</th>
                    <th>Sales Person</th>
                    <th>PO</th>
                    <th>Item Name</th>
                    <th>Total Qty</th>
                    <th>Price</th>
                    <th>Value</th>
                    <th>Sent</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="notifTableBody">
                <tr><td colspan="12" style="text-align:center;color:#94a3b8;padding:28px;">Loading notifications...</td></tr>
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
    'commercial-pi': 'sales.php',
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

let notifItems = [];
let notifBlockedCustomers = [];
let notifLoading = false;
let notifAutoRefreshTimer = null;

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

function notifMoney(value) {
    const n = Number(value || 0);
    return Number.isFinite(n) ? '$' + n.toFixed(2) : notifEscape(value);
}

function notifNumber(value) {
    const n = Number(value || 0);
    return Number.isFinite(n) ? n.toLocaleString() : notifEscape(value);
}

function notifFilterValue(id) {
    return String(document.getElementById(id)?.value || '').trim().toLowerCase();
}

function notifMatches(value, needle) {
    if (!needle) return true;
    return String(value || '').toLowerCase().includes(needle);
}

function renderNotificationWorklist() {
    const body = document.getElementById('notifTableBody');
    const unreadEl = document.getElementById('notifUnreadCount');
    const orderQ = notifFilterValue('notifSearchOrder');
    const poQ = notifFilterValue('notifSearchPo');
    const customerQ = notifFilterValue('notifSearchCustomer');
    const buyerQ = notifFilterValue('notifSearchBuyer');
    const salesQ = notifFilterValue('notifSearchSalesPerson');
    const rows = notifItems.filter(n => {
        const orderText = [n.erp_order_no, n.order_id, n.title, n.message].join(' ');
        const poText = [n.customer_po_no, n.message].join(' ');
        return notifMatches(orderText, orderQ)
            && notifMatches(poText, poQ)
            && notifMatches([n.customer_name, n.message].join(' '), customerQ)
            && notifMatches([n.buyer, n.message].join(' '), buyerQ)
            && notifMatches(n.sales_person, salesQ);
    });
    if (unreadEl) unreadEl.textContent = String(rows.length);

    if (!rows.length) {
        body.innerHTML = '<tr><td colspan="12" style="text-align:center;color:#94a3b8;padding:28px;">No notifications found.</td></tr>';
        return;
    }

    body.innerHTML = rows.map(n => `
        <tr>
            <td>
                <span style="display:inline-block;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:800;${Number(n.is_read) ? 'background:#e2e8f0;color:#475569;' : 'background:#dbeafe;color:#1d4ed8;'}">
                    ${Number(n.is_read) ? 'Read' : 'Unread'}
                </span>
            </td>
            <td><strong>${notifEscape(n.erp_order_no ? 'ERP ' + n.erp_order_no : n.order_id)}</strong></td>
            <td>${notifEscape(n.customer_name || '-')}</td>
            <td>${notifEscape(n.buyer || '-')}</td>
            <td>${notifEscape(n.sales_person || '-')}</td>
            <td>${notifEscape(n.customer_po_no || '-')}</td>
            <td>${notifEscape(n.item_name || n.title || '-')}</td>
            <td style="text-align:right;">${n.total_qty ? notifNumber(n.total_qty) : '-'}</td>
            <td style="text-align:right;">${notifEscape(n.price || '-')}</td>
            <td style="text-align:right;">${n.total_value ? notifMoney(n.total_value) : '-'}</td>
            <td>${notifFormatDate(n.created_at)}</td>
            <td>
                <button type="button" class="primary-btn" style="padding:6px 12px;font-size:12px;" onclick="openNotificationStep('${notifEscape(n.id)}', '${encodeURIComponent(n.order_id || '')}', '${notifEscape(n.step_name || '')}', '${notifEscape(n.type || 'workflow')}', '${encodeURIComponent(n.erp_order_no || '')}')">
                    ${n.type === 'erp_order' ? 'Create Work Order' : (n.type === 'commercial_pi' ? 'Create Another PI' : 'Open')}
                </button>
            </td>
        </tr>
    `).join('');
}

function setNotifLiveStatus(text, tone = 'live') {
    const status = document.getElementById('notifLiveStatus');
    if (!status) return;
    const dotColor = tone === 'error' ? '#ef4444' : (tone === 'loading' ? '#f59e0b' : '#22c55e');
    status.innerHTML = `<span style="width:8px;height:8px;border-radius:999px;background:${dotColor};"></span>${notifEscape(text)}`;
}

async function loadNotificationWorklist(silent = false) {
    if (notifLoading) return;
    notifLoading = true;
    const body = document.getElementById('notifTableBody');
    const unreadEl = document.getElementById('notifUnreadCount');
    if (!silent) setNotifLiveStatus('Updating...', 'loading');
    try {
        const res = await fetch(APP_BASE + '/api/notifications.php?full=1');
        const json = await res.json();
        notifItems = json.items || [];
        unreadEl.textContent = String(notifItems.length);
        renderNotificationWorklist();
        setNotifLiveStatus('Live', 'live');
    } catch (e) {
        body.innerHTML = '<tr><td colspan="12" style="text-align:center;color:#dc2626;padding:28px;">Could not load notifications.</td></tr>';
        setNotifLiveStatus('Offline', 'error');
    } finally {
        notifLoading = false;
    }
}

function renderBlockedCustomers() {
    const list = document.getElementById('notifBlockedCustomerList');
    if (!list) return;
    if (!notifBlockedCustomers.length) {
        list.innerHTML = '<span style="font-size:12px;color:#94a3b8;">No hidden customers yet.</span>';
        return;
    }
    list.innerHTML = notifBlockedCustomers.map(customer => `
        <span style="display:inline-flex;align-items:center;gap:7px;padding:6px 9px;border-radius:999px;background:#eef2ff;color:#3730a3;font-size:12px;font-weight:700;">
            ${notifEscape(customer.customer_name || '')}
            <button type="button"
                    title="Remove"
                    onclick="removeBlockedCustomer(${Number(customer.id || 0)})"
                    style="border:none;background:#fff;color:#dc2626;border-radius:999px;width:20px;height:20px;line-height:18px;cursor:pointer;font-weight:800;">x</button>
        </span>
    `).join('');
}

async function loadBlockedCustomers() {
    const panel = document.getElementById('notifBlockedCustomerPanel');
    const msg = document.getElementById('notifBlockedMsg');
    try {
        const res = await fetch(APP_BASE + '/api/notifications.php?blocked_customers=1');
        if (res.status === 403) {
            if (panel) panel.style.display = 'none';
            return;
        }
        const json = await res.json();
        if (!res.ok || json.error) throw new Error(json.error || 'Could not load hidden customers.');
        notifBlockedCustomers = json.items || [];
        if (panel) panel.style.display = 'block';
        if (msg) msg.textContent = '';
        renderBlockedCustomers();
    } catch (e) {
        if (panel) panel.style.display = 'block';
        if (msg) {
            msg.style.color = '#dc2626';
            msg.textContent = e.message || 'Could not load hidden customers.';
        }
    }
}

async function addBlockedCustomer() {
    const input = document.getElementById('notifBlockedCustomerInput');
    const msg = document.getElementById('notifBlockedMsg');
    const customer = String(input?.value || '').trim();
    if (!customer) return;
    if (msg) {
        msg.style.color = '#64748b';
        msg.textContent = 'Saving...';
    }
    try {
        const res = await fetch(APP_BASE + '/api/notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add_blocked_customer', customer_name: customer })
        });
        const json = await res.json();
        if (!res.ok || json.error) throw new Error(json.error || 'Could not add customer.');
        notifBlockedCustomers = json.items || [];
        if (input) input.value = '';
        if (msg) {
            msg.style.color = '#16a34a';
            msg.textContent = 'Customer hidden from notifications.';
        }
        renderBlockedCustomers();
        loadNotificationWorklist();
    } catch (e) {
        if (msg) {
            msg.style.color = '#dc2626';
            msg.textContent = e.message || 'Could not add customer.';
        }
    }
}

async function removeBlockedCustomer(id) {
    const msg = document.getElementById('notifBlockedMsg');
    if (!id) return;
    if (msg) {
        msg.style.color = '#64748b';
        msg.textContent = 'Removing...';
    }
    try {
        const res = await fetch(APP_BASE + '/api/notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'remove_blocked_customer', id })
        });
        const json = await res.json();
        if (!res.ok || json.error) throw new Error(json.error || 'Could not remove customer.');
        notifBlockedCustomers = json.items || [];
        if (msg) {
            msg.style.color = '#16a34a';
            msg.textContent = 'Customer removed from hidden list.';
        }
        renderBlockedCustomers();
        loadNotificationWorklist();
    } catch (e) {
        if (msg) {
            msg.style.color = '#dc2626';
            msg.textContent = e.message || 'Could not remove customer.';
        }
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
    loadBlockedCustomers();
    notifAutoRefreshTimer = window.setInterval(() => loadNotificationWorklist(true), 15000);
    window.addEventListener('focus', () => loadNotificationWorklist(true));
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) loadNotificationWorklist(true);
    });
    document.getElementById('notifBlockedRefreshBtn')?.addEventListener('click', loadBlockedCustomers);
    document.getElementById('notifBlockedAddBtn')?.addEventListener('click', addBlockedCustomer);
    document.getElementById('notifBlockedCustomerInput')?.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            addBlockedCustomer();
        }
    });
    ['notifSearchOrder', 'notifSearchPo', 'notifSearchCustomer', 'notifSearchBuyer', 'notifSearchSalesPerson'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', renderNotificationWorklist);
    });
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
