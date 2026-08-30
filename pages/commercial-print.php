<?php
$pageTitle  = 'Commercial Invoice Print';
$activePage = 'commercial';
$navSection = 'order';
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/print-brand.php';
?>
<style>
.ci-ctrl {
    background:#1e1e3a;
    padding:14px 24px;
    display:flex;
    gap:18px;
    align-items:center;
    flex-wrap:wrap;
}
.ci-ctrl-group {
    display:flex;
    flex-direction:column;
    gap:4px;
}
.ci-ctrl-label {
    font-size:10px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.05em;
    color:#a5b4fc;
}
.ci-ctrl select {
    background:#2d2d50;
    color:#fff;
    border:1.5px solid #4f46e5;
    border-radius:6px;
    padding:6px 12px;
    font-size:12px;
    outline:none;
    min-width:190px;
}
.ci-print-btn {
    margin-left:auto;
    background:#22c55e;
    color:#fff;
    border:none;
    border-radius:8px;
    padding:10px 28px;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
}
.ci-print-btn:hover { background:#16a34a; }
.ci-excel-btn {
    background:#2563eb;
    color:#fff;
    border:none;
    border-radius:8px;
    padding:10px 22px;
    font-size:13px;
    font-weight:700;
    cursor:pointer;
}
.ci-excel-btn:hover { background:#1d4ed8; }
#ciWrap {
    background:#d1d5db;
    padding:28px 0;
    min-height:520px;
}
.ci-empty {
    text-align:center;
    padding:60px 20px;
    color:#94a3b8;
    font-family:sans-serif;
}
.ci-page {
    position:relative;
    box-sizing:border-box;
    width:210mm;
    height:296mm;
    max-width:820px;
    margin:0 auto 18px;
    background:#fff;
    box-shadow:0 4px 24px rgba(0,0,0,.14);
    padding:14mm 14mm 30mm;
    font-family:'Times New Roman', Times, serif;
    color:#111;
    font-size:10px;
    line-height:1.25;
    display:flex;
    flex-direction:column;
}
.ci-page:not(:last-child) {
    break-after:page;
    page-break-after:always;
}
.ci-page:last-child {
    break-after:auto;
    page-break-after:auto;
}
.ci-page .zzal-print-brand--footer {
    position:static;
    margin:0 !important;
    margin-top:auto !important;
    padding-top:6px !important;
}
.ci-title {
    text-align:center;
    font-size:12px;
    font-weight:700;
    text-transform:uppercase;
    text-decoration:underline;
    margin:1px 0 6px;
}
.ci-topbox {
    width:100%;
    border-collapse:collapse;
    margin-bottom:8px;
}
.ci-topbox td {
    border:1px solid #333;
    vertical-align:top;
    padding:4px 6px;
    width:50%;
}
.ci-topgrid { display:grid; gap:2px; }
.ci-meta-row {
    display:grid;
    grid-template-columns:82px 1fr;
    gap:6px;
}
.ci-meta-label { font-weight:700; }
.ci-buyer {
    margin:5px 0 2px;
    font-weight:700;
}
.ci-items {
    width:100%;
    border-collapse:collapse;
    margin-top:4px;
}
.ci-items th,
.ci-items td {
    border:1px solid #333;
    padding:3px 5px;
    vertical-align:top;
}
.ci-items th {
    font-weight:700;
    text-align:center;
}
.ci-items td.center { text-align:center; }
.ci-items td.right { text-align:right; }
.ci-footnote { margin-top:6px; }
.ci-sign-block { margin-top:24px; }
.ci-sign-line {
    width:120px;
    border-top:1px solid #000;
    margin-top:28px;
    margin-bottom:4px;
}
.ci-bottom-bar {
    margin-top:auto;
    padding-top:4px;
    border-top:1px solid #000;
    font-size:9px;
    text-align:center;
}
@media print {
    @page { size:A4 portrait; margin:0; }
    .ci-ctrl, nav.page-nav, .order-id-bar, .no-print { display:none !important; }
    html, body {
        width:210mm !important;
        min-height:0 !important;
        margin:0 !important;
        padding:0 !important;
        overflow:visible !important;
    }
    .app-shell, .form-stack {
        display:block !important;
        margin:0 !important;
        padding:0 !important;
        background:#fff !important;
    }
    .form-stack > *:not(#ciWrap) {
        display:none !important;
    }
    #ciWrap {
        display:block !important;
        background:none !important;
        padding:0 !important;
        margin:0 !important;
        width:210mm !important;
        min-height:0 !important;
    }
    #ciPages {
        display:block !important;
        margin:0 !important;
        padding:0 !important;
    }
    .ci-page {
        box-shadow:none;
        box-sizing:border-box;
        margin:0;
        max-width:210mm;
        width:210mm !important;
        height:auto !important;
        min-height:286mm !important;
        padding:14mm 14mm 30mm !important;
        overflow:hidden;
        display:flex;
        flex-direction:column;
        break-inside:avoid;
        page-break-inside:avoid;
    }
    .ci-page .zzal-print-brand--footer {
        position:static !important;
        margin:0 !important;
        margin-top:auto !important;
    }
    .ci-page:not(:last-child) { break-after:page; page-break-after:always; }
    .ci-page:last-child { break-after:auto !important; page-break-after:auto !important; }
    body, html { background:#fff !important; }
}
</style>

<div class="ci-ctrl no-print">
    <div class="ci-ctrl-group">
        <span class="ci-ctrl-label">Invoice Copy</span>
        <select id="ciDocSel"></select>
    </div>
    <button type="button" class="ci-excel-btn" onclick="downloadCommercialExcel()">Download Excel</button>
    <button type="button" class="ci-print-btn" onclick="printCommercialInvoice()">Print / Save PDF</button>
</div>

<div id="ciWrap">
    <div id="ciPages">
        <div class="ci-empty">Load an order to generate the Commercial Invoice.</div>
    </div>
</div>

<script>
const CI_COMPANY_NAME = 'Zaber & Zubair Accessories Ltd.';
const CI_BRAND_HEADER = <?php echo json_encode(zzal_print_brand_header(), JSON_UNESCAPED_SLASHES); ?>;
const CI_BRAND_FOOTER = <?php echo json_encode(zzal_print_brand_footer(), JSON_UNESCAPED_SLASHES); ?>;

function ciEsc(val) {
    return String(val ?? '-')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function ciSplit(val) {
    return String(val || '')
        .split(/\r?\n/)
        .map(v => v.trim())
        .filter(Boolean);
}

function ciLines(val) {
    return ciSplit(val).join('<br>');
}

function ciPlainText(val) {
    return ciSplit(val).join(', ');
}

const CI_BANKS = {
    ncc: {
        name: 'National Credit & Commerce Bank Plc.',
        address: 'Motijheel main Branch, 6 Motijheel C/A, Dhaka-1000, Bangladesh.',
        account: '0002-0259000092',
        swift: 'NCCLBDDHNBB',
        routing: '160150137'
    },
    dbbl: {
        name: 'Dutch-Bangla Bank Plc.',
        address: 'Local Office, 1, Dilkusha C/A, Dhaka-1000, Bangladesh.',
        account: 'ERQ-101.117.1382',
        swift: 'DBBLBDDHCTS',
        routing: '090273889'
    }
};

function ciResolveBank(text, fallbackKey = 'ncc') {
    const raw = String(text ?? '').trim();
    const lower = raw.toLowerCase();
    const looksLikeLabel = !raw || /^(reimbursment|reimbursement|negotiating|benificiary|beneficiary)\b/i.test(raw) || /bank details|auto-filled|full bank/i.test(lower);
    const bank = CI_BANKS[fallbackKey] || CI_BANKS.ncc;
    if (looksLikeLabel) {
        return [bank.name, bank.address, `Account No: ${bank.account}`, `Swift Code: ${bank.swift}`, `Bank Routing No: ${bank.routing}`].join('\n');
    }
    if (lower.includes('dutch-bangla') || lower.includes('dbbl')) {
        const b = CI_BANKS.dbbl;
        return [b.name, b.address, `Account No: ${b.account}`, `Swift Code: ${b.swift}`, `Bank Routing No: ${b.routing}`].join('\n');
    }
    if (lower.includes('national credit') || lower.includes('ncc')) {
        const b = CI_BANKS.ncc;
        return [b.name, b.address, `Account No: ${b.account}`, `Swift Code: ${b.swift}`, `Bank Routing No: ${b.routing}`].join('\n');
    }
    return raw;
}

function ciMoney(num, digits = 2) {
    const n = parseFloat(num || 0) || 0;
    return n.toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });
}

function ciQty(num) {
    const n = parseFloat(num || 0) || 0;
    return Number.isInteger(n) ? ciMoney(n, 0) : ciMoney(n, 2);
}

function ciDate(val) {
    if (!val) return '-';
    if (/^\d{4}-\d{2}-\d{2}$/.test(val)) {
        const [y,m,d] = val.split('-');
        return `${d}/${m}/${y}`;
    }
    return val;
}

function ciResolveDocs(res) {
    const order = res.order || {};
    const sales = res.pages?.sales || {};
    const comm = res.pages?.commercial || {};
    const resolved = window.atsResolveDisplayPos ? window.atsResolveDisplayPos(res) : { pos: sales.pos || [] };
    const pos = resolved.pos || [];
    if (pos.length) {
        return pos.map((po, idx) => ({
            key: `po_${idx}`,
            title: po.poNum || po.piNum || `Invoice ${idx + 1}`,
            po
        }));
    }
    return [{
        key: 'all',
        title: comm.invoiceNo || sales.piNum || order.order_id || 'Commercial Invoice',
        po: {
            poNum: comm.proformaNo || sales.piNum || '',
            buyer: order.buyer_name || '',
            items: []
        }
    }];
}

function ciBuildPages() {
    const res = window._ciRes;
    const holder = document.getElementById('ciPages');
    if (!res || !holder) return;

    const order = res.order || {};
    const sales = res.pages?.sales || {};
    const lc = res.pages?.lc || {};
    const exch = res.pages?.exchange || {};
    const comm = res.pages?.commercial || {};
    const docs = ciResolveDocs(res);
    const selected = document.getElementById('ciDocSel')?.value || 'all';
    const chosen = selected === 'all' ? docs : docs.filter(doc => doc.key === selected);

    if (!chosen.length) {
        holder.innerHTML = '<div class="ci-empty">No Commercial Invoice source found for this order.</div>';
        return;
    }

    const beneficiaryName = comm.commercialBeneficiaryName || CI_COMPANY_NAME;
    const beneficiaryAddress = comm.commercialBeneficiaryAddress || '';
    const factoryAddress = comm.commercialFactoryAddress || '';
    const advisingBank = ciResolveBank(comm.commercialAdvisingBank || exch.payToBankName || lc.reimbursementBank || '', 'ncc');
    const consigneeName = comm.commercialConsigneeName || order.customer_name || sales.customer || '';
    const consigneeAddress = comm.commercialConsigneeAddress || sales.buyerAddress || '';
    const consigneeBank = ciResolveBank(comm.commercialConsigneeBankAddress || exch.beneficiaryBankAddress || exch.negotiatingBankAddress || lc.negotiatingBeneficiaryBank || '', 'dbbl');
    const issuingBankAddress = ciResolveBank(comm.commercialIssuingBankAddress || exch.applicantBank || lc.lcIssuingBank || '', 'ncc');
    const issuingBankName = comm.commercialIssuingBankName || (issuingBankAddress.split('\n')[0] || '');
    const invoiceNo = comm.invoiceNo || sales.piNum || order.order_id || '-';
    const invoiceDate = ciDate(comm.invoiceDate || sales.piDate || order.created_at?.slice(0,10) || '');
    const lcNo = comm.commercialLcNo || exch.masterLcNo || lc.lcNumber || '-';
    const lcDate = ciDate(comm.commercialLcDate || exch.masterLcDate || lc.lcDate || '');
    const proformaNo = comm.proformaNo || sales.piNum || '-';
    const proformaDate = ciDate(comm.proformaDate || sales.piDate || '');
    // §4 — these three are fixed/static, never taken from saved data.
    const placeLoading = "Supplier's Factory";
    const placeDelivery = "Opener's Factory";
    const carrier = 'Bangladesh, By Truck';
    const applicantsText = comm.commercialApplicantsText || '';

    let html = '';
    chosen.forEach(doc => {
        const items = doc.po?.items || [];
        let totalQty = 0;
        let totalAmt = 0;
        const rowsHtml = items.length
            ? items.map((item, idx) => {
                const desc = item.desc || item.itemName || '-';
                const qty = parseFloat(item.qty || 0) || 0;
                const price = parseFloat(item.price || item.unitPrc || 0) || 0;
                const amt = parseFloat(item.total || (qty * price)) || 0;
                totalQty += qty;
                totalAmt += amt;
                return `<tr>
                    <td class="center">${idx + 1}</td>
                    <td>${ciEsc(desc)}</td>
                    <td class="right">${ciEsc(ciQty(qty))}</td>
                    <td class="right">$ ${ciEsc(ciMoney(price, 4))}</td>
                    <td class="right">$ ${ciEsc(ciMoney(amt, 2))}</td>
                </tr>`;
            }).join('')
            : '<tr><td colspan="5" class="center">No items found</td></tr>';

        if (!items.length) {
            totalQty = parseFloat(doc.po?.qty || 0) || 0;
            totalAmt = parseFloat(doc.po?.val || 0) || 0;
        }

        html += `
        <div class="ci-page">
            ${CI_BRAND_HEADER}
            <div class="ci-title">Commercial Invoice</div>

            <table class="ci-topbox">
                <tr>
                    <td>
                        <div class="ci-topgrid">
                            <div><strong>Beneficiary</strong></div>
                            <div>${ciLines(beneficiaryAddress)}</div>
                            <div>${ciLines(factoryAddress)}</div>
                            <div style="margin-top:4px;"><strong>Advising Bank</strong></div>
                            <div>${ciLines(advisingBank)}</div>
                            <div style="margin-top:4px;"><strong>Consignee</strong></div>
                            <div>${ciEsc(consigneeName)}</div>
                            <div>${ciLines(consigneeAddress)}</div>
                            <div style="margin-top:4px;"><strong>Consignee's Bank</strong></div>
                            <div>${ciLines(consigneeBank)}</div>
                        </div>
                    </td>
                    <td>
                        <div class="ci-topgrid">
                            <div class="ci-meta-row"><span class="ci-meta-label">Invoice No</span><span>${ciEsc(invoiceNo)}</span></div>
                            <div class="ci-meta-row"><span class="ci-meta-label">Date</span><span>${ciEsc(invoiceDate)}</span></div>
                            <div class="ci-meta-row"><span class="ci-meta-label">L/C No</span><span>${ciEsc(lcNo)}</span></div>
                            <div class="ci-meta-row"><span class="ci-meta-label">Dated</span><span>${ciEsc(lcDate)}</span></div>
                            <div class="ci-meta-row"><span class="ci-meta-label">PI No</span><span>${ciEsc(proformaNo)}</span></div>
                            <div class="ci-meta-row"><span class="ci-meta-label">Dated</span><span>${ciEsc(proformaDate)}</span></div>
                            <div class="ci-meta-row"><span class="ci-meta-label">L/C Bank</span><span>${ciEsc(issuingBankName)}</span></div>
                            <div style="margin-left:88px;">${ciLines(issuingBankAddress)}</div>
                            <div class="ci-meta-row"><span class="ci-meta-label">Place of Loading</span><span>${ciEsc(placeLoading)}</span></div>
                            <div class="ci-meta-row"><span class="ci-meta-label">Place of Delivery</span><span>${ciEsc(placeDelivery)}</span></div>
                            <div class="ci-meta-row"><span class="ci-meta-label">Delivery</span><span>${ciEsc(carrier)}</span></div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="ci-buyer">BUYER: ${ciEsc(doc.po?.buyer || order.buyer_name || '')}</div>

            <table class="ci-items">
                <thead>
                    <tr>
                        <th style="width:42px;">SL NO.</th>
                        <th>Description of Goods</th>
                        <th style="width:78px;">Quantity</th>
                        <th style="width:90px;">Price $</th>
                        <th style="width:100px;">Amount $</th>
                    </tr>
                </thead>
                <tbody>
                    ${rowsHtml}
                    <tr>
                        <td colspan="2" class="right"><strong>Total</strong></td>
                        <td class="right"><strong>${ciEsc(ciQty(totalQty))}</strong></td>
                        <td></td>
                        <td class="right"><strong>$ ${ciEsc(ciMoney(totalAmt, 2))}</strong></td>
                    </tr>
                </tbody>
            </table>

            <div class="ci-footnote">Freight prepaid</div>
            <div class="ci-footnote">${ciEsc(applicantsText)}</div>

            <div class="ci-sign-block">
                <div><strong>For ${ciEsc(CI_COMPANY_NAME)}</strong></div>
                <div class="ci-sign-line"></div>
                <div>Authorized signature</div>
            </div>

            ${CI_BRAND_FOOTER}
        </div>`;
    });

    holder.innerHTML = html;
}

function ciPopulateSelector() {
    const sel = document.getElementById('ciDocSel');
    if (!sel || !window._ciRes) return;
    const docs = ciResolveDocs(window._ciRes);
    sel.innerHTML = '<option value="all">All Commercial Invoice</option>' + docs.map(doc =>
        `<option value="${ciEsc(doc.key)}">${ciEsc(doc.title)}</option>`
    ).join('');
}

let _ciExcelDone = false;
function downloadCommercialExcel() {
    atsDownloadExcelFromElement({
        elementId:'ciPages',
        filename:'commercial-invoice-'+((window.getCurrentOrderId && window.getCurrentOrderId()) || 'document'),
        title:document.title
    });
}
function printCommercialInvoice() {
    window.print();
}
window.onOrderLoad = function(res) {
    window._ciRes = res;
    ciPopulateSelector();
    ciBuildPages();
    if (atsShouldAutoExcel() && !_ciExcelDone) {
        _ciExcelDone = true;
        setTimeout(downloadCommercialExcel, 250);
    }
};

document.getElementById('ciDocSel')?.addEventListener('change', ciBuildPages);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
