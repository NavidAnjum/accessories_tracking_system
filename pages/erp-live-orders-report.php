<?php
$pageTitle = 'ERP Orders Report';
$activePage = 'erp-orders-report';
$navSection = 'order';
include __DIR__ . '/../includes/header.php';
?>

<style>
/* This is a standalone ERP report — hide the order-workflow chrome (Work Order bar + Sales Items panel). */
#orderIdBar, #sharedOrderItemsPanel { display:none !important; }
/* Use the full page width for this report (override the app's 1380px content cap). */
.app-shell { max-width:100% !important; }
.form-card[data-page="erp-live-orders-report"] { max-width:100% !important; }
.erp-live-card { background:#fff; border:1.5px solid #dbe3ff; border-radius:18px; overflow:hidden; }
.erp-live-head { display:flex; justify-content:space-between; align-items:flex-end; gap:14px; padding:18px 20px; border-bottom:1px solid #eef2ff; flex-wrap:wrap; }
.erp-live-filters { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.erp-live-filters input, .erp-live-filters select { padding:11px 13px; border:1.5px solid #dbe3ff; border-radius:12px; font-size:14px; background:#fff; }
.erp-status-pill { display:inline-flex; padding:4px 9px; border-radius:999px; font-size:11px; font-weight:800; }
.erp-status-new { background:#dcfce7; color:#166534; }
.erp-status-created { background:#e2e8f0; color:#475569; }
.erp-row-action { padding:6px 10px; border:1px solid #c7d2fe; border-radius:8px; background:#fff; color:#4338ca; cursor:pointer; font-weight:700; white-space:nowrap; }
.erp-live-meta { padding:16px 20px; background:#f8faff; border-bottom:1px solid #dbe3ff; font-size:14px; color:#64748b; }
.erp-live-table-wrap { overflow:auto; }
.erp-live-table { width:100%; border-collapse:collapse; font-size:13px; }
.erp-live-table th, .erp-live-table td { border:1px solid #dbe3ff; padding:11px 12px; vertical-align:top; }
.erp-live-table th { background:#f8fafc; color:#334155; font-size:12px; text-transform:uppercase; letter-spacing:.04em; }
.erp-live-table td.num, .erp-live-table th.num { text-align:right; white-space:nowrap; }
/* Keep long text (buyer/PO/item) from blowing out the table width */
.erp-live-table td { word-break:break-word; overflow-wrap:anywhere; }
.erp-live-table th:nth-child(1), .erp-live-table td:nth-child(1) { white-space:nowrap; }  /* Created */
.erp-live-table th:nth-child(2), .erp-live-table td:nth-child(2) { white-space:nowrap; }  /* Order No */
.erp-live-table th:nth-child(3), .erp-live-table td:nth-child(3) { min-width:130px; max-width:170px; }  /* Customer */
.erp-live-table th:nth-child(4), .erp-live-table td:nth-child(4) { min-width:150px; max-width:240px; }  /* Buyer */
.erp-live-table th:nth-child(5), .erp-live-table td:nth-child(5) { max-width:120px; }  /* Sales Person */
.erp-live-table th:nth-child(6), .erp-live-table td:nth-child(6) { min-width:120px; max-width:180px; }  /* PO */
.erp-live-table th:nth-child(7), .erp-live-table td:nth-child(7) { min-width:150px; max-width:230px; }  /* Item Name */
.erp-live-empty { padding:36px 18px !important; text-align:center !important; color:#94a3b8; font-size:14px; }
.erp-live-pager { display:none; align-items:center; justify-content:flex-end; gap:10px; padding:12px 18px; border-top:1px solid #dbe3ff; background:#f8faff; }
.erp-live-pager button { padding:7px 12px; border:1px solid #c7d2fe; border-radius:8px; background:#fff; color:#4f46e5; cursor:pointer; }
.erp-live-pager button:disabled { opacity:.45; cursor:not-allowed; }
.erp-live-page-info { min-width:130px; text-align:center; color:#64748b; font-size:12px; font-weight:700; }
</style>

<section class="form-card" data-page="erp-live-orders-report">
    <div class="erp-live-card">
        <div class="erp-live-head">
            <div class="erp-live-filters">
                <label style="font-size:12px;color:#64748b;font-weight:700;">From <input type="date" id="erpLiveFrom"></label>
                <label style="font-size:12px;color:#64748b;font-weight:700;">To <input type="date" id="erpLiveTo"></label>
                <label style="font-size:12px;color:#64748b;font-weight:700;">Work Order
                    <select id="erpLiveStatusFilter" onchange="erpLiveApplyFilter()">
                        <option value="all">All</option>
                        <option value="new">Not Created</option>
                        <option value="created">Already Created</option>
                    </select>
                </label>
                <label style="font-size:12px;color:#64748b;font-weight:700;">Read
                    <select id="erpLiveReadFilter" onchange="erpLiveApplyFilter()">
                        <option value="all">All</option>
                        <option value="unread">Unread</option>
                        <option value="read">Read</option>
                    </select>
                </label>
                <label style="font-size:12px;color:#64748b;font-weight:700;">Customer
                    <input type="text" id="erpLiveCustSearch" placeholder="Search customer" oninput="erpLiveApplyFilter()" style="width:150px;">
                </label>
                <label style="font-size:12px;color:#64748b;font-weight:700;">Buyer
                    <input type="text" id="erpLiveBuyerSearch" placeholder="Search buyer" oninput="erpLiveApplyFilter()" style="width:120px;">
                </label>
                <label style="font-size:12px;color:#64748b;font-weight:700;">Sales Person
                    <input type="text" id="erpLiveSalesSearch" placeholder="Search sales person" oninput="erpLiveApplyFilter()" style="width:130px;">
                </label>
                <label style="font-size:12px;color:#64748b;font-weight:700;">PO
                    <input type="text" id="erpLivePoSearch" placeholder="Search PO" oninput="erpLiveApplyFilter()" style="width:120px;">
                </label>
                <button type="button" class="primary-btn" id="erpLiveBtn" onclick="loadErpLiveOrdersReport()">Load Report</button>
            </div>
        </div>
        <div class="erp-live-meta" id="erpLiveMeta">Loading live ERP orders...</div>
        <div class="erp-live-table-wrap">
            <table class="erp-live-table">
                <thead>
                    <tr>
                        <th>Created</th>
                        <th>Order No</th>
                        <th>Customer</th>
                        <th>Buyer</th>
                        <th>Sales Person</th>
                        <th>PO</th>
                        <th>Item Name</th>
                        <th class="num">Total Qty</th>
                        <th class="num">Price</th>
                        <th class="num">Value</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="erpLiveBody">
                    <tr><td colspan="12" class="erp-live-empty">No report loaded yet.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
function erpLiveEscape(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function erpLiveDate(value) {
    var raw = String(value || '').trim();
    return raw ? raw.slice(0, 10) : '-';
}

function erpLiveNum(value) {
    return Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 4 });
}

function erpLivePrice(value) {
    return Number(value || 0).toLocaleString(undefined, { maximumFractionDigits: 6 });
}

function erpLiveMoney(value) {
    return '$' + Number(value || 0).toFixed(2);
}

function erpLiveToday() {
    return new Date().toISOString().slice(0, 10);
}

function erpLiveDaysAgo(days) {
    var d = new Date();
    d.setDate(d.getDate() - days);
    return d.toISOString().slice(0, 10);
}

async function erpLiveFetchPage(fromDate, toDate, offset) {
    var url = APP_BASE + '/api/erp_live_orders_report.php?paged=1&from=' + encodeURIComponent(fromDate)
        + '&to=' + encodeURIComponent(toDate) + '&offset=' + encodeURIComponent(offset);
    var response = await fetch(url, { headers: { Accept: 'application/json' } });
    var text = await response.text();
    var json;
    try {
        json = JSON.parse(text);
    } catch (_) {
        var serverMessage = text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 180);
        throw new Error('ERP report failed (HTTP ' + response.status + ')' + (serverMessage ? ': ' + serverMessage : '.'));
    }
    if (!response.ok || json.error) {
        throw new Error(json.error || ('ERP report failed with HTTP ' + response.status + '.'));
    }
    return json;
}

// Fast read of previously-saved rows from the local cache (single call, no ERP round-trip).
async function erpLiveFetchCached(fromDate, toDate) {
    var url = APP_BASE + '/api/erp_live_orders_report.php?cached=1&from=' + encodeURIComponent(fromDate)
        + '&to=' + encodeURIComponent(toDate);
    var response = await fetch(url, { headers: { Accept: 'application/json' } });
    var text = await response.text();
    var json;
    try { json = JSON.parse(text); } catch (_) { return { orders: [], lineCount: 0, totalQty: 0, totalValue: 0 }; }
    if (!response.ok || json.error) return { orders: [], lineCount: 0, totalQty: 0, totalValue: 0 };
    return json;
}

function erpLiveMergeReport(combined, json) {
    (json.orders || []).forEach(function (order) {
        var orderNo = String(order.saleOrderNo || 'UNKNOWN');
        if (!combined.orderMap[orderNo]) {
            combined.orderMap[orderNo] = Object.assign({}, order, {
                items: [],
                itemMap: {},
                lineCount: 0,
                totalQty: 0,
                totalValue: 0
            });
        }
        var target = combined.orderMap[orderNo];
        target.lineCount = Number(target.lineCount || 0) + Number(order.lineCount || 0);
        target.totalQty = Number(target.totalQty || 0) + Number(order.totalQty || 0);
        target.totalValue = Number(target.totalValue || 0) + Number(order.totalValue || 0);
        (order.items || []).forEach(function (item) {
            var key = [item.itemName, item.itemCode, item.uom, item.lineStatus, item.remarks, item.price].join('|');
            if (!target.itemMap[key]) {
                target.itemMap[key] = Object.assign({}, item, { qty: 0, value: 0, lines: 0 });
                target.items.push(target.itemMap[key]);
            }
            target.itemMap[key].qty += Number(item.qty || 0);
            target.itemMap[key].value += Number(item.value || 0);
            target.itemMap[key].lines += Number(item.lines || 0);
        });
    });
    combined.lineCount += Number(json.lineCount || 0);
    combined.totalQty += Number(json.totalQty || 0);
    combined.totalValue += Number(json.totalValue || 0);
    combined.orders = Object.values(combined.orderMap);
}

function erpLiveSortOrders(orders) {
    return (orders || []).sort(function (a, b) {
        var dateA = a.createdDate || a.orderedDate || a.requestDate || '';
        var dateB = b.createdDate || b.orderedDate || b.requestDate || '';
        return dateA === dateB
            ? String(b.saleOrderNo || '').localeCompare(String(a.saleOrderNo || ''))
            : String(dateB).localeCompare(String(dateA));
    });
}

function erpLiveFlattenOrders(orders) {
    var rows = [];
    (orders || []).forEach(function (order) {
        var items = Array.isArray(order.items) ? order.items : [];
        items.forEach(function (item, index) {
            rows.push({
                first: index === 0,
                created: order.createdDate || order.orderedDate || order.requestDate || '',
                orderNo: order.saleOrderNo || '-',
                customerName: order.customerName || '',
                buyer: order.buyer || '',
                salesPerson: order.salesPerson || '',
                customerPo: order.customerPo || '-',
                itemName: item.itemName || item.itemCode || '-',
                qty: item.qty || 0,
                price: item.price || 0,
                value: item.value || 0
                ,conversionStatus: order.conversionStatus || 'new'
                ,readStatus: order.readStatus || 'unread'
                ,workOrderId: order.workOrderId || ''
            });
        });
    });
    return rows;
}

var erpLiveRows = [];
var erpLiveTotals = { qty: 0, value: 0 };
var erpLiveOrders = [];

function erpLiveApplyFilter() {
    var filter = document.getElementById('erpLiveStatusFilter')?.value || 'all';
    var readF  = document.getElementById('erpLiveReadFilter')?.value || 'all';
    var custQ  = (document.getElementById('erpLiveCustSearch')?.value  || '').trim().toLowerCase();
    var buyerQ = (document.getElementById('erpLiveBuyerSearch')?.value || '').trim().toLowerCase();
    var salesQ = (document.getElementById('erpLiveSalesSearch')?.value || '').trim().toLowerCase();
    var poQ    = (document.getElementById('erpLivePoSearch')?.value    || '').trim().toLowerCase();
    var orders = erpLiveOrders.filter(function (order) {
        if (filter !== 'all' && (order.conversionStatus || 'new') !== filter) return false;
        if (readF !== 'all' && (order.readStatus || 'unread') !== readF) return false;
        if (custQ  && (order.customerName || '').toLowerCase().indexOf(custQ)  === -1) return false;
        if (buyerQ && (order.buyer || '').toLowerCase().indexOf(buyerQ) === -1) return false;
        if (salesQ && (order.salesPerson || '').toLowerCase().indexOf(salesQ) === -1) return false;
        if (poQ    && (order.customerPo || '').toLowerCase().indexOf(poQ) === -1) return false;
        return true;
    });
    erpLiveRows = erpLiveFlattenOrders(orders);
    erpLiveTotals = orders.reduce(function (totals, order) {
        totals.qty += Number(order.totalQty || 0);
        totals.value += Number(order.totalValue || 0);
        return totals;
    }, { qty: 0, value: 0 });
    erpLiveRenderPage();
}

async function erpLiveMarkRead(orderNo) {
    try {
        await fetch(APP_BASE + '/api/erp_order_mark_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ erp_order_no: orderNo, read: true })
        });
        erpLiveOrders.forEach(function (o) {
            if (String(o.saleOrderNo) === String(orderNo)) o.readStatus = 'read';
        });
        erpLiveApplyFilter();
    } catch (_) { /* ignore — will retry on next load */ }
}

async function erpLiveOrderAction(orderNo, workOrderId) {
    if (workOrderId) {
        sessionStorage.setItem('ats_current_order_id', workOrderId);
        window.location.href = APP_BASE + '/pages/sales.php?erp_order=' + encodeURIComponent(orderNo);
        return;
    }
    try {
        var response = await fetch(APP_BASE + '/api/erp_create_work_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ erp_order_no: orderNo })
        });
        var responseText = await response.text();
        var result;
        try { result = JSON.parse(responseText); }
        catch (_) { throw new Error('Work-order creation returned an invalid response.'); }
        if (!response.ok || result.error) throw new Error(result.error || 'Could not create work order.');
        sessionStorage.setItem('ats_current_order_id', result.order_id);
        window.location.href = APP_BASE + '/pages/sales.php?erp_order=' + encodeURIComponent(orderNo);
    } catch (error) {
        alert(error.message || 'Could not create work order.');
    }
}

function erpLiveRenderPage() {
    var body = document.getElementById('erpLiveBody');
    var pageRows = erpLiveRows;

    if (!pageRows.length) {
        body.innerHTML = '<tr><td colspan="12" class="erp-live-empty">No orders match this filter.</td></tr>';
        return;
    }

    body.innerHTML = pageRows.map(function (row, index) {
        var showOrder = row.first || index === 0;
        var edge = showOrder ? ' style="border-top:2px solid #dbe3ff;"' : '';
        return '<tr>'
            + '<td' + edge + '>' + (showOrder ? erpLiveEscape(erpLiveDate(row.created)) : '') + '</td>'
            + '<td' + edge + '>' + (showOrder ? erpLiveEscape(row.orderNo) : '') + '</td>'
            + '<td' + edge + '>' + (showOrder ? erpLiveEscape(row.customerName || '-') : '') + '</td>'
            + '<td' + edge + '>' + (showOrder ? erpLiveEscape(row.buyer || '-') : '') + '</td>'
            + '<td' + edge + '>' + (showOrder ? erpLiveEscape(row.salesPerson || '-') : '') + '</td>'
            + '<td' + edge + '>' + (showOrder ? erpLiveEscape(row.customerPo) : '') + '</td>'
            + '<td' + edge + '>' + erpLiveEscape(row.itemName) + '</td>'
            + '<td class="num"' + edge + '>' + erpLiveNum(row.qty) + '</td>'
            + '<td class="num"' + edge + '>' + erpLivePrice(row.price) + '</td>'
            + '<td class="num"' + edge + '>' + erpLiveMoney(row.value) + '</td>'
            + '<td' + edge + '>' + (showOrder ? (function () {
                var isRead = row.workOrderId || row.readStatus === 'read';
                var label = row.workOrderId ? 'Already Created' : (row.readStatus === 'read' ? 'Read' : 'Unread');
                return '<span class="erp-status-pill erp-status-' + (isRead ? 'created' : 'new') + '">' + label + '</span>';
            })() : '') + '</td>'
            + '<td' + edge + '>' + (showOrder ? ('<button type="button" class="erp-row-action" onclick="erpLiveOrderAction(\'' + erpLiveEscape(row.orderNo) + '\', \'' + erpLiveEscape(row.workOrderId) + '\')">' + (row.workOrderId ? 'Open ' + erpLiveEscape(row.workOrderId) : 'Create Work Order') + '</button>'
                + (row.readStatus === 'unread' ? ' <button type="button" class="erp-row-action" style="background:#f0fdf4;color:#166534;border-color:#bbf7d0;" onclick="erpLiveMarkRead(\'' + erpLiveEscape(row.orderNo) + '\')">Mark read</button>' : '')) : '') + '</td>'
            + '</tr>';
    }).join('') + '<tr style="background:#f8fafc;font-weight:800;">'
        + '<td colspan="7" style="text-align:right;">Totals</td>'
        + '<td class="num">' + erpLiveNum(erpLiveTotals.qty) + '</td>'
        + '<td></td>'
        + '<td class="num">' + erpLiveMoney(erpLiveTotals.value) + '</td>'
        + '<td colspan="2"></td>'
        + '</tr>';
}

async function loadErpLiveOrdersReport() {
    var fromInput = document.getElementById('erpLiveFrom');
    var toInput = document.getElementById('erpLiveTo');
    var meta = document.getElementById('erpLiveMeta');
    var body = document.getElementById('erpLiveBody');
    var btn = document.getElementById('erpLiveBtn');
    var fromDate = fromInput.value || erpLiveToday();
    var toDate = toInput.value || erpLiveToday();

    btn.disabled = true;
    btn.textContent = 'Loading...';
    meta.textContent = 'Loading saved ERP orders from ' + fromDate + ' to ' + toDate + '...';
    body.innerHTML = '<tr><td colspan="12" class="erp-live-empty">Loading report...</td></tr>';
    erpLiveOrders = [];   // clear any previous run before this load's cache/live fill

    try {
        // ── Step 1: instant display from the saved cache (display only) ────
        // Kept separate from `combined` because live re-fetches the whole range;
        // merging overlapping cache + live would double-count quantities.
        try {
            var cachedJson = await erpLiveFetchCached(fromDate, toDate);
            if ((cachedJson.orders || []).length) {
                var cacheCombined = { orders: [], orderMap: {}, lineCount: 0, totalQty: 0, totalValue: 0 };
                erpLiveMergeReport(cacheCombined, cachedJson);
                cacheCombined.orders = erpLiveSortOrders(cacheCombined.orders);
                erpLiveOrders = cacheCombined.orders;
                erpLiveApplyFilter();
                meta.textContent = 'Showing ' + erpLiveNum(cacheCombined.lineCount) + ' saved line(s). Checking live ERP for new/missing orders...';
            }
        } catch (_) { /* cache miss is fine — the live fetch below fills it */ }

        // ── Step 2: authoritative live ERP (adds & saves anything missing) ─
        var combined = { orders: [], orderMap: {}, lineCount: 0, totalQty: 0, totalValue: 0 };
        var savedNewTotal = 0;
        var offset = 0;
        var pageNumber = 0;
        var hasMore = true;
        var pageLimit = 1000;
        var parallelPages = 4;

        while (hasMore && pageNumber < 200) {
            var offsets = [];
            for (var i = 0; i < parallelPages && pageNumber + i < 200; i++) {
                offsets.push(offset + (i * pageLimit));
            }

            var pages = await Promise.all(offsets.map(function (pageOffset) {
                return erpLiveFetchPage(fromDate, toDate, pageOffset);
            }));

            for (var pageIndex = 0; pageIndex < pages.length; pageIndex++) {
                var json = pages[pageIndex];
                erpLiveMergeReport(combined, json);
                savedNewTotal += Number(json.savedNew || 0);
                pageNumber++;
                hasMore = Boolean(json.hasMore);
                offset = Number(json.nextOffset || (offsets[pageIndex] + pageLimit));
                if (!hasMore) break;
            }

            combined.orders = erpLiveSortOrders(combined.orders);
            meta.textContent = 'Loaded ' + erpLiveNum(combined.lineCount) + ' line(s) from ' + pageNumber
                + ' ERP page(s)' + (hasMore ? '. Loading more...' : '.');
        }

        combined.orders = erpLiveSortOrders(combined.orders);
        var rows = erpLiveFlattenOrders(combined.orders);
        meta.textContent = erpLiveNum(combined.lineCount) + ' line(s) | Total Qty ' + erpLiveNum(combined.totalQty)
            + ' | Total Value ' + erpLiveMoney(combined.totalValue)
            + ' | Created ' + fromDate + ' to ' + toDate + '.';

        // If live truly returned nothing but we already showed saved rows, keep them.
        if (!rows.length) {
            if (erpLiveOrders.length) {
                meta.textContent = 'Showing saved data — live ERP returned no orders for this range.';
            } else {
                erpLiveRows = [];
                body.innerHTML = '<tr><td colspan="12" class="erp-live-empty">No live ERP orders found for this date range.</td></tr>';
            }
            return;
        }

        erpLiveOrders = combined.orders;
        erpLiveApplyFilter();
    } catch (error) {
        // A live failure should not wipe the fast cached display if we already have one.
        if (erpLiveOrders.length) {
            meta.textContent = 'Showing saved data. Live ERP check failed: ' + (error.message || 'connection error') + '.';
        } else {
            erpLiveRows = [];
            meta.textContent = 'Could not load live ERP report.';
            body.innerHTML = '<tr><td colspan="12" class="erp-live-empty" style="color:#ef4444;">' + erpLiveEscape(error.message) + '</td></tr>';
        }
    } finally {
        btn.disabled = false;
        btn.textContent = 'Load Report';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var fromEl = document.getElementById('erpLiveFrom');
    var toEl = document.getElementById('erpLiveTo');
    fromEl.value = erpLiveToday();
    toEl.value = erpLiveToday();

    // Reload automatically when the user changes either date.
    fromEl.addEventListener('change', loadErpLiveOrdersReport);
    toEl.addEventListener('change', loadErpLiveOrdersReport);

    // If we arrived from a "create work order" notification, handle that instead.
    var notificationOrder = new URLSearchParams(window.location.search).get('create_erp_order') || '';
    if (/^\d+$/.test(notificationOrder)) {
        erpLiveOrderAction(notificationOrder, '');
        return;
    }

    // Auto-load today's orders on open (cache-first shows instantly) - no click needed.
    loadErpLiveOrdersReport();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
