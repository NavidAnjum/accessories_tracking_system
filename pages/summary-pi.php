<?php
$pageTitle  = 'Summary PI';
$activePage = 'summary-pi';
$navSection = 'order';
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/print-brand.php';
?>
<style>
/* ── Controls ─────────────────────────────────────────────────────── */
.mspi-ctrl {
    background:#1e1e3a; padding:14px 24px;
    display:flex; gap:20px; align-items:center; flex-wrap:wrap;
}
.mspi-ctrl-group { display:flex; flex-direction:column; gap:4px; }
.mspi-ctrl-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#a5b4fc; }
.mspi-ctrl select {
    background:#2d2d50; color:#fff; border:1.5px solid #4f46e5;
    border-radius:6px; padding:6px 12px; font-size:12px; outline:none; min-width:160px;
}
.mspi-print-btn {
    margin-left:auto; background:#22c55e; color:#fff; border:none;
    border-radius:8px; padding:10px 28px; font-size:13px; font-weight:700; cursor:pointer;
}
.mspi-print-btn:hover { background:#16a34a; }
.mspi-excel-btn {
    background:#2563eb; color:#fff; border:none;
    border-radius:8px; padding:10px 22px; font-size:13px; font-weight:700; cursor:pointer;
}
.mspi-excel-btn:hover { background:#1d4ed8; }

/* ── PI Document ──────────────────────────────────────────────────── */
#mspiWrap { background:#d1d5db; padding:30px 0; min-height:500px; }
.mspi-doc {
    position:relative; box-sizing:border-box;
    width:210mm; height:297mm; max-width:900px; margin:0 auto;
    font-family:'Times New Roman',Times,serif;
    font-size:11pt; color:#000; background:#fff;
    padding:14mm 14mm 30mm; box-shadow:0 4px 24px rgba(0,0,0,.15);
    overflow:hidden;
}
.mspi-doc .zzal-print-brand--footer { position:absolute; left:14mm; right:14mm; bottom:8mm; margin:0!important; padding-top:6px!important; }

/* Header */
.mspi-hd {
    display:flex; align-items:center;
    border-bottom:3px solid #1a3a6e;
    padding-bottom:10px; margin-bottom:0;
}
.mspi-logo-box {
    flex-shrink:0; margin-right:16px;
    border:2px solid #1a3a6e; padding:4px 6px;
    display:flex; flex-direction:column; align-items:center;
    width:62px; min-height:62px; justify-content:center;
}
.mspi-logo-z  { font-size:28px; font-weight:900; color:#1a3a6e; font-family:Georgia,serif; line-height:1; }
.mspi-logo-zzal { font-size:9px; font-weight:900; letter-spacing:3px; color:#1a3a6e; margin-top:2px; }
.mspi-company-name {
    font-size:22pt; font-weight:900; color:#1a3a6e;
    font-family:Georgia,serif; letter-spacing:.5px;
    font-variant:small-caps; line-height:1.1;
}

.mspi-title {
    text-align:center; font-size:13pt; font-weight:700;
    letter-spacing:4px; color:#000;
    border-top:2px solid #1a3a6e; border-bottom:2px solid #1a3a6e;
    padding:5px 0; margin:4px 0 8px;
}

.mspi-meta   { display:flex; justify-content:space-between; font-size:10.5pt; margin-bottom:6px; }
.mspi-buyer  { font-size:10.5pt; margin:4px 0 2px; }
.mspi-to-label { font-size:10.5pt; font-weight:700; margin:4px 0 2px; }
.mspi-to     { font-size:10.5pt; margin:0 0 2px; line-height:1.6; }
.mspi-conf   { font-size:10pt; margin:6px 0 10px; }

/* Table */
.mspi-tbl { width:100%; border-collapse:collapse; font-size:10pt; }
.mspi-tbl th {
    background:#1a3a6e; color:#fff; padding:5px 8px;
    border:1px solid #1a3a6e; text-align:center; font-size:9.5pt; line-height:1.3;
}
.mspi-tbl td { border:1px solid #7a7a7a; padding:4px 8px; vertical-align:top; }
.mspi-tbl td.tc { text-align:center; }
.mspi-tbl td.tr { text-align:right; }
.mspi-tbl tr.ref-row td { border:1px solid #7a7a7a; padding:2px 8px; }
.mspi-tbl tr.total-row td { font-weight:700; border-top:2px solid #1a3a6e; }
.mspi-tbl tr { page-break-inside:avoid; }
.mspi-ref-bold { font-weight:700; font-size:10pt; }

/* Total words */
.mspi-words {
    font-size:10pt; font-weight:700; text-transform:uppercase;
    margin:8px 0 12px; color:#000;
    border-top:1px dashed #333; border-bottom:1px dashed #333;
    padding:4px 0;
}

/* Terms */
.mspi-terms-title { font-size:10pt; font-weight:700; text-decoration:underline; margin:0 0 4px; }
.mspi-terms-list  { margin:0; padding-left:32px; font-size:9.5pt; line-height:1.65; }
.mspi-terms-list li { margin-bottom:1px; }

/* Signatures */
.mspi-sig-area { margin-top:36px; }
.mspi-sig-right-block { text-align:right; margin-bottom:8px; }
.mspi-sig-co   { font-size:10pt; font-weight:700; margin-bottom:36px; }
.mspi-sig-line { border-top:1.5px solid #000; width:220px; margin:0 0 3px auto; }
.mspi-sig-auth { font-size:9.5pt; }
.mspi-sig-bottom {
    display:flex; justify-content:space-between;
    padding-top:6px; margin-top:100px;
}
.mspi-sig-bottom-label { font-size:10pt; font-weight:700; }

/* Footer bar */
.mspi-footer-bar {
    margin-top:24px; border:1.5px solid #000;
    padding:6px 12px; font-size:8.5pt; line-height:1.7;
    text-align:center;
}

/* Empty state */
.mspi-empty { text-align:center; padding:60px 20px; color:#94a3b8; font-family:sans-serif; }

@page { size:A4 portrait; margin:0; }
@media print {
    .mspi-ctrl, nav.page-nav, .order-id-bar { display:none !important; }
    #mspiWrap { background:none !important; padding:0 !important; }
    .mspi-doc { box-shadow:none; margin:0; width:210mm!important; height:297mm!important; max-width:210mm; padding:14mm 14mm 30mm!important; overflow:hidden; }
    .mspi-doc .zzal-print-brand--footer { position:absolute!important; left:14mm!important; right:14mm!important; bottom:8mm!important; margin:0!important; }
    .mspi-hd { display:none !important; }
    body, html, .app-shell { width:210mm!important; min-height:297mm!important; margin:0!important; padding:0!important; background:#fff !important; overflow:visible!important; }
    .form-stack { padding:0 !important; }
    .no-print { display:none !important; }
}
</style>

<!-- ── Controls ── -->
<div class="mspi-ctrl no-print">
    <!-- Hidden selects populated from URL params -->
    <select id="mspiDays"      style="display:none;"><option value="At Sight">At Sight</option><option value="30">30</option><option value="60">60</option><option value="90">90</option><option value="120">120</option></select>
    <select id="mspiLcType"    style="display:none;"><option value="Sight">Sight</option><option value="Usance">Usance</option><option value="Deferred Payment">Deferred Payment</option><option value="Acceptance">Acceptance</option></select>
    <select id="mspiTolerance" style="display:none;"><option value="5">5</option><option value="3">3</option><option value="10">10</option></select>
    <button class="mspi-excel-btn" onclick="downloadSummaryPiExcel()">Download Excel</button>
    <button class="mspi-print-btn" onclick="window.print()">Print / Save PDF</button>
</div>
<script>
(function(){
    const p = new URLSearchParams(window.location.search);
    const set = (id, val) => { const el = document.getElementById(id); if (el && val) el.value = val; };
    set('mspiDays',      p.get('days'));
    set('mspiLcType',    p.get('lctype'));
    set('mspiTolerance', p.get('tol'));
    window._mspiBank = p.get('bank') || 'ncc';
    window._mspiBin  = p.get('bin')  || '';
})();
</script>

<!-- ── Document ── -->
<div id="mspiWrap">
<div class="mspi-doc" id="mspiDocument">
    <div class="mspi-empty" id="mspiEmpty">Load an order to generate the Summary PI</div>
    <div id="mspiContent" style="display:none;">

        <?= zzal_print_brand_header() ?>

        <!-- Header: Logo + Company Name -->
        <div class="mspi-hd">
            <div class="mspi-logo-box">
                <span class="mspi-logo-z">Z</span>
                <span class="mspi-logo-zzal">ZZAL</span>
            </div>
            <div class="mspi-company-name">Zaber &amp; Zubair Accessories Ltd.</div>
        </div>

        <!-- Title -->
        <div class="mspi-title">PROFORMA &nbsp; INVOICE &nbsp; SUMMARY</div>

        <!-- PI Number + Date -->
        <div class="mspi-meta">
            <div><strong>PROFOMA INVOICE NO :</strong> <span id="mspiNum">-</span></div>
            <div><strong>Date :</strong> <span id="mspiDate">-</span></div>
        </div>

        <!-- Buyer / TO -->
        <div class="mspi-buyer"><strong>BUYER:</strong> <span id="mspiBuyer">-</span></div>
        <div class="mspi-to-label">TO</div>
        <div class="mspi-to" id="mspiTo">-</div>
        <div class="mspi-conf">WE CONFIRM HAVING SOLD TO YOU THE FOLLOWING MERCHANDISE AS PER TERMS AND CONDITION STATED BELOW.</div>

        <!-- Item Table -->
        <table class="mspi-tbl">
            <thead>
                <tr>
                    <th style="width:60px;">PI NO</th>
                    <th>Description of goods</th>
                    <th style="width:50px;">PLY</th>
                    <th style="width:100px;">Quantity/<br>Pcs/con</th>
                    <th style="width:90px;">Unit Price</th>
                    <th style="width:115px;">Total Amount<br>(USD)</th>
                </tr>
            </thead>
            <tbody id="mspiBody"></tbody>
            <tbody id="mspiFoot">
                <tr class="total-row">
                    <td colspan="2"></td>
                    <td></td>
                    <td class="tc" id="mspiTotalQty"><strong>-</strong></td>
                    <td></td>
                    <td class="tr" id="mspiTotalVal"><strong>-</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Total in words -->
        <div class="mspi-words">TOTAL AMOUNT : US DOLLER: <span id="mspiWords">-</span></div>

        <!-- Signature area -->
        <div class="mspi-sig-area" style="margin-top:80px;">
            <div class="mspi-sig-bottom">
                <div class="mspi-sig-bottom-label">SIGNATURE OF BUYER</div>
                <div class="mspi-sig-bottom-label">SIGNATURE OF SELLER</div>
            </div>
        </div>

        <!-- Footer bar -->
        <?= zzal_print_brand_footer() ?>

    </div><!-- #mspiContent -->
</div><!-- .mspi-doc -->
</div><!-- #mspiWrap -->

<script>
let _mspiOrderData = null;
let _mspiExcelDone = false;

/* ── Helpers ─────────────────────────────────────────────────── */
function mspiNumWords(n) {
    const a = ['','ONE','TWO','THREE','FOUR','FIVE','SIX','SEVEN','EIGHT','NINE','TEN','ELEVEN','TWELVE','THIRTEEN','FOURTEEN','FIFTEEN','SIXTEEN','SEVENTEEN','EIGHTEEN','NINETEEN'];
    const b = ['','','TWENTY','THIRTY','FORTY','FIFTY','SIXTY','SEVENTY','EIGHTY','NINETY'];
    function w(x) {
        if (x === 0) return '';
        if (x < 20)  return a[x] + ' ';
        if (x < 100) return b[Math.floor(x/10)] + (x%10 ? ' '+a[x%10] : '') + ' ';
        return a[Math.floor(x/100)] + ' HUNDRED ' + w(x % 100);
    }
    const dollars = Math.floor(n);
    const cents   = Math.round((n % 1) * 100);
    let result = (w(dollars) || 'ZERO ').trim();
    if (cents > 0) result += ' & CENTS ' + (w(cents) || '').trim();
    return result + ' ONLY.';
}

function mspiUSD(v) {
    return '$ ' + parseFloat(v || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
}

function mspiFormatDate(d) {
    if (!d) return '-';
    const dt = new Date(d);
    if (isNaN(dt)) return d;
    return dt.toLocaleDateString('en-GB', {day:'2-digit', month:'2-digit', year:'numeric'});
}

// "ZZAL/PI/26/562" → "26/562"
function mspiShortNum(piNum) {
    if (!piNum) return '-';
    const parts = piNum.split('/');
    return parts.length >= 4 ? parts[2] + '/' + parts[3] : piNum;
}

/* ── Render ───────────────────────────────────────────────────── */
function renderSummaryPi() {
    const res = _mspiOrderData;
    if (!res) return;

    const days      = document.getElementById('mspiDays').value;
    const daysLabel = days === 'At Sight' ? 'At Sight' : days + ' Days';
    const tolerance = document.getElementById('mspiTolerance').value;

    const pis     = res.pis   || [];
    const order   = res.order || {};
    const salesPg = res.pages?.sales || {};
    const intake  = res.pages?.['marketing-intake'] || {};

    document.getElementById('mspiEmpty').style.display   = 'none';
    document.getElementById('mspiContent').style.display = 'block';

    // Header uses first PI's number
    const firstPi = pis[0] || {};
    const piNum   = firstPi.pi_number || salesPg.piNum || order.order_id + '-SPI';
    const piDate  = firstPi.pi_date   || salesPg.piDate || order.created_at?.slice(0,10) || '';
    document.getElementById('mspiNum').textContent  = piNum;
    document.getElementById('mspiDate').textContent = mspiFormatDate(piDate);

    // Buyer / TO
    const firstPo0 = firstPi.pos?.[0] || {};
    const buyer    = salesPg.buyer || firstPo0.sharedBuyer || firstPo0.endBuyer || intake.pos?.[0]?.endBuyer || '—';
    const custName = salesPg.customer || intake.customer || order.customer_name || '—';
    const custAddr = salesPg.buyerAddress || firstPo0.sharedBuyerAddress || '';
    document.getElementById('mspiBuyer').textContent = buyer;
    document.getElementById('mspiTo').innerHTML =
        `<strong>${custName}</strong>` + (custAddr ? '<br>' + custAddr.replace(/\n/g,'<br>') : '');

    // Build rows — all PIs
    const tbody = document.getElementById('mspiBody');
    tbody.innerHTML = '';
    let totalQty = 0, totalVal = 0;

    pis.forEach(pi => {
        const shortNum  = mspiShortNum(pi.pi_number);
        const pos       = pi.pos || [];
        let   firstPiPO = true;

        pos.forEach(po => {
            const orderRef = po.orderRef || po.salesOrder || po.salesOrderNo || '';
            const poNum    = po.poNum    || po.customerPo || '';
            const style    = po.style    || '';
            const piCell   = firstPiPO ? `<td class="tc" style="font-weight:700;vertical-align:top;">${shortNum}</td>` : `<td></td>`;
            firstPiPO = false;

            // ORDER REF + PO # combined in one row (no border between them)
            const refLines = [
                orderRef ? 'ORDER REF: ' + orderRef : '',
                poNum    ? 'PO # ' + poNum + (style ? ' &nbsp;&nbsp; Style# ' + style + '/' : '') : ''
            ].filter(Boolean).join('<br>');
            if (refLines) {
                const rtr = document.createElement('tr');
                rtr.className = 'ref-row';
                rtr.innerHTML = `${piCell}<td colspan="5" class="mspi-ref-bold">${refLines}</td>`;
                tbody.appendChild(rtr);
            }

            // Item rows
            (po.items || []).forEach(item => {
                const qty = parseFloat(item.qty   || 0);
                const prc = parseFloat(item.price || item.unitPrice || 0);
                const tot = parseFloat(item.total || (qty * prc)) || 0;
                totalQty += qty;
                totalVal += tot;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td></td>
                    <td>${item.desc || item.itemName || '—'}</td>
                    <td class="tc">${item.ply || '—'}</td>
                    <td class="tc">${qty.toLocaleString()}</td>
                    <td class="tr">${prc ? mspiUSD(prc) : '—'}</td>
                    <td class="tr">${tot ? mspiUSD(tot) : '—'}</td>`;
                tbody.appendChild(tr);
            });
        });
    });

    document.getElementById('mspiTotalQty').innerHTML = `<strong>${totalQty.toLocaleString()}</strong>`;
    document.getElementById('mspiTotalVal').innerHTML = `<strong>${mspiUSD(totalVal)}</strong>`;
    document.getElementById('mspiWords').textContent  = mspiNumWords(totalVal);

}

async function downloadSummaryPiExcel() {
    const res = _mspiOrderData;
    if (!res) { alert('No order loaded.'); return; }

    const pis     = res.pis   || [];
    const order   = res.order || {};
    const salesPg = res.pages?.sales || {};
    const intake  = res.pages?.['marketing-intake'] || {};

    const firstPi  = pis[0] || {};
    const firstPo0 = firstPi.pos?.[0] || {};
    const piNum    = firstPi.pi_number || salesPg.piNum || order.order_id + '-SPI';
    const piDate   = firstPi.pi_date   || salesPg.piDate || order.created_at?.slice(0,10) || '';
    const buyer    = salesPg.buyer || firstPo0.sharedBuyer || firstPo0.endBuyer || intake.pos?.[0]?.endBuyer || '';
    const custName = salesPg.customer || intake.customer || order.customer_name || '';
    const custAddr = salesPg.buyerAddress || firstPo0.sharedBuyerAddress || '';

    let totalQty = 0, totalVal = 0;
    const piRows = [];
    pis.forEach(pi => {
        const shortNum = mspiShortNum(pi.pi_number);
        (pi.pos || []).forEach(po => {
            const items = (po.items || []).map(item => {
                const qty = parseFloat(item.qty   || 0);
                const prc = parseFloat(item.price || item.unitPrice || 0);
                const tot = parseFloat(item.total || (qty * prc)) || 0;
                totalQty += qty; totalVal += tot;
                return { desc: item.desc || item.itemName || '', ply: item.ply || '', qty, prc, tot };
            });
            piRows.push({
                shortNum,
                orderRef: po.orderRef || po.salesOrder || po.salesOrderNo || '',
                poNum: po.poNum || po.customerPo || '',
                style: po.style || '',
                items
            });
        });
    });

    const payload = {
        type: 'summary', orderId: order.order_id || (window.getCurrentOrderId && window.getCurrentOrderId()) || 'document',
        piNum, piDate: mspiFormatDate(piDate), buyer, custName, custAddr,
        piRows, totalQty, totalVal, totalWords: mspiNumWords(totalVal)
    };

    try {
        const resp = await fetch(window.APP_BASE + '/api/pi_excel_data.php', {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
        });
        const blob = await resp.blob();
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'summary-pi-' + payload.orderId + '.xls';
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
    } catch(e) { alert('Excel export failed.'); }
}

/* ── Hook into order loader ────────────────────────────────────── */
window.onOrderLoad = (function(_prev) {
    return function(res) {
        if (typeof _prev === 'function') _prev(res);
        _mspiOrderData = res;
        renderSummaryPi();
        if (atsShouldAutoExcel() && !_mspiExcelDone) {
            _mspiExcelDone = true;
            setTimeout(downloadSummaryPiExcel, 250);
        }
    };
})(window.onOrderLoad);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
