<?php
$pageTitle = 'ERP Orders Report';
$activePage = 'erp-orders-report';
$navSection = 'order';
include __DIR__ . '/../includes/header.php';
?>

<style>
.erp-report-card { background:#fff; border:1.5px solid #dbe3ff; border-radius:18px; overflow:hidden; }
.erp-report-head { display:flex; justify-content:space-between; align-items:flex-end; gap:14px; padding:18px 20px; border-bottom:1px solid #eef2ff; flex-wrap:wrap; }
.erp-report-filters { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.erp-report-filters input, .erp-report-filters select {
    padding:11px 13px; border:1.5px solid #dbe3ff; border-radius:12px; font-size:14px; background:#fff;
}
.erp-report-meta { padding:14px 20px; background:#f8faff; border-bottom:1px solid #eef2ff; font-size:13px; color:#64748b; }
.erp-report-list { padding:18px; display:flex; flex-direction:column; gap:14px; }
.erp-report-group { border:1.5px solid #dbe3ff; border-radius:16px; overflow:hidden; background:#fff; }
.erp-report-group-head { padding:14px 16px; background:#fbfcff; border-bottom:1px solid #eef2ff; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.erp-report-po { font-size:16px; font-weight:800; color:#1f2937; }
.erp-report-sub { font-size:13px; color:#64748b; margin-top:4px; }
.erp-report-chips { display:flex; gap:6px; flex-wrap:wrap; margin-top:8px; }
.erp-report-chip { display:inline-block; padding:4px 9px; border-radius:999px; background:#eef2ff; color:#4f46e5; font-size:11px; font-weight:700; }
.erp-report-stats { display:flex; gap:16px; flex-wrap:wrap; align-items:flex-start; }
.erp-report-stat { min-width:90px; text-align:right; }
.erp-report-stat strong { display:block; font-size:16px; color:#111827; }
.erp-report-stat span { font-size:11px; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em; }
.erp-report-table-wrap { overflow:auto; }
.erp-report-table { width:100%; border-collapse:collapse; font-size:12px; }
.erp-report-table th, .erp-report-table td { border:1px solid #e5e7eb; padding:8px 9px; vertical-align:top; }
.erp-report-table th { background:#f8fafc; color:#475569; font-size:11px; text-transform:uppercase; letter-spacing:.05em; }
.erp-report-empty { padding:36px 18px; text-align:center; color:#94a3b8; font-size:14px; }
@media (max-width: 900px) {
    .erp-report-group-head { flex-direction:column; }
    .erp-report-stat { text-align:left; }
}
</style>

<section class="form-card" data-page="erp-orders-report">
    <div class="section-head">
        <div class="section-title">
            <span class="section-tag">ERP Report</span>
            <h2>Orders By Date</h2>
        </div>
        <div class="section-summary">
            <strong>Saved ERP Orders</strong>
            <span>Filtered by ERP created date. Defaults to the last 10 days.</span>
        </div>
    </div>

    <div class="erp-report-card">
        <div class="erp-report-head">
            <div>
                <div style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#6366f1;margin-bottom:6px;">Created-Date Report</div>
                <div style="font-size:15px;font-weight:800;color:#1f2937;">Cached ERP orders by ERP created date</div>
            </div>
            <div class="erp-report-filters">
                <label style="font-size:12px;color:#64748b;font-weight:700;">From <input type="date" id="erpReportFrom"></label>
                <label style="font-size:12px;color:#64748b;font-weight:700;">To <input type="date" id="erpReportTo"></label>
                <button type="button" class="primary-btn" id="erpReportBtn" onclick="loadErpOrdersReport()">Load Report</button>
            </div>
        </div>
        <div class="erp-report-meta" id="erpReportMeta">Loading the last 10 days of ERP orders…</div>
        <div class="erp-report-table-wrap">
            <table class="erp-report-table" id="erpReportTable">
                <thead>
                    <tr>
                        <th>Created</th>
                        <th>Order No</th>
                        <th>PO</th>
                        <th>Item Name</th>
                        <th style="text-align:right;">Total Qty</th>
                        <th style="text-align:right;">Price</th>
                        <th style="text-align:right;">Value</th>
                    </tr>
                </thead>
                <tbody id="erpReportBody">
                    <tr><td colspan="7" class="erp-report-empty">No report loaded yet.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
function erpReportEscape(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function erpReportDateOnly(value) {
    var raw = String(value || '').trim();
    return raw ? raw.slice(0, 10) : '-';
}

function erpReportMoney(value) {
    var num = Number(value || 0);
    return '$' + num.toFixed(2);
}

function erpReportNum(value) {
    return Number(value || 0).toLocaleString();
}

async function loadErpOrdersReport() {
    var fromInput = document.getElementById('erpReportFrom');
    var toInput = document.getElementById('erpReportTo');
    var meta = document.getElementById('erpReportMeta');
    var body = document.getElementById('erpReportBody');
    var btn = document.getElementById('erpReportBtn');

    var fromDate = (fromInput && fromInput.value) ? fromInput.value : erpReportDaysAgo(9);
    var toDate = (toInput && toInput.value) ? toInput.value : erpReportToday();

    if (btn) { btn.disabled = true; btn.textContent = 'Loading...'; }
    if (meta) meta.textContent = 'Loading ERP orders from ' + fromDate + ' to ' + toDate + '...';
    if (body) body.innerHTML = '<tr><td colspan="7" class="erp-report-empty">Loading report...</td></tr>';

    try {
        var res = await fetch(APP_BASE + '/api/erp_orders_report.php?from=' + encodeURIComponent(fromDate) + '&to=' + encodeURIComponent(toDate));
        var json = await res.json();

        if (json.error) {
            if (meta) meta.textContent = 'Could not load report.';
            if (body) body.innerHTML = '<tr><td colspan="7" class="erp-report-empty" style="color:#ef4444;">' + erpReportEscape(json.error) + '</td></tr>';
            return;
        }

        var rows = Array.isArray(json.rows) ? json.rows : [];
        if (meta) {
            meta.textContent = erpReportNum(json.lineCount || 0) + ' line(s) · Total Qty ' + erpReportNum(json.totalQty || 0)
                + ' · Total Value ' + erpReportMoney(json.totalValue || 0)
                + ' · Created ' + erpReportEscape(fromDate) + ' → ' + erpReportEscape(toDate) + '.';
        }

        if (!rows.length) {
            if (body) body.innerHTML = '<tr><td colspan="7" class="erp-report-empty">No cached ERP orders created between ' + erpReportEscape(fromDate) + ' and ' + erpReportEscape(toDate) + '.</td></tr>';
            return;
        }

        var prevOrder = null;
        var html = rows.map(function (r) {
            var order = r.saleOrderNo || '-';
            var isNewOrder = order !== prevOrder;
            prevOrder = order;
            // Only show Created / Order / PO on the first row of each order block.
            var edge = isNewOrder ? 'border-top:2px solid #dbe3ff;' : '';
            var cell = function (content, extra) {
                var style = edge + (extra || '');
                return '<td' + (style ? ' style="' + style + '"' : '') + '>' + content + '</td>';
            };
            var right = 'text-align:right;';
            return '<tr>'
                + cell(isNewOrder ? erpReportEscape(erpReportDateOnly(r.createdDate)) : '')
                + cell(isNewOrder ? erpReportEscape(order) : '')
                + cell(isNewOrder ? erpReportEscape(r.customerPo || '-') : '')
                + cell(erpReportEscape(r.itemName || r.itemCode || '-'))
                + cell(erpReportNum(r.qty || 0), right)
                + cell(erpReportEscape(r.price || 0), right)
                + cell(erpReportMoney(r.value || 0), right)
                + '</tr>';
        }).join('');

        html += '<tr style="background:#f8fafc;font-weight:800;">'
            + '<td colspan="4" style="text-align:right;">Totals</td>'
            + '<td style="text-align:right;">' + erpReportNum(json.totalQty || 0) + '</td>'
            + '<td></td>'
            + '<td style="text-align:right;">' + erpReportMoney(json.totalValue || 0) + '</td>'
            + '</tr>';

        if (body) body.innerHTML = html;
    } catch (error) {
        if (meta) meta.textContent = 'Could not load report.';
        if (body) body.innerHTML = '<tr><td colspan="7" class="erp-report-empty" style="color:#ef4444;">Could not reach report service.</td></tr>';
    } finally {
        if (btn) { btn.disabled = false; btn.textContent = 'Load Report'; }
    }
}

function erpReportToday() {
    return new Date().toISOString().slice(0, 10);
}
function erpReportDaysAgo(days) {
    var d = new Date();
    d.setDate(d.getDate() - days);
    return d.toISOString().slice(0, 10);
}

document.addEventListener('DOMContentLoaded', function () {
    var fromInput = document.getElementById('erpReportFrom');
    var toInput = document.getElementById('erpReportTo');
    if (fromInput && !fromInput.value) fromInput.value = erpReportDaysAgo(9); // last 10 days inclusive
    if (toInput && !toInput.value) toInput.value = erpReportToday();
    loadErpOrdersReport();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
