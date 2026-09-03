<?php
$pageTitle   = 'Marketing';
$activePage  = 'marketing';
$navSection  = 'order';
$pageSubtitle = 'Marketing reviews and approves the PI before it proceeds to LC.';
include __DIR__ . '/../includes/header.php';
?>

<style>
/* â”€â”€ Order Summary Panel â”€â”€ */
.mkt-summary {
    background: #fff; border: 1.5px solid #e0e3ff;
    border-radius: 16px; overflow: hidden; margin-bottom: 18px;
}
.mkt-summary-hdr {
    background: linear-gradient(135deg,#1e1e2e,#2d2d44);
    padding: 14px 22px; display: flex; align-items: center;
    justify-content: space-between; flex-wrap: wrap; gap: 10px;
}
.mkt-summary-hdr h3 { margin:0; color:#fff; font-size:15px; font-weight:800; }
.mkt-summary-hdr span { color:#94a3b8; font-size:12px; }
.mkt-summary-body { padding: 18px 22px; display:flex; flex-direction:column; gap:16px; }

/* Order meta chips */
.mkt-meta-row { display:flex; flex-wrap:wrap; gap:10px; }
.mkt-chip {
    background:#f8f9ff; border:1.5px solid #e0e3ff;
    border-radius:10px; padding:7px 14px;
}
.mkt-chip label { font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; display:block; margin-bottom:2px; }
.mkt-chip strong { font-size:13px; color:#1e1e2e; }

/* PI list */
.mkt-pi-section-title {
    font-size:11px; font-weight:800; color:#6366f1;
    text-transform:uppercase; letter-spacing:.08em;
    margin-bottom:6px;
}
.mkt-pi-card {
    border-radius:12px; overflow:hidden;
    border: 1.5px solid #e0e3ff; margin-bottom:10px;
}
.mkt-pi-card:last-child { margin-bottom:0; }
.mkt-pi-card-hdr {
    display:flex; align-items:center; gap:10px;
    padding:9px 14px; flex-wrap:wrap;
    cursor:pointer; user-select:none;
}
.mkt-pi-card-hdr:hover { filter:brightness(.97); }
.mkt-pi-card-hdr.hdr-master     { background:#ede9fe; }
.mkt-pi-card-hdr.hdr-included   { background:#f0fdf4; }
.mkt-pi-card-hdr.hdr-standalone { background:#fef9c3; }
.mkt-pi-badge { font-size:10px; font-weight:800; padding:2px 8px; border-radius:999px; flex-shrink:0; }
.mkt-pi-badge.b-master     { background:#7c3aed; color:#fff; }
.mkt-pi-badge.b-included   { background:#16a34a; color:#fff; }
.mkt-pi-badge.b-standalone { background:#d97706; color:#fff; }
.mkt-pi-num  { font-size:13px; font-weight:800; color:#1e1e2e; min-width:130px; }
.mkt-pi-meta { font-size:12px; color:#64748b; flex:1; }
.mkt-pi-val  { font-size:13px; font-weight:800; color:#4f46e5; white-space:nowrap; }
.mkt-pi-toggle { font-size:12px; color:#94a3b8; margin-left:4px; }

/* PO table inside PI card */
.mkt-po-table-wrap { padding:10px 14px 12px; background:#fafbff; border-top:1.5px solid #e8eaff; }
.mkt-po-table { width:100%; border-collapse:collapse; font-size:12px; }
.mkt-po-table th {
    background:#f1f5f9; padding:6px 8px; text-align:left;
    font-size:10px; font-weight:700; color:#64748b;
    border-bottom:1.5px solid #e2e8f0; white-space:nowrap;
}
.mkt-po-table td { padding:5px 8px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.mkt-po-table tr:last-child td { border-bottom:none; }
.mkt-po-table .td-val { font-weight:700; color:#4f46e5; text-align:right; }
.mkt-po-source { font-size:10px; color:#94a3b8; }

/* Grand total */
.mkt-grand-total {
    display:flex; align-items:center; justify-content:flex-end; gap:20px;
    background:#f8f9ff; border:1.5px solid #e0e3ff; border-radius:10px;
    padding:10px 18px; flex-wrap:wrap;
}
.mkt-gt-item { text-align:center; }
.mkt-gt-num  { font-size:16px; font-weight:800; color:#4f46e5; }
.mkt-gt-lbl  { font-size:10px; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; margin-top:1px; }

.mkt-empty { color:#94a3b8; font-size:13px; text-align:center; padding:16px 0; }
.mkt-sales-block {
    border:1.5px solid #e0e3ff; border-radius:12px;
    background:#fafbff; padding:14px 16px;
}
.mkt-sales-grid {
    display:grid; grid-template-columns:repeat(2,minmax(0,1fr));
    gap:12px; margin-bottom:12px;
}
.mkt-sales-field {
    background:#fff; border:1px solid #e8eaff; border-radius:10px; padding:10px 12px;
}
.mkt-sales-field.wide { grid-column:1 / -1; }
.mkt-sales-field label {
    display:block; font-size:10px; font-weight:700; color:#94a3b8;
    text-transform:uppercase; letter-spacing:.06em; margin-bottom:4px;
}
.mkt-sales-field div {
    font-size:13px; color:#1e1e2e; white-space:pre-wrap;
}
.mkt-sales-po-wrap { display:flex; flex-direction:column; gap:10px; }
.mkt-sales-po {
    background:#fff; border:1.5px solid #dbe4ff; border-radius:12px; padding:12px 14px;
}
.mkt-sales-po-top {
    display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:8px;
}
.mkt-sales-po-tag {
    background:#6366f1; color:#fff; font-size:10px; font-weight:800;
    border-radius:999px; padding:2px 8px;
}
.mkt-sales-po-meta {
    display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:8px;
}
.mkt-sales-po-meta div {
    background:#f8f9ff; border-radius:8px; padding:8px 10px; font-size:12px; color:#1e1e2e;
}
.mkt-sales-po-meta strong {
    display:block; font-size:10px; color:#94a3b8; text-transform:uppercase; margin-bottom:2px;
}
.mkt-review-panel {
    margin:0;
    border:0;
    border-radius:0;
    overflow:hidden;
    background:#fff;
}
.mkt-review-head {
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:16px;
    padding:0 0 12px;
    border-bottom:0;
    background:#fff;
}
.mkt-review-head > div:first-child { display:none; }
.mkt-review-head h3 { margin:0 0 4px; font-size:18px; color:#111827; }
.mkt-review-head p { margin:0; color:#64748b; font-size:13px; }
.mkt-review-actions { display:flex; gap:10px; flex-wrap:wrap; }
.mkt-pi-frame {
    display:block;
    width:100%;
    min-height:760px;
    border:1px solid #d9e1ea;
    background:#f8fafc;
}
.mkt-pi-scroll {
    width:100%;
    max-width:100%;
    min-width:0;
    overflow:auto;
    -webkit-overflow-scrolling:touch;
}
.mkt-review-note {
    padding:12px 18px;
    color:#475569;
    font-size:13px;
    border-top:1px solid #e0e7ff;
    background:#eef4ff;
}
[data-page="marketing"] > .section-head,
[data-page="marketing"] > .source-glance,
.mkt-summary,
.mkt-review-note {
    display:none !important;
}
@media (max-width: 900px) {
    .mkt-sales-grid, .mkt-sales-po-meta { grid-template-columns:1fr; }
    .mkt-review-head {
        align-items:stretch;
        flex-direction:column;
        padding:0 0 10px;
    }
    .mkt-review-actions,
    .mkt-review-actions .ghost-btn {
        width:100%;
    }
    .mkt-review-actions .ghost-btn {
        text-align:center;
    }
    /* Scale the fixed A4 PI to fit the phone width (no horizontal scroll). */
    .mkt-pi-scroll {
        position:relative;
        overflow:hidden;
        contain:inline-size;
    }
    .mkt-pi-frame {
        position:absolute;
        inset:0 auto auto 0;
        width:794px;               /* A4 width @96dpi */
        height:1123px;             /* A4 height */
        min-height:0;
        max-width:none;
        transform-origin:top left;
        transform:scale(var(--mkt-pi-scale, 1));
    }
    [data-page="marketing"] {
        padding-top:12px;
    }
}
@media (max-width: 640px) {
    [data-page="marketing"] {
        padding-left:10px;
        padding-right:10px;
    }
}
</style>

<!-- Order Summary Panel -->
<div class="mkt-summary" id="mktSummary" style="display:none;">
    <div class="mkt-summary-hdr">
        <h3>Order Summary</h3>
        <span id="mktOrderId">-</span>
    </div>
    <div class="mkt-summary-body">
        <!-- Meta chips -->
        <div class="mkt-meta-row" id="mktMetaRow"></div>

        <!-- Sales snapshot -->
        <div>
            <div class="mkt-pi-section-title">Sales Details</div>
            <div id="mktSalesSnapshot"><p class="mkt-empty">No saved Sales data yet.</p></div>
        </div>

        <!-- PIs -->
        <div>
            <div class="mkt-pi-section-title">Proforma Invoices</div>
            <div id="mktPiList"><p class="mkt-empty">No PIs saved for this order yet.</p></div>
        </div>

        <!-- Grand total -->
        <div class="mkt-grand-total" id="mktGrandTotal" style="display:none;">
            <div class="mkt-gt-item"><div class="mkt-gt-num" id="mktGtPis">0</div><div class="mkt-gt-lbl">PIs</div></div>
            <div class="mkt-gt-item"><div class="mkt-gt-num" id="mktGtPos">0</div><div class="mkt-gt-lbl">POs</div></div>
            <div class="mkt-gt-item"><div class="mkt-gt-num" id="mktGtQty">0</div><div class="mkt-gt-lbl">Total Qty</div></div>
            <div class="mkt-gt-item"><div class="mkt-gt-num" id="mktGtVal">$0.00</div><div class="mkt-gt-lbl">Total Value</div></div>
        </div>
    </div>
</div>

<section class="form-card" data-page="marketing">
    <div class="section-head">
        <div class="section-title">
            <span class="section-tag">Section 3</span>
            <h2>Marketing</h2>
        </div>
        <div class="section-summary">
            <strong>Owner</strong>
            <span>Marketing checks the PI before Commercial starts LC work.</span>
        </div>
    </div>
    <div class="source-glance">
        <div class="source-glance-item"><span>Matched Sales Order</span><strong data-bind="salesOrder">-</strong></div>
        <div class="source-glance-item"><span>Customer PO</span><strong data-bind="customerPo">-</strong></div>
        <div class="source-glance-item"><span>Buyer</span><strong data-bind="buyerName">-</strong></div>
        <div class="source-glance-item"><span>Customer</span><strong data-bind="customerName">-</strong></div>
        <div class="source-glance-item"><span>Consignee Address</span><strong data-pi-bind="buyerAddress">-</strong></div>
        <div class="source-glance-item"><span>Consignee Bank</span><strong data-pi-bind="consigneeBank">-</strong></div>
    </div>
    <div class="mkt-review-panel">
        <div class="mkt-review-head">
            <div>
                <h3>Submitted PI Review</h3>
                <p>Review the submitted PI exactly as Commercial prints it. No Marketing input is required here.</p>
            </div>
            <div class="mkt-review-actions">
                <a class="ghost-btn" id="mktOpenPiBtn" href="#" target="_blank" rel="noopener">Open Printable PI</a>
            </div>
        </div>
        <div class="mkt-pi-scroll">
            <iframe id="mktPiPreview" class="mkt-pi-frame" title="Submitted PI Preview"></iframe>
        </div>
        <div class="mkt-review-note">After checking the PI, approve it to send the work order back to PI for printing and Summary/Master PI work.</div>
    </div>
    <div class="page-actions">
        <div class="page-actions-left">
            <button type="button" class="ghost-btn js-prev-page" data-prev-page="sales">Previous</button>
        </div>
        <div class="page-actions-right">
            <button type="button" class="primary-btn" id="universalSaveBtn" onclick="approvePiAndReturnToPi()">Approve PI &amp; Send to PI</button>
        </div>
    </div>
</section>

<script>
function escapeHtml(val) {
    return String(val ?? '-')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function renderSalesSnapshot(salesData, order, mktData) {
    const holder = document.getElementById('mktSalesSnapshot');
    if (!holder) return;

    if (!salesData) {
        holder.innerHTML = '<p class="mkt-empty">No saved Sales data yet.</p>';
        return;
    }

    const pos = salesData.pos || [];
    const mktPos = mktData?.pos || [];
    const salesOrderVal = pos.map(po => po.salesOrder).filter(Boolean).join(', ') || order?.po_number || '-';
    const customerPoVal = pos.map(po => po.poNum).filter(Boolean).join(', ') || order?.po_number || '-';
    const buyerVal = [...new Set(pos.map(po => po.buyer).filter(Boolean))].join(', ') || order?.to_buyer || '-';

    const buildItemTable = (items, mktRows) => {
        const salesRows = (items || []).map((item, i) => `
            <tr>
                <td style="color:#64748b;text-align:center;">${i+1}</td>
                <td>${escapeHtml(item.desc || '-')}</td>
                <td style="text-align:right;">${parseFloat(item.qty||0).toLocaleString()}</td>
                <td style="text-align:right;">$${parseFloat(item.price||0).toFixed(4)}</td>
                <td style="text-align:right;font-weight:700;color:#4f46e5;">$${parseFloat(item.total||0).toFixed(2)}</td>
            </tr>`).join('');

        const intakeRows = (mktRows || []).map((row, i) => `
            <tr style="background:#f8fff8;">
                <td style="color:#64748b;text-align:center;">${i+1}</td>
                <td>${escapeHtml(row.itemName || '-')}<br><span style="font-size:10px;color:#94a3b8;">${escapeHtml(row.prodLine || '')}</span></td>
                <td style="text-align:right;">${parseFloat(row.qty||0).toLocaleString()}</td>
                <td style="text-align:right;">$${parseFloat(row.unitPrc||0).toFixed(4)}</td>
                <td style="text-align:right;font-weight:700;color:#16a34a;">$${(parseFloat(row.qty||0)*parseFloat(row.unitPrc||0)).toFixed(2)}</td>
            </tr>`).join('');

        if (!salesRows && !intakeRows) return '<tr><td colspan="5" style="color:#94a3b8;text-align:center;padding:10px;">No item rows</td></tr>';
        let out = '';
        if (salesRows) out += salesRows;
        if (intakeRows && !salesRows) out += intakeRows;
        return out;
    };

    holder.innerHTML = `
        <div class="mkt-sales-block">
            <div class="mkt-sales-grid">
                <div class="mkt-sales-field"><label>PI Number</label><div>${escapeHtml(salesData.piNum || '-')}</div></div>
                <div class="mkt-sales-field"><label>PI Date</label><div>${escapeHtml(salesData.piDate || '-')}</div></div>
                <div class="mkt-sales-field"><label>Customer</label><div>${escapeHtml(salesData.customer || order?.customer_name || '-')}</div></div>
                <div class="mkt-sales-field"><label>Product Line</label><div>${escapeHtml(salesData.productLine || '-')}</div></div>
                <div class="mkt-sales-field"><label>Matched Sales Order</label><div>${escapeHtml(salesOrderVal)}</div></div>
                <div class="mkt-sales-field"><label>Customer PO</label><div>${escapeHtml(customerPoVal)}</div></div>
                <div class="mkt-sales-field"><label>Buyer</label><div>${escapeHtml(buyerVal)}</div></div>
                <div class="mkt-sales-field"><label>Total POs</label><div>${escapeHtml(pos.length)}</div></div>
                <div class="mkt-sales-field wide"><label>Buyer / Consignee Address</label><div>${escapeHtml(salesData.buyerAddress || '-')}</div></div>
                <div class="mkt-sales-field wide"><label>Consignee Bank</label><div>${escapeHtml(salesData.consigneeBank || '-')}</div></div>
            </div>
            <div class="mkt-sales-po-wrap">
                ${pos.map((po, idx) => {
                    const mktPo = mktPos[idx] || null;
                    const itemCount = (po.items || []).length || (mktPo?.rows || []).length;
                    const poId = 'mktSalesPo_' + idx;
                    return `
                    <div class="mkt-sales-po">
                        <div class="mkt-sales-po-top" onclick="toggleMktSalesPo('${poId}')" style="cursor:pointer;">
                            <span class="mkt-sales-po-tag">PO ${idx + 1}</span>
                            <strong style="font-size:13px;color:#1e1e2e;">${escapeHtml(po.poNum || '-')}</strong>
                            <span style="font-size:12px;color:#64748b;">${escapeHtml(po.buyer || '-')}</span>
                            <span style="margin-left:auto;font-size:12px;color:#6366f1;font-weight:700;">${itemCount} item${itemCount!==1?'s':''}</span>
                            <span id="${poId}_arrow" style="font-size:12px;color:#94a3b8;">?</span>
                        </div>
                        <div class="mkt-sales-po-meta">
                            <div><strong>Sales Order</strong>${escapeHtml(po.salesOrder || '-')}</div>
                            <div><strong>Requested Date</strong>${escapeHtml(po.reqDate || '-')}</div>
                            <div><strong>Status</strong>${escapeHtml(po.status || '-')}</div>
                            <div><strong>Items</strong>${escapeHtml(itemCount)}</div>
                        </div>
                        <div id="${poId}" style="display:none;margin-top:10px;">
                            <table class="mkt-po-table" style="border:1.5px solid #d1d5ff;border-radius:8px;overflow:hidden;">
                                <thead>
                                    <tr>
                                        <th style="width:32px;">#</th>
                                        <th>Description / Item</th>
                                        <th style="text-align:right;">Qty</th>
                                        <th style="text-align:right;">Price $</th>
                                        <th style="text-align:right;">Amount $</th>
                                    </tr>
                                </thead>
                                <tbody>${buildItemTable(po.items, mktPo?.rows)}</tbody>
                            </table>
                        </div>
                    </div>`;
                }).join('') || '<p class="mkt-empty">No saved PO rows yet.</p>'}
            </div>
        </div>`;
}

function toggleMktSalesPo(id) {
    const body  = document.getElementById(id);
    const arrow = document.getElementById(id + '_arrow');
    if (!body) return;
    const open = body.style.display !== 'none';
    body.style.display = open ? 'none' : 'block';
    if (arrow) arrow.textContent = open ? '?' : '?';
}

function removeMarketingSalesOrderFields() {
    document.querySelectorAll('.source-glance-item').forEach(el => {
        const lbl = el.querySelector('span');
        if (lbl && lbl.textContent.trim().toLowerCase() === 'matched sales order') {
            el.remove();
        }
    });

    document.querySelectorAll('.mkt-sales-field').forEach(el => {
        const lbl = el.querySelector('label');
        if (lbl && lbl.textContent.trim().toLowerCase() === 'matched sales order') {
            el.remove();
        }
    });

    document.querySelectorAll('.mkt-sales-po-meta > div').forEach(el => {
        const lbl = el.querySelector('strong');
        if (lbl && lbl.textContent.trim().toLowerCase() === 'sales order') {
            el.remove();
        }
    });
}

// Map the order's PI type to its printable page + build the embed URL. The L/C
// term params aren't stored on the order, so use the same defaults the sales
// page uses (days 90, Sight, 5% tolerance, UD, NCC); hsCode is persisted.
function marketingPiUrl(orderId, salesData, embed) {
    const piType  = (salesData?.piType || 'single');
    const pageMap = { single: 'single-pi', summary: 'summary-pi', master: 'master-pi' };
    const page    = pageMap[piType] || 'single-pi';
    const hs      = salesData?.hsCode || '4819.10.00';
    const params = new URLSearchParams({
        order_id: orderId || '',
        days: '90',
        lctype: 'Sight',
        tol: '5',
        hs: hs,
        doc: 'UD',
        bank: 'ncc'
    });
    if (embed) params.set('embed', '1');
    return APP_BASE + '/pages/' + page + '.php?' + params.toString();
}

function setMarketingPiPreview(orderId, salesData) {
    const frame  = document.getElementById('mktPiPreview');
    const openBtn = document.getElementById('mktOpenPiBtn');
    if (!frame || !orderId) return;
    // The iframe shares sessionStorage (same origin) and auto-loads the current
    // order, so ensure the id is stored before it navigates.
    try { sessionStorage.setItem('ats_current_order_id', orderId); } catch (e) {}
    const embedUrl = marketingPiUrl(orderId, salesData, true);
    if (frame.getAttribute('src') !== embedUrl) frame.src = embedUrl;
    if (openBtn) openBtn.href = marketingPiUrl(orderId, salesData, false);
    frame.addEventListener('load', mktScheduleFit, { once: true });
    mktScheduleFit();
}

// On phones, scale the fixed-width A4 PI iframe down so the whole page fits the
// screen (no horizontal scrolling / cut-off). On desktop it stays full width.
// Screen-agnostic: driven by a ResizeObserver so it re-fits on any resize/rotate.
function mktFitPreview() {
    const scroll = document.querySelector('.mkt-pi-scroll');
    const frame  = document.getElementById('mktPiPreview');
    if (!scroll || !frame) return;
    const A4_W = 794, A4_H = 1123;
    const width = scroll.getBoundingClientRect().width || scroll.clientWidth || 0;
    if (width <= 0) return; // not laid out yet
    if (window.innerWidth <= 900) {
        const scale = Math.max(0.1, Math.min(1, width / A4_W));
        frame.style.setProperty('--mkt-pi-scale', scale);
        scroll.style.height = (A4_H * scale) + 'px';
    } else {
        frame.style.removeProperty('--mkt-pi-scale');
        scroll.style.height = '';
    }
}

// Recompute now, next frame, and shortly after — catches late layout/reflow.
function mktScheduleFit() {
    mktFitPreview();
    requestAnimationFrame(mktFitPreview);
    setTimeout(mktFitPreview, 250);
}

(function mktInitPreviewFit() {
    const start = function () {
        mktScheduleFit();
        const scroll = document.querySelector('.mkt-pi-scroll');
        if (scroll && window.ResizeObserver) {
            new ResizeObserver(function () { mktFitPreview(); }).observe(scroll);
        }
        window.addEventListener('resize', mktFitPreview);
        window.addEventListener('orientationchange', mktScheduleFit);
    };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

function printMarketingPi() {
    const frame = document.getElementById('mktPiPreview');
    if (frame && frame.contentWindow) {
        frame.contentWindow.focus();
        frame.contentWindow.print();
    } else {
        window.print();
    }
}

function loadMktSummary(orderId, order, salesData, mktData) {
    const panel = document.getElementById('mktSummary');
    if (!orderId) { panel.style.display = 'none'; return; }

    // Show panel immediately
    panel.style.display = 'block';
    document.getElementById('mktOrderId').textContent = orderId;

    // Populate source-glance fields
    if (order) {
        const binds = {
            salesOrder:   order.po_number      || '-',
            customerPo:   order.po_number      || '-',
            buyerName:    order.to_buyer       || '-',
            customerName: order.customer_name  || '-',
        };
        binds.salesOrder = salesData?.pos?.map(po => po.salesOrder).filter(Boolean).join(', ') || binds.salesOrder;
        binds.customerPo = salesData?.pos?.map(po => po.poNum).filter(Boolean).join(', ') || binds.customerPo;
        binds.buyerName = [...new Set((salesData?.pos || []).map(po => po.buyer).filter(Boolean))].join(', ') || binds.buyerName;
        Object.entries(binds).forEach(([key, val]) => {
            const el = document.querySelector('[data-bind="' + key + '"]');
            if (el) el.textContent = val;
        });
    }

    // Meta chips
    const metaRow = document.getElementById('mktMetaRow');
    const meta = [
        { label: 'Customer',    val: order?.customer_name  || '-' },
        { label: 'Buyer',       val: order?.to_buyer       || '-' },
        { label: 'PO Number',   val: order?.po_number      || '-' },
        { label: 'TRIMS / IPO', val: order?.trims_ipo      || '-' },
        { label: 'Step',        val: (order?.current_step  || '-').replace(/-/g,' ').replace(/\b\w/g,c=>c.toUpperCase()) },
        { label: 'Date',        val: order?.intake_date    || '-' },
    ];
    metaRow.innerHTML = meta.map(m => `
        <div class="mkt-chip">
            <label>${m.label}</label>
            <strong>${m.val}</strong>
        </div>`).join('');

    renderSalesSnapshot(salesData, order, mktData);
    removeMarketingSalesOrderFields();

    // Load the submitted PI into the read-only preview (single / summary / master).
    setMarketingPiPreview(orderId, salesData);

    // Fetch PIs for this order
    document.getElementById('mktPiList').innerHTML = '<p class="mkt-empty" style="color:#94a3b8;">Loading PIs...</p>';
    fetch(APP_BASE + '/api/pis.php?order_id=' + encodeURIComponent(orderId))
        .then(r => r.json())
        .then(pis => {
            const piList = document.getElementById('mktPiList');

            if (!pis || !pis.length) {
                piList.innerHTML = '<p class="mkt-empty">No PIs saved for this order yet.</p>';
                document.getElementById('mktGrandTotal').style.display = 'none';
                return;
            }

            const masters     = pis.filter(p => p.is_master);
            const individuals = pis.filter(p => !p.is_master);
            const includedNums = new Set(masters.flatMap(m => m.included_pis || []));
            const lastPiType = salesData?.piType || 'summary';
            const selectedPiNumbers = new Set((salesData?.selectedPiNumbers || []).filter(Boolean));

            let html = '';
            let grandQty = 0, grandVal = 0, grandPos = 0;

            // Render a PI card with full expandable PO + item rows
            function piCard(pi, cls, badgeCls, badgeLabel, subText) {
                const qty = parseFloat(pi.grand_qty || 0);
                const val = parseFloat(pi.grand_val || 0);
                grandQty += qty; grandVal += val;
                grandPos += (pi.pos || []).length;

                const cardId = 'mktPiCard_' + pi.id;

                // Build PO blocks - each PO has its own header + item rows table
                const poBlocks = (pi.pos || []).map((po, pIdx) => {
                    const poQty = parseFloat(po.qty || 0);
                    const poVal = parseFloat(po.val || 0);
                    const sourceBadge = po.sourcePi
                        ? `<span class="mkt-po-source" style="background:#ede9fe;color:#7c3aed;padding:1px 6px;border-radius:6px;font-size:10px;font-weight:700;">via ${po.sourcePi}</span>`
                        : '';

                    const itemRows = (po.items || []).map((item, i) => `
                        <tr>
                            <td style="color:#64748b;text-align:center;">${i + 1}</td>
                            <td>${item.desc || '-'}</td>
                            <td style="text-align:right;">${parseFloat(item.qty||0).toLocaleString()}</td>
                            <td style="text-align:right;">$${parseFloat(item.price||0).toFixed(4)}</td>
                            <td style="text-align:right;font-weight:700;color:#4f46e5;">$${parseFloat(item.total||0).toFixed(2)}</td>
                        </tr>`).join('');

                    return `
                    <div style="margin-bottom:${pIdx < (pi.pos||[]).length-1 ? '12px' : '0'};">
                        <div style="display:flex;align-items:center;gap:10px;background:#f1f5ff;border-radius:8px 8px 0 0;padding:8px 12px;border:1.5px solid #d1d5ff;border-bottom:none;">
                            <span style="background:#6366f1;color:#fff;font-size:10px;font-weight:800;padding:2px 8px;border-radius:999px;">PO ${pIdx+1}</span>
                            <strong style="font-size:13px;color:#1e1e2e;">${po.poNum || '-'}</strong>
                            ${sourceBadge}
                            <span style="font-size:12px;color:#64748b;margin-left:4px;">${po.buyer || ''}</span>
                            <span style="margin-left:auto;font-size:12px;color:#64748b;">${poQty.toLocaleString()} pcs</span>
                            <strong style="font-size:13px;color:#4f46e5;">$${poVal.toFixed(2)}</strong>
                        </div>
                        <table class="mkt-po-table" style="border:1.5px solid #d1d5ff;border-top:none;border-radius:0 0 8px 8px;overflow:hidden;">
                                <thead>
                                    <tr>
                                        <th style="width:32px;">#</th>
                                        <th>Description of Goods</th>
                                        <th style="text-align:right;">Quantity</th>
                                        <th style="text-align:right;">Price $</th>
                                        <th style="text-align:right;">Amount $</th>
                                    </tr>
                                </thead>
                                <tbody>
                                ${itemRows || '<tr><td colspan="5" style="color:#94a3b8;text-align:center;padding:10px;">No item rows</td></tr>'}
                                <tr style="background:#f8f9ff;border-top:2px solid #e0e3ff;">
                                    <td colspan="2" style="text-align:right;font-weight:700;color:#64748b;font-size:11px;">SUBTOTAL</td>
                                    <td style="text-align:right;font-weight:800;color:#1e1e2e;">${poQty.toLocaleString()}</td>
                                    <td></td>
                                    <td style="text-align:right;font-weight:800;color:#4f46e5;">$${poVal.toFixed(2)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>`;
                }).join('') || '<p style="color:#94a3b8;font-size:13px;padding:8px 0;">No POs in this PI</p>';

                return `
                <div class="mkt-pi-card">
                    <div class="mkt-pi-card-hdr ${cls}" onclick="toggleMktPiCard('${cardId}')">
                        <span class="mkt-pi-badge ${badgeCls}">${badgeLabel}</span>
                        <div class="mkt-pi-num">${pi.pi_number}</div>
                        <div class="mkt-pi-meta">
                            ${pi.customer || '-'} ? ${pi.product_line || ''} ? ${(pi.pos||[]).length} PO(s) ? ${qty.toLocaleString()} pcs
                            ${subText ? '<br><span style="font-size:11px;color:#7c3aed;">' + subText + '</span>' : ''}
                        </div>
                        <div class="mkt-pi-val">$${val.toFixed(2)}</div>
                        <span class="mkt-pi-toggle" id="${cardId}_arrow">?</span>
                    </div>
                    <div class="mkt-po-table-wrap" id="${cardId}" style="display:none;">
                        ${poBlocks}
                    </div>
                </div>`;
            }

            function customMasterCard(groups) {
                if (!groups || !groups.length) return '';
                const fakePi = {
                    id: 'custom_master',
                    pi_number: salesData?.piNum || (orderId + '-MASTER'),
                    customer: salesData?.customer || order?.customer_name || '-',
                    product_line: '',
                    grand_qty: salesData?.grandQty || 0,
                    grand_val: salesData?.grandVal || 0,
                    pos: groups.map((grp, idx) => {
                        let qty = 0;
                        let val = 0;
                        (grp.items || []).forEach(item => {
                            const itemQty = parseFloat(item.qty || 0);
                            const itemVal = parseFloat(item.total || (itemQty * parseFloat(item.price || item.unitPrice || 0))) || 0;
                            qty += itemQty;
                            val += itemVal;
                        });
                        return {
                            poNum: grp.poNum || ('Selected ' + (idx + 1)),
                            buyer: grp.sharedBuyer || salesData?.buyer || '',
                            qty,
                            val,
                            items: grp.items || []
                        };
                    })
                };
                return piCard(fakePi, 'hdr-master', 'b-master', 'MASTER', 'Last selected Master PI');
            }

            if (lastPiType === 'master' && Array.isArray(salesData?.masterPiSelection) && salesData.masterPiSelection.length) {
                html = customMasterCard(salesData.masterPiSelection);
            } else if (lastPiType === 'single') {
                const lastSingle = individuals.find(pi => pi.pi_number === salesData?.piNum)
                    || individuals[0]
                    || masters[0];
                if (lastSingle) {
                    const inMaster = includedNums.has(lastSingle.pi_number);
                    html = piCard(
                        lastSingle,
                        inMaster ? 'hdr-included' : 'hdr-standalone',
                        inMaster ? 'b-included' : 'b-standalone',
                        inMaster ? 'IN MASTER' : 'STANDALONE',
                        'Last selected Single PI'
                    );
                }
            } else {
                const summaryPis = selectedPiNumbers.size
                    ? individuals.filter(pi => selectedPiNumbers.has(pi.pi_number))
                    : individuals;
                summaryPis.forEach(pi => {
                    const inMaster = includedNums.has(pi.pi_number);
                    if (inMaster) {
                        html += piCard(pi, 'hdr-included', 'b-included', 'IN MASTER', null);
                    } else {
                        html += piCard(pi, 'hdr-standalone', 'b-standalone', 'STANDALONE', null);
                    }
                });
            }

            piList.innerHTML = html;

            // Grand total
            const gt = document.getElementById('mktGrandTotal');
            gt.style.display = 'flex';
            document.getElementById('mktGtPis').textContent = html ? (html.match(/mkt-pi-card/g) || []).length : 0;
            document.getElementById('mktGtPos').textContent = grandPos;
            document.getElementById('mktGtQty').textContent = grandQty.toLocaleString();
            document.getElementById('mktGtVal').textContent = '$' + grandVal.toFixed(2);
        })
        .catch(() => {
            document.getElementById('mktPiList').innerHTML = '<p class="mkt-empty">Could not load PIs - check server connection.</p>';
        });
}

function toggleMktPiCard(id) {
    const body  = document.getElementById(id);
    const arrow = document.getElementById(id + '_arrow');
    if (!body) return;
    const open = body.style.display !== 'none';
    body.style.display  = open ? 'none'  : 'block';
    if (arrow) arrow.textContent = open ? '?' : '?';
}

// API auth failures redirect to login.php (HTML); a server error may return an
// HTML page too. Detect that so the user sees the real cause, not "Unexpected
// token '<'". Returns the parsed JSON or throws a clear Error.
async function mktReadJson(response) {
    if (response.redirected && /\/login\.php/i.test(response.url || '')) {
        throw new Error('Your session has expired on the server. Please refresh the page and sign in again.');
    }
    const text = await response.text();
    try {
        return JSON.parse(text);
    } catch (e) {
        if (/login\.php|<!doctype|<html/i.test(text)) {
            throw new Error('Your session has expired on the server. Please refresh the page and sign in again.');
        }
        throw new Error('Server error ' + response.status + ': ' + text.slice(0, 200));
    }
}

async function approvePiAndReturnToPi() {
    const orderId  = window.getCurrentOrderId();
    const pageName = 'marketing';
    if (!orderId) { alert('No order loaded.'); return; }

    const btn = document.getElementById('universalSaveBtn');
    if (btn) { btn.textContent = ' Saving...'; btn.disabled = true; }

    try {
        const fields = {};
        document.querySelectorAll('[id]').forEach(el => {
            if (!el.id) return;
            if (el.tagName === 'INPUT' && el.type !== 'button' && el.type !== 'submit') fields[el.id] = el.value;
            else if (el.tagName === 'TEXTAREA' || el.tagName === 'SELECT') fields[el.id] = el.value;
        });
        const approvalRes = await fetch(APP_BASE + '/api/save_page.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({
                order_id: orderId,
                page_name: pageName,
                ...fields,
                marketingApproved: true,
                piApprovalStatus: 'approved',
                approvedAt: new Date().toISOString(),
            }),
        });
        const approvalJson = await mktReadJson(approvalRes);
        if (!approvalRes.ok || approvalJson.error) {
            throw new Error(approvalJson.error || 'Marketing approval could not be saved.');
        }
        // Marketing approval returns the order to PI so Commercial can print/create Summary/Master PI.
        const orderRes = await fetch(APP_BASE + '/api/orders.php?id=' + encodeURIComponent(orderId) + '&step=sales', { method: 'PUT' });
        const orderJson = await mktReadJson(orderRes);
        if (!orderRes.ok || orderJson.error) {
            throw new Error(orderJson.error || 'Order could not be returned to PI.');
        }
        if (btn) {
            btn.textContent = ' Approved - sent to PI';
            btn.style.background = '#16a34a';
            btn.disabled = false;
            setTimeout(() => { btn.textContent = 'Approve PI & Send to PI'; btn.style.background = ''; }, 3000);
        }
    } catch (e) {
        if (btn) { btn.textContent = 'Approve PI & Send to PI'; btn.disabled = false; }
        alert('Approval failed: ' + (e.message || 'Could not reach server.'));
    }
}

window.onOrderLoad = function(res) {
    const orderId = res.order?.order_id;
    loadMktSummary(orderId, res.order, res.pages?.sales || null, res.pages?.['marketing-intake'] || null);
};

window.onNewOrder = function(orderId) {
    loadMktSummary(orderId, null, null);
};

// Auto-load if order already in session
const _mktStoredId = sessionStorage.getItem('ats_current_order_id');
if (_mktStoredId) loadMktSummary(_mktStoredId, null, null);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>



