<?php
$pageTitle    = 'Dashboard';
$activePage   = 'dashboard';
$navSection   = 'order';
$pageSubtitle = 'All orders tracked by ZNZ ID — click Load to resume any order.';
include __DIR__ . '/../includes/header.php';
?>

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
        <div class="page-actions-left">
            <button type="button" class="primary-btn" id="dashNewOrderTop">+ New Order</button>
            <?php if (in_array($__user['role'] ?? '', ['commercial', 'commercial_dept', 'admin'], true)): ?>
            <button type="button" class="ghost-btn" id="dashNewPiTop" style="color:#4f46e5;border-color:#c7d2fe;">+ Start from PI</button>
            <?php endif; ?>
        </div>
    </div>
    <div class="packing-items-wrap">
        <table class="packing-items-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Created By</th>
                    <th>Customer</th>
                    <th>PO Number</th>
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
</section>

<script>
// Self-contained dashboard — does NOT depend on script.js loading.
(function () {
    const BASE = window.APP_BASE || ('/' + window.location.pathname.split('/')[1]);

    const STEP_LABELS = {
        'dashboard':'Dashboard','marketing-intake':'Marketing Intake','costing-review':'Costing Review',
        'sales':'PI','marketing':'LC','lc':'LC','po-overview':'PO Status','exchange':'Bill of Exchange',
        'commercial':'Commercial Invoice','packing':'Packing List','delivery':'Delivery Challan',
        'truck':'Truck Challan','origin':'Certificate of Origin','beneficiary':"Beneficiary's Certificate",
        'forwarding':'Forwarding','po-status':'Challan Sheet',
    };
    const STEP_ORDER = ['marketing-intake','costing-review','sales','lc','commercial','packing',
        'delivery','truck','origin','beneficiary','forwarding'];
    const STEP_PAGES = {
        'marketing-intake':'marketing-intake.php','costing-review':'costing-review.php','sales':'sales.php',
        'marketing':'lc.php','lc':'lc.php','po-overview':'po-overview.php','exchange':'exchange.php',
        'commercial':'commercial.php','packing':'packing.php','delivery':'delivery.php','truck':'truck.php',
        'origin':'origin.php','beneficiary':'beneficiary.php','forwarding':'forwarding.php','po-status':'po-status.php',
    };

    window.loadOrderFromDashboard = function (orderId, step) {
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
        if (!body) return;
        let orders = [];
        try {
            const res = await fetch(BASE + '/api/orders.php');
            if (res.ok) {
                const db = await res.json();
                orders = (db || []).map(o => ({
                    id: o.order_id, customer: o.customer_name, poNumber: o.po_number,
                    salesperson: o.salesperson, buyerCode: o.to_buyer, deliveryDate: o.delivery_date,
                    currentStep: o.current_step, savedAt: o.updated_at,
                    createdBy: o.created_by_name,
                    itemCount: o.item_count || 0, totalQty: o.total_qty || 0, totalVal: o.total_val || 0,
                }));
            }
        } catch (_) {}

        if (!orders.length) {
            body.innerHTML = '<tr><td colspan="11" class="dash-empty" style="text-align:center;padding:20px;color:#94a3b8;">No orders yet — click “+ New Order” to start.</td></tr>';
            return;
        }

        body.innerHTML = orders.map(o => {
            const stepLabel = STEP_LABELS[o.currentStep] || 'Marketing Intake';
            const stepIdx   = STEP_ORDER.indexOf(o.currentStep || 'marketing-intake');
            const pct       = Math.max(8, Math.round(((stepIdx + 1) / STEP_ORDER.length) * 100));
            const saved     = o.savedAt ? new Date(o.savedAt).toLocaleDateString('en-GB') : '-';
            const step      = o.currentStep || 'marketing-intake';
            return `<tr>
                <td><span class="znz-id" style="cursor:pointer;" onclick="loadOrderFromDashboard('${o.id}','${step}')">${o.id || '-'}</span></td>
                <td>${o.createdBy || '-'}</td>
                <td>${o.customer || '-'}</td>
                <td>${o.poNumber || '-'}</td>
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
                    <button class="primary-btn ghost-btn--sm" onclick="loadOrderFromDashboard('${o.id}','${step}')">▶ Go to ${stepLabel}</button>
                </td>
            </tr>`;
        }).join('');
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
