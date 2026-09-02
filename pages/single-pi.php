<?php
$pageTitle  = 'Single PI';
$activePage = 'single-pi';
$navSection = 'order';
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/print-brand.php';
?>
<style>
/* ── Controls ─────────────────────────────────────────────────────── */
.spi-ctrl {
    background:#1e1e3a; padding:14px 24px;
    display:flex; gap:20px; align-items:center; flex-wrap:wrap;
}
.spi-ctrl-group { display:flex; flex-direction:column; gap:4px; }
.spi-ctrl-label { font-size:7.5px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#a5b4fc; }
.spi-ctrl select {
    background:#2d2d50; color:#fff; border:1.5px solid #4f46e5;
    border-radius:6px; padding:6px 12px; font-size:9px; outline:none; min-width:160px;
}
.spi-print-btn {
    margin-left:auto; background:#22c55e; color:#fff; border:none;
    border-radius:8px; padding:10px 28px; font-size:9.75px; font-weight:700; cursor:pointer;
}
.spi-print-btn:hover { background:#16a34a; }
.spi-excel-btn {
    background:#2563eb; color:#fff; border:none;
    border-radius:8px; padding:10px 22px; font-size:9.75px; font-weight:700; cursor:pointer;
}
.spi-excel-btn:hover { background:#1d4ed8; }

/* ── PI Document ──────────────────────────────────────────────────── */
#spiWrap { background:#d1d5db; padding:30px 0; min-height:500px; }
.spi-doc {
    position:relative; box-sizing:border-box;
    width:210mm; height:297mm; max-width:900px; margin:0 auto 10px;
    font-family:'Times New Roman',Times,serif;
    font-size:8.25pt; color:#000; background:#fff;
    padding:4mm 14mm 12mm; box-shadow:0 4px 24px rgba(0,0,0,.15);
    overflow:hidden;
    display:flex;
    flex-direction:column;
}
.spi-doc .zzal-print-brand--footer {
    position:static;
    margin-top:auto!important;
    padding-top:6px!important;
    page-break-inside:avoid!important;
    break-inside:avoid-page!important;
}
#spiContent {
    min-height:0;
    flex:1 1 auto;      /* fill the page so the footer's margin-top:auto reaches the bottom */
    display:flex;
    flex-direction:column;
}
.spi-continuation { display:none; }
.spi-continuation.is-active { display:flex; }

/* Header */
.spi-hd {
    display:none; align-items:center;
    border-bottom:3px solid #1a3a6e;
    padding-bottom:10px; margin-bottom:0;
}
.spi-logo-box {
    flex-shrink:0; margin-right:16px;
    border:2px solid #1a3a6e; padding:4px 6px;
    display:flex; flex-direction:column; align-items:center;
    width:62px; min-height:62px; justify-content:center;
}
.spi-logo-z {
    font-size:21px; font-weight:900; color:#1a3a6e;
    font-family:Georgia,serif; line-height:1;
}
.spi-logo-zzal {
    font-size:6.75px; font-weight:900; letter-spacing:3px;
    color:#1a3a6e; margin-top:2px;
}
.spi-company-name {
    font-size:16.5pt; font-weight:900; color:#1a3a6e;
    font-family:Georgia,serif; letter-spacing:.5px;
    font-variant:small-caps; line-height:1.1;
}

.spi-title {
    text-align:center; font-size:9.75pt; font-weight:700;
    letter-spacing:8px; color:#000;
    border-top:2px solid #1a3a6e; border-bottom:2px solid #1a3a6e;
    padding:4px 0; margin:2px 0 3px;
}

/* Meta row */
.spi-meta { display:flex; justify-content:space-between; font-size:7.875pt; margin-bottom:2px; }

/* Address section */
.spi-buyer { font-size:7.875pt; margin:2px 0 1px; }
.spi-to-label { font-size:7.875pt; font-weight:700; margin:1px 0; }
.spi-to    { font-size:7.875pt; margin:0 0 1px; line-height:1.35; }
.spi-conf  { font-size:7.5pt; margin:4px 0; }

/* Table */
.spi-tbl { width:100%; border-collapse:collapse; font-size:7.5pt; }
.spi-tbl th {
    background:#fff; color:#111; padding:5px 8px;
    border:1px solid #1a3a6e; text-align:center; font-size:7.125pt; line-height:1.3;
}
.spi-tbl td { border:1px solid #7a7a7a; padding:4px 8px; vertical-align:top; }
.spi-tbl tr { page-break-inside: avoid; }
.spi-tbl td.tc { text-align:center; }
.spi-tbl td.tr { text-align:right; }
.spi-tbl tr.ref-row td { border:none; padding:3px 8px 1px; }
.spi-tbl tr.total-row td { font-weight:700; border-top:2px solid #1a3a6e; }
.spi-ref-bold { font-weight:700; font-size:7.5pt; }
.spi-addr-row { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; }
.spi-addr-left { flex:1 1 auto; }
.spi-orderref { font-size:7.5pt; font-weight:700; margin:0; line-height:1.5; text-align:right; white-space:nowrap; flex:0 0 auto; }

/* Total words */
.spi-words {
    font-size:7.5pt; font-weight:700; text-transform:uppercase;
    margin:8px 0 12px; color:#000;
    border-top:1px dashed #333; border-bottom:1px dashed #333;
    padding:4px 0;
}

/* Terms */
.spi-terms-title { font-size:6.375pt; font-weight:700; text-decoration:underline; margin:0 0 4px; }
.spi-terms-list  { margin:0; padding-left:32px; font-size:6.05625pt; line-height:1.3; }
.spi-terms-list li { margin-bottom:0; }

/* Signatures */
.spi-sig-area { margin-top:36px; }
.spi-sig-right-block {
    text-align:right; margin-bottom:8px;
}
.spi-sig-co   { font-size:7.5pt; font-weight:700; margin-bottom:36px; }
.spi-sig-line { border-top:1.5px solid #000; width:220px; margin:0 0 3px auto; }
.spi-sig-auth { font-size:7.125pt; }

.spi-sig-bottom {
    display:flex; justify-content:space-between; align-items:flex-end;
    padding-top:6px; margin-top:40px;
}
.spi-sig-bottom-label { font-size:7.5pt; font-weight:700; }
/* Footer bar */
.spi-footer-bar {
    margin-top:24px; border:1.5px solid #000;
    padding:6px 12px; font-size:6.375pt; line-height:1.7;
    text-align:center;
}

/* Empty state */
.spi-empty { text-align:center; padding:60px 20px; color:#94a3b8; font-family:sans-serif; }

@page { size:A4 portrait; margin:0; }
@media print {
    .spi-ctrl, nav.page-nav, .order-id-bar { display:none !important; }
    #spiWrap { background:none !important; padding:0 !important; }
    .spi-doc  { box-shadow:none; margin:0; width:210mm!important; height:297mm!important; max-width:210mm; padding:4mm 14mm 12mm!important; overflow:hidden!important; display:flex!important; flex-direction:column!important; }
    .spi-continuation:not(.is-active) { display:none!important; }
    .spi-doc .zzal-print-brand--footer { position:static!important; margin-top:auto!important; page-break-inside:avoid!important; break-inside:avoid-page!important; }
    #spiContent { min-height:0!important; flex:1 1 auto!important; display:flex!important; flex-direction:column!important; }
    .spi-hd { display:none !important; }
    body, html, .app-shell { width:210mm!important; min-height:297mm!important; margin:0!important; padding:0!important; background:#fff !important; overflow:visible!important; }
    .form-stack { padding:0 !important; }
    .no-print { display:none !important; }
}
html.spi-embed .page-nav,
html.spi-embed .order-id-bar,
html.spi-embed .spi-ctrl {
    display:none!important;
}
html.spi-embed .app-shell {
    max-width:none!important;
    padding:0!important;
    margin:0!important;
}
html.spi-embed #spiWrap {
    padding:10px 0!important;
    min-height:auto!important;
}
html.pi-preview .spi-ctrl {
    display:none!important;
}
</style>

<!-- ── Controls ── -->
<div class="spi-ctrl no-print">
    <div class="spi-ctrl-group">
        <span class="spi-ctrl-label">Select PI / PO</span>
        <select id="spiPoSel" onchange="renderSinglePi()">
            <option value="">- Load an order first -</option>
        </select>
    </div>
    <!-- Hidden inputs populated from URL params passed by sales.php -->
    <select id="spiDays"      style="display:none;"><option value="At Sight">At Sight</option><option value="30">30</option><option value="60">60</option><option value="90">90</option><option value="120">120</option></select>
    <select id="spiLcType"    style="display:none;"><option value="Sight">Sight</option><option value="Usance">Usance</option><option value="Deferred Payment">Deferred Payment</option><option value="Acceptance">Acceptance</option></select>
    <select id="spiTolerance" style="display:none;"><option value="5">5</option><option value="3">3</option><option value="10">10</option></select>
    <button class="spi-excel-btn" onclick="downloadSinglePiExcel()">Download Excel</button>
    <button class="spi-print-btn" onclick="window.print()">Print / Save PDF</button>
</div>
<script>
(function(){
    const p = new URLSearchParams(window.location.search);
    const orderId = p.get('order_id');
    if (orderId) {
        try { sessionStorage.setItem('ats_current_order_id', orderId); } catch (e) {}
    }
    if (p.get('embed') === '1') document.documentElement.classList.add('spi-embed');
    if (p.get('preview') === '1') document.documentElement.classList.add('pi-preview');
    const set = (id, val) => { const el = document.getElementById(id); if (el && val) el.value = val; };
    set('spiDays',      p.get('days'));
    set('spiLcType',    p.get('lctype'));
    set('spiTolerance', p.get('tol'));
    window._spiHsCode = p.get('hs') || '4819.10.00';
    window._spiDocMust = p.get('doc') || 'UD';
    window._spiBank = p.get('bank') || 'ncc';
})();
</script>

<!-- ── PI Document ── -->
<div id="spiWrap">
<div class="spi-doc" id="spiDocument">
    <div class="spi-empty" id="spiEmpty">Load an order to generate the Proforma Invoice</div>
    <div id="spiContent" style="display:none;">

        <?= zzal_print_brand_header() ?>

        <!-- Header: Logo + Company Name -->
        <div class="spi-hd">
            <div class="spi-logo-box">
                <span class="spi-logo-z">Z</span>
                <span class="spi-logo-zzal">ZZAL</span>
            </div>
            <div class="spi-company-name">Zaber &amp; Zubair Accessories Ltd.</div>
        </div>

        <!-- PROFORMA INVOICE title -->
        <div class="spi-title">PROFORMA &nbsp; INVOICE</div>

        <!-- PI Number + Date -->
        <div class="spi-meta">
            <div><strong>PROFOMA INVOICE NO :</strong> <span id="spiNum">-</span></div>
            <div><strong>Date :</strong> <span id="spiDate">-</span></div>
        </div>

        <!-- Buyer / TO (left)  +  Order reference / PO (right) -->
        <div class="spi-addr-row">
            <div class="spi-addr-left">
                <div class="spi-buyer"><strong>BUYER:</strong> <span id="spiBuyer">-</span></div>
                <div class="spi-to-label">TO</div>
                <div class="spi-to" id="spiTo">-</div>
            </div>
            <div class="spi-orderref" id="spiOrderRef" style="display:none;"></div>
        </div>

        <div class="spi-conf">WE CONFIRM HAVING SOLD TO YOU THE FOLLOWING MERCHANDISE AS PER TERMS AND CONDITION STATED BELOW.</div>

        <!-- Item Table -->
        <table class="spi-tbl">
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
            <tbody id="spiBody"></tbody>
            <tbody id="spiTotFoot">
                <tr class="total-row">
                    <td colspan="2"></td>
                    <td></td>
                    <td class="tc" id="spiTotalQty"><strong>-</strong></td>
                    <td></td>
                    <td class="tr" id="spiTotalVal"><strong>-</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Total in words -->
        <div class="spi-words">TOTAL AMOUNT : US DOLLER: <span id="spiWords">-</span></div>

        <!-- Terms & Conditions -->
        <div id="spiTermsBlock">
            <div class="spi-terms-title">Terms &amp; Conditions:</div>
            <ol class="spi-terms-list" id="spiTerms"></ol>
        </div>

        <!-- Signature area -->
        <div class="spi-sig-area" id="spiSigArea" style="margin-top:28px;">
            <div class="spi-sig-right-block" style="margin-bottom:8px;"></div>
            <div class="spi-sig-bottom">
                <div class="spi-sig-bottom-label">SIGNATURE OF BUYER</div>
                <div class="spi-sig-bottom-label" style="display:flex;flex-direction:column;align-items:center;gap:1px;">
                    <img src="<?= BASE_PATH ?>/AKM.png" alt="Authorised Signature" style="height:75px;max-width:270px;object-fit:contain;">
                    <span>SIGNATURE OF SELLER</span>
                </div>
            </div>
        </div>

        <!-- Footer bar -->
        <?= zzal_print_brand_footer() ?>

    </div><!-- #spiContent -->
</div><!-- .spi-doc -->
<div class="spi-doc spi-continuation" id="spiContinuation">
    <?= zzal_print_brand_header() ?>
    <div class="spi-title">PROFORMA &nbsp; INVOICE</div>
    <div class="spi-meta">
        <div><strong>PROFOMA INVOICE NO :</strong> <span id="spiContNum">-</span></div>
        <div><strong>Date :</strong> <span id="spiContDate">-</span></div>
    </div>
    <div id="spiTermsContBlock">
        <div class="spi-terms-title">Terms &amp; Conditions:</div>
        <ol class="spi-terms-list" id="spiTermsCont" start="16"></ol>
    </div>
    <div class="spi-sig-area" style="margin-top:28mm;">
        <div class="spi-sig-right-block" style="margin-bottom:8px;"></div>
        <div class="spi-sig-bottom">
            <div class="spi-sig-bottom-label">SIGNATURE OF BUYER</div>
            <div class="spi-sig-bottom-label" style="display:flex;flex-direction:column;align-items:center;gap:1px;">
                    <img src="<?= BASE_PATH ?>/AKM.png" alt="Authorised Signature" style="height:75px;max-width:270px;object-fit:contain;">
                    <span>SIGNATURE OF SELLER</span>
                </div>
        </div>
    </div>
    <?= zzal_print_brand_footer() ?>
</div>
</div><!-- #spiWrap -->

<script>
let _spiOrderData = null;
let _spiExcelDone = false;

/* ── Number to words ─────────────────────────────────────────── */
function spiNumWords(n) {
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

function spiUSD(v) {
    return '$ ' + parseFloat(v||0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
}

function spiFormatDate(d) {
    if (!d) return '—';
    const dt = new Date(d);
    if (isNaN(dt)) return d;
    return dt.toLocaleDateString('en-GB',{day:'2-digit',month:'2-digit',year:'numeric'});
}

/* ── Populate PO selector ─────────────────────────────────────── */
function spiPopulateSel(res) {
    const sel  = document.getElementById('spiPoSel');
    const pis  = res.pis || [];
    sel.innerHTML = '';
    if (!pis.length) {
        sel.innerHTML = '<option value="">No PIs found - save a PI first</option>';
        return;
    }
    pis.forEach((pi, idx) => {
        (pi.pos || [{}]).forEach((po, pidx) => {
            const opt = document.createElement('option');
            opt.value = idx + ',' + pidx;
            const label = pi.pi_number || (res.order?.order_id + '-PI');
            const poNum = po.poNum || po.customerPo || ('PO ' + (pidx+1));
            opt.textContent = label + (pi.pos?.length > 1 ? ' — ' + poNum : '');
            sel.appendChild(opt);
        });
    });
}

/* ── Render the PI ────────────────────────────────────────────── */
function renderSinglePi() {
    const res = _spiOrderData;
    if (!res) return;

    const days      = document.getElementById('spiDays').value;
    const daysLabel = days === 'At Sight' ? 'At Sight' : days + ' Days';
    const lcType    = document.getElementById('spiLcType').value;
    const tolerance = document.getElementById('spiTolerance').value;
    const hsCode    = window._spiHsCode || '4819.10.00';
    const docMust   = window._spiDocMust || 'UD';

    // Pick selected PI + PO
    const selVal = document.getElementById('spiPoSel').value;
    const [piIdx, poIdx] = selVal ? selVal.split(',').map(Number) : [0, 0];
    const pis    = res.pis || [];
    const pi     = pis[piIdx] || pis[0] || {};
    const po     = (pi.pos || [])[poIdx] || (pi.pos || [])[0] || {};

    const order    = res.order      || {};
    const salesPg  = res.pages?.sales || {};
    const intake   = res.pages?.['marketing-intake'] || {};

    // Show content
    document.getElementById('spiEmpty').style.display   = 'none';
    document.getElementById('spiContent').style.display = 'flex';

    // PI number + date
    const piNum  = pi.pi_number || salesPg.piNum  || order.order_id + '-PI';
    const piDate = pi.pi_date   || salesPg.piDate || order.created_at?.slice(0,10) || '';
    document.getElementById('spiNum').textContent  = piNum;
    document.getElementById('spiDate').textContent = spiFormatDate(piDate);
    document.getElementById('spiContNum').textContent  = piNum;
    document.getElementById('spiContDate').textContent = spiFormatDate(piDate);

    // Buyer (end brand) + Customer (TO)
    const buyer    = salesPg.buyer || po.sharedBuyer || po.buyer || po.endBuyer || intake.pos?.[0]?.endBuyer || '—';
    const custName = salesPg.customer || intake.customer || order.customer_name || '—';
    const custAddr = salesPg.buyerAddress || po.sharedBuyerAddress || '';
    document.getElementById('spiBuyer').textContent = buyer;
    const toHtml = `<strong>${custName}</strong>` + (custAddr ? '<br>' + custAddr.replace(/\n/g,'<br>') : '');
    document.getElementById('spiTo').innerHTML = toHtml;

    // Items
    const tbody   = document.getElementById('spiBody');
    tbody.innerHTML = '';
    let totalQty = 0, totalVal = 0, sl = 0;

    // Order reference + PO — shown above the table (near the top of the PI).
    const orderRef = po.orderRef || po.salesOrder || po.salesOrderNo || '';
    const poNum    = po.poNum    || po.customerPo  || '';
    const style    = po.style    || '';
    const refEl = document.getElementById('spiOrderRef');
    if (orderRef || poNum) {
        refEl.style.display = 'block';
        refEl.innerHTML =
            (orderRef ? 'ORDER REF: ' + orderRef + '<br>' : '') +
            (poNum    ? 'PO # ' + poNum + (style ? ' &nbsp; Style# ' + style + '/' : '') : '');
    } else {
        refEl.style.display = 'none';
        refEl.innerHTML = '';
    }

    const items = po.items || [];
    if (items.length) {
        items.forEach(item => {
            sl++;
            const qty = parseFloat(item.qty   || 0);
            const prc = parseFloat(item.price || item.unitPrice || 0);
            const tot = parseFloat(item.total || (qty * prc)) || 0;
            totalQty += qty;
            totalVal += tot;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="tc">${sl}</td>
                <td>${item.desc || item.itemName || '—'}</td>
                <td class="tc">${item.ply || '—'}</td>
                <td class="tc">${qty.toLocaleString()}</td>
                <td class="tr">${prc ? spiUSD(prc) : '—'}</td>
                <td class="tr">${tot ? spiUSD(tot) : '—'}</td>`;
            tbody.appendChild(tr);
        });
    } else {
        tbody.innerHTML = '<tr class="no-print"><td colspan="6" style="text-align:center;color:#999;padding:16px;font-family:sans-serif;font-size:6.75pt;">No items saved yet — add items in the PI page and save.</td></tr>';
    }

    document.getElementById('spiTotalQty').innerHTML = `<strong>${totalQty.toLocaleString()}</strong>`;
    document.getElementById('spiTotalVal').innerHTML = `<strong>${spiUSD(totalVal)}</strong>`;
    document.getElementById('spiWords').textContent  = spiNumWords(totalVal);

    // Terms & Conditions
    const terms = [
        `100% Irrevocable confirmed <strong>${daysLabel}</strong>${days !== 'At Sight' ? ' Sight' : ''} L/C to be opened in favour of <strong>Zaber &amp; Zubair ACC. Ltd.</strong>`,
        `P.I Validity : <strong>45 Working days</strong>.`,
        `Letter of Credit to allow acceptability of <strong>+/- ${tolerance}% tolerance</strong> in quantity and Value.`,
        `Letter of Credit to allow for <strong>Partial Shipment</strong>.`,
        `The Buyer should provide a copy of the master L/C and Garment Export UD before the delivery of mentioned goods.`,
        `Where GSP certificate is required, applicant is requested to furnish full detail of the Master L/C in BBLC opened in favour of Zaber &amp; Zubair ACC. Ltd.`,
        `Prior to delivery- we will inform you full particulars of the consignment and forward the original delivery challan for the signature of the authorised signatory of your organisation. Please make arrangements to hand over the duly signed delivery challan at the time of delivery of goods.`,
        `Payment to be made on Maturity in US Dollar and Maturity date will be counted <strong>${daysLabel}</strong> from the date of DELIVERY Challan / Truck Receipt / <strong>This clause Will be integral Parts of L/C.</strong>`,
        `Interest to be paid at LIBOR by the Buyer till Maturity. If payment is not made within maturity then interest <strong>@16%</strong> will be charged for overdue period and buyer's is liable to pay. <strong>This clause Must be appeared on the L/C</strong>`,
        `Quality complaint, if any, should be notified to us prior before sewing.`,
        `The above mention terms &amp; condition will be the integral part of the BTB L/C &amp; it must be mention in the BTB L/C.`,
        `Beneficiary Bin No : <strong>${window._spiBin || '—'}</strong>`,
        `H.S. Code : <strong>4819.10.00</strong>`,
        `Total Gross Weight: Kgs`,
        `Delivery Terms: <strong>CPT</strong>`,
        `UD Mustbe`,
        (function(){
            const BANKS = {
                ncc:  { name:'National Credit &amp; Commerce Bank Plc.', addr:'Motijheel main Branch, 6 Motijheel C/A Dhaka-1000 Bangladesh.', acct:'0002-0259000092', swift:'NCCLBDDHNBB', routing:'160150137' },
                dbbl: { name:'Dutch-Bangla Bank Plc.',                   addr:'Local Office, 1, Dilkusha C/A, Dhaka-1000, Bangladesh.',         acct:'ERQ-101.117.1382',  swift:'DBBLBDDHCTS',  routing:'090273889'  }
            };
            const b = BANKS[window._spiBank] || BANKS.ncc;
            return `Advising Bank : <strong>${b.name}</strong><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;${b.addr}<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Account No: ${b.acct} &nbsp;|&nbsp; Swift Code: ${b.swift} &nbsp;|&nbsp; Bank Routing No: ${b.routing}`;
        })(),
    ];
    terms[11] = `Beneficiary Bin No : <strong>000230256-0103</strong>`;
    terms[12] = `H.S. Code : <strong>${hsCode}</strong>`;
    terms[15] = `${docMust} Mustbe`;
    const firstTermsEl = document.getElementById('spiTerms');
    const contTermsEl = document.getElementById('spiTermsCont');
    const continuationEl = document.getElementById('spiContinuation');
    const sigAreaEl = document.getElementById('spiSigArea');
    const docEl = document.getElementById('spiDocument');
    const footerEl = docEl.querySelector('.zzal-print-brand--footer');
    const termsBlockEl = document.getElementById('spiTermsBlock');

    function renderTermSplit(firstCount) {
        const firstPageTerms = terms.slice(0, firstCount);
        const continuedTerms = terms.slice(firstCount);
        firstTermsEl.innerHTML = firstPageTerms.map(t => `<li>${t}</li>`).join('');
        contTermsEl.innerHTML = continuedTerms.map(t => `<li>${t}</li>`).join('');
        contTermsEl.start = firstPageTerms.length + 1;
        continuationEl.classList.toggle('is-active', continuedTerms.length > 0);
        sigAreaEl.style.display = continuedTerms.length ? 'none' : 'block';
        return continuedTerms.length;
    }

    let firstCount = terms.length;
    renderTermSplit(firstCount);

    function pageBottom(el) {
        return el.offsetTop + el.offsetHeight;
    }

    // Only move terms to page 2 when page 1 genuinely overflows. The doc is a
    // fixed-height A4 with overflow:hidden, so scrollHeight > clientHeight means
    // the content (terms + signature + footer) no longer fits on one page.
    while (firstCount > 1 && docEl.scrollHeight > docEl.clientHeight + 2) {
        firstCount -= 1;
        renderTermSplit(firstCount);
    }

    // Moving the first term also hides the signature block, which may make the
    // complete PI fit again. Re-test the full one-page layout before keeping a
    // continuation page.
    if (firstCount < terms.length) {
        renderTermSplit(terms.length);
        if (docEl.scrollHeight <= docEl.clientHeight + 2) {
            firstCount = terms.length;
        } else {
            renderTermSplit(firstCount);
        }
    }
}
async function downloadSinglePiExcel() {
    const res = _spiOrderData;
    if (!res) { alert('No order loaded.'); return; }

    const days      = document.getElementById('spiDays').value;
    const daysLabel = days === 'At Sight' ? 'At Sight' : days + ' Days';
    const tolerance = document.getElementById('spiTolerance').value;
    const hsCode    = window._spiHsCode || '4819.10.00';
    const docMust   = window._spiDocMust || 'UD';
    const bank      = window._spiBank || 'ncc';

    const selVal = document.getElementById('spiPoSel').value;
    const [piIdx, poIdx] = selVal ? selVal.split(',').map(Number) : [0, 0];
    const pis    = res.pis || [];
    const pi     = pis[piIdx] || pis[0] || {};
    const po     = (pi.pos || [])[poIdx] || (pi.pos || [])[0] || {};

    const order   = res.order || {};
    const salesPg = res.pages?.sales || {};
    const intake  = res.pages?.['marketing-intake'] || {};

    const piNum   = pi.pi_number || salesPg.piNum  || order.order_id + '-PI';
    const piDate  = pi.pi_date   || salesPg.piDate || order.created_at?.slice(0,10) || '';
    const buyer   = salesPg.buyer || po.sharedBuyer || po.buyer || po.endBuyer || intake.pos?.[0]?.endBuyer || '';
    const custName = salesPg.customer || intake.customer || order.customer_name || '';
    const custAddr = salesPg.buyerAddress || po.sharedBuyerAddress || '';
    const orderRef = po.orderRef || po.salesOrder || po.salesOrderNo || '';
    const poNum    = po.poNum    || po.customerPo  || '';
    const style    = po.style    || '';

    let totalQty = 0, totalVal = 0, sl = 0;
    const items = (po.items || []).map(item => {
        const qty = parseFloat(item.qty   || 0);
        const prc = parseFloat(item.price || item.unitPrice || 0);
        const tot = parseFloat(item.total || (qty * prc)) || 0;
        totalQty += qty; totalVal += tot; sl++;
        return { sl, desc: item.desc || item.itemName || '', ply: item.ply || '', qty, prc, tot };
    });

    const payload = {
        type: 'single', orderId: order.order_id || (window.getCurrentOrderId && window.getCurrentOrderId()) || 'document',
        piNum, piDate: spiFormatDate(piDate), buyer, custName, custAddr,
        orderRef, poNum, style, items,
        totalQty, totalVal, totalWords: spiNumWords(totalVal),
        days: daysLabel, tolerance, hsCode, docMust, bank
    };

    try {
        const resp = await fetch(window.APP_BASE + '/api/pi_excel_data.php', {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
        });
        const blob = await resp.blob();
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'single-pi-' + payload.orderId + '.xls';
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
    } catch(e) { alert('Excel export failed.'); }
}

/* ── Hook into order loader ───────────────────────────────────── */
window.onOrderLoad = (function(_prev){
    return function(res) {
        if (typeof _prev === 'function') _prev(res);
        _spiOrderData = res;
        spiPopulateSel(res);
        renderSinglePi();
        if (atsShouldAutoExcel() && !_spiExcelDone) {
            _spiExcelDone = true;
            setTimeout(downloadSinglePiExcel, 250);
        }
    };
})(window.onOrderLoad);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
