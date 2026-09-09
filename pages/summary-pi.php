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
    font-size:8.25pt; color:#000; background:#fff;
    padding:14mm 14mm 14mm; box-shadow:0 4px 24px rgba(0,0,0,.15);
    overflow:hidden;
    display:flex;
    flex-direction:column;
}
.mspi-doc .zzal-print-brand--footer { position:static; margin-top:auto!important; padding-top:6px!important; }
.mspi-content { min-height:0; flex:1 1 auto; display:flex; flex-direction:column; }
#mspiPrintPages { display:none; }
#mspiWrap.mspi-paginated > .mspi-doc { display:none; }
#mspiWrap.mspi-paginated #mspiPrintPages { display:block; }
.mspi-print-page {
    box-sizing:border-box; position:relative; width:210mm; height:297mm; max-width:900px;
    margin:0 auto 12px; padding:4mm 14mm 8mm; overflow:hidden; background:#fff;
    display:flex; flex-direction:column; font-family:'Times New Roman',Times,serif;
    font-size:8.25pt; color:#000; box-shadow:0 4px 24px rgba(0,0,0,.15);
}
.mspi-print-page .zzal-print-brand--header { margin-bottom:5px!important; }
.mspi-print-page .zzal-print-brand--footer { position:static!important; margin-top:auto!important; padding-top:5px!important; }
.mspi-print-page .mspi-title { margin:2px 0 3px; }
.mspi-print-page .mspi-meta { margin-bottom:3px; }
.mspi-print-page .mspi-tbl { font-size:7.25pt; }
.mspi-print-page .mspi-tbl th { padding:4px 7px; }
.mspi-print-page .mspi-tbl td { padding:3px 7px; }
.mspi-print-page .mspi-sig-area { margin-top:9mm!important; }
.mspi-print-page .mspi-sig-bottom { margin-top:0!important; }

/* Header */
.mspi-hd {
    display:none; align-items:center;
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
    text-align:center; font-size:9.75pt; font-weight:700;
    letter-spacing:4px; color:#000;
    border-top:2px solid #1a3a6e; border-bottom:2px solid #1a3a6e;
    padding:5px 0; margin:4px 0 8px;
}

.mspi-meta   { display:flex; justify-content:space-between; font-size:7.875pt; margin-bottom:5px; }
.mspi-buyer  { font-size:7.875pt; margin:3px 0 2px; }
.mspi-to-label { font-size:7.875pt; font-weight:700; margin:3px 0 2px; }
.mspi-to     { font-size:7.875pt; margin:0 0 2px; line-height:1.45; }
.mspi-conf   { font-size:7.5pt; margin:5px 0 8px; }

/* Table */
.mspi-tbl { width:100%; border-collapse:collapse; font-size:7.5pt; }
.mspi-tbl th {
    background:#fff; color:#111; padding:5px 8px;
    border:1px solid #1a3a6e; text-align:center; font-size:7.125pt; line-height:1.2;
}
.mspi-tbl td { border:1px solid #7a7a7a; padding:3px 7px; vertical-align:top; }
.mspi-tbl td.tc { text-align:center; }
.mspi-tbl td.tr { text-align:right; }
.mspi-tbl tr.ref-row td { border:1px solid #7a7a7a; padding:2px 8px; }
.mspi-tbl tr.total-row td { font-weight:700; border-top:2px solid #1a3a6e; }
.mspi-tbl tr { page-break-inside:avoid; }
.mspi-ref-bold { font-weight:700; font-size:7.5pt; }

/* Total words */
.mspi-words {
    font-size:7.5pt; font-weight:700; text-transform:uppercase;
    margin:7px 0 10px; color:#000;
    border-top:1px dashed #333; border-bottom:1px dashed #333;
    padding:4px 0;
}
.mspi-terms-title { font-size:6.375pt; font-weight:700; text-decoration:underline; margin:4px 0; }
.mspi-terms-list { margin:0; padding-left:28px; font-size:6.05625pt; line-height:1.3; }
.mspi-terms-list li { margin-bottom:0; }

/* Signatures */
.mspi-sig-area { margin-top:36px; }
.mspi-sig-right-block { text-align:right; margin-bottom:8px; }
.mspi-sig-co   { font-size:7.5pt; font-weight:700; margin-bottom:36px; }
.mspi-sig-line { border-top:1.5px solid #000; width:220px; margin:0 0 3px auto; }
.mspi-sig-auth { font-size:7.125pt; }
.mspi-sig-bottom {
    display:flex; justify-content:space-between; align-items:flex-end;
    padding-top:6px; margin-top:100px;
}
.mspi-sig-bottom-label { font-size:7.5pt; font-weight:700; }

/* Footer bar */
.mspi-footer-bar {
    margin-top:24px; border:1.5px solid #000;
    padding:6px 12px; font-size:8.5pt; line-height:1.7;
    text-align:center;
}

/* Empty state */
.mspi-empty { text-align:center; padding:60px 20px; color:#94a3b8; font-family:sans-serif; }
html.pi-preview .mspi-ctrl {
    display:none!important;
}

@page { size:A4 portrait; margin:0; }
@media print {
    .mspi-ctrl, nav.page-nav, .order-id-bar { display:none !important; }
    html, body { width:210mm!important; min-height:0!important; margin:0!important; padding:0!important; background:#fff!important; overflow:visible!important; }
    .app-shell, .form-stack { display:block!important; margin:0!important; padding:0!important; background:#fff!important; }
    .form-stack > *:not(#mspiWrap) { display:none!important; }
    #mspiWrap { display:block!important; background:none!important; padding:0!important; margin:0!important; width:210mm!important; min-height:0!important; }
    #mspiWrap > .mspi-doc { display:none!important; }
    #mspiPrintPages { display:block!important; }
    .mspi-print-page { box-sizing:border-box; width:210mm; height:297mm; padding:4mm 14mm 8mm; overflow:hidden; background:#fff; display:flex; flex-direction:column; margin:0!important; box-shadow:none!important; break-after:page; page-break-after:always; }
    .mspi-print-page:last-child { break-after:auto; page-break-after:auto; }
    .mspi-print-page .zzal-print-brand--header { position:static!important; margin:0 0 5px!important; }
    .mspi-print-page .zzal-print-brand--footer { position:static!important; margin-top:auto!important; padding-top:5px!important; }
    .mspi-print-page .mspi-title { margin:2px 0 3px; }
    .mspi-print-page .mspi-meta { margin-bottom:3px; }
    .mspi-print-page .mspi-tbl { font-size:7.25pt; }
    .mspi-print-page .mspi-tbl th { padding:4px 7px; }
    .mspi-print-page .mspi-tbl td { padding:3px 7px; }
    .mspi-print-page .mspi-sig-area { margin-top:9mm!important; break-inside:avoid!important; }
    .mspi-print-page .mspi-sig-bottom { margin-top:0!important; }
    .mspi-hd { display:none !important; }
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
    <?php if (($__user['role'] ?? '') !== 'marketing'): ?>
    <button class="mspi-excel-btn" style="background:#0f6cbd;" onclick="emailThisPi()">📧 Email PI (Outlook)</button>
    <?php endif; ?>
    <button class="mspi-print-btn" onclick="atsPrintPi()">Print / Save PDF</button>
</div>
<script>
(function(){
    const p = new URLSearchParams(window.location.search);
    const set = (id, val) => { const el = document.getElementById(id); if (el && val) el.value = val; };
    set('mspiDays',      p.get('days'));
    set('mspiLcType',    p.get('lctype'));
    set('mspiTolerance', p.get('tol'));
    if (p.get('preview') === '1') document.documentElement.classList.add('pi-preview');
    // Cross-order Summary: render the exact already-created PIs picked on the PI
    // page (preview/print only). Gated by ?summary=1 so a stale selection never
    // affects a normal single-order summary load.
    if (p.get('summary') === '1') {
        try {
            const sel = JSON.parse(sessionStorage.getItem('summary_selected_pis') || 'null');
            if (Array.isArray(sel) && sel.length) window._mspiSelectionPis = sel;
        } catch (e) {}
    }
    window._mspiBank = p.get('bank') || 'ncc';
    window._mspiBin  = p.get('bin')  || '';
    window._mspiHsCode = p.get('hs') || '4819.10.00';
    window._mspiDocMust = p.get('doc') || 'UD';
})();
</script>

<!-- ── Document ── -->
<div id="mspiWrap">
<div class="mspi-doc" id="mspiDocument">
    <div class="mspi-empty" id="mspiEmpty">Load an order to generate the Summary PI</div>
    <div id="mspiContent" class="mspi-content" style="display:none;">

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

        <div id="mspiTermsBlock">
            <div class="mspi-terms-title">Terms &amp; Conditions:</div>
            <ol class="mspi-terms-list" id="mspiTerms"></ol>
        </div>

        <!-- Signature area -->
        <div class="mspi-sig-area" id="mspiSigArea" style="margin-top:80px;">
            <div class="mspi-sig-bottom">
                <div class="mspi-sig-bottom-label">SIGNATURE OF BUYER</div>
                <div class="mspi-sig-bottom-label" style="display:flex;flex-direction:column;align-items:center;gap:1px;">
                    <img src="<?= BASE_PATH ?>/AKM.png" alt="Authorised Signature" style="height:75px;max-width:270px;object-fit:contain;">
                    <span>SIGNATURE OF SELLER</span>
                </div>
            </div>
        </div>

        <!-- Footer bar -->
        <?= zzal_print_brand_footer() ?>

    </div><!-- #mspiContent -->
</div><!-- .mspi-doc -->
<div id="mspiPrintPages" aria-hidden="true"></div>
</div><!-- #mspiWrap -->

<script>
let _mspiOrderData = null;
let _mspiExcelDone = false;

// PI number → safe file name, e.g. "ZZAL/PI/26/2" → "ZZAL-PI-26-2"
function mspiSanitizeName(s){ return String(s || '').replace(/[\/\\:*?"<>|]+/g, '-').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, ''); }
function mspiFileName(piNum, customer){ const c = mspiSanitizeName(customer); const p = mspiSanitizeName(piNum) || 'PI'; return (c ? c + '-' : '') + p; }

/* ── Helpers ─────────────────────────────────────────────────── */
function mspiNumWords(n) {
    const amount = parseFloat(n || 0) || 0;
    const ones = ['ZERO','ONE','TWO','THREE','FOUR','FIVE','SIX','SEVEN','EIGHT','NINE','TEN','ELEVEN','TWELVE','THIRTEEN','FOURTEEN','FIFTEEN','SIXTEEN','SEVENTEEN','EIGHTEEN','NINETEEN'];
    const tens = ['','','TWENTY','THIRTY','FORTY','FIFTY','SIXTY','SEVENTY','EIGHTY','NINETY'];
    const scales = ['', 'THOUSAND', 'MILLION', 'BILLION'];
    function chunkWords(x) {
        x = Math.floor(x);
        if (x === 0) return '';
        if (x < 20) return ones[x];
        if (x < 100) return tens[Math.floor(x / 10)] + (x % 10 ? ' ' + ones[x % 10] : '');
        return ones[Math.floor(x / 100)] + ' HUNDRED' + (x % 100 ? ' ' + chunkWords(x % 100) : '');
    }
    function fullWords(x) {
        x = Math.floor(x);
        if (x === 0) return 'ZERO';
        const parts = [];
        let scale = 0;
        while (x > 0) {
            const chunk = x % 1000;
            if (chunk) {
                const words = chunkWords(chunk);
                parts.unshift(words + (scales[scale] ? ' ' + scales[scale] : ''));
            }
            x = Math.floor(x / 1000);
            scale++;
        }
        return parts.join(' ').trim();
    }
    let centsTotal = Math.round(amount * 100);
    let dollars = Math.floor(centsTotal / 100);
    let cents = centsTotal % 100;
    if (cents === 100) {
        dollars += 1;
        cents = 0;
    }
    let result = fullWords(dollars);
    if (cents > 0) result += ' & CENTS ' + fullWords(cents);
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
    // Cross-order Summary uses the picked already-created PIs; otherwise fall back
    // to the current order's PIs (single-order summary — unchanged behavior).
    const usingSelection = Array.isArray(window._mspiSelectionPis) && window._mspiSelectionPis.length;
    const res = _mspiOrderData || {};
    if (!usingSelection && !_mspiOrderData) return;

    const days      = document.getElementById('mspiDays').value;
    const daysLabel = days === 'At Sight' ? 'At Sight' : days + ' Days';
    const tolerance = document.getElementById('mspiTolerance').value;
    const hsCode    = window._mspiHsCode || '4819.10.00';
    const docMust   = window._mspiDocMust || 'UD';

    const pis     = usingSelection ? window._mspiSelectionPis : (res.pis || []);
    const order   = res.order || {};
    const salesPg = res.pages?.sales || {};
    const intake  = res.pages?.['marketing-intake'] || {};

    document.getElementById('mspiEmpty').style.display   = 'none';
    document.getElementById('mspiContent').style.display = 'flex';

    // Header uses first PI's number
    const firstPi = pis[0] || {};
    const firstPo0 = firstPi.pos?.[0] || {};
    const piNum   = firstPi.pi_number || salesPg.piNum || (order.order_id || '') + '-SPI';
    const piDate  = firstPi.pi_date   || salesPg.piDate || order.created_at?.slice(0,10) || '';
    document.getElementById('mspiNum').textContent  = piNum;
    document.title = mspiFileName(piNum, (usingSelection ? firstPi.customer : (salesPg.customer || intake.customer || order.customer_name)) || ''); // Save-as-PDF / print default file name = Customer-PINumber
    document.getElementById('mspiDate').textContent = mspiFormatDate(piDate);
    const contNumEl = document.getElementById('mspiContNum');
    const contDateEl = document.getElementById('mspiContDate');
    if (contNumEl) contNumEl.textContent = piNum;
    if (contDateEl) contDateEl.textContent = mspiFormatDate(piDate);

    // Buyer / TO — when using a selection, take them from the first picked PI.
    const buyer    = (usingSelection ? (firstPo0.sharedBuyer || firstPo0.buyer || firstPo0.endBuyer) : (salesPg.buyer || firstPo0.sharedBuyer || firstPo0.endBuyer || intake.pos?.[0]?.endBuyer)) || '—';
    const custName = (usingSelection ? firstPi.customer : (salesPg.customer || intake.customer || order.customer_name)) || '—';
    const custAddr = (usingSelection ? firstPo0.sharedBuyerAddress : (salesPg.buyerAddress || firstPo0.sharedBuyerAddress)) || '';
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
            const poNum    = po.poNum    || po.customerPo || '';
            const style    = po.style    || '';
            const piCell   = firstPiPO ? `<td class="tc" style="font-weight:700;vertical-align:top;">${shortNum}</td>` : `<td></td>`;
            firstPiPO = false;

            // Show the customer PO reference only; internal/ERP order numbers
            // are intentionally omitted from the Summary PI.
            const refLines = [
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
                    <td class="tc" style="font-weight:700;">${shortNum}</td>
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
    const banks = {
        ncc:  {name:'National Credit & Commerce Bank Plc.', address:'Motijheel main Branch, 6 Motijheel C/A Dhaka-1000 Bangladesh.', account:'0002-0259000092', swift:'NCCLBDDHNBB', routing:'160150137'},
        dbbl: {name:'Dutch-Bangla Bank Plc.', address:'Local Office, 1, Dilkusha C/A, Dhaka-1000, Bangladesh.', account:'ERQ-101.117.1382', swift:'DBBLBDDHCTS', routing:'090273889'}
    };
    const bank = banks[window._mspiBank] || banks.ncc;
    const terms = [
        `100% Irrevocable confirmed <strong>${daysLabel}</strong>${days !== 'At Sight' ? ' Sight' : ''} L/C to be opened in favour of <strong>Zaber &amp; Zubair ACC. Ltd.</strong>`,
        `P.I Validity : <strong>45 Working days</strong>.`,
        `Letter of Credit to allow acceptability of <strong>+/- ${tolerance}% tolerance</strong> in quantity and Value.`,
        `Letter of Credit to allow for <strong>Partial Shipment</strong>.`,
        `The Buyer should provide a copy of the master L/C and Garment Export ${docMust} before the delivery of mentioned goods.`,
        `Where GSP certificate is required, applicant is requested to furnish full detail of the Master L/C in BBLC opened in favour of Zaber &amp; Zubair ACC. Ltd.`,
        `Prior to delivery- we will inform you full particulars of the consignment and forward the original delivery challan for the signature of the authorised signatory of your organisation. Please make arrangements to hand over the duly signed delivery challan at the time of delivery of goods.`,
        `Payment to be made on Maturity in US Dollar and Maturity date will be counted <strong>${daysLabel}</strong> from the date of DELIVERY Challan / Truck Receipt / <strong>This clause Will be integral Parts of L/C.</strong>`,
        `Interest to be paid at LIBOR by the Buyer till Maturity. If payment is not made within maturity then interest <strong>@16%</strong> will be charged for overdue period and buyer's is liable to pay. <strong>This clause Must be appeared on the L/C</strong>`,
        `Quality complaint, if any, should be notified to us prior before sewing.`,
        `The above mention terms &amp; condition will be the integral part of the BTB L/C &amp; it must be mention in the BTB L/C.`,
        `Beneficiary Bin No : <strong>000230256-0103</strong>`,
        `H.S. Code : <strong>${hsCode}</strong>`,
        `Total Gross Weight: Kgs`,
        `Delivery Terms: <strong>CPT</strong>`,
        `${docMust} Mustbe`,
        `Advising Bank : <strong>${bank.name}</strong><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;${bank.address}<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Account No: ${bank.account} &nbsp;|&nbsp; Swift Code: ${bank.swift} &nbsp;|&nbsp; Bank Routing No: ${bank.routing}`
    ];
    document.getElementById('mspiTerms').innerHTML = terms.map(term => `<li>${term}</li>`).join('');
    mspiBuildPrintPages();

}

function mspiBuildPrintPages() {
    const host = document.getElementById('mspiPrintPages');
    const content = document.getElementById('mspiContent');
    const body = document.getElementById('mspiBody');
    if (!host || !content || !body) return;
    const clean = html => String(html || '').replace(/\s+id="[^"]*"/g, '');
    const rows = Array.from(body.children).map(row => clean(row.outerHTML));
    // Match Single PI pagination: the first/item-only pages use nearly the full
    // A4 body (about 29 items plus a PO reference row), while the final page
    // keeps a small row remainder so totals, terms and signatures fit below it.
    const chunks = [];
    let offset = 0;
    const fullPageRows = 30;
    const finalPageRows = 8;
    if (rows.length <= finalPageRows) {
        chunks.push(rows.slice());
    } else {
        while (rows.length - offset > finalPageRows) {
            const remaining = rows.length - offset;
            const take = Math.min(fullPageRows, remaining - finalPageRows);
            chunks.push(rows.slice(offset, offset + take));
            offset += take;
        }
        chunks.push(rows.slice(offset));
    }

    const brandHeader = clean(content.querySelector('.zzal-print-brand--header')?.outerHTML);
    const brandFooter = clean(content.querySelector('.zzal-print-brand--footer')?.outerHTML);
    const title = clean(content.querySelector('.mspi-title')?.outerHTML);
    const meta = clean(content.querySelector('.mspi-meta')?.outerHTML);
    const buyer = clean(content.querySelector('.mspi-buyer')?.outerHTML);
    const toLabel = clean(content.querySelector('.mspi-to-label')?.outerHTML);
    const to = clean(content.querySelector('.mspi-to')?.outerHTML);
    const confirmation = clean(content.querySelector('.mspi-conf')?.outerHTML);
    const tableHead = clean(content.querySelector('.mspi-tbl thead')?.outerHTML);
    const totalFoot = clean(document.getElementById('mspiFoot')?.outerHTML);
    const words = clean(content.querySelector('.mspi-words')?.outerHTML);
    const terms = clean(document.getElementById('mspiTermsBlock')?.outerHTML);
    const signature = clean(document.getElementById('mspiSigArea')?.outerHTML);

    const pages = chunks.map((chunk, index) => {
        const isFirst = index === 0;
        const isLast = index === chunks.length - 1;
        const partyBlock = isFirst ? buyer + toLabel + to + confirmation : '';
        const finalBlock = isLast ? words + terms + signature : '';
        return `<section class="mspi-print-page">
            ${brandHeader}${title}${meta}${partyBlock}
            <table class="mspi-tbl">${tableHead}<tbody>${chunk.join('')}</tbody>${isLast ? totalFoot : ''}</table>
            ${finalBlock}${brandFooter}
        </section>`;
    });
    document.getElementById('mspiWrap')?.classList.toggle('mspi-paginated', pages.length > 1);
    host.innerHTML = pages.join('');
}

async function downloadSummaryPiExcel() {
    const usingSelection = Array.isArray(window._mspiSelectionPis) && window._mspiSelectionPis.length;
    const res = _mspiOrderData || {};
    if (!usingSelection && !_mspiOrderData) { alert('No order loaded.'); return; }

    const pis     = usingSelection ? window._mspiSelectionPis : (res.pis || []);
    const order   = res.order || {};
    const salesPg = res.pages?.sales || {};
    const intake  = res.pages?.['marketing-intake'] || {};

    const firstPi  = pis[0] || {};
    const firstPo0 = firstPi.pos?.[0] || {};
    const piNum    = firstPi.pi_number || salesPg.piNum || (order.order_id || 'SUMMARY') + '-SPI';
    const piDate   = firstPi.pi_date   || salesPg.piDate || order.created_at?.slice(0,10) || '';
    const buyer    = (usingSelection ? (firstPo0.sharedBuyer || firstPo0.buyer || firstPo0.endBuyer) : (salesPg.buyer || firstPo0.sharedBuyer || firstPo0.endBuyer || intake.pos?.[0]?.endBuyer)) || '';
    const custName = (usingSelection ? firstPi.customer : (salesPg.customer || intake.customer || order.customer_name)) || '';
    const custAddr = (usingSelection ? firstPo0.sharedBuyerAddress : (salesPg.buyerAddress || firstPo0.sharedBuyerAddress)) || '';

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
        a.download = mspiFileName(piNum, custName) + '.xls';
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

// Cross-order Summary: render immediately from the picked PIs — this path doesn't
// depend on an order being auto-loaded.
document.addEventListener('DOMContentLoaded', function () {
    if (Array.isArray(window._mspiSelectionPis) && window._mspiSelectionPis.length) {
        renderSummaryPi();
        if (atsShouldAutoExcel() && !_mspiExcelDone) {
            _mspiExcelDone = true;
            setTimeout(downloadSummaryPiExcel, 250);
        }
    }
});
</script>

<script src="<?= BASE_PATH ?>/assets/ats-email-pi.js"></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
