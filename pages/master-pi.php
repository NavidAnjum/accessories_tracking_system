<?php
$pageTitle  = 'Master PI';
$activePage = 'master-pi';
$navSection = 'order';
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/print-brand.php';
?>
<style>
/* ── Controls ─────────────────────────────────────────────────────── */
.mpi-ctrl {
    background:#1e1e3a; padding:14px 24px;
    display:flex; gap:20px; align-items:flex-start; flex-wrap:wrap;
}
.mpi-ctrl-label { font-size:7.5px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#a5b4fc; margin-bottom:4px; }
.mpi-ctrl select {
    background:#2d2d50; color:#fff; border:1.5px solid #4f46e5;
    border-radius:6px; padding:6px 12px; font-size:9px; outline:none; min-width:160px;
}
.mpi-print-btn {
    margin-left:auto; align-self:center; background:#22c55e; color:#fff; border:none;
    border-radius:8px; padding:10px 28px; font-size:9.75px; font-weight:700; cursor:pointer;
}
.mpi-print-btn:hover { background:#16a34a; }
.mpi-excel-btn {
    background:#2563eb; color:#fff; border:none;
    border-radius:8px; padding:10px 22px; font-size:9.75px; font-weight:700; cursor:pointer;
}
.mpi-excel-btn:hover { background:#1d4ed8; }

/* PI selector panel */
.mpi-pi-panel {
    background:#2d2d50; border-radius:8px; padding:10px 14px;
    min-width:260px; max-width:380px; max-height:180px; overflow-y:auto;
}
.mpi-pi-panel-hd {
    display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;
}
.mpi-pi-panel-hd span { font-size:7.5px; font-weight:700; text-transform:uppercase; color:#a5b4fc; }
.mpi-pi-panel-hd button {
    background:none; border:1px solid #4f46e5; color:#a5b4fc;
    font-size:7.5px; border-radius:4px; padding:2px 8px; cursor:pointer;
}
.mpi-pi-check-row {
    display:flex; align-items:center; gap:8px;
    padding:4px 0; border-bottom:1px solid #3d3d60; font-size:9px; color:#e2e8f0;
}
.mpi-pi-check-row:last-child { border-bottom:none; }
.mpi-pi-check-row input[type=checkbox] { accent-color:#6366f1; width:14px; height:14px; cursor:pointer; }
.mpi-pi-check-row label { cursor:pointer; flex:1; }
.mpi-pi-count { font-size:7.5px; color:#94a3b8; white-space:nowrap; }

/* ── PI Document ──────────────────────────────────────────────────── */
#mpiWrap { background:#d1d5db; padding:30px 0; min-height:500px; }
.mpi-doc {
    position:relative; box-sizing:border-box;
    width:210mm; height:297mm; max-width:900px; margin:0 auto 10px;
    font-family:'Times New Roman',Times,serif;
    font-size:8.25pt; color:#000; background:#fff;
    padding:4mm 14mm 12mm; box-shadow:0 4px 24px rgba(0,0,0,.15);
    overflow:hidden;
    display:flex;
    flex-direction:column;
}
.mpi-doc .zzal-print-brand--footer { position:static; margin-top:auto!important; padding-top:6px!important; }
.mpi-content { min-height:0; flex:1 1 auto; display:flex; flex-direction:column; }
.mpi-continuation { display:none; }
.mpi-continuation.is-active { display:flex; }

/* Header */
.mpi-hd {
    display:none; align-items:center;
    border-bottom:3px solid #1a3a6e;
    padding-bottom:10px; margin-bottom:0;
}
.mpi-logo-box {
    flex-shrink:0; margin-right:16px;
    border:2px solid #1a3a6e; padding:4px 6px;
    display:flex; flex-direction:column; align-items:center;
    width:62px; min-height:62px; justify-content:center;
}
.mpi-logo-z    { font-size:21px; font-weight:900; color:#1a3a6e; font-family:Georgia,serif; line-height:1; }
.mpi-logo-zzal { font-size:6.75px; font-weight:900; letter-spacing:3px; color:#1a3a6e; margin-top:2px; }
.mpi-company-name {
    font-size:16.5pt; font-weight:900; color:#1a3a6e;
    font-family:Georgia,serif; letter-spacing:.5px;
    font-variant:small-caps; line-height:1.1;
}
.mpi-title {
    text-align:center; font-size:9.75pt; font-weight:700;
    letter-spacing:8px; color:#000;
    border-top:2px solid #1a3a6e; border-bottom:2px solid #1a3a6e;
    padding:4px 0; margin:2px 0 3px;
}
.mpi-meta   { display:flex; justify-content:space-between; font-size:7.875pt; margin-bottom:2px; }
.mpi-buyer  { font-size:7.875pt; margin:2px 0 1px; }
.mpi-to-label { font-size:7.875pt; font-weight:700; margin:1px 0; }
.mpi-to     { font-size:7.875pt; margin:0 0 1px; line-height:1.35; }
.mpi-conf   { font-size:7.5pt; margin:4px 0; }

/* Table */
.mpi-tbl { width:100%; border-collapse:collapse; font-size:7.5pt; }
.mpi-tbl th {
    background:#fff; color:#111; padding:5px 8px;
    border:1px solid #1a3a6e; text-align:center; font-size:7.125pt; line-height:1.3;
}
.mpi-tbl td { border:1px solid #7a7a7a; padding:4px 8px; vertical-align:top; }
.mpi-tbl tr { page-break-inside:avoid; }
.mpi-tbl td.tc { text-align:center; }
.mpi-tbl td.tr { text-align:right; }
.mpi-tbl tr.ref-row td { border:none; padding:3px 8px 1px; }
.mpi-tbl tr.total-row td { font-weight:700; border-top:2px solid #1a3a6e; }
.mpi-ref-bold { font-weight:700; font-size:7.5pt; }

/* Total words */
.mpi-words {
    font-size:7.5pt; font-weight:700; text-transform:uppercase;
    margin:8px 0 12px; color:#000;
    border-top:1px dashed #333; border-bottom:1px dashed #333;
    padding:4px 0;
}

/* Terms */
.mpi-terms-title { font-size:6.375pt; font-weight:700; text-decoration:underline; margin:0 0 4px; }
.mpi-terms-list  { margin:0; padding-left:32px; font-size:6.05625pt; line-height:1.3; }
.mpi-terms-list li { margin-bottom:0; }

/* Signatures */
.mpi-sig-area { margin-top:36px; }
.mpi-sig-bottom {
    display:flex; justify-content:space-between;
    padding-top:6px; margin-top:160px;
}
.mpi-sig-bottom-label { font-size:7.5pt; font-weight:700; }

/* Footer bar */
.mpi-footer-bar {
    margin-top:24px; border:1.5px solid #000;
    padding:6px 12px; font-size:6.375pt; line-height:1.7;
    text-align:center;
}

/* Empty state */
.mpi-empty { text-align:center; padding:60px 20px; color:#94a3b8; font-family:sans-serif; }

@page { size:A4 portrait; margin:0; }
@media print {
    .mpi-ctrl, nav.page-nav, .order-id-bar { display:none !important; }
    #mpiWrap { background:none !important; padding:0 !important; }
    .mpi-doc { box-shadow:none; margin:0; width:210mm!important; height:297mm!important; max-width:210mm; padding:4mm 14mm 12mm!important; overflow:hidden; display:flex!important; flex-direction:column!important; }
    .mpi-doc .zzal-print-brand--footer { position:static!important; margin-top:auto!important; }
    .mpi-content { min-height:0!important; flex:1 1 auto!important; display:flex!important; flex-direction:column!important; }
    .mpi-continuation:not(.is-active) { display:none!important; }
    .mpi-hd { display:none !important; }
    body, html, .app-shell { width:210mm!important; min-height:297mm!important; margin:0!important; padding:0!important; background:#fff !important; overflow:visible!important; }
    .form-stack { padding:0 !important; }
    .no-print { display:none !important; }
}
html.pi-preview .mpi-ctrl {
    display:none!important;
}
</style>

<!-- ── Controls ── -->
<div class="mpi-ctrl no-print">
    <!-- Hidden selects populated from URL params -->
    <select id="mpiDays"      style="display:none;"><option value="At Sight">At Sight</option><option value="30">30</option><option value="60">60</option><option value="90">90</option><option value="120">120</option></select>
    <select id="mpiLcType"    style="display:none;"><option value="Sight">Sight</option><option value="Usance">Usance</option><option value="Deferred Payment">Deferred Payment</option><option value="Acceptance">Acceptance</option></select>
    <select id="mpiTolerance" style="display:none;"><option value="5">5</option><option value="3">3</option><option value="10">10</option></select>

    <!-- PI checklist -->
    <div>
        <div class="mpi-ctrl-label">Select PIs to Include</div>
        <div class="mpi-pi-panel" id="mpiPiPanel">
            <div class="mpi-pi-panel-hd">
                <span>Available PIs</span>
                <button onclick="mpiToggleAll()">Toggle All</button>
            </div>
            <div id="mpiPiList"><div style="color:#94a3b8;font-size:11px;">Load an order first</div></div>
        </div>
    </div>

    <button class="mpi-excel-btn" onclick="downloadMasterPiExcel()">Download Excel</button>
    <button class="mpi-print-btn" onclick="window.print()">Print / Save PDF</button>
</div>
<script>
(function(){
    const p = new URLSearchParams(window.location.search);
    const set = (id, val) => { const el = document.getElementById(id); if (el && val) el.value = val; };
    set('mpiDays',      p.get('days'));
    set('mpiLcType',    p.get('lctype'));
    set('mpiTolerance', p.get('tol'));
    if (p.get('preview') === '1') document.documentElement.classList.add('pi-preview');
    window._mpiHsCode = p.get('hs') || '4819.10.00';
    window._mpiDocMust = p.get('doc') || 'UD';
    window._mpiBank = p.get('bank') || 'ncc';
})();
</script>

<!-- ── Document ── -->
<div id="mpiWrap">
<div class="mpi-doc" id="mpiDocument">
    <div class="mpi-empty" id="mpiEmpty">Load an order to generate the Master PI</div>
    <div id="mpiContent" class="mpi-content" style="display:none;">

        <?= zzal_print_brand_header() ?>

        <!-- Header -->
        <div class="mpi-hd">
            <div class="mpi-logo-box">
                <span class="mpi-logo-z">Z</span>
                <span class="mpi-logo-zzal">ZZAL</span>
            </div>
            <div class="mpi-company-name">Zaber &amp; Zubair Accessories Ltd.</div>
        </div>

        <!-- Title -->
        <div class="mpi-title">PROFORMA &nbsp;&nbsp;&nbsp; INVOICE</div>

        <!-- PI Number + Date -->
        <div class="mpi-meta">
            <div><strong>PROFOMA INVOICE NO :</strong> <span id="mpiNum">-</span></div>
            <div><strong>Date :</strong> <span id="mpiDate">-</span></div>
        </div>

        <!-- Buyer / TO -->
        <div class="mpi-buyer"><strong>BUYER:</strong> <span id="mpiBuyer">-</span></div>
        <div class="mpi-to-label">TO</div>
        <div class="mpi-to" id="mpiTo">-</div>
        <div class="mpi-conf">WE CONFIRM HAVING SOLD TO YOU THE FOLLOWING MERCHANDISE AS PER TERMS AND CONDITION STATED BELOW.</div>

        <!-- Item Table -->
        <table class="mpi-tbl">
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
            <tbody id="mpiBody"></tbody>
            <tbody id="mpiTotFoot">
                <tr class="total-row">
                    <td colspan="2"></td>
                    <td></td>
                    <td class="tc" id="mpiTotalQty"><strong>-</strong></td>
                    <td></td>
                    <td class="tr" id="mpiTotalVal"><strong>-</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Total in words -->
        <div class="mpi-words">TOTAL AMOUNT : US DOLLER: <span id="mpiWords">-</span></div>

        <!-- Terms & Conditions -->
        <div>
            <div class="mpi-terms-title">Terms &amp; Conditions:</div>
            <ol class="mpi-terms-list" id="mpiTerms"></ol>
        </div>

        <!-- Signatures -->
        <div class="mpi-sig-area" id="mpiSigArea">
            <div class="mpi-sig-bottom">
                <div class="mpi-sig-bottom-label">SIGNATURE OF BUYER</div>
                <div class="mpi-sig-bottom-label">SIGNATURE OF SELLER</div>
            </div>
        </div>

        <!-- Footer bar -->
        <?= zzal_print_brand_footer() ?>

    </div><!-- #mpiContent -->
</div><!-- .mpi-doc -->
<div class="mpi-doc mpi-continuation" id="mpiContinuation">
    <?= zzal_print_brand_header() ?>
    <div class="mpi-title">PROFORMA &nbsp;&nbsp;&nbsp; INVOICE</div>
    <div class="mpi-meta">
        <div><strong>PROFOMA INVOICE NO :</strong> <span id="mpiContNum">-</span></div>
        <div><strong>Date :</strong> <span id="mpiContDate">-</span></div>
    </div>
    <div>
        <div class="mpi-terms-title">Terms &amp; Conditions:</div>
        <ol class="mpi-terms-list" id="mpiTermsCont" start="12"></ol>
    </div>
    <div class="mpi-sig-area" style="margin-top:28mm;">
        <div class="mpi-sig-bottom">
            <div class="mpi-sig-bottom-label">SIGNATURE OF BUYER</div>
            <div class="mpi-sig-bottom-label">SIGNATURE OF SELLER</div>
        </div>
    </div>
    <?= zzal_print_brand_footer() ?>
</div>
</div><!-- #mpiWrap -->

<script>
let _mpiOrderData = null;
let _mpiSelected  = new Set();
let _mpiExcelDone = false;

/* ── Helpers ─────────────────────────────────────────────────── */
function mpiNumWords(n) {
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
function mpiUSD(v) {
    return '$ ' + parseFloat(v||0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
}
function mpiFormatDate(d) {
    if (!d) return '-';
    const dt = new Date(d);
    if (isNaN(dt)) return d;
    return dt.toLocaleDateString('en-GB',{day:'2-digit',month:'2-digit',year:'numeric'});
}
function mpiRenderTerms(terms) {
    const firstTermsEl = document.getElementById('mpiTerms');
    const contTermsEl = document.getElementById('mpiTermsCont');
    const continuationEl = document.getElementById('mpiContinuation');
    const sigAreaEl = document.getElementById('mpiSigArea');
    const docEl = document.getElementById('mpiDocument');

    function splitAt(firstCount) {
        const firstPageTerms = terms.slice(0, firstCount);
        const continuedTerms = terms.slice(firstCount);
        firstTermsEl.innerHTML = firstPageTerms.map(t => `<li>${t}</li>`).join('');
        contTermsEl.innerHTML = continuedTerms.map(t => `<li>${t}</li>`).join('');
        contTermsEl.start = firstPageTerms.length + 1;
        continuationEl.classList.toggle('is-active', continuedTerms.length > 0);
        sigAreaEl.style.display = continuedTerms.length ? 'none' : 'block';
    }

    let firstCount = terms.length;
    splitAt(firstCount);
    while (firstCount > 1 && docEl.scrollHeight > docEl.clientHeight + 2) {
        firstCount -= 1;
        splitAt(firstCount);
    }
}

/* ── Build PI checklist ───────────────────────────────────────── */
function mpiBuildList(res) {
    const pis  = res.pis || [];
    const list = document.getElementById('mpiPiList');
    if (!pis.length) {
        list.innerHTML = '<div style="color:#94a3b8;font-size:11px;">No PIs found - save PIs first</div>';
        return;
    }
    _mpiSelected = new Set(pis.map((_, i) => i)); // select all by default
    list.innerHTML = '';
    pis.forEach((pi, i) => {
        const po       = (pi.pos || [])[0] || {};
        const label    = pi.pi_number || ('PI ' + (i+1));
        const itemCnt  = (po.items || []).length;
        const poRef    = po.poNum || po.customerPo || '';
        const row = document.createElement('div');
        row.className = 'mpi-pi-check-row';
        row.innerHTML = `
            <input type="checkbox" id="mpiChk_${i}" checked onchange="mpiToggle(${i})">
            <label for="mpiChk_${i}">${label}${poRef ? ' - PO '+poRef : ''}</label>
            <span class="mpi-pi-count">${itemCnt} item${itemCnt !== 1 ? 's' : ''}</span>`;
        list.appendChild(row);
    });
}

function mpiToggle(i) {
    if (_mpiSelected.has(i)) _mpiSelected.delete(i);
    else _mpiSelected.add(i);
    renderMasterPi();
}

function mpiToggleAll() {
    const pis = (_mpiOrderData?.pis || []);
    if (_mpiSelected.size === pis.length) {
        _mpiSelected.clear();
        pis.forEach((_, i) => document.getElementById('mpiChk_' + i).checked = false);
    } else {
        pis.forEach((_, i) => { _mpiSelected.add(i); document.getElementById('mpiChk_' + i).checked = true; });
    }
    renderMasterPi();
}

/* ── Render document ──────────────────────────────────────────── */
function renderMasterPi() {
    const res = _mpiOrderData;
    if (!res) return;

    const pis       = res.pis   || [];
    const order     = res.order || {};
    const salesPg   = res.pages?.sales || {};
    const intake    = res.pages?.['marketing-intake'] || {};
    const days      = document.getElementById('mpiDays').value;
    const daysLabel = days === 'At Sight' ? 'At Sight' : days + ' Days';
    const tolerance = document.getElementById('mpiTolerance').value;
    const hsCode    = window._mpiHsCode || '4819.10.00';
    const docMust   = window._mpiDocMust || 'UD';

    const selectedPis = pis.filter((_, i) => _mpiSelected.has(i));

    document.getElementById('mpiEmpty').style.display   = selectedPis.length ? 'none' : 'block';
    document.getElementById('mpiContent').style.display = selectedPis.length ? 'block' : 'none';
    if (!selectedPis.length) return;

    // PI number = first selected PI
    const firstPi = selectedPis[0];
    const masterPiNum = firstPi.pi_number || order.order_id + '-MPI';
    const masterPiDate = mpiFormatDate(firstPi.pi_date || salesPg.piDate || order.created_at?.slice(0,10) || '');
    document.getElementById('mpiNum').textContent  = masterPiNum;
    document.getElementById('mpiDate').textContent = masterPiDate;
    document.getElementById('mpiContNum').textContent  = masterPiNum;
    document.getElementById('mpiContDate').textContent = masterPiDate;

    // Buyer / TO
    const firstPo0 = firstPi.pos?.[0] || {};
    const buyer    = salesPg.buyer || firstPo0.sharedBuyer || firstPo0.endBuyer || intake.pos?.[0]?.endBuyer || '—';
    const custName = salesPg.customer || intake.customer || order.customer_name || '—';
    const custAddr = salesPg.buyerAddress || firstPo0.sharedBuyerAddress || '';
    document.getElementById('mpiBuyer').textContent = buyer;
    document.getElementById('mpiTo').innerHTML =
        `<strong>${custName}</strong>` + (custAddr ? '<br>' + custAddr.replace(/\n/g,'<br>') : '');

    // Build items
    const tbody = document.getElementById('mpiBody');
    tbody.innerHTML = '';
    let totalQty = 0, totalVal = 0, sl = 0;

    selectedPis.forEach(pi => {
        (pi.pos || []).forEach(po => {
            // Item rows
            (po.items || []).forEach(item => {
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
                    <td class="tr">${prc ? mpiUSD(prc) : '—'}</td>
                    <td class="tr">${tot ? mpiUSD(tot) : '—'}</td>`;
                tbody.appendChild(tr);
            });
        });
    });

    document.getElementById('mpiTotalQty').innerHTML = `<strong>${totalQty.toLocaleString()}</strong>`;
    document.getElementById('mpiTotalVal').innerHTML = `<strong>${mpiUSD(totalVal)}</strong>`;
    document.getElementById('mpiWords').textContent  = mpiNumWords(totalVal);

    // Terms & Conditions
    const BANKS = {
        ncc:  { name:'National Credit &amp; Commerce Bank Plc.', addr:'Motijheel main Branch, 6 Motijheel C/A Dhaka-1000 Bangladesh.', acct:'0002-0259000092', swift:'NCCLBDDHNBB', routing:'160150137' },
        dbbl: { name:'Dutch-Bangla Bank Plc.',                   addr:'Local Office, 1, Dilkusha C/A, Dhaka-1000, Bangladesh.',         acct:'ERQ-101.117.1382',  swift:'DBBLBDDHCTS',  routing:'090273889'  }
    };
    const b = BANKS[window._mpiBank] || BANKS.ncc;
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
        `Beneficiary Bin No : <strong>${window._mpiBin || '—'}</strong>`,
        `H.S. Code : <strong>4819.10.00</strong>`,
        `Total Gross Weight: Kgs`,
        `Delivery Terms: <strong>CPT</strong>`,
        `UD Mustbe`,
        `Advising Bank : <strong>${b.name}</strong><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;${b.addr}<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Account No: ${b.acct} &nbsp;|&nbsp; Swift Code: ${b.swift} &nbsp;|&nbsp; Bank Routing No: ${b.routing}`,
    ];
    terms[11] = `Beneficiary Bin No : <strong>000230256-0103</strong>`;
    terms[12] = `H.S. Code : <strong>${hsCode}</strong>`;
    terms[15] = `${docMust} Mustbe`;
    mpiRenderTerms(terms);
}

/* ── Hook into order loader ────────────────────────────────────── */
/* ── Custom item selection (from modal in sales.php) ─────────── */
function renderMasterPiFromCustom(groups, res) {
    // Replace checklist panel with a status label
    const panel = document.getElementById('mpiPiPanel');
    if (panel) panel.innerHTML = '<div style="color:#22c55e;font-size:12px;font-weight:700;">✓ Custom item selection active</div>';

    const order   = res.order || {};
    const salesPg = res.pages?.sales || {};
    const intake  = res.pages?.['marketing-intake'] || {};
    const days    = document.getElementById('mpiDays').value;
    const daysLabel = days === 'At Sight' ? 'At Sight' : days + ' Days';
    const tolerance = document.getElementById('mpiTolerance').value;
    const hsCode    = window._mpiHsCode || '4819.10.00';
    const docMust   = window._mpiDocMust || 'UD';

    const firstPi = (res.pis || [])[0] || {};
    const firstPo = firstPi.pos?.[0] || {};
    const firstGrp = groups[0] || {};

    document.getElementById('mpiEmpty').style.display   = 'none';
    document.getElementById('mpiContent').style.display = 'flex';

    const customPiNum = firstGrp.piNumber || firstPi.pi_number || order.order_id + '-MPI';
    const customPiDate = mpiFormatDate(firstPi.pi_date || salesPg.piDate || '');
    document.getElementById('mpiNum').textContent  = customPiNum;
    document.getElementById('mpiDate').textContent = customPiDate;
    document.getElementById('mpiContNum').textContent  = customPiNum;
    document.getElementById('mpiContDate').textContent = customPiDate;

    const buyer    = salesPg.buyer || firstGrp.sharedBuyer || firstPo.sharedBuyer || '—';
    const custName = salesPg.customer || intake.customer || order.customer_name || '—';
    const custAddr = salesPg.buyerAddress || firstGrp.sharedBuyerAddress || firstPo.sharedBuyerAddress || '';
    document.getElementById('mpiBuyer').textContent = buyer;
    document.getElementById('mpiTo').innerHTML = `<strong>${custName}</strong>` + (custAddr ? '<br>' + custAddr.replace(/\n/g,'<br>') : '');

    const tbody = document.getElementById('mpiBody');
    tbody.innerHTML = '';
    let totalQty = 0, totalVal = 0, sl = 0;

    groups.forEach(grp => {
        (grp.items || []).forEach(item => {
            sl++;
            const qty = parseFloat(item.qty   || 0);
            const prc = parseFloat(item.price || item.unitPrice || 0);
            const tot = parseFloat(item.total || (qty * prc)) || 0;
            totalQty += qty; totalVal += tot;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="tc">${sl}</td>
                <td>${item.desc || item.itemName || '—'}</td>
                <td class="tc">${item.ply || '—'}</td>
                <td class="tc">${qty.toLocaleString()}</td>
                <td class="tr">${prc ? mpiUSD(prc) : '—'}</td>
                <td class="tr">${tot ? mpiUSD(tot) : '—'}</td>`;
            tbody.appendChild(tr);
        });
    });

    document.getElementById('mpiTotalQty').innerHTML = `<strong>${totalQty.toLocaleString()}</strong>`;
    document.getElementById('mpiTotalVal').innerHTML = `<strong>${mpiUSD(totalVal)}</strong>`;
    document.getElementById('mpiWords').textContent  = mpiNumWords(totalVal);

    // Terms (same as renderMasterPi)
    const BANKS = {
        ncc:  { name:'National Credit &amp; Commerce Bank Plc.', addr:'Motijheel main Branch, 6 Motijheel C/A Dhaka-1000 Bangladesh.', acct:'0002-0259000092', swift:'NCCLBDDHNBB', routing:'160150137' },
        dbbl: { name:'Dutch-Bangla Bank Plc.',                   addr:'Local Office, 1, Dilkusha C/A, Dhaka-1000, Bangladesh.',         acct:'ERQ-101.117.1382',  swift:'DBBLBDDHCTS',  routing:'090273889'  }
    };
    const b = BANKS[window._mpiBank] || BANKS.ncc;
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
        `Beneficiary Bin No : <strong>${window._mpiBin || '—'}</strong>`,
        `H.S. Code : <strong>4819.10.00</strong>`,
        `Total Gross Weight: Kgs`, `Delivery Terms: <strong>CPT</strong>`, `UD Mustbe`,
        `Advising Bank : <strong>${b.name}</strong><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;${b.addr}<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Account No: ${b.acct} &nbsp;|&nbsp; Swift Code: ${b.swift} &nbsp;|&nbsp; Bank Routing No: ${b.routing}`,
    ];
    terms[11] = `Beneficiary Bin No : <strong>000230256-0103</strong>`;
    terms[12] = `H.S. Code : <strong>${hsCode}</strong>`;
    terms[15] = `${docMust} Mustbe`;
    mpiRenderTerms(terms);
}

function downloadMasterPiExcel() {
    atsDownloadExcelFromElement({
        elementId:'mpiDocument',
        filename:'master-pi-'+((window.getCurrentOrderId && window.getCurrentOrderId()) || 'document'),
        title:document.title
    });
}

window.onOrderLoad = (function(_prev) {
    return function(res) {
        if (typeof _prev === 'function') _prev(res);
        _mpiOrderData = res;

        // Custom item selection from the modal in sales.php
        const customJson = sessionStorage.getItem('mpi_custom_items');
        if (customJson) {
            try {
                const groups = JSON.parse(customJson);
                sessionStorage.removeItem('mpi_custom_items');
                renderMasterPiFromCustom(groups, res);
                if (atsShouldAutoExcel() && !_mpiExcelDone) {
                    _mpiExcelDone = true;
                    setTimeout(downloadMasterPiExcel, 250);
                }
                return;
            } catch(e) {}
        }

        mpiBuildList(res);
        renderMasterPi();
        if (atsShouldAutoExcel() && !_mpiExcelDone) {
            _mpiExcelDone = true;
            setTimeout(downloadMasterPiExcel, 250);
        }
    };
})(window.onOrderLoad);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
