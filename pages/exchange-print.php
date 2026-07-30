<?php
$pageTitle  = 'Bill of Exchange Print';
$activePage = 'exchange';
$navSection = 'order';
include __DIR__ . '/../includes/header.php';
?>
<style>
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

#boeWrap { background:#d1d5db; padding:28px 0; min-height:520px; }
.boe-empty { text-align:center; padding:60px 20px; color:#94a3b8; font-family:sans-serif; }
.boe-page {
    position:relative;
    max-width:820px; min-height:1120px; margin:0 auto 18px;
    background:#fff; box-shadow:0 4px 24px rgba(0,0,0,.14);
    padding:34px 54px 44px;
    font-family:'Times New Roman',Times,serif; color:#111; font-size:11pt; line-height:1.45;
    page-break-after:always;
}
.boe-page:last-child { page-break-after:auto; }
.boe-watermark {
    position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
    font-size:190px; font-weight:700; color:rgba(100,100,100,.14); pointer-events:none;
}
.boe-head { text-align:center; margin-bottom:18px; }
.boe-company { font-size:15px; font-weight:700; }
.boe-address { font-size:10px; margin-top:2px; }
.boe-lc-line { font-size:10px; text-align:left; margin-top:10px; }
.boe-title { text-align:center; font-size:24px; font-weight:700; margin:28px 0 10px; }
.boe-meta {
    display:flex; justify-content:space-between; align-items:flex-start; gap:12px;
    font-size:12px; font-weight:700; margin-bottom:14px;
}
.boe-body { font-size:10.5px; text-align:justify; margin-bottom:28px; }
.boe-value { font-size:12px; font-weight:700; margin:26px 0 18px; }
.boe-bottom {
    margin-top:86px;
    display:grid; grid-template-columns:1fr 220px; gap:32px; align-items:end;
}
.boe-to { font-size:10.5px; white-space:pre-line; }
.boe-sign { text-align:center; }
.boe-sign-line { border-top:1.5px solid #000; margin-bottom:6px; }
.boe-sign-label { font-size:10px; }
.boe-customer { margin-top:58px; font-size:10.5px; white-space:pre-line; }

@media print {
    .boe-ctrl, nav.page-nav, .order-id-bar, .no-print { display:none !important; }
    #boeWrap { background:none !important; padding:0 !important; }
    .boe-page { box-shadow:none; margin:0; max-width:100%; min-height:auto; }
    .form-stack, body, html, .app-shell { background:#fff !important; }
}
</style>

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
    <button type="button" class="boe-print-btn" onclick="window.print()">Print / Save PDF</button>
</div>

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

function boeAmountWords(amount) {
    const n = Math.round((parseFloat(amount || 0) || 0) * 100);
    const ones = ['Zero','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
    const tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
    const chunk = num => {
        if (num < 20) return ones[num];
        if (num < 100) return tens[Math.floor(num / 10)] + (num % 10 ? ' ' + ones[num % 10] : '');
        if (num < 1000) return ones[Math.floor(num / 100)] + ' Hundred' + (num % 100 ? ' ' + chunk(num % 100) : '');
        if (num < 1000000) return chunk(Math.floor(num / 1000)) + ' Thousand' + (num % 1000 ? ' ' + chunk(num % 1000) : '');
        return chunk(Math.floor(num / 1000000)) + ' Million' + (num % 1000000 ? ' ' + chunk(num % 1000000) : '');
    };
    const dollars = Math.floor(n / 100);
    const cents = n % 100;
    let out = chunk(dollars) + ' USD';
    if (cents) out += ' and ' + chunk(cents) + ' Cents';
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
    return parseFloat(exch.exchangeAmount || lc.lcAmount || 0) || 0;
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

    if (standalonePis.length) {
        standalonePis.forEach(pi => {
            const poList = pi.pos?.length ? pi.pos : [{}];
            poList.forEach((po, idx) => {
                docs.push({
                    key: `${pi.pi_number || 'pi'}_${idx}`,
                    title: `${pi.pi_number || 'Bill'}${po.poNum ? ' - ' + po.poNum : ''}`,
                    pi,
                    po
                });
            });
        });
        return docs;
    }

    const fallbackPos = sales.pos?.length ? sales.pos
        : (allPis.find(p => p.is_master)?.pos?.length ? allPis.find(p => p.is_master).pos : (intake.pos || []));

    fallbackPos.forEach((po, idx) => {
        docs.push({
            key: `fallback_${idx}`,
            title: `${po.piNum || sales.piNum || 'Bill'}${po.poNum ? ' - ' + po.poNum : ''}`,
            pi: { pi_number: po.piNum || sales.piNum || '', customer: sales.customer || order.customer_name || '' },
            po
        });
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
    const sel   = document.getElementById('boeDocSel')?.value || 'all';
    const copies = parseInt(document.getElementById('boeCopies')?.value || '2', 10) || 2;

    const chosenDocs = sel === 'all' ? docs : docs.filter(d => d.key === sel);
    if (!chosenDocs.length) {
        holder.innerHTML = '<div class="boe-empty">No Bill of Exchange source found for this order.</div>';
        return;
    }

    const customerName = sales.customer || order.customer_name || chosenDocs[0]?.pi?.customer || '';
    const customerAddress = sales.buyerAddress || '';
    const topCompany = 'Zaber & Zubair Accessories Ltd.';
    const topAddress = 'Mawna, Sreepur, Gazipur.';

    let html = '';
    chosenDocs.forEach(doc => {
        const amount = boeDocAmount(doc, exch, lc);
        const words = (chosenDocs.length === 1 && copies === 1 && exch.tenorWordsMaster)
            ? exch.tenorWordsMaster
            : boeAmountWords(amount);
        const issuingBankInline = boePlainText(exch.applicantBank || lc.lcIssuingBank || '');
        const toBankText = boeMultiline(exch.applicantBank || lc.lcIssuingBank || '');
        const payToText = boePlainText(exch.beneficiaryBankAddress || exch.payToBankName || lc.negotiatingBeneficiaryBank || '');
        const contractNo = exch.exportSalesContractNo || '-';
        const contractDate = boeFmtDate(exch.exportSalesContractDate || '-');
        const vatBin = exch.beneficiaryVatBin || exch.applicantVatBin || '-';
        const hsCode = exch.hsCodeMaster || '-';
        const tenor = exch.lcTenorMaster || lc.paymentTerms || '120';
        const displayPi = doc.pi?.pi_number || doc.po?.piNum || sales.piNum || '-';

        for (let copyNo = 1; copyNo <= copies; copyNo++) {
            html += `
            <div class="boe-page">
                <div class="boe-watermark">${copyNo}</div>
                <div class="boe-head">
                    <div class="boe-company">${boeEsc(topCompany)}</div>
                    <div class="boe-address">${boeEsc(topAddress)}</div>
                    <div class="boe-lc-line">Drawn under Letter of Credit No. ${boeEsc(exch.masterLcNo || lc.lcNumber || '-')} Dated ${boeEsc(boeFmtDate(exch.masterLcDate || lc.lcDate || '-'))} of ${boeEsc(issuingBankInline || '-')}</div>
                </div>

                <div class="boe-title">Bill of Exchange</div>

                <div class="boe-meta">
                    <div>Exchange for USD ${boeEsc(boeFmtMoney(amount))}</div>
                    <div>Date: ${boeEsc(boeFmtDate(exch.exchangeDate || new Date().toISOString().slice(0,10)))}</div>
                </div>

                <div class="boe-body">
                    At ${boeEsc(tenor)} Days of this ${copyNo === 1 ? 'First' : copyNo === 2 ? 'Second' : copyNo + 'th'} of exchange (${copyNo === 1 ? 'first' : copyNo === 2 ? 'second' : copyNo + 'th'} of the same tenor unpaid) please pay to the order of ${boeEsc(payToText || '-')} the same of USD: ${boeEsc(words)}. Export Sales Contract No. ${boeEsc(contractNo)} Dated ${boeEsc(contractDate)}, Beneficiary's Vat/bin: ${boeEsc(vatBin)} and H.S Code No: ${boeEsc(hsCode)}.
                </div>

                <div class="boe-value">Value Received</div>

                <div class="boe-bottom">
                    <div class="boe-to">
                        <div>To</div>
                        <div>${boeEsc(toBankText || '-').replace(/\n/g,'<br>')}</div>
                    </div>
                    <div class="boe-sign">
                        <div class="boe-sign-line"></div>
                        <div class="boe-sign-label">Authorized signature</div>
                    </div>
                </div>

                <div class="boe-customer">
                    <strong>${boeEsc(customerName || '-')}</strong><br>
                    ${boeEsc(customerAddress || '').replace(/\n/g,'<br>')}
                </div>
            </div>`;
        }
    });

    holder.innerHTML = html;
}

function populateBoeSelector() {
    const sel = document.getElementById('boeDocSel');
    if (!sel || !window._boeRes) return;
    const docs = buildExchangeDocs(window._boeRes);
    sel.innerHTML = '<option value="all">All Bill of Exchange</option>' + docs.map(doc =>
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
window.onOrderLoad = function(res) {
    window._boeRes = res;
    populateBoeSelector();
    renderBoePages();
    if (atsShouldAutoExcel() && !_boeExcelDone) {
        _boeExcelDone = true;
        setTimeout(downloadBoeExcel, 250);
    }
};

document.getElementById('boeDocSel')?.addEventListener('change', renderBoePages);
document.getElementById('boeCopies')?.addEventListener('change', renderBoePages);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
