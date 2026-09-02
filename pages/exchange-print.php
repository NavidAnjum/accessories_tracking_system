<?php
$pageTitle  = 'Bill of Exchange Print';
$activePage = 'exchange';
$navSection = 'order';
$isPdfMode = isset($_GET['pdf']) && $_GET['pdf'] === '1';
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/print-brand.php';
?>
<style>
.zzal-print-brand {
    margin: 0;
    padding: 0;
    color: #111;
    font-family: Arial, Helvetica, sans-serif;
}
.zzal-print-brand--header {
    display:grid;
    grid-template-columns:58px 1fr;
    align-items:center;
    column-gap:14px;
    row-gap:6px;
    margin:0 0 12px;
}
.zzal-print-brand__logo {
    width:48px;
    height:48px;
    border:2px solid #2d9cdb;
    color:#2d9cdb;
    display:block;
    font-weight:700;
    text-align:center;
    letter-spacing:.02em;
    position:relative;
    font-family:Georgia, "Times New Roman", serif;
    overflow:hidden;
}
.zzal-print-brand__z {
    position:absolute;
    top:2px;
    left:8px;
    font-size:28px;
    line-height:.8;
    color:#2d9cdb;
    font-style:italic;
    font-weight:700;
}
.zzal-print-brand__z2 {
    position:absolute;
    top:9px;
    left:17px;
    font-size:28px;
    line-height:.8;
    color:#7b5a68;
    font-style:italic;
    font-weight:700;
}
.zzal-print-brand__al {
    position:absolute;
    top:16px;
    right:5px;
    font-size:8px;
    font-weight:700;
    color:#7b5a68;
}
.zzal-print-brand__zzal {
    position:absolute;
    left:0;
    right:0;
    bottom:0;
    width:100%;
    font-size:12px;
    line-height:1;
    color:#fff;
    background:#2d9cdb;
    padding:1px 0 2px;
    letter-spacing:.04em;
}
.zzal-print-brand__title-wrap { flex:1; }
.zzal-print-brand__title {
    font-size:26px;
    font-weight:700;
    text-align:left;
    letter-spacing:.01em;
    line-height:1;
    text-transform:uppercase;
    color:#7b5a68;
    font-family:Georgia, "Times New Roman", serif;
}
.zzal-print-brand__header-line {
    grid-column:1 / -1;
    height:2px;
    background:#2d9cdb;
    box-shadow:0 1px 0 #b9deef inset, 0 -1px 0 #1f84ba inset;
}
.zzal-print-brand--footer {
    margin-top:auto;
}
.zzal-print-brand__divider {
    height:1px;
    background:#111;
    margin:0 auto 8px;
}
.zzal-print-brand__footer-line {
    text-align:center;
    font-size:9px;
    line-height:1.4;
    margin:0 0 2px;
}
.boe-page .zzal-print-brand--header { margin-bottom:16px; }

.boe-ctrl {
    background:#1e1e3a; padding:14px 24px;
    display:flex; gap:18px; align-items:center; flex-wrap:wrap;
}
.boe-ctrl-group { display:flex; flex-direction:column; gap:4px; }
.boe-ctrl-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#a5b4fc; }
.boe-ctrl select {
    background:#2d2d50; color:#fff; border:1.5px solid #4f46e5;
    border-radius:6px; padding:6px 12px; font-size:12px; outline:none; min-width:180px;
}
.boe-print-btn {
    margin-left:auto; background:#22c55e; color:#fff; border:none;
    border-radius:8px; padding:10px 28px; font-size:13px; font-weight:700; cursor:pointer;
}
.boe-print-btn:hover { background:#16a34a; }
.boe-excel-btn {
    background:#2563eb; color:#fff; border:none;
    border-radius:8px; padding:10px 22px; font-size:13px; font-weight:700; cursor:pointer;
}
.boe-excel-btn:hover { background:#1d4ed8; }
.boe-pdf-btn {
    background:#0ea5e9; color:#fff; border:none;
    border-radius:8px; padding:10px 22px; font-size:13px; font-weight:700; cursor:pointer;
}
.boe-pdf-btn:hover { background:#0284c7; }

#boeWrap { background:#d1d5db; padding:28px 0; min-height:520px; }
.boe-empty { text-align:center; padding:60px 20px; color:#94a3b8; font-family:sans-serif; }
.boe-page {
    position:relative;
    box-sizing:border-box;
    width:210mm;
    height:296mm;
    max-width:210mm;
    margin:0 auto 18px;
    overflow:hidden;
    display:flex;
    flex-direction:column;
    background:#fff; box-shadow:0 4px 24px rgba(0,0,0,.14);
    padding:14mm 14mm 12mm;
    font-family:'Times New Roman',Times,serif; color:#111; font-size:11pt; line-height:1.45;
    break-inside:avoid;
    page-break-inside:avoid;
}
.boe-page:not(:last-child) { break-after:page; page-break-after:always; }
.boe-page:last-child { break-after:auto; page-break-after:auto; }
.boe-watermark {
    position:absolute;
    left:50%;
    top:29%;
    transform:translate(-50%, -50%);
    font-size:170px;
    font-weight:700;
    color:rgba(120,120,120,.10);
    pointer-events:none;
    opacity:1;
    z-index:0;
}
.boe-content {
    position:relative;
    z-index:1;
    flex:1;
    display:flex;
    flex-direction:column;
}
.boe-lc-line { font-size:12px; text-align:left; margin-top:8px; }
.boe-title { text-align:center; font-size:24px; font-weight:700; margin:18px 0 4px; }
.boe-docref {
    display:flex;
    justify-content:space-between;
    gap:16px;
    margin:0 0 6px;
    font-size:11px;
    font-weight:700;
}
.boe-inwords { font-size:11px; font-weight:700; margin:4px 0 10px; }
.boe-meta {
    display:flex; justify-content:space-between; align-items:flex-start; gap:12px;
    font-size:12px; font-weight:700; margin-bottom:14px;
}
.boe-body { font-size:10.5px; text-align:justify; margin-bottom:14px; }
.boe-value { font-size:12px; font-weight:700; margin:12px 0 10px; }
.boe-bottom {
    margin-top:60mm;
    display:grid; grid-template-columns:1fr 220px; gap:32px; align-items:end;
}
.boe-left-bottom { display:flex; flex-direction:column; gap:24px; min-height:100px; }
.boe-page .zzal-print-brand--footer {
    position:static;
    margin:0;
    margin-top:auto;
    padding-top:10px;
}
.boe-to { font-size:10.5px; white-space:pre-line; }
.boe-sign { text-align:center; }
.boe-sign-line { border-top:1.5px solid #000; margin-bottom:6px; }
.boe-sign-label { font-size:10px; }

@media print {
    @page { size: A4; margin: 0; }
    .boe-ctrl, nav.page-nav, .order-id-bar, .no-print { display:none !important; }
    .form-stack { display:block!important; margin:0!important; padding:0!important; min-height:0!important; }
    .form-stack > *:not(#boeWrap) { display:none!important; }
    #boeWrap { display:block!important; background:none!important; padding:0!important; margin:0!important; width:210mm!important; min-height:0!important; height:auto!important; overflow:visible!important; }
    #boePages { display:block!important; margin:0!important; padding:0!important; min-height:0!important; height:auto!important; }
    .boe-page {
        box-shadow:none;
        box-sizing:border-box;
        width:210mm;
        height:296mm;
        min-height:296mm;
        max-width:210mm;
        margin:0;
        overflow:hidden;
        display:flex;
        flex-direction:column;
        break-inside:avoid;
        page-break-inside:avoid;
        padding:14mm 14mm 12mm;
    }
    /* Break BEFORE each later copy (never after the last) so no trailing blank page is produced. */
    .boe-page:not(:last-child) { break-after:auto; page-break-after:auto; }
    .boe-page + .boe-page { break-before:page; page-break-before:always; }
    .boe-page .zzal-print-brand--footer {
        position:static;
        margin:0;
        margin-top:auto;
        padding-top:10px;
    }
    html, body { width:210mm!important; min-height:0!important; margin:0!important; padding:0!important; overflow:visible!important; background:#fff!important; }
    body::before { display:none!important; content:none!important; }
    .app-shell { width:210mm!important; max-width:210mm!important; min-height:0!important; margin:0!important; padding:0!important; background:#fff!important; }
    .form-stack { background:#fff!important; }
    .boe-page, .boe-page * { color:#111 !important; }
    .boe-watermark { color:rgba(120,120,120,.12) !important; }
    .zzal-print-brand__title,
    .zzal-print-brand__al,
    .zzal-print-brand__z2 { color:#7b5a68 !important; }
    .zzal-print-brand__logo { color:#2d9cdb !important; border-color:#2d9cdb !important; }
    .zzal-print-brand__zzal { color:#fff !important; background:#2d9cdb !important; }
    .zzal-print-brand__header-line { background:#2d9cdb !important; }
}
</style>

<?php if (!$isPdfMode): ?>
<div class="boe-ctrl no-print">
    <div class="boe-ctrl-group">
        <span class="boe-ctrl-label">Select Bill</span>
        <select id="boeDocSel"></select>
    </div>
    <div class="boe-ctrl-group">
        <span class="boe-ctrl-label">Copies</span>
        <select id="boeCopies">
            <option value="1">1 Copy</option>
            <option value="2" selected>2 Copies</option>
            <option value="3">3 Copies</option>
            <option value="4">4 Copies</option>
        </select>
    </div>
    <button type="button" class="boe-excel-btn" onclick="downloadBoeExcel()">Download Excel</button>
    <button type="button" class="boe-pdf-btn" onclick="downloadBoePdf()">Download PDF</button>
    <button type="button" class="boe-print-btn" onclick="window.print()">Print / Save PDF</button>
</div>
<?php endif; ?>

<div id="boeWrap">
    <div id="boePages">
        <div class="boe-empty">Load an order to generate the Bill of Exchange.</div>
    </div>
</div>

<script>
function boeEsc(val) {
    return String(val ?? '-')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function boeFmtDate(val) {
    if (!val) return '-';
    if (/^\d{4}-\d{2}-\d{2}$/.test(val)) {
        const [y,m,d] = val.split('-');
        return `${d}.${m}.${y}`;
    }
    return val;
}

function boeFmtMoney(num) {
    const n = parseFloat(num || 0) || 0;
    return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function boeSplitText(val) {
    return String(val || '')
        .split(/\r?\n|,(?=\s*[A-Z0-9])/)
        .map(v => v.trim())
        .filter(Boolean);
}

function boePlainText(val) {
    return boeSplitText(val).join(', ');
}

function boeMultiline(val) {
    return boeSplitText(val).join('\n');
}

const BOE_BANKS = {
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
const BOE_BRAND_FOOTER = <?php echo json_encode(zzal_print_brand_footer(), JSON_UNESCAPED_SLASHES); ?>;
const BOE_BRAND_HEADER = <?php echo json_encode(zzal_print_brand_header(), JSON_UNESCAPED_SLASHES); ?>;
const BOE_DEFAULT_HS_CODE = '4819.10.00';
const BOE_BENEFICIARY_VAT_BIN = '000230256-0103';

function boeResolveBank(text, fallbackKey = 'ncc') {
    const raw = String(text || '').trim();
    const lower = raw.toLowerCase();
    let key = fallbackKey in BOE_BANKS ? fallbackKey : 'ncc';
    let matched = false;
    if (lower.includes('dutch-bangla') || lower.includes('dbbl')) {
        key = 'dbbl';
        matched = true;
    } else if (lower.includes('national credit') || lower.includes('ncc')) {
        key = 'ncc';
        matched = true;
    }
    const bank = BOE_BANKS[key] || BOE_BANKS.ncc;
    const lines = boeSplitText(raw);
    const display = raw || [bank.name, bank.address, bank.account, bank.swift, bank.routing].filter(Boolean).join('\n');
    return {
        key,
        name: matched ? bank.name : (lines[0] || bank.name),
        address: matched ? bank.address : (lines.slice(1).join('\n') || bank.address),
        account: matched ? bank.account : (lines[2] || ''),
        swift: matched ? bank.swift : '',
        routing: matched ? bank.routing : '',
        raw,
        display
    };
}

function boeAmountWords(amount) {
    const parsed = parseFloat(amount || 0) || 0;
    const ones = ['Zero','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
    const tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
    const scales = ['', 'Thousand', 'Million', 'Billion'];
    const chunk = num => {
        num = Math.floor(num);
        if (num === 0) return '';
        if (num < 20) return ones[num];
        if (num < 100) return tens[Math.floor(num / 10)] + (num % 10 ? ' ' + ones[num % 10] : '');
        return ones[Math.floor(num / 100)] + ' Hundred' + (num % 100 ? ' ' + chunk(num % 100) : '');
    };
    const fullWords = num => {
        num = Math.floor(num);
        if (num === 0) return 'Zero';
        const parts = [];
        let scale = 0;
        while (num > 0) {
            const piece = num % 1000;
            if (piece) {
                parts.unshift(chunk(piece) + (scales[scale] ? ' ' + scales[scale] : ''));
            }
            num = Math.floor(num / 1000);
            scale++;
        }
        return parts.join(' ').trim();
    };
    let centsTotal = Math.round(parsed * 100);
    let dollars = Math.floor(centsTotal / 100);
    let cents = centsTotal % 100;
    if (cents === 100) {
        dollars += 1;
        cents = 0;
    }
    let out = fullWords(dollars) + ' USD';
    if (cents) out += ' and ' + fullWords(cents) + ' Cents';
    return out + ' Only';
}

function boeDocAmount(doc, exch, lc) {
    const poTotal = parseFloat(doc.po?.val || 0) || 0;
    if (poTotal > 0) return poTotal;
    const fromItems = (doc.po?.items || []).reduce((sum, item) => {
        const total = parseFloat(item.total || ((parseFloat(item.qty || 0) || 0) * (parseFloat(item.price || 0) || 0))) || 0;
        return sum + total;
    }, 0);
    if (fromItems > 0) return fromItems;
    return parseFloat(exch.receivedAmount || exch.exchangeAmount || lc.lcAmount || 0) || 0;
}

function buildExchangeDocs(res) {
    const order = res.order || {};
    const sales = res.pages?.sales || {};
    const exch  = res.pages?.exchange || {};
    const lc    = res.pages?.lc || {};
    const intake = res.pages?.['marketing-intake'] || {};
    const allPis = res.pis || [];
    const standalonePis = allPis.filter(p => !p.is_master);
    const docs = [];

    // One Bill of Exchange per PI (its POs are aggregated into a single bill),
    // so a single PI prints as First + Second copy = 2 pages, not one per PO.
    const aggregatePo = (pos) => {
        const list = pos || [];
        return {
            poNum: [...new Set(list.map(p => p.poNum).filter(Boolean))].join(', '),
            val: list.reduce((s, p) => s + (parseFloat(p.val || 0) || 0), 0),
            items: list.flatMap(p => p.items || [])
        };
    };

    if (standalonePis.length) {
        standalonePis.forEach(pi => {
            const po = aggregatePo(pi.pos);
            if (!(po.val > 0)) po.val = parseFloat(pi.grand_val || 0) || 0;
            docs.push({
                key: `${pi.pi_number || 'pi'}`,
                title: `${pi.pi_number || 'Bill'}`,
                pi,
                po
            });
        });
        return docs;
    }

    const fallbackPos = sales.pos?.length ? sales.pos
        : (allPis.find(p => p.is_master)?.pos?.length ? allPis.find(p => p.is_master).pos : (intake.pos || []));

    docs.push({
        key: 'fallback',
        title: sales.piNum || 'Bill',
        pi: { pi_number: sales.piNum || '', customer: sales.customer || order.customer_name || '' },
        po: aggregatePo(fallbackPos)
    });
    return docs;
}

function renderBoePages() {
    const res = window._boeRes;
    const holder = document.getElementById('boePages');
    if (!res || !holder) return;

    const exch  = res.pages?.exchange || {};
    const lc    = res.pages?.lc || {};
    const sales = res.pages?.sales || {};
    const order = res.order || {};
    const docs  = buildExchangeDocs(res);
    const params = new URLSearchParams(window.location.search);
    const sel = document.getElementById('boeDocSel')?.value || params.get('doc') || docs[0]?.key || '';
    const copies = parseInt(document.getElementById('boeCopies')?.value || params.get('copies') || '2', 10) || 2;

    const chosenDocs = docs.filter(d => d.key === sel);
    if (!chosenDocs.length) {
        holder.innerHTML = '<div class="boe-empty">No Bill of Exchange source found for this order.</div>';
        return;
    }

    const customerName = sales.customer || order.customer_name || chosenDocs[0]?.pi?.customer || '';
    const customerAddress = sales.buyerAddress || chosenDocs[0]?.po?.sharedBuyerAddress || '';
    let html = '';
    chosenDocs.forEach(doc => {
        const amount = boeDocAmount(doc, exch, lc);
        const words = exch.tenorWordsMaster || boeAmountWords(amount);
        const issuingBank = boeResolveBank(exch.applicantBank || lc.lcIssuingBank || '');
        const payToBank = boeResolveBank(exch.payToBankName || lc.reimbursementBank || exch.beneficiaryBankAddress || '');
        const toBankText = boeMultiline(exch.payToBankAddress || payToBank.display || exch.beneficiaryBankAddress || lc.negotiatingBeneficiaryBank || '');
        const payToText = boePlainText(exch.beneficiaryBankAddress || exch.payToBankAddress || payToBank.display || lc.negotiatingBeneficiaryBank || '');
        const contractNo = exch.exportSalesContractNo || '-';
        const contractDate = boeFmtDate(exch.exportSalesContractDate || '-');
        const vatBin = exch.beneficiaryVatBin || BOE_BENEFICIARY_VAT_BIN;
        // §3.2 HS Code comes from the PI (sales), not manual entry on the exchange page.
        const hsCode = sales.hsCode || exch.hsCodeMaster || BOE_DEFAULT_HS_CODE;
        const tenor = exch.lcTenorMaster || lc.paymentTerms || '120';
        for (let copyNo = 1; copyNo <= copies; copyNo++) {
            const displayPi = doc.pi?.pi_number || doc.po?.piNum || sales.piNum || '-';
            const copyLabel = copyNo === 1 ? 'SECOND' : 'FIRST';
            const copyLabelOpposite = copyNo === 1 ? 'FIRST' : 'SECOND';
            html += `
            <div class="boe-page">
                <div class="boe-watermark">${copyNo}</div>
                <div class="boe-content">
                ${BOE_BRAND_HEADER}

                <div class="boe-lc-line">Drawn under Letter of Credit No. ${boeEsc(exch.masterLcNo || lc.lcNumber || '-')} Dated ${boeEsc(boeFmtDate(exch.masterLcDate || lc.lcDate || '-'))} of ${boeEsc(boePlainText(issuingBank.display) || '-')}</div>

                <div class="boe-title">Bill of Exchange</div>
                <div class="boe-docref">
                    <div>NO: ${boeEsc(displayPi)}</div>
                    <div>Date: ${boeEsc(boeFmtDate(exch.exchangeDate || new Date().toISOString().slice(0,10)))}</div>
                </div>

                <div class="boe-meta">
                    <div>Exchange for USD ${boeEsc(boeFmtMoney(amount))}</div>
                </div>
                <div class="boe-inwords">In Words: USD ${boeEsc(words)}</div>

                <div class="boe-body">
                    At ${boeEsc(tenor)} Days of this ${copyLabel} of exchange (${copyLabelOpposite} of the same tenor unpaid) please pay to the order of ${boeEsc(payToText || '-')} the same of USD: ${boeEsc(words)}. Export Sales Contract No. ${boeEsc(contractNo)} Dated ${boeEsc(contractDate)}, Beneficiary's Vat/bin: ${boeEsc(vatBin)} and H.S Code No: ${boeEsc(hsCode)}.
                </div>

                <div class="boe-value">Value Received</div>

                <div class="boe-bottom">
                    <div class="boe-left-bottom">
                        <div class="boe-to">
                            <div>To</div>
                            <div>${boeEsc(toBankText || '-').replace(/\n/g,'<br>')}</div>
                        </div>
                    </div>
                    <div class="boe-sign">
                        <img src="<?= BASE_PATH ?>/AKM.png" alt="For Zaber & Zubair Accessories Ltd. — Authorised Signature" style="height:100px;max-width:300px;object-fit:contain;display:block;margin-left:auto;">
                    </div>
                </div>
                </div>
                ${BOE_BRAND_FOOTER}
            </div>`;
        }
    });

    holder.innerHTML = html;
}

function populateBoeSelector() {
    const sel = document.getElementById('boeDocSel');
    if (!sel || !window._boeRes) return;
    const docs = buildExchangeDocs(window._boeRes);
    sel.innerHTML = docs.map(doc =>
        `<option value="${boeEsc(doc.key)}">${boeEsc(doc.title)}</option>`
    ).join('');
}

let _boeExcelDone = false;
function downloadBoeExcel() {
    atsDownloadExcelFromElement({
        elementId:'boePages',
        filename:'bill-of-exchange-'+((window.getCurrentOrderId && window.getCurrentOrderId()) || 'document'),
        title:document.title
    });
}
function downloadBoePdf() {
    const url = new URL(window.location.href);
    url.searchParams.set('pdf', '1');
    url.searchParams.set('doc', document.getElementById('boeDocSel')?.value || '');
    url.searchParams.set('copies', document.getElementById('boeCopies')?.value || '2');
    window.open(url.toString(), '_blank', 'noopener');
}
window.onOrderLoad = function(res) {
    window._boeRes = res;
    populateBoeSelector();
    renderBoePages();
    if (atsShouldAutoExcel() && !_boeExcelDone) {
        _boeExcelDone = true;
        setTimeout(downloadBoeExcel, 250);
    }
    <?php if ($isPdfMode): ?>
    setTimeout(() => window.print(), 350);
    <?php endif; ?>
};

document.getElementById('boeDocSel')?.addEventListener('change', renderBoePages);
document.getElementById('boeCopies')?.addEventListener('change', renderBoePages);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
