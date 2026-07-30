<?php
$pageTitle   = 'Costing Review';
$activePage  = 'costing-review';
$navSection  = 'order';
include __DIR__ . '/../includes/header.php';
?>

<style>
/* ── Costing Review ── */
.cr-status-bar {
    display: flex;
    gap: 0;
    margin-bottom: 24px;
    border-radius: 14px;
    overflow: hidden;
    border: 1.5px solid #e0e3ff;
    background: #fff;
    box-shadow: 0 2px 8px rgba(99,102,241,.06);
}
.cr-status-item {
    flex: 1;
    padding: 14px 18px;
    border-right: 1.5px solid #e8eaff;
    min-width: 0;
}
.cr-status-item:last-child { border-right: none; }
.cr-status-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #94a3b8;
    margin-bottom: 5px;
}
.cr-status-value {
    font-size: 14px;
    font-weight: 700;
    color: #1e1e2e;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cr-status-item.status-col .cr-status-value {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.cr-status-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #f59e0b;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px #fef3c7;
}

/* Intake mirror */
.cr-intake-card {
    background: #f8f9ff;
    border: 1.5px solid #e0e3ff;
    border-radius: 14px;
    padding: 20px 22px;
    margin-bottom: 20px;
}
.cr-intake-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}
.cr-intake-badge {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #6366f1;
    background: #ede9fe;
    padding: 3px 10px;
    border-radius: 999px;
}
.cr-intake-title {
    font-size: 13px;
    font-weight: 700;
    color: #374151;
}

/* Read-only display fields */
.cr-field-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px 20px;
}
.cr-field {
    min-width: 0;
}
.cr-field-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #94a3b8;
    margin-bottom: 4px;
}
.cr-field-value {
    font-size: 13px;
    font-weight: 600;
    color: #1e1e2e;
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 12px;
    min-height: 36px;
    word-break: break-word;
}
.cr-field-value.empty { color: #94a3b8; font-weight: 400; font-style: italic; }
.cr-field.span-2 { grid-column: span 2; }
.cr-field.span-4 { grid-column: span 4; }

/* Items table */
.cr-table-card {
    background: #fff;
    border: 1.5px solid #e0e3ff;
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(99,102,241,.05);
}
.cr-table-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    background: #f8f9ff;
    border-bottom: 1.5px solid #e0e3ff;
    flex-wrap: wrap;
    gap: 10px;
}
.cr-table-title { font-size: 13px; font-weight: 700; color: #1e1e2e; }
.cr-table-totals { display: flex; gap: 20px; }
.cr-total-chip {
    font-size: 12px; font-weight: 700;
    background: #ede9fe; color: #4f46e5;
    padding: 4px 12px; border-radius: 999px;
}
.cr-table-wrap { overflow-x: auto; }
.cr-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.cr-table th {
    background: #f1f5f9;
    padding: 9px 10px;
    text-align: left;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #64748b;
    border-bottom: 1.5px solid #e2e8f0;
    white-space: nowrap;
}
.cr-table td {
    padding: 6px 8px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    color: #374151;
}
.cr-table tr:last-child td { border-bottom: none; }
.cr-table tr:hover td { background: #fafbff; }

.cr-table td input {
    width: 100%;
    padding: 5px 8px;
    border: 1.5px solid #e2e8f0;
    border-radius: 6px;
    font-size: 12px;
    outline: none;
    box-sizing: border-box;
    transition: border-color .15s;
}
.cr-table td input:focus { border-color: #6366f1; }
.cr-table td input[readonly] { background: #f8fafc; color: #64748b; }

/* Price status badge */
.cr-price-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}
.cr-price-badge.pending  { background: #fef3c7; color: #92400e; }
.cr-price-badge.approved { background: #dcfce7; color: #166534; }
.cr-price-badge.revised  { background: #dbeafe; color: #1d4ed8; }

/* Notes & actions */
.cr-notes-card {
    background: #fff;
    border: 1.5px solid #e0e3ff;
    border-radius: 14px;
    padding: 18px 22px;
    margin-bottom: 20px;
}
.cr-notes-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #64748b;
    margin-bottom: 8px;
}
.cr-notes-card textarea {
    width: 100%;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 13px;
    resize: vertical;
    outline: none;
    box-sizing: border-box;
    font-family: inherit;
    color: #374151;
    line-height: 1.6;
    transition: border-color .15s;
}
.cr-notes-card textarea:focus { border-color: #6366f1; }

/* Action buttons */
.cr-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.cr-btn-group { display: flex; gap: 10px; flex-wrap: wrap; }
</style>

<!-- Step-locked banner (shown when order has not yet reached costing) -->
<div id="stepLockedBanner" style="display:none;background:#fef3c7;border:1.5px solid #f59e0b;border-radius:12px;padding:14px 20px;margin-bottom:18px;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
    <div>
        <strong style="color:#92400e;">⚠ This order has not reached Costing Review yet.</strong>
        <div style="font-size:12px;color:#a16207;margin-top:3px;">Complete Marketing Intake first and click "Send To Costing →" to advance the order here.</div>
    </div>
    <a href="javascript:void(0)" onclick="window.location.href=APP_BASE+'/pages/marketing-intake.php'" style="font-size:12px;font-weight:700;color:#4f46e5;text-decoration:none;white-space:nowrap;">← Go to Marketing Intake</a>
</div>

<!-- Status bar -->
<div class="cr-status-bar">
    <div class="cr-status-item status-col">
        <div class="cr-status-label">Status</div>
        <div class="cr-status-value">
            <span class="cr-status-dot"></span>
            <span id="costingStatusText">Waiting For Costing</span>
        </div>
    </div>
    <div class="cr-status-item">
        <div class="cr-status-label">Customer</div>
        <div class="cr-status-value" id="costingCustomerText">—</div>
    </div>
    <div class="cr-status-item">
        <div class="cr-status-label">Sales Person</div>
        <div class="cr-status-value" id="costingSalespersonText">—</div>
    </div>
    <div class="cr-status-item">
        <div class="cr-status-label">PO</div>
        <div class="cr-status-value" id="costingPoText">—</div>
    </div>
    <div class="cr-status-item">
        <div class="cr-status-label">Buyer</div>
        <div class="cr-status-value" id="costingBuyerText">—</div>
    </div>
</div>

<!-- From Marketing Intake -->
<div class="cr-intake-card">
    <div class="cr-intake-header">
        <span class="cr-intake-badge">From Marketing Intake</span>
        <span class="cr-intake-title">Order Reference Details</span>
    </div>
    <div class="cr-field-grid">
        <div class="cr-field">
            <div class="cr-field-label">Date</div>
            <div class="cr-field-value" id="costingDate"><span class="empty">—</span></div>
        </div>
        <div class="cr-field">
            <div class="cr-field-label">TRIMS / IPO No.</div>
            <div class="cr-field-value" id="costingTrimsNo"><span class="empty">—</span></div>
        </div>
        <div class="cr-field">
            <div class="cr-field-label">Paper Quality</div>
            <div class="cr-field-value" id="costingPaperQuality"><span class="empty">—</span></div>
        </div>
        <div class="cr-field">
            <div class="cr-field-label">Without ARL</div>
            <div class="cr-field-value" id="costingWithoutArl"><span class="empty">—</span></div>
        </div>
        <div class="cr-field">
            <div class="cr-field-label">Buyer (End Buyer)</div>
            <div class="cr-field-value" id="costingBuyerName"><span class="empty">—</span></div>
        </div>
        <div class="cr-field">
            <div class="cr-field-label">Design</div>
            <div class="cr-field-value" id="costingDesign"><span class="empty">—</span></div>
        </div>
        <div class="cr-field">
            <div class="cr-field-label">Order No</div>
            <div class="cr-field-value" id="costingOrderNo"><span class="empty">—</span></div>
        </div>
        <div class="cr-field">
            <div class="cr-field-label">Type</div>
            <div class="cr-field-value" id="costingType"><span class="empty">—</span></div>
        </div>
        <div class="cr-field">
            <div class="cr-field-label">Delivery Date</div>
            <div class="cr-field-value" id="costingDeliveryDate"><span class="empty">—</span></div>
        </div>
        <div class="cr-field span-3">
            <div class="cr-field-label">Sub / Work Order Description</div>
            <div class="cr-field-value" id="costingSubject"><span class="empty">—</span></div>
        </div>
        <div class="cr-field span-4">
            <div class="cr-field-label">Notes (from Marketing Intake)</div>
            <div class="cr-field-value" id="costingMarketingNotes"><span class="empty">—</span></div>
        </div>
    </div>
</div>

<!-- Item Lines for Costing -->
<div class="cr-table-card">
    <div class="cr-table-head">
        <div class="cr-table-title">Item Lines — Price Review</div>
        <div class="cr-table-totals">
            <span class="cr-total-chip">Total Qty: <span id="crTotalQty">—</span></span>
            <span class="cr-total-chip">Total: $<span id="crTotalAmt">—</span></span>
        </div>
    </div>
    <div class="cr-table-wrap">
        <table class="cr-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Colour</th>
                    <th>Order No</th>
                    <th>Prod No</th>
                    <th>Batch</th>
                    <th>Product Line</th>
                    <th>Item Description</th>
                    <th>Art / Size</th>
                    <th>Spec 1</th>
                    <th>Spec 2</th>
                    <th>Qty</th>
                    <th>Marketing Price</th>
                    <th>Revised Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="costingItemsBody">
                <tr id="crNoDataRow">
                    <td colspan="14" style="text-align:center;padding:24px;color:#94a3b8;font-style:italic;">
                        Load an order to see item lines from Marketing Intake.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Costing Notes -->
<div class="cr-notes-card">
    <div class="cr-notes-label">Costing Notes</div>
    <textarea id="costingNotes" rows="3" placeholder="Add pricing notes, remarks, or revision reasons here…"></textarea>
</div>

<!-- Actions -->
<div class="cr-actions">
    <div class="cr-btn-group">
        <button type="button" class="ghost-btn js-prev-page" data-prev-page="marketing-intake">← Previous</button>
        <button type="button" class="ghost-btn" onclick="sendBack()" style="border-color:#fca5a5;color:#c0392b;">
            ↩ Send Back with Revised Price
        </button>
    </div>
    <div class="cr-btn-group">
        <button type="button" id="approveCostingBtn" class="primary-btn" onclick="approveCosting()" style="padding:10px 28px;">
            ✓ Approve &amp; Send to Production
        </button>
    </div>
</div>

<script>
const _CR_STEP_ORDER = ['marketing-intake','costing-review','production','sales','marketing','lc','po-overview','exchange','commercial','packing','delivery','truck','origin','beneficiary','forwarding','bank-forwarding','po-status'];

function updateStatus(rowNum) {
    const val  = document.getElementById('revPrice_' + rowNum)?.value?.trim();
    const cell = document.getElementById('statusCell_' + rowNum);
    if (!cell) return;
    cell.innerHTML = val
        ? '<span class="cr-price-badge revised">Revised</span>'
        : '<span class="cr-price-badge pending">Pending</span>';
}

function updateCrTotals(pos) {
    let tq = 0, tv = 0;
    (pos || []).forEach(po => {
        (po.rows || []).forEach(row => {
            tq += parseFloat(row.qty    || 0) || 0;
            tv += (parseFloat(row.qty   || 0) || 0) * (parseFloat(row.unitPrc || 0) || 0);
        });
    });
    const qEl = document.getElementById('crTotalQty');
    const aEl = document.getElementById('crTotalAmt');
    if (qEl) qEl.textContent = tq.toLocaleString();
    if (aEl) aEl.textContent = tv.toFixed(2);
}

window.onOrderLoad = function(res) {
    const d = res.pages?.['marketing-intake'];

    // Step gating
    const currentStep = res.order?.current_step || 'marketing-intake';
    const thisIdx = _CR_STEP_ORDER.indexOf('costing-review');
    const curIdx  = _CR_STEP_ORDER.indexOf(currentStep);
    const banner  = document.getElementById('stepLockedBanner');
    if (banner) banner.style.display = curIdx < thisIdx ? 'flex' : 'none';

    // If order has already passed costing review, show approved state and lock button
    const approveBtn = document.getElementById('approveCostingBtn');
    if (curIdx > thisIdx) {
        document.getElementById('costingStatusText').textContent = 'Approved';
        const dot = document.querySelector('.cr-status-dot');
        if (dot) { dot.style.background = '#22c55e'; dot.style.boxShadow = '0 0 0 3px #dcfce7'; }
        if (approveBtn) { approveBtn.disabled = true; approveBtn.style.opacity = '0.5'; approveBtn.style.cursor = 'not-allowed'; approveBtn.textContent = '✓ Already Approved'; }
    } else {
        document.getElementById('costingStatusText').textContent = 'Waiting For Costing';
        const dot = document.querySelector('.cr-status-dot');
        if (dot) { dot.style.background = ''; dot.style.boxShadow = ''; }
        if (approveBtn) { approveBtn.disabled = false; approveBtn.style.opacity = ''; approveBtn.style.cursor = ''; approveBtn.textContent = '✓ Approve & Send to Production'; }
    }

    if (!d) return;

    const set = (id, val) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.innerHTML = val ? String(val) : '<span class="empty">—</span>';
    };

    const firstPo = d.pos?.[0];

    // Status bar
    set('costingCustomerText',    d.customer);
    set('costingSalespersonText', d.salesPerson);
    set('costingPoText',    firstPo?.poNum);
    set('costingBuyerText', firstPo?.endBuyer);

    // Reference card
    set('costingDate',         d.intakeDate);
    set('costingTrimsNo',      firstPo?.trims);
    set('costingPaperQuality', d.paperQuality);
    set('costingWithoutArl',   firstPo != null ? (firstPo.withoutArl ? 'Yes' : 'No') : null);
    set('costingBuyerName',    firstPo?.endBuyer);
    set('costingDesign',       firstPo?.design);
    set('costingOrderNo',      firstPo?.orderNo);
    set('costingType',         firstPo?.type);
    set('costingDeliveryDate', firstPo?.delivery);
    set('costingSubject',      d.subject);

    // Restore saved costing notes and revised prices if any
    const cd = res.pages?.['costing-review'];
    if (cd?.notes) {
        const notesEl = document.getElementById('costingNotes');
        if (notesEl) notesEl.value = cd.notes;
    }
    window._pendingRevisedByRow = cd?.revisedByRow || null;

    // Populate item lines from intake PO rows
    const tbody = document.getElementById('costingItemsBody');
    if (tbody && d.pos?.length) {
        tbody.innerHTML = '';
        let rowNum = 0;
        d.pos.forEach(po => {
            (po.rows || []).forEach(row => {
                rowNum++;
                const tr = document.createElement('tr');
                const esc = v => String(v || '').replace(/"/g, '&quot;');

                // Derive spec display values from new cartonExtra / detailExtra
                let spec1val = row.spec1 || '';
                let spec2val = row.spec2 || '';
                if (row.cartonExtra) {
                    const cx = row.cartonExtra;
                    spec1val = [cx.ply ? cx.ply + 'Ply' : '', cx.paperGrade].filter(Boolean).join(' ');
                    spec2val = [cx.paperType, cx.printStatus].filter(Boolean).join(' · ');
                } else if (row.detailExtra) {
                    const vals = Object.values(row.detailExtra).filter(Boolean);
                    spec1val = vals[0] || '';
                    spec2val = vals[1] || '';
                }

                tr.innerHTML = `
                    <td style="text-align:center;color:#94a3b8;font-weight:700;">${rowNum}</td>
                    <td><input style="min-width:80px;" placeholder="—"></td>
                    <td><input readonly value="${esc(po.orderNo)}" style="min-width:80px;background:#f8fafc;"></td>
                    <td><input style="min-width:70px;" placeholder="—"></td>
                    <td><input style="min-width:60px;" placeholder="—"></td>
                    <td><input readonly value="${esc(row.prodLine)}" style="min-width:90px;background:#f8fafc;"></td>
                    <td><input readonly value="${esc(row.itemName)}" title="${esc(row.itemName)}" style="min-width:160px;background:#f8fafc;"></td>
                    <td><input readonly value="${esc(row.artSize)}" style="min-width:80px;background:#f8fafc;"></td>
                    <td><input readonly value="${esc(spec1val)}" style="min-width:90px;background:#f8fafc;"></td>
                    <td><input readonly value="${esc(spec2val)}" style="min-width:130px;background:#f8fafc;"></td>
                    <td><input readonly value="${esc(row.qty)}" style="min-width:60px;background:#f8fafc;"></td>
                    <td><input readonly value="${esc(row.unitPrc)}" style="min-width:80px;background:#fff8f0;color:#92400e;font-weight:700;"></td>
                    <td><input id="revPrice_${rowNum}" placeholder="Enter revised" style="min-width:100px;border-color:#c7d2fe;" oninput="updateStatus(${rowNum})"></td>
                    <td id="statusCell_${rowNum}"><span class="cr-price-badge pending">Pending</span></td>`;
                tbody.appendChild(tr);
            });
        });
        updateCrTotals(d.pos);

        // Restore previously saved revised prices
        if (window._pendingRevisedByRow) {
            Object.entries(window._pendingRevisedByRow).forEach(([rowNum, price]) => {
                const el = document.getElementById('revPrice_' + rowNum);
                if (el) { el.value = price; updateStatus(parseInt(rowNum)); }
            });
            window._pendingRevisedByRow = null;
        }
    }
};

function collectRevisedPrices() {
    const revisedByRow = {};
    document.querySelectorAll('#costingItemsBody tr').forEach((tr, i) => {
        const val = document.getElementById('revPrice_' + (i + 1))?.value?.trim();
        if (val) revisedByRow[i + 1] = val;
    });
    return revisedByRow;
}

window.onNewOrder = function() {
    document.getElementById('costingItemsBody').innerHTML =
        '<tr id="crNoDataRow"><td colspan="14" style="text-align:center;padding:24px;color:#94a3b8;font-style:italic;">Load an order to see item lines from Marketing Intake.</td></tr>';
    ['costingCustomerText','costingSalespersonText','costingPoText','costingBuyerText'].forEach(id => {
        const el = document.getElementById(id); if (el) el.textContent = '—';
    });
    ['costingDate','costingTrimsNo','costingPaperQuality','costingWithoutArl','costingBuyerName',
     'costingDesign','costingOrderNo','costingType','costingDeliveryDate','costingSubject','costingMarketingNotes'].forEach(id => {
        const el = document.getElementById(id); if (el) el.innerHTML = '<span class="empty">—</span>';
    });
    const dot = document.querySelector('.cr-status-dot');
    if (dot) { dot.style.background = '#f59e0b'; dot.style.boxShadow = '0 0 0 3px #fef3c7'; }
    document.getElementById('costingStatusText').textContent = 'Waiting For Costing';
    const banner = document.getElementById('stepLockedBanner');
    if (banner) banner.style.display = 'none';
};

async function approveCosting() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('No order loaded. Please load an order first.'); return; }

    const notes = document.getElementById('costingNotes')?.value?.trim() || '';
    const revisedByRow = collectRevisedPrices();
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order_id: orderId, page_name: 'costing-review', notes, revisedByRow })
        });
        const r   = await fetch(APP_BASE + '/api/orders.php?id=' + encodeURIComponent(orderId) + '&step=production', { method: 'PUT' });
        const res = await r.json();
        if (!res.ok) { alert('Could not advance step.'); return; }
    } catch (e) { alert('Server error.'); return; }

    document.querySelectorAll('#costingItemsBody tr').forEach((tr, i) => {
        const cell = document.getElementById('statusCell_' + (i + 1));
        if (cell) cell.innerHTML = '<span class="cr-price-badge approved">Approved ✓</span>';
    });
    document.getElementById('costingStatusText').textContent = 'Approved';
    document.querySelector('.cr-status-dot').style.background = '#22c55e';
    document.querySelector('.cr-status-dot').style.boxShadow  = '0 0 0 3px #dcfce7';

    if (confirm('Costing approved! Go to Production now?')) {
        window.location.href = APP_BASE + '/pages/production.php';
    }
}

async function sendBack() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('No order loaded.'); return; }

    const notes = document.getElementById('costingNotes')?.value?.trim() || '';
    const revisedByRow = collectRevisedPrices();
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST', headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ order_id: orderId, page_name: 'costing-review', notes, revisedByRow })
        });
        const r   = await fetch(APP_BASE + '/api/orders.php?id=' + encodeURIComponent(orderId) + '&step=marketing-intake', { method: 'PUT' });
        const res = await r.json();
        if (!res.ok) { alert('Could not send back.'); return; }
    } catch (e) { alert('Server error.'); return; }

    document.getElementById('costingStatusText').textContent = 'Sent Back for Revision';
    document.querySelector('.cr-status-dot').style.background = '#3b82f6';
    document.querySelector('.cr-status-dot').style.boxShadow  = '0 0 0 3px #dbeafe';

    if (confirm('Order sent back to Marketing Intake. Go there now?')) {
        window.location.href = APP_BASE + '/pages/marketing-intake.php';
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
