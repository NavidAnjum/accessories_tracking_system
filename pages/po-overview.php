<?php
$pageTitle   = 'PO Finder';
$activePage  = 'po-overview';
$navSection  = 'order';
$pageSubtitle = 'Find the correct ERP customer PO by partial match.';
include __DIR__ . '/../includes/header.php';
?>

<style>
.po-finder-card {
    background:#fff;
    border:1.5px solid #dbe3ff;
    border-radius:18px;
    overflow:hidden;
}
.po-finder-head {
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:16px;
    padding:18px 20px;
    border-bottom:1px solid #eef2ff;
    flex-wrap:wrap;
}
.po-finder-search {
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
}
.po-finder-search input {
    min-width:320px;
    max-width:520px;
    flex:1;
    padding:12px 14px;
    border:1.5px solid #dbe3ff;
    border-radius:12px;
    font-size:14px;
}
.po-finder-search input:focus {
    outline:none;
    border-color:#6366f1;
}
.po-finder-meta {
    padding:14px 20px;
    background:#f8faff;
    border-bottom:1px solid #eef2ff;
    font-size:13px;
    color:#64748b;
}
.po-finder-table-wrap {
    overflow:auto;
}
.po-finder-table {
    width:100%;
    border-collapse:collapse;
    background:#fff;
}
.po-finder-table th,
.po-finder-table td {
    border:1px solid #e5e7eb;
    padding:11px 10px;
    vertical-align:top;
    font-size:12px;
}
.po-finder-table th {
    background:#f8fafc;
    color:#475569;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.05em;
}
.po-finder-po {
    font-weight:800;
    color:#1f2937;
    font-size:13px;
}
.po-finder-chip {
    display:inline-block;
    margin:2px 6px 2px 0;
    padding:3px 8px;
    border-radius:999px;
    background:#eef2ff;
    color:#4f46e5;
    font-size:11px;
    font-weight:700;
}
.po-finder-copy {
    white-space:nowrap;
}
.po-finder-empty {
    padding:36px 18px;
    text-align:center;
    color:#94a3b8;
    font-size:14px;
}
.po-finder-snippet {
    color:#475569;
    line-height:1.5;
}
@media (max-width: 900px) {
    .po-finder-search input {
        min-width:0;
        width:100%;
    }
}
</style>

<section class="form-card" data-page="po-overview">
    <div class="section-head">
        <div class="section-title">
            <span class="section-tag">ERP Report</span>
            <h2>PO Finder</h2>
        </div>
        <div class="section-summary">
            <strong>Search Helper</strong>
            <span>Search partial PO text, style, remarks, item code, or sales order number to find the correct ERP customer PO.</span>
        </div>
    </div>

    <div class="po-finder-card">
        <div class="po-finder-head">
            <div>
                <div style="font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#6366f1;margin-bottom:6px;">ERP PO Finder</div>
                <div style="font-size:15px;font-weight:800;color:#1f2937;">Find the exact ERP PO before filling the PI page</div>
                <div style="font-size:13px;color:#64748b;margin-top:4px;">Examples: <strong>265</strong>, <strong>Ikea 2535</strong>, <strong>247589</strong>, <strong>band roll</strong>.</div>
            </div>
            <div class="po-finder-search">
                <input id="poFinderInput" placeholder="Search by partial customer PO, style/remarks, item code, buyer, or sales order..." onkeydown="if(event.key==='Enter'){event.preventDefault();runPoFinderSearch();}">
                <button type="button" class="primary-btn" id="poFinderBtn" onclick="runPoFinderSearch()">Search ERP</button>
                <button type="button" class="ghost-btn" onclick="clearPoFinder()">Clear</button>
            </div>
        </div>
        <div class="po-finder-meta" id="poFinderMeta">Type a partial PO or style text and click Search ERP.</div>
        <div class="po-finder-table-wrap">
            <table class="po-finder-table" id="poFinderTable" style="display:none;">
                <thead>
                    <tr>
                        <th style="min-width:220px;">Customer PO</th>
                        <th style="min-width:140px;">Sales Order</th>
                        <th style="min-width:180px;">Customer / Buyer</th>
                        <th style="min-width:120px;">Dates</th>
                        <th style="min-width:120px;">Status</th>
                        <th style="min-width:260px;">Matched Items / Style</th>
                        <th style="min-width:130px;">Action</th>
                    </tr>
                </thead>
                <tbody id="poFinderBody"></tbody>
            </table>
            <div class="po-finder-empty" id="poFinderEmpty">No search yet.</div>
        </div>
    </div>

    <div class="page-actions">
        <div class="page-actions-left">
            <button type="button" class="ghost-btn js-prev-page" data-prev-page="sales">Back to PI</button>
        </div>
    </div>
</section>

<script>
function poFinderEscape(val) {
    return String(val ?? '-')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function poFinderFormatDate(val) {
    var raw = String(val || '').trim();
    if (!raw) return '-';
    var parsed = new Date(raw.replace(' ', 'T'));
    if (!Number.isNaN(parsed.getTime())) {
        return parsed.toLocaleDateString('en-GB');
    }
    return raw;
}

function poFinderSnippet(parts) {
    return (parts || []).filter(Boolean).slice(0, 3).map(function (part) {
        return '<div class="po-finder-snippet">' + poFinderEscape(part) + '</div>';
    }).join('') || '<span style="color:#94a3b8;">-</span>';
}

function renderPoFinderResults(data) {
    var table = document.getElementById('poFinderTable');
    var body = document.getElementById('poFinderBody');
    var empty = document.getElementById('poFinderEmpty');
    var meta = document.getElementById('poFinderMeta');
    if (!table || !body || !empty || !meta) return;

    var results = Array.isArray(data.results) ? data.results : [];
    var cache = data.cache || {};
    var cacheInfo = '';
    if (cache.totalPos || cache.totalRows) {
        cacheInfo = ' Cache: ' + (cache.totalPos || 0) + ' PO(s), ' + (cache.totalRows || 0) + ' row(s)';
        if (cache.lastSyncedAt) {
            cacheInfo += ', last synced ' + cache.lastSyncedAt;
        }
        cacheInfo += '.';
    }
    if (!results.length) {
        table.style.display = 'none';
        body.innerHTML = '';
        empty.style.display = 'block';
        empty.textContent = 'No ERP PO matched "' + data.query + '".';
        meta.textContent = '0 matches found for "' + data.query + '". Try another part of the PO, style, or order text.' + cacheInfo;
        return;
    }

    body.innerHTML = results.map(function (row) {
        var salesOrders = (row.salesOrders || []).map(function (so) {
            return '<span class="po-finder-chip">' + poFinderEscape(so) + '</span>';
        }).join('') || '<span style="color:#94a3b8;">-</span>';

        var dates = [
            'Ordered: ' + poFinderFormatDate(row.orderedDate),
            'Booked: ' + poFinderFormatDate(row.bookedDate),
            'Request: ' + poFinderFormatDate(row.requestDate)
        ].join('<br>');

        return '<tr>'
            + '<td><div class="po-finder-po">' + poFinderEscape(row.customerPo) + '</div><div style="margin-top:8px;color:#64748b;">' + poFinderEscape(row.orderType || row.operatingUnit || '') + '</div></td>'
            + '<td>' + salesOrders + '</td>'
            + '<td><strong>' + poFinderEscape(row.customerName || '-') + '</strong><br><span style="color:#64748b;">Buyer: ' + poFinderEscape(row.buyer || '-') + '</span></td>'
            + '<td>' + dates + '</td>'
            + '<td><span class="po-finder-chip" style="background:#ecfdf5;color:#047857;">' + poFinderEscape(row.status || '-') + '</span></td>'
            + '<td>' + poFinderSnippet([].concat(row.items || [], row.remarks || [])) + '</td>'
            + '<td class="po-finder-copy"><button type="button" class="ghost-btn" onclick="copyPoValue(\'' + String(row.customerPo || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'") + '\')">Copy PO</button></td>'
            + '</tr>';
    }).join('');

    table.style.display = '';
    empty.style.display = 'none';
    meta.textContent = data.count + ' match(es) found for "' + data.query + '". Copy the exact ERP PO and paste it into the PI search.' + cacheInfo;
}

function copyPoValue(value) {
    if (!value) return;
    navigator.clipboard.writeText(value).then(function () {
        var meta = document.getElementById('poFinderMeta');
        if (meta) meta.textContent = 'Copied ERP PO: ' + value;
    }).catch(function () {
        prompt('Copy this ERP PO:', value);
    });
}

function clearPoFinder() {
    var input = document.getElementById('poFinderInput');
    var table = document.getElementById('poFinderTable');
    var body = document.getElementById('poFinderBody');
    var empty = document.getElementById('poFinderEmpty');
    var meta = document.getElementById('poFinderMeta');
    if (input) input.value = '';
    if (table) table.style.display = 'none';
    if (body) body.innerHTML = '';
    if (empty) {
        empty.style.display = 'block';
        empty.textContent = 'No search yet.';
    }
    if (meta) meta.textContent = 'Type a partial PO or style text and search the saved ERP cache.';
}

async function runPoFinderSearch() {
    var input = document.getElementById('poFinderInput');
    var btn = document.getElementById('poFinderBtn');
    var empty = document.getElementById('poFinderEmpty');
    var table = document.getElementById('poFinderTable');
    var body = document.getElementById('poFinderBody');
    var meta = document.getElementById('poFinderMeta');
    var query = input ? String(input.value || '').trim() : '';
    if (!query) {
        if (meta) meta.textContent = 'Please enter at least part of the PO, style, or order text.';
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Searching...';
    }
    if (table) table.style.display = 'none';
    if (body) body.innerHTML = '';
    if (empty) {
        empty.style.display = 'block';
        empty.textContent = 'Searching ERP for "' + query + '"...';
    }
    if (meta) meta.textContent = 'Searching saved ERP cache for partial matches...';

    try {
        var res = await fetch(APP_BASE + '/api/erp_po_report.php?q=' + encodeURIComponent(query));
        var json = await res.json();
        if (json.error) {
            if (empty) empty.textContent = 'ERP error: ' + json.error + (json.detail ? ' (' + json.detail + ')' : '');
            if (meta) meta.textContent = 'Could not complete ERP search.';
            return;
        }
        renderPoFinderResults(json);
    } catch (e) {
        if (empty) empty.textContent = 'Could not reach ERP search service.';
        if (meta) meta.textContent = 'Search failed. Check server connectivity.';
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Search ERP';
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var params = new URLSearchParams(window.location.search);
    var q = params.get('q') || '';
    if (q) {
        var input = document.getElementById('poFinderInput');
        if (input) input.value = q;
        runPoFinderSearch();
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
