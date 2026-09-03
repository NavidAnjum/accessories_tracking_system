<?php
$pageTitle    = 'Dashboard';
$activePage   = 'dashboard';
$navSection   = 'order';
$pageSubtitle = 'All orders tracked by ZNZ ID — click Load to resume any order.';
include __DIR__ . '/../includes/header.php';
$__u = currentUser();
?>
<script>window.__ATS_USER = { id: <?= (int)($__u['id'] ?? 0) ?>, role: '<?= htmlspecialchars($__u['role'] ?? '', ENT_QUOTES) ?>' };</script>

<style>
.dash-mobile-list { display: none; }
.dash-status-only {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 10px;
    border-radius: 10px;
    background: #f1f5f9;
    color: #475569;
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
}
.dash-status-id {
    cursor: default;
    opacity: 0.85;
}

@media screen and (max-width: 760px) {
    .dash-table-wrap { display: none; }
    .dash-mobile-list { display: grid; gap: 12px; }
    .dashboard-actions { display: grid; grid-template-columns: 1fr; gap: 8px; }
    .dashboard-actions .primary-btn,
    .dashboard-actions .ghost-btn { width: 100%; min-height: 44px; }
    .dashboard-empty {
        border: 1.5px dashed #cbd5e1;
        border-radius: 12px;
        background: #f8fafc;
        color: #64748b;
        padding: 18px;
        text-align: center;
        font-weight: 700;
    }
    .dash-order-card {
        border: 1.5px solid #dbe4ff;
        border-radius: 12px;
        background: #fff;
        padding: 14px;
        box-shadow: none;
    }
    .dash-order-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }
    .dash-order-id {
        display: inline-block;
        border: 0;
        font-family: monospace;
        font-size: 13px;
        font-weight: 800;
        color: #1f6feb;
        background: #eef4ff;
        padding: 4px 8px;
        border-radius: 7px;
        overflow-wrap: anywhere;
        text-align: left;
    }
    .dash-order-date { color: #64748b; font-size: 12px; white-space: nowrap; }
    .dash-order-main { display: grid; gap: 6px; margin-bottom: 12px; }
    .dash-order-customer { color: #111827; font-size: 16px; font-weight: 800; line-height: 1.35; }
    .dash-order-po { color: #475569; font-size: 13px; }
    .dash-order-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin: 12px 0;
    }
    .dash-order-field {
        background: #f8faff;
        border: 1px solid #e0e7ff;
        border-radius: 10px;
        padding: 8px 10px;
        min-width: 0;
    }
    .dash-order-field span {
        display: block;
        color: #64748b;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        margin-bottom: 2px;
    }
    .dash-order-field strong {
        display: block;
        color: #17202b;
        font-size: 13px;
        overflow-wrap: anywhere;
    }
    .dash-order-card .dash-step-wrap {
        min-width: 0;
        margin: 10px 0 12px;
    }
    .dash-order-card .primary-btn { width: 100%; }
    .dash-status-only.mobile {
        width: 100%;
        min-height: 44px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 14px;
    }
}

@media screen and (max-width: 420px) {
    .dash-order-grid { grid-template-columns: 1fr; }
}
</style>

<section class="form-card page-screen active" data-page="dashboard">
    <div class="section-head">
        <div class="section-title">
            <span class="section-tag">Home</span>
            <h2>Order Tracking Dashboard</h2>
        </div>
        <div class="section-summary">
            <strong>All Orders</strong>
            <span>Every order gets an auto-generated ZNZ ID. Track which workflow step each order is currently on.</span>
        </div>
    </div>
    <div class="page-actions compact-actions">
        <div class="page-actions-left dashboard-actions">
            <?php if (($__user['role'] ?? '') !== 'marketing'): ?>
            <button type="button" class="primary-btn" id="dashNewOrderTop">+ New Order</button>
            <?php endif; ?>
            <?php if (in_array($__user['role'] ?? '', ['commercial', 'commercial_dept', 'admin'], true)): ?>
            <button type="button" class="ghost-btn" id="dashNewPiTop" style="color:#4f46e5;border-color:#c7d2fe;">+ Start from PI</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="packing-items-wrap dash-table-wrap">
        <table class="packing-items-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Created By</th>
                    <th>Customer</th>
                    <th>PO Number</th>
                    <th>PI Number</th>
                    <th>LC Number</th>
                    <th>Sales Person</th>
                    <th>Buyer</th>
                    <th>Delivery Date</th>
                    <th>Items</th>
                    <th>Current Step</th>
                    <th>Last Saved</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="dashOrdersBody"></tbody>
        </table>
    </div>
    <div class="dash-mobile-list" id="dashMobileOrders"></div>
</section>

<script>
// Self-contained dashboard — does NOT depend on script.js loading.
(function () {
    const BASE = window.APP_BASE || ('/' + window.location.pathname.split('/')[1]);

    const STEP_LABELS = {
        'dashboard':'Dashboard','marketing-intake':'Marketing Intake','costing-review':'Costing Review',
        'sales':'PI','marketing':'Marketing','lc':'LC','po-overview':'PO Status','exchange':'Bill of Exchange',
        'commercial':'Commercial Invoice','packing':'Packing List','delivery':'Delivery Challan',
        'truck':'Truck Challan','origin':'Certificate of Origin','beneficiary':"Beneficiary's Certificate",
        'forwarding':'Forwarding','po-status':'Challan Sheet',
    };
    const STEP_ORDER = ['marketing-intake','costing-review','sales','marketing','lc','commercial','packing',
        'delivery','truck','origin','beneficiary','forwarding'];
    const STEP_PAGES = {
        'marketing-intake':'marketing-intake.php','costing-review':'costing-review.php','sales':'sales.php',
        'marketing':'marketing.php','lc':'lc.php','po-overview':'po-overview.php','exchange':'exchange.php',
        'commercial':'commercial.php','packing':'packing.php','delivery':'delivery.php','truck':'truck.php',
        'origin':'origin.php','beneficiary':'beneficiary.php','forwarding':'forwarding.php','po-status':'po-status.php',
    };

    function canOpenDashboardStep(step) {
        const role = (window.__ATS_USER?.role || '').toLowerCase();
        const normalizedStep = step || 'marketing-intake';
        if (role === 'marketing') {
            return ['marketing-intake', 'marketing'].includes(normalizedStep);
        }
        return true;
    }

    window.loadOrderFromDashboard = function (orderId, step) {
        if (!canOpenDashboardStep(step)) return;
        sessionStorage.setItem('ats_current_order_id', orderId);
        const page = STEP_PAGES[step || 'marketing-intake'] || 'marketing-intake.php';
        window.location.href = BASE + '/pages/' + page;
    };
    window.deleteOrderFromDashboard = function (orderId) {
        if (!confirm('Delete order ' + orderId + '?')) return;
        fetch(BASE + '/api/orders.php?order_id=' + encodeURIComponent(orderId), { method: 'DELETE' })
            .catch(() => {})
            .finally(() => renderDash());
    };

    async function renderDash() {
        const body = document.getElementById('dashOrdersBody');
        const mobileList = document.getElementById('dashMobileOrders');
        if (!body) return;
        let orders = [];
        try {
            const res = await fetch(BASE + '/api/orders.php');
            if (res.ok) {
                const db = await res.json();
                orders = (db || []).map(o => ({
                    id: o.order_id, customer: o.customer_name, poNumber: o.po_number,
                    piNumber: o.pi_number, lcNumber: o.lc_number,
                    marketingUserId: o.marketing_user_id,
                    salesperson: o.salesperson || o.marketing_user_name || '', buyerCode: o.to_buyer, deliveryDate: o.delivery_date,
                    currentStep: o.current_step, savedAt: o.updated_at,
                    createdBy: o.created_by_name,
                    itemCount: o.item_count || 0, totalQty: o.total_qty || 0, totalVal: o.total_val || 0,
                }));
            }
        } catch (_) {}

        // Marketing sees its worklist: orders sitting at a marketing step (matches
        // the notification worklist) plus any specifically assigned to this person.
        const U = window.__ATS_USER || {};
        if (U.role === 'marketing') {
            orders = orders.filter(o =>
                canOpenDashboardStep(o.currentStep) ||
                String(o.marketingUserId || '') === String(U.id)
            );
        }

        if (!orders.length) {
            body.innerHTML = '<tr><td colspan="13" class="dash-empty" style="text-align:center;padding:20px;color:#94a3b8;">No orders yet — click “+ New Order” to start.</td></tr>';
            if (mobileList) {
                mobileList.innerHTML = '<div class="dashboard-empty">No orders yet. Use New Order to start.</div>';
            }
            return;
        }

        body.innerHTML = orders.map(o => {
            const stepLabel = STEP_LABELS[o.currentStep] || 'Marketing Intake';
            const stepIdx   = STEP_ORDER.indexOf(o.currentStep || 'marketing-intake');
            const pct       = Math.max(8, Math.round(((stepIdx + 1) / STEP_ORDER.length) * 100));
            const saved     = o.savedAt ? new Date(o.savedAt).toLocaleDateString('en-GB') : '-';
            const step      = o.currentStep || 'marketing-intake';
            const canOpen   = canOpenDashboardStep(step);
            const orderCell = canOpen
                ? `<span class="znz-id" style="cursor:pointer;" onclick="loadOrderFromDashboard('${o.id}','${step}')">${o.id || '-'}</span>`
                : `<span class="znz-id dash-status-id">${o.id || '-'}</span>`;
            const actionCell = canOpen
                ? `<button class="primary-btn ghost-btn--sm" onclick="loadOrderFromDashboard('${o.id}','${step}')">Open ${stepLabel}</button>`
                : `<span class="dash-status-only">Currently at ${stepLabel}</span>`;
            return `<tr>
                <td>${orderCell}</td>
                <td>${o.createdBy || '-'}</td>
                <td>${o.customer || '-'}</td>
                <td>${o.poNumber || '-'}</td>
                <td>${o.piNumber || '-'}</td>
                <td>${o.lcNumber || '-'}</td>
                <td>${o.salesperson || '-'}</td>
                <td>${o.buyerCode || '-'}</td>
                <td>${o.deliveryDate || '-'}</td>
                <td>${o.itemCount || 0}</td>
                <td>
                    <div class="dash-step-wrap">
                        <span class="step-badge">${stepLabel}</span>
                        <div class="dash-progress"><div class="dash-progress-fill" style="width:${pct}%"></div></div>
                    </div>
                </td>
                <td>${saved}</td>
                <td class="dash-actions">
                    ${actionCell}
                </td>
            </tr>`;
        }).join('');

        if (mobileList) {
            mobileList.innerHTML = orders.map(o => {
                const stepLabel = STEP_LABELS[o.currentStep] || 'Marketing Intake';
                const stepIdx   = STEP_ORDER.indexOf(o.currentStep || 'marketing-intake');
                const pct       = Math.max(8, Math.round(((stepIdx + 1) / STEP_ORDER.length) * 100));
                const saved     = o.savedAt ? new Date(o.savedAt).toLocaleDateString('en-GB') : '-';
                const step      = o.currentStep || 'marketing-intake';
                const canOpen   = canOpenDashboardStep(step);
                const orderControl = canOpen
                    ? `<button type="button" class="dash-order-id" onclick="loadOrderFromDashboard('${o.id}','${step}')">${o.id || '-'}</button>`
                    : `<span class="dash-order-id dash-status-id">${o.id || '-'}</span>`;
                const actionControl = canOpen
                    ? `<button class="primary-btn" onclick="loadOrderFromDashboard('${o.id}','${step}')">Open ${stepLabel}</button>`
                    : `<div class="dash-status-only mobile">Currently at ${stepLabel}</div>`;
                return `
                <article class="dash-order-card">
                    <div class="dash-order-top">
                        ${orderControl}
                        <div class="dash-order-date">${saved}</div>
                    </div>
                    <div class="dash-order-main">
                        <div class="dash-order-customer">${o.customer || '-'}</div>
                        <div class="dash-order-po">PO: <strong>${o.poNumber || '-'}</strong></div>
                    </div>
                    <div class="dash-order-grid">
                        <div class="dash-order-field"><span>Created By</span><strong>${o.createdBy || '-'}</strong></div>
                        <div class="dash-order-field"><span>Buyer</span><strong>${o.buyerCode || '-'}</strong></div>
                        <div class="dash-order-field"><span>PI Number</span><strong>${o.piNumber || '-'}</strong></div>
                        <div class="dash-order-field"><span>LC Number</span><strong>${o.lcNumber || '-'}</strong></div>
                        <div class="dash-order-field"><span>Sales Person</span><strong>${o.salesperson || '-'}</strong></div>
                        <div class="dash-order-field"><span>Items</span><strong>${o.itemCount || 0}</strong></div>
                    </div>
                    <div class="dash-step-wrap">
                        <span class="step-badge">${stepLabel}</span>
                        <div class="dash-progress"><div class="dash-progress-fill" style="width:${pct}%"></div></div>
                    </div>
                    ${actionControl}
                </article>`;
            }).join('');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        renderDash();
        document.getElementById('dashNewOrderTop')?.addEventListener('click', function () {
            sessionStorage.removeItem('ats_current_order_id');
            sessionStorage.setItem('ats_new_order', '1'); // start a blank draft, no DB row yet
            window.location.href = BASE + '/pages/marketing-intake.php';
        });
        // Start directly from PI (commercial) — skips intake/costing/production
        document.getElementById('dashNewPiTop')?.addEventListener('click', function () {
            sessionStorage.removeItem('ats_current_order_id');
            sessionStorage.setItem('ats_new_order', '1'); // blank PI draft, order created on first save
            window.location.href = BASE + '/pages/sales.php';
        });
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
