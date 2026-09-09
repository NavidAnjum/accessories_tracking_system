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
.dash-download-pi {
    background:#16a34a !important;
    border-color:#16a34a !important;
    color:#fff !important;
}
.dash-download-pi:hover { background:#15803d !important; }
.dash-pi-panel{display:none;margin:0 0 18px;padding:16px;border:1.5px solid #c7d2fe;border-radius:14px;background:#f8faff}
.dash-pi-panel.open{display:block}
.dash-pi-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
.dash-pi-head h3{margin:0;color:#1e1b4b;font-size:17px}
.dash-pi-filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
.dash-pi-filter.active{background:#4f46e5!important;border-color:#4f46e5!important;color:#fff!important}
.dash-pi-table-wrap{overflow-x:auto;background:#fff;border:1px solid #e2e8f0;border-radius:10px}
.dash-pi-table{width:100%;border-collapse:collapse;font-size:12px}
.dash-pi-table th,.dash-pi-table td{padding:10px 12px;border-bottom:1px solid #e2e8f0;text-align:left;vertical-align:top;white-space:nowrap}
.dash-pi-table th{background:#eef2ff;color:#3730a3;font-size:10px;text-transform:uppercase;letter-spacing:.05em}
.dash-pi-status{display:inline-flex;padding:4px 8px;border-radius:999px;font-size:11px;font-weight:800}
.dash-pi-status.approved{background:#dcfce7;color:#166534}.dash-pi-status.pending{background:#fef3c7;color:#92400e}
.dash-pi-reassign{display:flex;align-items:center;gap:7px}.dash-pi-reassign select{min-width:165px;padding:7px 9px;border:1px solid #c7d2fe;border-radius:8px;background:#fff;font-size:12px}

@media screen and (max-width: 760px) {
    .dash-table-wrap { display: none; }
    .dash-mobile-list { display: grid; gap: 12px; }
    .dashboard-actions { display: grid; grid-template-columns: 1fr; gap: 8px; }
    .dashboard-actions .primary-btn,
    .dashboard-actions .ghost-btn { width: 100%; min-height: 44px; }
    .dash-pi-head{align-items:flex-start}.dash-pi-filters{display:grid;grid-template-columns:1fr}.dash-pi-filter{width:100%}
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
            <button type="button" class="ghost-btn" id="dashPiListTop" style="color:#166534;border-color:#86efac;">PI List</button>
        </div>
    </div>
    <div class="dash-pi-panel" id="dashPiListPanel">
        <div class="dash-pi-head">
            <div>
                <h3>PI List</h3>
                <div style="font-size:12px;color:#64748b;margin-top:3px;">Marketing approval and Commercial PDF creation times</div>
            </div>
            <button type="button" class="ghost-btn ghost-btn--sm" id="dashPiListClose">Close</button>
        </div>
        <div class="dash-pi-filters">
            <button type="button" class="ghost-btn dash-pi-filter active" data-pi-filter="approved">Approved by Marketing</button>
            <button type="button" class="ghost-btn dash-pi-filter" data-pi-filter="pending">Not Approved Yet</button>
        </div>
        <div class="dash-pi-table-wrap">
            <table class="dash-pi-table">
                <thead><tr><th>Order ID</th><th>PI Number</th><th>PI Type</th><th>Customer</th><th>Marketing Person</th><th>Status</th><th>Time of Approval</th><th>Commercial PDF Created</th><th>PDF Created By</th><th>Change Marketing</th></tr></thead>
                <tbody id="dashPiListBody"></tbody>
            </table>
        </div>
    </div>
    <div class="packing-items-wrap dash-table-wrap">
        <table class="packing-items-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>ERP Order</th>
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
// This page owns the dashboard render. Tell script.js's legacy renderDashboard()
// to stand down so the two don't fight over #dashOrdersBody (which hid the ERP
// Order column / × chips and left the old localStorage-only "Del" button).
window.__ATS_PHP_DASHBOARD = true;

// Self-contained dashboard — does NOT depend on script.js loading.
(function () {
    const BASE = window.APP_BASE || ('/' + window.location.pathname.split('/')[1]);
    let dashboardOrders = [];
    let piListFilter = 'approved';
    let dashboardMarketingUsers = [];

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
        sessionStorage.setItem('ats_open_order', '1'); // deliberate open → restore on the entry page
        const page = STEP_PAGES[step || 'marketing-intake'] || 'marketing-intake.php';
        window.location.href = BASE + '/pages/' + page;
    };

    // Open the saved PI in its own printable page, matching the Commercial
    // Invoice workflow. The PI page provides the Print / Save PDF action after
    // the order data has finished rendering.
    window.downloadPiFromDashboard = function (orderId, piType, hsCode) {
        const pageMap = { single:'single-pi.php', summary:'summary-pi.php', master:'master-pi.php' };
        const page = pageMap[String(piType || '').toLowerCase()] || pageMap.single;
        const params = new URLSearchParams({
            order_id: orderId || '', days:'90', lctype:'Sight', tol:'5',
            hs: hsCode || '4819.10.00', doc:'UD', bank:'ncc', dashboard_pi:'1'
        });
        try { sessionStorage.setItem('ats_current_order_id', orderId); } catch (_) {}
        const piWindow = window.open(BASE + '/pages/' + page + '?' + params.toString(), '_blank');
        if (!piWindow) alert('Please allow pop-ups to open the PI print page.');
    };
    // Unique name so script.js's localStorage-only deleteOrderFromDashboard (a
    // global function declaration) can't clobber this DB-backed delete.
    window.dashDeleteOrder = function (orderId) {
        if (!confirm('Delete the whole order ' + orderId + '? This cannot be undone.')) return;
        fetch(BASE + '/api/orders.php?order_id=' + encodeURIComponent(orderId), { method: 'DELETE' })
            .then(r => r.json())
            .then(res => { if (res && res.error) alert('Delete failed: ' + res.error); })
            .catch(() => alert('Could not reach server.'))
            .finally(() => renderDash());
    };

    function escAttr(s) { return String(s || '').replace(/'/g, "\\'").replace(/"/g, '&quot;'); }
    function escHtml(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
    }
    function formatPiTime(value) {
        if (!value) return '-';
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? escHtml(value) : date.toLocaleString('en-GB', {
            day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'
        });
    }
    function isCommercialUser() {
        return ['commercial','commercial_dept'].includes(String(window.__ATS_USER?.role || '').toLowerCase());
    }
    async function loadDashboardMarketingUsers() {
        if (!isCommercialUser()) return;
        try {
            const response = await fetch(BASE + '/api/users.php?role=marketing');
            const data = await response.json();
            dashboardMarketingUsers = Array.isArray(data) ? data : [];
        } catch (_) { dashboardMarketingUsers = []; }
    }
    function marketingChangeControl(order) {
        if (!isCommercialUser()) return '-';
        if (order.marketingApproved) return '<span style="color:#64748b;">Approval completed</span>';
        const options = dashboardMarketingUsers.map(user => {
            const selected = String(user.id) === String(order.marketingUserId || '') ? ' selected' : '';
            const team = user.team ? ` (${escHtml(user.team)})` : '';
            return `<option value="${Number(user.id)}"${selected}>${escHtml(user.name || '')}${team}</option>`;
        }).join('');
        return `<div class="dash-pi-reassign"><select id="dashMarketing_${escHtml(order.id)}"><option value="">Select Marketing</option>${options}</select><button type="button" class="ghost-btn ghost-btn--sm" onclick="reassignDashboardMarketing('${escAttr(order.id)}')">Change</button></div>`;
    }
    window.reassignDashboardMarketing = async function(orderId) {
        const select = document.getElementById('dashMarketing_' + orderId);
        const marketingUserId = Number(select?.value || 0);
        if (!marketingUserId) { alert('Select a Marketing person first.'); return; }
        try {
            const response = await fetch(BASE + '/api/reassign_marketing.php', {
                method:'POST', headers:{'Content-Type':'application/json'},
                body:JSON.stringify({order_id:orderId, marketing_user_id:marketingUserId})
            });
            const result = await response.json();
            if (!response.ok || result.error) throw new Error(result.error || 'Could not change Marketing person.');
            alert('Marketing person changed to ' + result.marketing_user_name + '.');
            await renderDash();
        } catch (error) { alert(error.message || 'Could not change Marketing person.'); }
    };
    function renderPiList() {
        const body = document.getElementById('dashPiListBody');
        if (!body) return;
        const rows = dashboardOrders.filter(o => {
            if (!String(o.piNumber || '').trim()) return false;
            return piListFilter === 'approved' ? o.marketingApproved : !o.marketingApproved;
        });
        if (!rows.length) {
            body.innerHTML = `<tr><td colspan="10" style="text-align:center;padding:22px;color:#64748b;">No ${piListFilter === 'approved' ? 'approved' : 'pending'} PIs found.</td></tr>`;
            return;
        }
        body.innerHTML = rows.map(o => `<tr>
            <td><span class="znz-id">${escHtml(o.id || '-')}</span></td>
            <td>${escHtml(o.piNumber || '-')}</td>
            <td>${escHtml(String(o.piType || 'single').replace(/^./, c => c.toUpperCase()))}</td>
            <td>${escHtml(o.customer || '-')}</td>
            <td>${escHtml(o.marketingUserName || '-')}</td>
            <td><span class="dash-pi-status ${o.marketingApproved ? 'approved' : 'pending'}">${o.marketingApproved ? 'Approved' : 'Not Approved Yet'}</span></td>
            <td>${formatPiTime(o.marketingApprovedAt)}</td>
            <td>${formatPiTime(o.commercialPdfCreatedAt)}</td>
            <td>${escHtml(o.commercialPdfCreatedBy || '-')}</td>
            <td>${marketingChangeControl(o)}</td>
        </tr>`).join('');
    }

    // ERP Order cell — each ERP number is a chip with an × to remove it from the order.
    function renderErpCell(orderId, erpOrderNo) {
        const nums = String(erpOrderNo || '').split(/\s*,\s*/).map(s => s.trim()).filter(Boolean);
        if (!nums.length) return '-';
        return nums.map(n =>
            `<span style="display:inline-flex;align-items:center;gap:4px;background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe;border-radius:999px;padding:1px 6px;margin:1px 3px 1px 0;font-size:11px;white-space:nowrap;">
                ${n}
                <button title="Remove this ERP order from ${escAttr(orderId)}"
                        onclick="removeErpFromOrder('${escAttr(orderId)}','${escAttr(n)}')"
                        style="border:none;background:none;color:#ef4444;cursor:pointer;font-size:13px;line-height:1;padding:0;">×</button>
            </span>`
        ).join('');
    }

    // Remove one ERP number from a work order (added by mistake). Unlinks the inbox
    // claim and strips it from the order's PI + sales snapshot.
    window.removeErpFromOrder = function (orderId, erpNo) {
        if (!confirm('Remove ERP sales order ' + erpNo + ' from ' + orderId + '?\n\nThis removes its PO block/items from the order and frees the ERP order for reuse.')) return;
        fetch(BASE + '/api/erp_order_unlink.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ work_order_id: orderId, sale_order_nos: [erpNo] })
        })
        .then(r => r.json())
        .then(res => {
            if (res.error) { alert('Could not remove: ' + res.error); return; }
            renderDash();
        })
        .catch(() => alert('Could not reach server.'))
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
                    id: o.order_id, erpOrderNo: o.erp_order_no || '', customer: o.customer_name, poNumber: o.po_number,
                    piNumber: o.pi_number, lcNumber: o.lc_number,
                    marketingUserId: o.marketing_user_id,
                    marketingUserName: o.marketing_user_name || '',
                    salesperson: o.salesperson || o.marketing_user_name || '', buyerCode: o.to_buyer, deliveryDate: o.delivery_date,
                    currentStep: o.current_step, savedAt: o.updated_at,
                    createdBy: o.created_by_name,
                    piType: o.pi_type || 'single', hsCode: o.hs_code || '',
                    marketingApproved: o.marketing_approved === true || o.marketing_approved === 1 || o.marketing_approved === '1',
                    marketingApprovedAt: o.marketing_approved_at || '',
                    commercialPdfCreatedAt: o.commercial_pdf_created_at || '',
                    commercialPdfCreatedBy: o.commercial_pdf_created_by || '',
                    itemCount: o.item_count || 0, totalQty: o.total_qty || 0, totalVal: o.total_val || 0,
                }));
            }
        } catch (_) {}

        // Marketing sees ONLY its own orders — those where THIS marketing person is
        // the selected approver. Do not show every order that merely sits at a
        // marketing step (that leaked other people's orders into each approver's view).
        const U = window.__ATS_USER || {};
        if (U.role === 'marketing') {
            orders = orders.filter(o =>
                String(o.marketingUserId || '') === String(U.id)
            );
        }
        dashboardOrders = orders;
        renderPiList();

        if (!orders.length) {
            body.innerHTML = '<tr><td colspan="14" class="dash-empty" style="text-align:center;padding:20px;color:#94a3b8;">No orders yet — click “+ New Order” to start.</td></tr>';
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
            const isMarketing = ['marketing','team_leader'].includes((window.__ATS_USER?.role || '').toLowerCase());
            const hasPi = String(o.piNumber || '').trim() !== '';
            const downloadAction = isMarketing && hasPi
                ? `<button class="primary-btn ghost-btn--sm dash-download-pi" onclick="downloadPiFromDashboard('${escAttr(o.id)}','${escAttr(o.piType)}','${escAttr(o.hsCode)}')">Print / Download PDF</button>`
                : '';
            const orderCell = canOpen
                ? `<span class="znz-id" style="cursor:pointer;" onclick="loadOrderFromDashboard('${o.id}','${step}')">${o.id || '-'}</span>`
                : `<span class="znz-id dash-status-id">${o.id || '-'}</span>`;
            const actionCell = canOpen
                ? `<button class="primary-btn ghost-btn--sm" onclick="loadOrderFromDashboard('${o.id}','${step}')">Open ${stepLabel}</button>`
                : `<span class="dash-status-only">Currently at ${stepLabel}</span>`;
            return `<tr>
                <td>${orderCell}</td>
                <td>${renderErpCell(o.id, o.erpOrderNo)}</td>
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
                    ${downloadAction}
                    <button class="ghost-btn ghost-btn--sm" title="Delete this whole order"
                            onclick="dashDeleteOrder('${o.id}')"
                            style="color:#ef4444;border-color:#fca5a5;margin-left:6px;">Delete</button>
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
                const isMarketing = ['marketing','team_leader'].includes((window.__ATS_USER?.role || '').toLowerCase());
                const hasPi = String(o.piNumber || '').trim() !== '';
                const downloadControl = isMarketing && hasPi
                    ? `<button class="primary-btn dash-download-pi" onclick="downloadPiFromDashboard('${escAttr(o.id)}','${escAttr(o.piType)}','${escAttr(o.hsCode)}')">Print / Download PDF</button>`
                    : '';
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
                        <div class="dash-order-field"><span>ERP Order</span><strong>${o.erpOrderNo || '-'}</strong></div>
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
                    ${downloadControl}
                </article>`;
            }).join('');
        }
    }

    // Expose so any delete/unlink handler (incl. script.js's global one) can
    // re-render the table without a manual page refresh.
    window.renderDash = renderDash;

    document.addEventListener('DOMContentLoaded', function () {
        loadDashboardMarketingUsers().then(renderDash);
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
        document.getElementById('dashPiListTop')?.addEventListener('click', function () {
            const panel = document.getElementById('dashPiListPanel');
            panel?.classList.add('open');
            renderPiList();
            panel?.scrollIntoView({behavior:'smooth', block:'start'});
        });
        document.getElementById('dashPiListClose')?.addEventListener('click', function () {
            document.getElementById('dashPiListPanel')?.classList.remove('open');
        });
        document.querySelectorAll('.dash-pi-filter').forEach(button => {
            button.addEventListener('click', function () {
                piListFilter = this.dataset.piFilter || 'approved';
                document.querySelectorAll('.dash-pi-filter').forEach(btn => btn.classList.toggle('active', btn === this));
                renderPiList();
            });
        });
    });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
