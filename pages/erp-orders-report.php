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
            <span>If no date is selected, the report shows today’s cached ERP orders.</span>
        </div>
    </div>

    <div class="erp-report-card">
        <div class="erp-report-head">
            <div>
                <div style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#6366f1;margin-bottom:6px;">Daily Report</div>
                <div style="font-size:15px;font-weight:800;color:#1f2937;">Browse all cached ERP orders for a selected date</div>
            </div>
            <div class="erp-report-filters">
                <input type="date" id="erpReportDate">
                <select id="erpReportDateField">
                    <option value="ordered_date">Ordered Date</option>
                    <option value="booked_date">Booked Date</option>
                    <option value="header_request_date">Request Date</option>
                    <option value="schedule_ship_date">Ship Date</option>
                </select>
                <button type="button" class="primary-btn" id="erpReportBtn" onclick="loadErpOrdersReport()">Load Report</button>
            </div>
        </div>
        <div class="erp-report-meta" id="erpReportMeta">Select a date to load ERP order details. Default is today.</div>
        <div class="erp-report-list" id="erpReportList">
            <div class="erp-report-empty">No report loaded yet.</div>
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

async function loadErpOrdersReport() {
    var dateInput = document.getElementById('erpReportDate');
    var fieldInput = document.getElementById('erpReportDateField');
    var meta = document.getElementById('erpReportMeta');
    var list = document.getElementById('erpReportList');
    var btn = document.getElementById('erpReportBtn');

    var selectedDate = (dateInput && dateInput.value) ? dateInput.value : new Date().toISOString().slice(0, 10);
    var selectedField = fieldInput ? fieldInput.value : 'ordered_date';

    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Loading...';
    }
    if (meta) meta.textContent = 'Loading ERP report for ' + selectedDate + '...';
    if (list) list.innerHTML = '<div class="erp-report-empty">Loading report...</div>';

    try {
        var res = await fetch(APP_BASE + '/api/erp_orders_report.php?date=' + encodeURIComponent(selectedDate) + '&date_field=' + encodeURIComponent(selectedField));
        var json = await res.json();

        if (json.error) {
            if (meta) meta.textContent = 'Could not load report.';
            if (list) list.innerHTML = '<div class="erp-report-empty" style="color:#ef4444;">' + erpReportEscape(json.error) + '</div>';
            return;
        }

        var groups = Array.isArray(json.results) ? json.results : [];
        if (meta) {
            meta.textContent = groups.length + ' order group(s), ' + (json.lineCount || 0) + ' line(s), qty ' + Number(json.totalQty || 0).toLocaleString() + ', value ' + erpReportMoney(json.totalValue || 0) + ' for ' + selectedDate + '.';
        }

        if (!groups.length) {
            if (list) list.innerHTML = '<div class="erp-report-empty">No cached ERP orders found for ' + erpReportEscape(selectedDate) + '.</div>';
            return;
        }

        list.innerHTML = groups.map(function (group) {
            var salesOrders = (group.salesOrders || []).map(function (so) {
                return '<span class="erp-report-chip">' + erpReportEscape(so) + '</span>';
            }).join('');

            var rows = (group.items || []).map(function (item) {
                return '<tr>'
                    + '<td>' + erpReportEscape(item.saleOrderNo || '-') + '</td>'
                    + '<td>' + erpReportEscape(item.itemCode || item.orderedItem || '-') + '</td>'
                    + '<td>' + erpReportEscape(item.description || '-') + '</td>'
                    + '<td>' + erpReportEscape(item.remarks || '-') + '</td>'
                    + '<td style="text-align:right;">' + erpReportEscape(item.qty || 0) + '</td>'
                    + '<td style="text-align:right;">' + erpReportEscape(item.price || 0) + '</td>'
                    + '<td style="text-align:right;">' + erpReportEscape(item.value || 0) + '</td>'
                    + '<td>' + erpReportEscape(item.delivery || '-') + '</td>'
                    + '<td>' + erpReportEscape(item.lineStatus || '-') + '</td>'
                    + '</tr>';
            }).join('');

            return '<div class="erp-report-group">'
                + '<div class="erp-report-group-head">'
                + '<div>'
                + '<div class="erp-report-po">' + erpReportEscape(group.customerPo || 'No PO') + '</div>'
                + '<div class="erp-report-sub"><strong>' + erpReportEscape(group.customerName || '-') + '</strong> · Buyer: ' + erpReportEscape(group.buyer || '-') + '</div>'
                + '<div class="erp-report-sub">Ordered: ' + erpReportEscape(erpReportDateOnly(group.orderedDate)) + ' · Booked: ' + erpReportEscape(erpReportDateOnly(group.bookedDate)) + ' · Request: ' + erpReportEscape(erpReportDateOnly(group.requestDate)) + ' · Ship: ' + erpReportEscape(erpReportDateOnly(group.shipDate)) + '</div>'
                + '<div class="erp-report-chips">' + salesOrders + '</div>'
                + '</div>'
                + '<div class="erp-report-stats">'
                + '<div class="erp-report-stat"><strong>' + erpReportEscape(group.lineCount || 0) + '</strong><span>Lines</span></div>'
                + '<div class="erp-report-stat"><strong>' + Number(group.totalQty || 0).toLocaleString() + '</strong><span>Total Qty</span></div>'
                + '<div class="erp-report-stat"><strong>' + erpReportMoney(group.totalValue || 0) + '</strong><span>Total Value</span></div>'
                + '</div>'
                + '</div>'
                + '<div class="erp-report-table-wrap"><table class="erp-report-table"><thead><tr><th>Sales Order</th><th>Item</th><th>Description</th><th>Remarks</th><th>Qty</th><th>Price</th><th>Value</th><th>Delivery</th><th>Status</th></tr></thead><tbody>' + rows + '</tbody></table></div>'
                + '</div>';
        }).join('');
    } catch (error) {
        if (meta) meta.textContent = 'Could not load report.';
        if (list) list.innerHTML = '<div class="erp-report-empty" style="color:#ef4444;">Could not reach report service.</div>';
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Load Report';
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var dateInput = document.getElementById('erpReportDate');
    if (dateInput && !dateInput.value) {
        dateInput.value = new Date().toISOString().slice(0, 10);
    }
    loadErpOrdersReport();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
