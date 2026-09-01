<?php
$pageTitle  = 'Print Proforma Invoice';
$activePage = 'sales';
$navSection = 'order';
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/print-brand.php';
?>
<style>
/* ── Controls (hidden on print) ─────────────────────────────────── */
.pi-ctrl { background:#1e1e3a; color:#fff; padding:14px 24px; display:flex; gap:16px; align-items:center; flex-wrap:wrap; }
.pi-ctrl label { font-size:8.25px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#a5b4fc; margin-right:4px; }
.pi-ctrl select, .pi-ctrl input[type=number] {
    background:#2d2d50; color:#fff; border:1.5px solid #4f46e5; border-radius:6px;
    padding:5px 10px; font-size:9px; outline:none;
}
.pi-type-btn { padding:7px 18px; border:1.5px solid #6366f1; border-radius:6px; background:transparent; color:#a5b4fc; font-size:9px; font-weight:700; cursor:pointer; transition:.15s; }
.pi-type-btn.active { background:#4f46e5; color:#fff; border-color:#4f46e5; }
.pi-print-btn { margin-left:auto; background:#22c55e; color:#fff; border:none; border-radius:8px; padding:9px 24px; font-size:9.75px; font-weight:700; cursor:pointer; }
.pi-po-sel { display:none; }

/* ── PI Document ─────────────────────────────────────────────────── */
.pi-wrap { position:relative; box-sizing:border-box; width:210mm; height:297mm; max-width:900px; margin:24px auto 10px; font-family:'Times New Roman',Times,serif; font-size:8.25pt; color:#000; background:#fff; padding:14mm 14mm 12mm; box-shadow:0 2px 20px rgba(0,0,0,.12); overflow:hidden; display:flex; flex-direction:column; }
.pi-wrap .zzal-print-brand--footer { position:static; margin-top:auto!important; padding-top:6px!important; }
.pi-continuation { display:none; }

/* Header */
.pi-header { display:none; align-items:center; border-bottom:3px solid #1a3a6e; padding-bottom:10px; margin-bottom:6px; }
.pi-logo { width:64px; height:64px; background:#1a3a6e; color:#fff; display:flex; flex-direction:column; align-items:center; justify-content:center; font-size:13.5px; font-weight:900; border-radius:4px; flex-shrink:0; margin-right:14px; letter-spacing:1px; }
.pi-logo span { font-size:5.25px; font-weight:400; letter-spacing:2px; margin-top:1px; }
.pi-company { flex:1; }
.pi-company h1 { font-size:18pt; font-weight:900; color:#1a3a6e; margin:0; letter-spacing:1px; font-family:Georgia,serif; }
.pi-title-line { text-align:center; font-size:10.5pt; font-weight:700; letter-spacing:4px; color:#1a3a6e; border-top:2px solid #1a3a6e; border-bottom:2px solid #1a3a6e; padding:4px 0; margin:8px 0; }

/* PI meta */
.pi-meta { display:flex; justify-content:space-between; margin:10px 0 6px; font-size:8.25pt; }
.pi-meta b { font-weight:700; }
.pi-to { margin:6px 0 10px; font-size:8.25pt; line-height:1.6; }
.pi-confirm { font-size:7.5pt; margin:8px 0 12px; font-style:italic; }

/* Table */
.pi-table { width:100%; border-collapse:collapse; margin-bottom:8px; font-size:7.5pt; }
.pi-table th { background:#fff; color:#111; padding:6px 8px; text-align:center; border:1px solid #1a3a6e; font-size:7.125pt; }
.pi-table td { border:1px solid #888; padding:5px 8px; vertical-align:top; }
.pi-table td.center { text-align:center; }
.pi-table td.right  { text-align:right; }
.pi-table tr.total-row td { font-weight:700; background:#f0f4ff; border-top:2.5px solid #1a3a6e; }
.pi-ref { font-weight:700; font-size:7.125pt; }
.pi-words { font-size:7.5pt; font-weight:700; margin:6px 0 12px; text-transform:uppercase; }

/* Terms */
.pi-terms { margin-top:10px; }
.pi-terms-grow { flex:1; display:flex; flex-direction:column; }
.pi-terms-grow .pi-terms { flex:1; }
.pi-terms h3 { font-size:7.5pt; font-weight:700; text-decoration:underline; margin-bottom:4px; }
.pi-terms ol { margin:0; padding-left:28px; font-size:7.125pt; line-height:1.55; }
.pi-terms ol li { margin-bottom:2px; }

/* Signatures */
.pi-sigs { display:flex; justify-content:space-between; margin-top:16px; }
.pi-sig-box { text-align:center; }
.pi-sig-line { border-top:1.5px solid #000; width:200px; margin:0 auto 4px; }
.pi-sig-label { font-size:7.125pt; font-weight:700; }
.pi-sig-sub { font-size:6.375pt; color:#444; }

/* Print */
@media print {
    @page { size:A4 portrait; margin:0; }
    .pi-ctrl, nav, .order-id-bar, .form-stack > *:not(#piWrap) { display:none !important; }
    body, html { width:210mm!important; min-height:297mm!important; margin:0!important; padding:0!important; background:#fff !important; overflow:visible!important; }
    .pi-wrap { box-shadow:none; margin:0; width:210mm!important; height:297mm!important; max-width:210mm; padding:14mm 14mm 12mm!important; overflow:hidden; display:flex!important; flex-direction:column!important; }
    .pi-wrap .zzal-print-brand--footer { position:static!important; margin-top:auto!important; }
    .pi-header { display:none !important; }
    .app-shell { display:block !important; }
    .form-stack { padding:0 !important; }
}
</style>

<!-- ── Controls ── -->
<div class="pi-ctrl no-print" id="piCtrl">
    <div>
        <label>PI Type</label>
        <button class="pi-type-btn active" onclick="setPiType('single',this)">Single PI</button>
        <button class="pi-type-btn" onclick="setPiType('summary',this)">Summary PI</button>
        <button class="pi-type-btn" onclick="setPiType('master',this)">Master PI</button>
    </div>
    <div class="pi-po-sel" id="piPoSelWrap">
        <label>Select PO</label>
        <select id="piPoSel" onchange="renderPi()"><option value="">— All —</option></select>
    </div>
    <div>
        <label>LC Days</label>
        <select id="piDays" onchange="renderPi()">
            <option value="90">90 Days</option>
            <option value="120">120 Days</option>
        </select>
    </div>
    <div>
        <label>LC Type</label>
        <select id="piLcType" onchange="renderPi()">
            <option value="Sight">Sight</option>
            <option value="Usance">Usance</option>
            <option value="Deferred Payment">Deferred Payment</option>
            <option value="Acceptance">Acceptance</option>
        </select>
    </div>
    <div>
        <label>Tolerance</label>
        <select id="piTolerance" onchange="renderPi()">
            <option value="5">+/- 5%</option>
            <option value="3">+/- 3%</option>
            <option value="10">+/- 10%</option>
        </select>
    </div>
    <button class="pi-print-btn" onclick="window.print()">🖨 Print / Save PDF</button>
</div>

<!-- ── PI Document ── -->
<div id="piWrap" style="background:#e8eaf0; padding:20px 0;">
<div class="pi-wrap" id="piDocument">

    <?= zzal_print_brand_header() ?>

    <div class="pi-header">
        <div class="pi-logo">ZZAL<span>ACCESSORIES</span></div>
        <div class="pi-company">
            <h1>Zaber &amp; Zubair Accessories Ltd.</h1>
        </div>
    </div>

    <div class="pi-title-line">PROFORMA &nbsp; INVOICE</div>

    <div class="pi-meta">
        <div><b>PROFOMA INVOICE NO :</b> <span id="piDocNum">—</span></div>
        <div><b>Date :</b> <span id="piDocDate">—</span></div>
    </div>
    <div style="margin:4px 0 2px;"><b>BUYER:</b> <span id="piDocBuyer">—</span></div>
    <div style="margin:4px 0 2px;"><b>TO</b></div>
    <div class="pi-to" id="piDocTo">—</div>
    <div class="pi-confirm">WE CONFIRM HAVING SOLD TO YOU THE FOLLOWING MERCHANDISE AS PER TERMS AND CONDITION STATED BELOW.</div>

    <table class="pi-table">
        <thead>
            <tr>
                <th style="width:40px;">SL NO</th>
                <th>Description of goods</th>
                <th style="width:100px;">Quantity/<br>Pcs/con</th>
                <th style="width:90px;">Unit Price</th>
                <th style="width:110px;">Total Amount<br>(USD)</th>
            </tr>
        </thead>
        <tbody id="piDocBody">
            <tr><td colspan="5" style="text-align:center;color:#999;padding:20px;">Load an order to generate PI</td></tr>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" style="text-align:right;font-weight:700;">TOTAL</td>
                <td class="center" id="piDocTotalQty">—</td>
                <td></td>
                <td class="right" id="piDocTotalVal">—</td>
            </tr>
        </tfoot>
    </table>

    <div class="pi-words">TOTAL AMOUNT : US DOLLAR: <span id="piDocWords">—</span></div>

    <div class="pi-terms-grow">
        <div class="pi-terms">
            <h3>Terms &amp; Conditions:</h3>
            <ol id="piTermsList">
                <!-- populated by JS -->
            </ol>
        </div>
        <div class="pi-sigs" id="piSigs">
            <div class="pi-sig-box">
                <div class="pi-sig-line"></div>
                <div class="pi-sig-label">SIGNATURE OF BUYER</div>
            </div>
            <div class="pi-sig-box" style="text-align:right;">
                <div class="pi-sig-line" style="margin-left:auto;margin-right:0;margin-top:40px;"></div>
                <div class="pi-sig-label">Authorised Signature</div>
            </div>
        </div>
    </div>

        <?= zzal_print_brand_footer() ?>

    </div><!-- .pi-wrap -->
    <div class="pi-wrap pi-continuation" id="piContinuation">
        <?= zzal_print_brand_header() ?>
        <div class="pi-title-line">PROFORMA &nbsp; INVOICE</div>
        <div class="pi-meta">
            <div><b>PROFOMA INVOICE NO :</b> <span id="piContDocNum">-</span></div>
            <div><b>Date :</b> <span id="piContDocDate">-</span></div>
        </div>
        <div class="pi-terms-grow">
            <div class="pi-terms">
                <h3>Terms &amp; Conditions:</h3>
                <ol id="piTermsCont" start="12"></ol>
            </div>
        <div class="pi-sigs" style="margin-top:16px;">
            <div class="pi-sig-box">
                <div class="pi-sig-line"></div>
                <div class="pi-sig-label">SIGNATURE OF BUYER</div>
            </div>
            <div class="pi-sig-box" style="text-align:right;">
                <div class="pi-sig-line" style="margin-left:auto;margin-right:0;margin-top:40px;"></div>
                <div class="pi-sig-label">Authorised Signature</div>
            </div>
        </div>
        </div>
        <?= zzal_print_brand_footer() ?>
    </div>
</div><!-- #piWrap -->

<script>
let _piOrder = null;
let _piType  = 'single';

function setPiType(type, btn) {
    _piType = type;
    document.querySelectorAll('.pi-type-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('piPoSelWrap').style.display = type === 'single' ? 'flex' : 'none';
    renderPi();
}

// ── Number to words ───────────────────────────────────────────────
function numToWords(n) {
    const amount = parseFloat(n || 0) || 0;
    const ones = ['ZERO','ONE','TWO','THREE','FOUR','FIVE','SIX','SEVEN','EIGHT','NINE','TEN','ELEVEN','TWELVE','THIRTEEN','FOURTEEN','FIFTEEN','SIXTEEN','SEVENTEEN','EIGHTEEN','NINETEEN'];
    const tens = ['','','TWENTY','THIRTY','FORTY','FIFTY','SIXTY','SEVENTY','EIGHTY','NINETY'];
    const scales = ['', 'THOUSAND', 'MILLION', 'BILLION'];
    const chunkWords = x => {
        x = Math.floor(x);
        if (x === 0) return '';
        if (x < 20) return ones[x];
        if (x < 100) return tens[Math.floor(x / 10)] + (x % 10 ? ' ' + ones[x % 10] : '');
        return ones[Math.floor(x / 100)] + ' HUNDRED' + (x % 100 ? ' ' + chunkWords(x % 100) : '');
    };
    const integerWords = x => {
        x = Math.floor(x);
        if (x === 0) return 'ZERO';
        let parts = [];
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
    };
    let centsTotal = Math.round(amount * 100);
    let dollars = Math.floor(centsTotal / 100);
    let cents = centsTotal % 100;
    if (cents === 100) {
        dollars += 1;
        cents = 0;
    }
    let words = integerWords(dollars);
    if (cents > 0) words += ' & CENTS ' + integerWords(cents);
    return words + ' ONLY.';
}

function formatUSD(v) { return '$ ' + parseFloat(v||0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); }

// ── Render PI document ────────────────────────────────────────────
function renderPi() {
    if (!_piOrder) return;

    const days      = document.getElementById('piDays').value;
    const lcType    = document.getElementById('piLcType').value;
    const tolerance = document.getElementById('piTolerance').value;
    const order     = _piOrder.order || {};
    const salesPage = _piOrder.pages?.sales || {};
    const intake    = _piOrder.pages?.['marketing-intake'] || {};
    const hsCode    = salesPage.termHsCode || '4819.10.00';
    const docMust   = salesPage.termDocMust || 'UD';

    // Determine which PIs to use based on type
    const allPis = _piOrder.pis || [];
    let workPis  = [];
    if (_piType === 'master') {
        const master = allPis.find(p => p.is_master);
        workPis = master ? [master] : allPis;
    } else if (_piType === 'summary') {
        workPis = allPis;
    } else {
        // single: use selected PO
        const selVal = document.getElementById('piPoSel').value;
        workPis = allPis.filter(p => !selVal || String(p.id) === selVal || p.pi_number === selVal);
        if (!workPis.length) workPis = allPis.slice(0,1);
    }

    // Collect all POs from selected PIs
    let allPos = [];
    workPis.forEach(pi => { (pi.pos || []).forEach(po => allPos.push({pi, po})); });

    // PI Number & Date
    const piNum  = workPis[0]?.pi_number || salesPage.piNum || (order.order_id + '-PI');
    const piDate = workPis[0]?.pi_date   || salesPage.piDate || order.created_at?.slice(0,10) || '';
    const displayDate = piDate ? new Date(piDate).toLocaleDateString('en-GB',{day:'2-digit',month:'2-digit',year:'numeric'}).replace(/\//g,'/') : '—';

    // Buyer & Customer
    const buyer    = allPos[0]?.po?.buyer      || salesPage.pos?.[0]?.buyer || intake.pos?.[0]?.endBuyer || '—';
    const custName = salesPage.customer         || intake.customer            || order.customer_name || '—';
    const custAddr = salesPage.buyerAddress     || intake.address            || '';

    document.getElementById('piDocNum').textContent   = piNum;
    document.getElementById('piDocDate').textContent  = displayDate;
    document.getElementById('piContDocNum').textContent   = piNum;
    document.getElementById('piContDocDate').textContent  = displayDate;
    document.getElementById('piDocBuyer').textContent = buyer;
    document.getElementById('piDocTo').innerHTML      = `<b>${custName}</b>${custAddr ? '<br>' + custAddr : ''}`;

    // Build item rows
    const tbody = document.getElementById('piDocBody');
    tbody.innerHTML = '';
    let totalQty = 0, totalVal = 0, sl = 0;

    if (_piType === 'single') {
        // One section per PO
        allPos.forEach(({pi, po}) => {
            const orderRef = po.orderRef || po.salesOrder || po.salesOrderNo || '';
            const poNum    = po.poNum    || po.customerPo || '';
            const style    = po.style    || '';
            if (orderRef || poNum) {
                const refTr = document.createElement('tr');
                refTr.innerHTML = `<td></td><td colspan="4"><span class="pi-ref">
                    ${orderRef ? 'ORDER REF: ' + orderRef + '<br>' : ''}
                    ${poNum    ? 'PO # ' + poNum + (style ? ' Style# ' + style + '/' : '') : ''}
                </span></td>`;
                tbody.appendChild(refTr);
            }
            (po.items || []).forEach(item => {
                sl++;
                const qty = parseFloat(item.qty || 0);
                const prc = parseFloat(item.price || item.unitPrice || 0);
                const tot = parseFloat(item.total || (qty * prc)) || 0;
                totalQty += qty;
                totalVal += tot;
                const tr = document.createElement('tr');
                tr.innerHTML = `<td class="center">${sl}</td>
                    <td>${item.desc || item.itemName || '—'}</td>
                    <td class="center">${qty.toLocaleString()}</td>
                    <td class="right">${prc ? formatUSD(prc) : '—'}</td>
                    <td class="right">${tot ? formatUSD(tot) : '—'}</td>`;
                tbody.appendChild(tr);
            });
        });
    } else {
        // Summary / Master: group by PI
        workPis.forEach(pi => {
            const piPos = pi.pos || [];
            if (piPos.length) {
                const allOrderRefs = piPos.map(p => p.orderRef || p.salesOrderNo).filter(Boolean).join(',');
                const allPoNums    = piPos.map(p => p.poNum || p.customerPo).filter(Boolean).join('/');
                if (allOrderRefs || allPoNums) {
                    const refTr = document.createElement('tr');
                    refTr.innerHTML = `<td></td><td colspan="4"><span class="pi-ref">
                        ${allOrderRefs ? 'ORDER REF: ' + allOrderRefs + '<br>' : ''}
                        ${allPoNums   ? 'PO # ' + allPoNums : ''}
                    </span></td>`;
                    tbody.appendChild(refTr);
                }
                piPos.forEach(po => {
                    (po.items || []).forEach(item => {
                        sl++;
                        const qty = parseFloat(item.qty || 0);
                        const prc = parseFloat(item.price || item.unitPrice || 0);
                        const tot = parseFloat(item.total || (qty * prc)) || 0;
                        totalQty += qty;
                        totalVal += tot;
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td class="center">${sl}</td>
                            <td>${item.desc || item.itemName || '—'}</td>
                            <td class="center">${qty.toLocaleString()}</td>
                            <td class="right">${prc ? formatUSD(prc) : '—'}</td>
                            <td class="right">${tot ? formatUSD(tot) : '—'}</td>`;
                        tbody.appendChild(tr);
                    });
                });
            }
        });
    }

    if (!sl) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#999;padding:14px;">No items found in this PI</td></tr>';
    }

    document.getElementById('piDocTotalQty').textContent = totalQty.toLocaleString();
    document.getElementById('piDocTotalVal').textContent  = formatUSD(totalVal);
    document.getElementById('piDocWords').textContent     = numToWords(totalVal);

    // Terms & Conditions
    const terms = [
        `100% Irrevocable confirmed <b>${days} Days ${lcType}</b> L/C to be opened in favour of <b>Zaber &amp; Zubair ACC. Ltd.</b>`,
        `P.I Validity : <b>45 Working days</b>.`,
        `Letter of Credit to allow acceptability of <b>+/- ${tolerance}% tolerance</b> in quantity and Value.`,
        `Letter of Credit to allow for <b>Partial Shipment</b>.`,
        `The Buyer should provide a copy of the master L/C and Garment Export UD before the delivery of mentioned goods.`,
        `Where GSP certificate is required, applicant is requested to furnish full detail of the Master L/C in BBLC opened in favour of Zaber &amp; Zubair ACC. Ltd.`,
        `Prior to delivery- we will inform you full particulars of the consignment and forward the original delivery challan for the signature of the authorised signatory of your organisation. Please make arrangements to hand over the duly signed delivery challan at the time of delivery of goods.`,
        `Payment to be made on Maturity in US Dollar and Maturity date will be counted <b>${days} Days</b> from the date of DELIVERY Challan/ Truck Receipt / <b>This clause Will be integral Parts of L/C.</b>`,
        `Interest to be paid at LIBOR by the Buyer till Maturity. If payment is not made within maturity then interest <b>@16%</b> will be charged for overdue period and buyer's is liable to pay. <b>This clause Must be appeared on the L/C</b>`,
        `Quality complaint, if any, should be notified to us prior before sewing.`,
        `The above mention terms &amp; condition will be the integral part of the BTB L/C &amp; it must be mention in the BTB L/C.`,
        `Beneficiary Bin No : <b>000230256-0103</b>`,
        `H.S.Code : <b>4819.10.00</b>`,
        `UD Mustbe`,
        `Swift : <b>NCCLBDDHMTB</b>`,
        `Advising Bank : <b>National Credit &amp; Commerce Bank LTD</b> Motijheel main Branch, 6 Motijheel C/A Dhaka-1000 Bangladesh.`,
    ];
    terms[12] = `H.S.Code : <b>${hsCode}</b>`;
    terms[13] = `${docMust} Mustbe`;

    const firstPageTerms = terms.slice(0, 10);
    const continuedTerms = terms.slice(10);
    const ol = document.getElementById('piTermsList');
    ol.innerHTML = firstPageTerms.map(t => `<li>${t}</li>`).join('');
    document.getElementById('piTermsCont').innerHTML = continuedTerms.map(t => `<li>${t}</li>`).join('');
    document.getElementById('piTermsCont').start = firstPageTerms.length + 1;
    document.getElementById('piContinuation').style.display = continuedTerms.length ? 'block' : 'none';
    document.getElementById('piSigs').style.display = continuedTerms.length ? 'none' : 'flex';

    // Populate PO selector for Single PI
    const sel = document.getElementById('piPoSel');
    const cur = sel.value;
    sel.innerHTML = '<option value="">— All POs —</option>';
    allPis.forEach(pi => {
        const opt = document.createElement('option');
        opt.value = pi.pi_number || pi.id;
        opt.textContent = pi.pi_number || ('PI ' + pi.id);
        sel.appendChild(opt);
    });
    if (cur) sel.value = cur;
}

// ── Order load hook ───────────────────────────────────────────────
window.onOrderLoad = (function(_prev) {
    return function(res) {
        if (typeof _prev === 'function') _prev(res);
        _piOrder = res;
        renderPi();
    };
})(window.onOrderLoad);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
