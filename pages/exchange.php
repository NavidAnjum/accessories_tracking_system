<?php
$pageTitle   = 'Bill of Exchange';
$activePage  = 'exchange';
$navSection  = 'order';
$pageSubtitle = 'Bill of Exchange prepare bank document.';
include __DIR__ . '/../includes/header.php';
?>

                <section class="form-card" data-page="exchange">
                    <div class="section-head">
                        <div class="section-title">
                            <span class="section-tag">Section 6</span>
                            <h2>Bill Of Exchange Input</h2>
                        </div>
                        <div class="section-summary">
                            <strong>Output</strong>
                            <span>Bill of Exchange should hold the manual LC, bank, export, and applicant details used by the later documents.</span>
                        </div>
                    </div>
                    <div class="erp-banner">
                        <div>
                            <strong>Bill Of Exchange Source</strong>
                            <span>PO search loads the buyer and line items. Enter the rest of the LC, bank, export, and applicant data here.</span>
                        </div>
                    </div>
                    <div class="source-glance">
                        <div class="source-glance-item"><span>Matched Sales Order</span><input id="salesOrder" name="salesOrder" placeholder="Sales order no..."></div>
                        <div class="source-glance-item"><span>Customer PO</span><input id="customerPo" name="customerPo" placeholder="Customer PO..."></div>
                        <div class="source-glance-item"><span>Buyer</span><input id="buyerName" name="buyerName" placeholder="Buyer code..."></div>
                        <div class="source-glance-item"><span>Customer</span><input id="customerName" name="customerName" placeholder="Customer name..."></div>
                    </div>
                    <div class="form-grid">
                        <div class="field span-6">
                            <label for="masterLcNo">Drawn under Letter of Credit No</label>
                            <input id="masterLcNo" name="masterLcNo" placeholder="LC number...">
                        </div>
                        <div class="field span-6">
                            <label for="masterLcDate">Dated</label>
                            <input id="masterLcDate" name="masterLcDate" type="date">
                        </div>

                        <div class="field span-12">
                            <label for="applicantBank">Of Bank / Full Bank Text</label>
                            <textarea id="applicantBank" name="applicantBank" rows="3" placeholder="Full LC issuing bank name and address..."></textarea>
                        </div>

                        <div class="field span-6">
                            <label for="exchangeAmount">Exchange for USD</label>
                            <input id="exchangeAmount" name="exchangeAmount" type="number" step="0.01" placeholder="0.00">
                        </div>
                        <div class="field span-6">
                            <label for="exchangeDate">Date</label>
                            <input id="exchangeDate" name="exchangeDate" type="date">
                        </div>

                        <div class="field span-4">
                            <label for="lcTenorMaster">At (Tenor)</label>
                            <input id="lcTenorMaster" name="lcTenorMaster" placeholder="e.g. 120 Days">
                        </div>
                        <div class="field span-4">
                            <label for="payToBankName">Reimbursment Bank</label>
                            <input id="payToBankName" name="payToBankName" placeholder="Auto-filled from LC reimbursment bank">
                        </div>
                        <div class="field span-4">
                            <label for="payToBankAddress">Bank Address</label>
                            <input id="payToBankAddress" name="payToBankAddress" placeholder="Bank address...">
                        </div>

                        <div class="field span-6">
                            <label for="beneficiaryBankAddress">Beneficiary Bank &amp; Address</label>
                            <textarea id="beneficiaryBankAddress" name="beneficiaryBankAddress" rows="3" placeholder="Beneficiary bank and address"></textarea>
                        </div>
                        <div class="field span-6">
                            <label for="negotiatingBankAddress">Negotiating Bank &amp; Address</label>
                            <textarea id="negotiatingBankAddress" name="negotiatingBankAddress" rows="3" placeholder="Negotiating bank and address"></textarea>
                        </div>

                        <div class="field span-3">
                            <label for="docSendToBuyerDate">Doc send to the Buyer (date)</label>
                            <input id="docSendToBuyerDate" name="docSendToBuyerDate" type="date">
                        </div>
                        <div class="field span-3">
                            <label for="acceptanceDate">Acceptance Date</label>
                            <input id="acceptanceDate" name="acceptanceDate" type="date">
                        </div>
                        <div class="field span-3">
                            <label for="docSentToNegotiatingBankDate">Doc sent to the Negotiating Bank</label>
                            <input id="docSentToNegotiatingBankDate" name="docSentToNegotiatingBankDate" type="date">
                        </div>
                        <div class="field span-3">
                            <label for="maturityDate">Maturity Date</label>
                            <input id="maturityDate" name="maturityDate" type="date">
                        </div>

                        <div class="field span-6">
                            <label for="receivedAmount">Received Amount</label>
                            <input id="receivedAmount" name="receivedAmount" type="number" step="0.01" placeholder="0.00">
                        </div>
                        <div class="field span-6">
                            <label for="receivedDate">Received Date</label>
                            <input id="receivedDate" name="receivedDate" type="date">
                        </div>

                        <div class="field span-12">
                            <label for="tenorWordsMaster">The same of USD in word</label>
                            <input id="tenorWordsMaster" name="tenorWordsMaster" placeholder="Amount in words...">
                        </div>

                        <div class="field span-6">
                            <label for="exportSalesContractNo">Export Sales Contract No</label>
                            <input id="exportSalesContractNo" name="exportSalesContractNo" placeholder="e.g. LIZ-LAMOUR-020-2026">
                        </div>
                        <div class="field span-6">
                            <label for="exportSalesContractDate">Contract Dated</label>
                            <input id="exportSalesContractDate" name="exportSalesContractDate" type="date">
                        </div>

                        <div class="field span-3">
                            <label for="applicantIrc">Applicants IRC No</label>
                            <input id="applicantIrc" name="applicantIrc" placeholder="IRC number...">
                        </div>
                        <div class="field span-3">
                            <label for="applicantTin">Applicants TIN No</label>
                            <input id="applicantTin" name="applicantTin" placeholder="TIN number...">
                        </div>
                        <div class="field span-3">
                            <label for="applicantVatBin">Applicants Vat/bin No</label>
                            <input id="applicantVatBin" name="applicantVatBin" placeholder="VAT/BIN...">
                        </div>
                        <div class="field span-3">
                            <label for="applicantBankBin">Applicants Bank Bin No</label>
                            <input id="applicantBankBin" name="applicantBankBin" placeholder="Bank BIN...">
                        </div>

                        <div class="field span-4">
                            <label for="bondLicenseNo">Bond License no</label>
                            <input id="bondLicenseNo" name="bondLicenseNo" placeholder="Bond license...">
                        </div>
                        <div class="field span-4">
                            <label for="beneficiaryVatBin">Beneficiary's Vat/Bin</label>
                            <input id="beneficiaryVatBin" name="beneficiaryVatBin" placeholder="Beneficiary VAT/BIN...">
                        </div>
                        <div class="field span-4">
                            <label for="hsCodeMaster">H.S Code No</label>
                            <input id="hsCodeMaster" name="hsCodeMaster" placeholder="e.g. 4819.10.00">
                        </div>

                        <div class="field span-12">
                            <label for="exchangePreviewText">Bill Of Exchange Text Preview</label>
                            <textarea id="exchangePreviewText" name="exchangePreviewText" rows="4" placeholder="Bill of Exchange preview will build here from the fields above..."></textarea>
                        </div>

                        <input id="applicantName" name="applicantName" type="hidden">
                        <input id="factoryAddress" name="factoryAddress" type="hidden">
                        <textarea id="beneficiaryOfficeAddress" name="beneficiaryOfficeAddress" hidden></textarea>
                        <textarea id="advisingBankAddress" name="advisingBankAddress" hidden></textarea>
                        <input id="packingDetailsMaster" name="packingDetailsMaster" type="hidden">
                        <input id="carrierNameMaster" name="carrierNameMaster" type="hidden">
                    </div>
                    <div class="page-actions">
                        <div class="page-actions-left">
                            <button type="button" class="ghost-btn js-prev-page" data-prev-page="lc">Previous</button>
                        </div>
                        <div class="page-actions-right">
                            <button type="button" class="ghost-btn" onclick="openExchangeExcel()">Download Exchange Excel</button>
                            <button type="button" class="ghost-btn" onclick="openExchangePrint()">Print Bill of Exchange</button>
                            <button type="button" class="primary-btn js-next-page" data-next-page="commercial">Next: Commercial Invoice</button>
                        </div>
                    </div>
                </section>

<script>
const EXCHANGE_BANKS = {
    ncc: {
        key: 'ncc',
        name: 'National Credit & Commerce Bank Plc.',
        address: 'Motijheel main Branch, 6 Motijheel C/A, Dhaka-1000, Bangladesh.',
        account: '0002-0259000092',
        swift: 'NCCLBDDHNBB',
        routing: '160150137'
    },
    dbbl: {
        key: 'dbbl',
        name: 'Dutch-Bangla Bank Plc.',
        address: 'Local Office, 1, Dilkusha C/A, Dhaka-1000, Bangladesh.',
        account: 'ERQ-101.117.1382',
        swift: 'DBBLBDDHCTS',
        routing: '090273889'
    }
};
const EXCHANGE_DEFAULT_HS_CODE = '4819.10.00';
const EXCHANGE_BENEFICIARY_VAT_BIN = '000230256-0103';

function exchangeSplitLines(val) {
    return String(val ?? '')
        .split(/\r?\n|,(?=\s*[A-Z0-9])/)
        .map(v => v.trim())
        .filter(Boolean);
}

function exchangeRenderBankBlock(bank) {
    if (!bank) return '';
    return [bank.name, bank.address, bank.account, bank.swift, bank.routing]
        .filter(Boolean)
        .join('\n');
}

function exchangeResolveBank(text, fallbackKey = 'ncc') {
    const raw = String(text ?? '').trim();
    const lower = raw.toLowerCase();
    let key = fallbackKey in EXCHANGE_BANKS ? fallbackKey : 'ncc';
    let matched = false;

    if (lower.includes('dutch-bangla') || lower.includes('dbbl')) {
        key = 'dbbl';
        matched = true;
    } else if (lower.includes('national credit') || lower.includes('ncc')) {
        key = 'ncc';
        matched = true;
    }

    const bank = EXCHANGE_BANKS[key] || EXCHANGE_BANKS.ncc;
    const lines = exchangeSplitLines(raw);
    const fullText = raw || exchangeRenderBankBlock(bank);
    const name = matched ? bank.name : (lines[0] || bank.name);
    const address = matched ? bank.address : (lines.slice(1).join('\n') || bank.address);
    const account = matched ? bank.account : (lines[2] || '');
    const swift = matched ? bank.swift : '';
    const routing = matched ? bank.routing : '';

    return {
        key,
        name,
        address,
        account,
        swift,
        routing,
        raw,
        display: fullText,
        fullText,
        lines: lines.length ? lines : exchangeSplitLines(exchangeRenderBankBlock(bank))
    };
}

function exchangeAmountWords(amount) {
    const centsTotal = Math.round((parseFloat(amount || 0) || 0) * 100);
    const ones = ['Zero','One','Two','Three','Four','Five','Six','Seven','Eight','Nine','Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen','Seventeen','Eighteen','Nineteen'];
    const tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
    const chunk = num => {
        if (num < 20) return ones[num];
        if (num < 100) return tens[Math.floor(num / 10)] + (num % 10 ? ' ' + ones[num % 10] : '');
        if (num < 1000) return ones[Math.floor(num / 100)] + ' Hundred' + (num % 100 ? ' ' + chunk(num % 100) : '');
        if (num < 1000000) return chunk(Math.floor(num / 1000)) + ' Thousand' + (num % 1000 ? ' ' + chunk(num % 1000) : '');
        return chunk(Math.floor(num / 1000000)) + ' Million' + (num % 1000000 ? ' ' + chunk(num % 1000000) : '');
    };
    const dollars = Math.floor(centsTotal / 100);
    const cents = centsTotal % 100;
    let out = chunk(dollars) + ' USD';
    if (cents) out += ' and ' + chunk(cents) + ' Cents';
    return out + ' Only';
}

function exchangeSetValue(id, value, force = false) {
    const el = document.getElementById(id);
    if (!el) return;
    const nextVal = value ?? '';
    if (force || !String(el.value || '').trim()) {
        el.value = nextVal;
    }
}

function exchangeSyncAmountWords(force = false) {
    const amountEl = document.getElementById('receivedAmount') || document.getElementById('exchangeAmount');
    const wordsEl  = document.getElementById('tenorWordsMaster');
    const preview  = document.getElementById('exchangePreviewText');
    const amount   = parseFloat(amountEl?.value || 0) || 0;
    const words    = amount ? exchangeAmountWords(amount) : '';

    if (wordsEl && (force || !String(wordsEl.value || '').trim() || wordsEl.dataset.autoWords === wordsEl.value)) {
        wordsEl.value = words;
        wordsEl.dataset.autoWords = words;
    }

    if (preview) {
        const lcNo = document.getElementById('masterLcNo')?.value || '-';
        const bank = document.getElementById('payToBankName')?.value || '-';
        preview.value = amount
            ? `Exchange for USD ${amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} (${words}). LC No: ${lcNo}. Pay to: ${bank}.`
            : '';
    }
}

function syncExchangeDerivedFields(res, force = false) {
    const order = res.order || {};
    const lc = res.pages?.lc || {};
    const sales = res.pages?.sales || {};
    const intake = res.pages?.['marketing-intake'] || {};

    const allPis = res.pis || [];
    const masterPi = allPis.find(p => p.is_master) || allPis[0] || {};
    const allPos = masterPi?.pos?.length ? masterPi.pos
        : sales.pos?.length ? sales.pos
        : intake.pos || [];

    const uniq = (...keys) => [...new Set(allPos.flatMap(p => keys.map(key => p?.[key]).filter(Boolean)))].join(', ');
    const salesOrderVal = uniq('salesOrder', 'salesOrderNo') || order.sales_order_no || '';
    const customerPoVal = uniq('poNum', 'customerPo') || order.customer_po || '';
    const buyerVal = lc.lcBuyer || uniq('buyer', 'buyerName', 'sharedBuyer', 'endBuyer') || sales.buyer || order.buyer_name || '';
    const customerVal = order.customer_name || sales.customer || masterPi.customer || '';
    const hsCodeVal = sales.hsCode || intake.hsCode || EXCHANGE_DEFAULT_HS_CODE;
    const beneficiaryVatBin = lc.beneficiaryVatBin || EXCHANGE_BENEFICIARY_VAT_BIN;

    const issuingBank = exchangeResolveBank(lc.lcIssuingBank || document.getElementById('applicantBank')?.value || '');
    const reimbursementBank = exchangeResolveBank(lc.reimbursementBank || document.getElementById('payToBankName')?.value || issuingBank.fullText || '');
    const negotiatedBankText = lc.negotiatingBeneficiaryBank || document.getElementById('beneficiaryBankAddress')?.value || reimbursementBank.fullText || '';

    exchangeSetValue('salesOrder', salesOrderVal, force);
    exchangeSetValue('customerPo', customerPoVal, force);
    exchangeSetValue('buyerName', buyerVal, force);
    exchangeSetValue('customerName', customerVal, force);

    exchangeSetValue('masterLcNo', lc.lcNumber || '', force);
    exchangeSetValue('masterLcDate', lc.lcDate || '', force);
    exchangeSetValue('applicantBank', issuingBank.fullText || '', force);
    exchangeSetValue('payToBankName', reimbursementBank.name || reimbursementBank.fullText || '', force);
    exchangeSetValue('payToBankAddress', reimbursementBank.address || reimbursementBank.fullText || '', force);
    exchangeSetValue('beneficiaryBankAddress', negotiatedBankText, force);
    exchangeSetValue('negotiatingBankAddress', negotiatedBankText, force);
    exchangeSetValue('beneficiaryVatBin', beneficiaryVatBin, force);
    exchangeSetValue('hsCodeMaster', hsCodeVal, force);

    const tenorEl = document.getElementById('lcTenorMaster');
    if (tenorEl && !String(tenorEl.value || '').trim() && lc.paymentTerms) {
        tenorEl.value = lc.paymentTerms;
    }

    const amountEl = document.getElementById('receivedAmount') || document.getElementById('exchangeAmount');
    if (amountEl && !String(amountEl.value || '').trim()) {
        const savedAmount = res.pages?.exchange?.receivedAmount || res.pages?.exchange?.exchangeAmount || res.pages?.commercial?.commercialTotalAmount || '';
        if (savedAmount) amountEl.value = savedAmount;
    }

    exchangeSyncAmountWords(force);
}

async function openExchangePrint() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'exchange', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/exchange-print.php';
}

async function openExchangeExcel() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'exchange', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/exchange-print.php?excel=1';
}

window.onOrderLoad = function(res) {
    window._exchangeRes = res;
    syncExchangeDerivedFields(res, true);
};

document.addEventListener('ats:orderloaded', function () {
    if (!window._exchangeRes) return;
    setTimeout(() => syncExchangeDerivedFields(window._exchangeRes, true), 0);
});

document.addEventListener('input', function (e) {
    if (e.target && (e.target.id === 'receivedAmount' || e.target.id === 'exchangeAmount')) {
        exchangeSyncAmountWords(false);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    exchangeSyncAmountWords(false);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
