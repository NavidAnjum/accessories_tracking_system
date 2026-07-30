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
    const order = res.order || {};
    const lc    = res.pages?.lc || {};

    const allPis   = res.pis || [];
    const masterPi = allPis.find(p => p.is_master) || allPis[0];
    const allPos   = masterPi?.pos?.length ? masterPi.pos
                   : res.pages?.sales?.pos?.length ? res.pages.sales.pos
                   : res.pages?.['marketing-intake']?.pos || [];

    const uniq = key => [...new Set(allPos.map(p => p[key]).filter(Boolean))].join(', ');
    const salesOrderVal = uniq('salesOrder') || order.sales_order_no || '';
    const customerPoVal = uniq('poNum') || order.customer_po || '';
    const buyerVal      = uniq('buyer') || order.buyer_name || '';
    const customerVal   = order.customer_name || masterPi?.customer || res.pages?.sales?.customer || '';

    const fill = (id, val) => {
        const el = document.getElementById(id);
        if (el && !el.value && val) el.value = val;
    };

    fill('salesOrder', salesOrderVal);
    fill('customerPo', customerPoVal);
    fill('buyerName', buyerVal);
    fill('customerName', customerVal);

    fill('masterLcNo', lc.lcNumber || '');
    fill('masterLcDate', lc.lcDate || '');
    fill('lcTenorMaster', lc.paymentTerms || '');
    fill('applicantBank', lc.lcIssuingBank || '');
    fill('payToBankName', lc.reimbursementBank || '');
    fill('beneficiaryBankAddress', lc.negotiatingBeneficiaryBank || '');
    fill('negotiatingBankAddress', lc.negotiatingBeneficiaryBank || '');
};
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
