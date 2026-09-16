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
    padding:4mm 14mm 12mm; box-shadow:0 4px 24px rgba(0,0,0,.15);
    overflow:hidden;
    display:flex;
    flex-direction:column;
}
.mspi-doc .zzal-print-brand--footer {
    position:static; margin-top:auto!important; padding-top:6px!important;
    page-break-inside:avoid!important; break-inside:avoid-page!important;
}
.mspi-content { min-height:0; flex:1 1 auto; display:flex; flex-direction:column; }
.mspi-continuation { display:none; }
.mspi-continuation.is-active { display:flex; height:auto; min-height:297mm; overflow:visible; }
html.ats-print-layout #mspiDocument { height:281mm!important; min-height:281mm!important; overflow:hidden!important; }

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
    letter-spacing:8px; color:#000;
    border-top:2px solid #1a3a6e; border-bottom:2px solid #1a3a6e;
    padding:4px 0; margin:2px 0 3px;
}

.mspi-meta   { display:flex; justify-content:space-between; font-size:7.875pt; margin-bottom:2px; }
.mspi-buyer  { font-size:7.875pt; margin:2px 0 1px; }
.mspi-to-label { font-size:7.875pt; font-weight:700; margin:1px 0; }
.mspi-to     { font-size:7.875pt; margin:0 0 1px; line-height:1.35; }
.mspi-orderref {
    margin:2px 0 3px; padding-top:3px; border-top:1px dotted #7a7a7a;
    font-size:7.5pt; font-weight:700; line-height:1.4;
    white-space:normal; overflow-wrap:anywhere; word-break:break-word;
}
.mspi-conf   { font-size:7.5pt; margin:4px 0; }

/* Table */
.mspi-tbl { width:100%; border-collapse:collapse; font-size:7.5pt; }
.mspi-tbl th {
    background:#fff; color:#111; padding:5px 8px;
    border:1px solid #1a3a6e; text-align:center; font-size:7.125pt; line-height:1.3;
}
.mspi-tbl td { border:1px solid #7a7a7a; padding:1.5px 8px; vertical-align:top; line-height:1.2; }
.mspi-tbl td.tc { text-align:center; }
.mspi-tbl td.tr { text-align:right; }
.mspi-tbl tr.ref-row td { border:1px solid #7a7a7a; padding:2px 8px; }
.mspi-tbl tr.total-row td { font-weight:700; border-top:2px solid #1a3a6e; }
.mspi-tbl tr { page-break-inside:avoid; }
.mspi-ref-bold { font-weight:700; font-size:7.5pt; }

/* Total words */
.mspi-words {
    font-size:7.5pt; font-weight:700; text-transform:uppercase;
    margin:8px 0 12px; color:#000;
    border-top:1px dashed #333; border-bottom:1px dashed #333;
    padding:4px 0;
}
.mspi-terms-title { font-size:6.375pt; font-weight:700; text-decoration:underline; margin:0 0 4px; }
.mspi-terms-list { margin:0; padding-left:32px; font-size:6.05625pt; line-height:1.3; }
.mspi-terms-list li { margin-bottom:0; }

/* Signatures */
.mspi-sig-area { margin-top:36px; }
.mspi-sig-right-block { text-align:right; margin-bottom:8px; }
.mspi-sig-co   { font-size:7.5pt; font-weight:700; margin-bottom:36px; }
.mspi-sig-line { border-top:1.5px solid #000; width:220px; margin:0 0 3px auto; }
.mspi-sig-auth { font-size:7.125pt; }
.mspi-sig-bottom {
    display:flex; justify-content:space-between; align-items:flex-end;
    padding-top:6px; margin-top:40px;
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

@page { size:A4 portrait; margin:0 0 16mm; }
@media print {
    .mspi-ctrl, nav.page-nav, .order-id-bar { display:none !important; }
    html, body { width:210mm!important; min-height:0!important; margin:0!important; padding:0!important; background:#fff!important; overflow:visible!important; }
    .app-shell, .form-stack { display:block!important; margin:0!important; padding:0!important; background:#fff!important; }
    .form-stack > *:not(#mspiWrap) { display:none!important; }
    #mspiWrap { display:block!important; background:none!important; padding:0!important; margin:0!important; width:210mm!important; min-height:0!important; }
    .mspi-doc { box-sizing:border-box; box-shadow:none; margin:0; width:210mm!important; height:auto!important; min-height:281mm!important; max-width:210mm; padding:4mm 14mm 8mm!important; overflow:visible!important; display:flex!important; flex-direction:column!important; }
    #mspiDocument { height:281mm!important; min-height:281mm!important; overflow:hidden!important; }
    .mspi-continuation.is-active { height:auto!important; min-height:0!important; overflow:visible!important; display:block!important; break-before:page; page-break-before:always; }
    .mspi-continuation .mspi-tbl tr { page-break-inside:avoid!important; break-inside:avoid-page!important; }
    .mspi-continuation:not(.is-active) { display:none!important; }
    #mspiDocument .zzal-print-brand--footer { position:fixed!important; left:14mm!important; right:14mm!important; bottom:2mm!important; width:auto!important; margin:0!important; padding:0!important; z-index:20; background:#fff; page-break-inside:avoid!important; break-inside:avoid-page!important; }
    .mspi-continuation .zzal-print-brand--footer { display:none!important; }
    .mspi-tbl thead { display:table-header-group!important; }
    .mspi-tbl tbody { break-inside:auto!important; page-break-inside:auto!important; }
    .mspi-tbl tr { break-inside:avoid-page!important; page-break-inside:avoid!important; }
    .mspi-content { min-height:0!important; flex:1 1 auto!important; display:flex!important; flex-direction:column!important; }
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
    <div class="mspi-ctrl-group">
        <span class="mspi-ctrl-label">PI Date</span>
        <input type="date" id="mspiDateInput"
               style="background:#2d2d50;color:#fff;border:1.5px solid #4f46e5;border-radius:6px;padding:6px 10px;font-size:12px;outline:none;color-scheme:dark;"
               onchange="mspiUpdateDate(this.value)">
    </div>
    <div class="mspi-ctrl-group">
        <span class="mspi-ctrl-label">Customer Name (TO)</span>
        <input type="text" id="mspiCustNameInput" placeholder="Override customer name…"
               style="background:#2d2d50;color:#fff;border:1.5px solid #4f46e5;border-radius:6px;padding:6px 10px;font-size:12px;outline:none;min-width:200px;"
               oninput="mspiUpdateCustomer()">
    </div>
    <div class="mspi-ctrl-group">
        <span class="mspi-ctrl-label">Customer Address</span>
        <textarea id="mspiCustAddrInput" rows="2" placeholder="Override address…"
               style="background:#2d2d50;color:#fff;border:1.5px solid #4f46e5;border-radius:6px;padding:6px 10px;font-size:12px;outline:none;min-width:220px;resize:vertical;"
               oninput="mspiUpdateCustomer()"></textarea>
    </div>
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
        try {
            const ov = JSON.parse(sessionStorage.getItem('summary_pi_cust_override') || 'null');
            if (ov) window._mspiCustOverride = ov; // { name, addr }
        } catch (e) {}
    }
    window._mspiBank = p.get('bank') || 'ncc';
    window._mspiBin  = p.get('bin')  || '';
    window._mspiHsCode = p.get('hs') || '4819.10.00';
    window._mspiDocMust = p.get('doc') || 'UD';
    // Default date input to today
    const dateInput = document.getElementById('mspiDateInput');
    if (dateInput) dateInput.value = new Date().toISOString().slice(0,10);
})();

function mspiUpdateDate(val) {
    const formatted = mspiFormatDate(val);
    const d1 = document.getElementById('mspiDate');
    const d2 = document.getElementById('mspiContDate');
    if (d1) d1.textContent = formatted;
    if (d2) d2.textContent = formatted;
}

function mspiUpdateCustomer() {
    const name = document.getElementById('mspiCustNameInput')?.value.trim() || window._mspiRenderedCustName || '';
    const addr = document.getElementById('mspiCustAddrInput')?.value.trim() || window._mspiRenderedCustAddr || '';
    const el = document.getElementById('mspiTo');
    if (el) el.innerHTML = `<strong>${name || '—'}</strong>` + (addr ? '<br>' + addr.replace(/\n/g,'<br>') : '');
}
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
        <div class="mspi-orderref" id="mspiOrderRef" style="display:none;"></div>
        <div class="mspi-conf">WE CONFIRM HAVING SOLD TO YOU THE FOLLOWING MERCHANDISE.</div>

        <!-- Item Table -->
        <table class="mspi-tbl">
            <thead>
                <tr>
                    <th style="width:62px;">PI NO</th>
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

        <div id="mspiTermsBlock" style="display:none;">
            <div class="mspi-terms-title">Terms &amp; Conditions:</div>
            <ol class="mspi-terms-list" id="mspiTerms"></ol>
        </div>

        <!-- Signature area -->
        <div class="mspi-sig-area" id="mspiSigArea" style="margin-top:28px;">
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
<div class="mspi-doc mspi-continuation" id="mspiContinuation">
    <?= zzal_print_brand_header() ?>
    <div class="mspi-title">PROFORMA &nbsp; INVOICE &nbsp; SUMMARY</div>
    <div class="mspi-meta">
        <div><strong>PROFOMA INVOICE NO :</strong> <span id="mspiContNum">-</span></div>
        <div><strong>Date :</strong> <span id="mspiContDate">-</span></div>
    </div>
    <table class="mspi-tbl" id="mspiContTblWrap" style="display:none;">
        <thead>
            <tr>
                <th style="width:40px;">SL NO</th>
                <th>Description of goods</th>
                <th style="width:50px;">PLY</th>
                <th style="width:100px;">Quantity/<br>Pcs/con</th>
                <th style="width:90px;">Unit Price</th>
                <th style="width:115px;">Total Amount<br>(USD)</th>
            </tr>
        </thead>
        <tbody id="mspiContBody"></tbody>
        <tbody id="mspiContTotFoot" style="display:none;">
            <tr class="total-row">
                <td colspan="2"></td>
                <td></td>
                <td class="tc" id="mspiContTotalQty"><strong>-</strong></td>
                <td></td>
                <td class="tr" id="mspiContTotalVal"><strong>-</strong></td>
            </tr>
        </tbody>
    </table>
    <div class="mspi-words" id="mspiContWordsWrap" style="display:none;">TOTAL AMOUNT : US DOLLER: <span id="mspiContWords">-</span></div>
    <div id="mspiTermsContBlock" style="display:none;">
        <div class="mspi-terms-title">Terms &amp; Conditions:</div>
        <ol class="mspi-terms-list" id="mspiTermsCont"></ol>
    </div>
    <div class="mspi-sig-area" style="margin-top:28mm;">
        <div class="mspi-sig-bottom">
            <div class="mspi-sig-bottom-label">SIGNATURE OF BUYER</div>
            <div class="mspi-sig-bottom-label" style="display:flex;flex-direction:column;align-items:center;gap:1px;">
                <img src="<?= BASE_PATH ?>/AKM.png" alt="Authorised Signature" style="height:75px;max-width:270px;object-fit:contain;">
                <span>SIGNATURE OF SELLER</span>
            </div>
        </div>
    </div>
    <?= zzal_print_brand_footer() ?>
</div>
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

function mspiUnitUSD(v) {
    return '$ ' + parseFloat(v || 0).toLocaleString('en-US', {minimumFractionDigits:4, maximumFractionDigits:4});
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
    // Use the date input value if set, otherwise fall back to PI data date; default to today
    const dateInputEl = document.getElementById('mspiDateInput');
    const effectiveDate = (dateInputEl && dateInputEl.value) ? dateInputEl.value : (piDate || new Date().toISOString().slice(0,10));
    if (dateInputEl && !dateInputEl.value) dateInputEl.value = effectiveDate;
    document.getElementById('mspiNum').textContent  = piNum;
    document.title = mspiFileName(piNum, (usingSelection ? firstPi.customer : (salesPg.customer || intake.customer || order.customer_name)) || ''); // Save-as-PDF / print default file name = Customer-PINumber
    document.getElementById('mspiDate').textContent = mspiFormatDate(effectiveDate);
    const contNumEl = document.getElementById('mspiContNum');
    const contDateEl = document.getElementById('mspiContDate');
    if (contNumEl) contNumEl.textContent = piNum;
    if (contDateEl) contDateEl.textContent = mspiFormatDate(effectiveDate);

    // Buyer / TO — when using a selection, take them from the first picked PI.
    const buyer    = (usingSelection ? (firstPo0.sharedBuyer || firstPo0.buyer || firstPo0.endBuyer) : (salesPg.buyer || firstPo0.sharedBuyer || firstPo0.endBuyer || intake.pos?.[0]?.endBuyer)) || '—';
    const custName = (usingSelection ? firstPi.customer : (salesPg.customer || intake.customer || order.customer_name)) || '—';
    const custAddr = (usingSelection ? firstPo0.sharedBuyerAddress : (salesPg.buyerAddress || firstPo0.sharedBuyerAddress)) || '';
    // Apply customer override from sales.php if provided (Summary-PI-only, doesn't affect Single/Master)
    const ov = window._mspiCustOverride || {};
    const displayName = (ov.name || custName) || '—';
    const displayAddr = (ov.addr !== undefined ? ov.addr : custAddr) || '';
    // Seed the controls-bar inputs so user can further tweak on this page
    const nameInp = document.getElementById('mspiCustNameInput');
    const addrInp = document.getElementById('mspiCustAddrInput');
    if (nameInp && !nameInp.dataset.userEdited) { nameInp.value = displayName; }
    if (addrInp && !addrInp.dataset.userEdited) { addrInp.value = displayAddr; }
    if (nameInp && !nameInp._watchSet) { nameInp._watchSet = true; nameInp.addEventListener('input', () => { nameInp.dataset.userEdited = '1'; }); }
    if (addrInp && !addrInp._watchSet) { addrInp._watchSet = true; addrInp.addEventListener('input', () => { addrInp.dataset.userEdited = '1'; }); }
    const finalName = nameInp?.dataset.userEdited ? (nameInp.value.trim() || displayName) : displayName;
    const finalAddr = addrInp?.dataset.userEdited ? (addrInp.value.trim()) : displayAddr;
    document.getElementById('mspiBuyer').textContent = buyer;
    document.getElementById('mspiTo').innerHTML =
        `<strong>${finalName}</strong>` + (finalAddr ? '<br>' + finalAddr.replace(/\n/g,'<br>') : '');

    const allSummaryPos = pis.flatMap(pi => pi.pos || []);
    const orderRefs = [...new Set(allSummaryPos.map(po => po.orderRef || po.salesOrder || po.salesOrderNo || '').filter(Boolean))];
    const poRefs = [...new Set(allSummaryPos.map(po => {
        const poNum = po.poNum || po.customerPo || '';
        const style = po.style || '';
        return poNum ? poNum + (style ? ' &nbsp; Style# ' + style + '/' : '') : '';
    }).filter(Boolean))];
    const orderRefEl = document.getElementById('mspiOrderRef');
    if (orderRefs.length || poRefs.length) {
        orderRefEl.style.display = 'block';
        orderRefEl.innerHTML =
            (orderRefs.length ? 'ORDER REF: ' + orderRefs.join(', ') + '<br>' : '') +
            (poRefs.length ? 'PO # ' + poRefs.join(' / ') : '');
    } else {
        orderRefEl.style.display = 'none';
        orderRefEl.innerHTML = '';
    }

    // Build rows — all PIs
    const tbody = document.getElementById('mspiBody');
    tbody.innerHTML = '';
    let totalQty = 0, totalVal = 0;

    pis.forEach(pi => {
        const pos = pi.pos || [];
        const piLabel = (pi.pi_number || '—').split('/').slice(-2).join('/');

        // Count total items across all POs for this PI (for rowspan)
        let piItemCount = 0;
        pos.forEach(po => { piItemCount += (po.items || []).length; });

        let piFirstRow = true;
        pos.forEach(po => {
            (po.items || []).forEach(item => {
                const qty = parseFloat(item.qty   || 0);
                const prc = parseFloat(item.price || item.unitPrice || 0);
                const tot = parseFloat(item.total || (qty * prc)) || 0;
                totalQty += qty;
                totalVal += tot;
                const tr = document.createElement('tr');
                // Show PI number only on first row of each PI; blank on subsequent rows
                const piCell = piFirstRow
                    ? `<td class="tc" style="font-size:9px;font-weight:700;word-break:break-all;border-bottom:none;">${piLabel}</td>`
                    : `<td style="border-top:none;border-bottom:none;"></td>`;
                if (piFirstRow) {
                    tr.style.borderTop = '1.5px solid #555';
                    piFirstRow = false;
                }
                tr.innerHTML = `
                    ${piCell}
                    <td>${item.desc || item.itemName || '—'}</td>
                    <td class="tc">${item.ply || '—'}</td>
                    <td class="tc">${qty.toLocaleString()}</td>
                    <td class="tr">${prc ? mspiUnitUSD(prc) : '—'}</td>
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
    mspiPaginateLikeSingle(terms);

}

function mspiPaginateLikeSingle(terms) {
    const docEl = document.getElementById('mspiDocument');
    const body = document.getElementById('mspiBody');
    const continuationEl = document.getElementById('mspiContinuation');
    const contBody = document.getElementById('mspiContBody');
    const contTblWrap = document.getElementById('mspiContTblWrap');
    const totalFoot = document.getElementById('mspiFoot');
    const contTotalFoot = document.getElementById('mspiContTotFoot');
    const wordsWrap = document.getElementById('mspiWords')?.closest('.mspi-words');
    const contWordsWrap = document.getElementById('mspiContWordsWrap');
    const termsBlock = document.getElementById('mspiTermsBlock');
    const firstTerms = document.getElementById('mspiTerms');
    const contTerms = document.getElementById('mspiTermsCont');
    const signature = document.getElementById('mspiSigArea');
    if (!docEl || !body || !continuationEl || !contBody) return;

    const mainTable = body.closest('.mspi-tbl');
    const pageFooter = docEl.querySelector('.zzal-print-brand--footer');
    const overflows = () => {
        if (docEl.scrollHeight > docEl.clientHeight + 2) return true;
        if (!mainTable || !pageFooter) return false;
        const pageBox = docEl.getBoundingClientRect();
        const tableBox = mainTable.getBoundingClientRect();
        const footerBox = pageFooter.getBoundingClientRect();
        const footerClearance = Math.max(footerBox.height + 28, 64);
        const safeTableBottom = Math.min(
            footerBox.top - 16,
            pageBox.bottom - footerClearance
        );
        return tableBox.bottom > safeTableBottom;
    };

    contBody.innerHTML = '';
    contTblWrap.style.display = 'none';
    contTotalFoot.style.display = 'none';
    contWordsWrap.style.display = 'none';
    totalFoot.style.display = '';
    if (wordsWrap) wordsWrap.style.display = '';
    if (termsBlock) termsBlock.style.display = 'none';
    firstTerms.innerHTML = '';
    contTerms.innerHTML = '';
    continuationEl.classList.remove('is-active');
    signature.style.display = 'block';

    if (!overflows()) return;

    continuationEl.classList.add('is-active');
    contTblWrap.style.display = '';
    totalFoot.style.display = 'none';
    if (wordsWrap) wordsWrap.style.display = 'none';
    contTotalFoot.style.display = '';
    contWordsWrap.style.display = '';
    document.getElementById('mspiContTotalQty').innerHTML = document.getElementById('mspiTotalQty').innerHTML;
    document.getElementById('mspiContTotalVal').innerHTML = document.getElementById('mspiTotalVal').innerHTML;
    document.getElementById('mspiContWords').textContent = document.getElementById('mspiWords').textContent;
    firstTerms.innerHTML = '';
    if (termsBlock) termsBlock.style.display = 'none';
    contTerms.innerHTML = '';
    contTerms.start = 1;
    signature.style.display = 'none';

    let guard = 0;
    while (overflows() && body.rows.length > 1 && guard++ < 1000) {
        contBody.insertBefore(body.rows[body.rows.length - 1], contBody.firstChild);
    }
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
window.atsRerenderPiForLayout = () => renderSummaryPi();

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
