<?php
$pageTitle   = 'LC';
$activePage  = 'lc';
$navSection  = 'order';
$pageSubtitle = 'Letter of Credit check and record LC details.';
include __DIR__ . '/../includes/header.php';
?>

                <section class="form-card" data-page="lc">
                    <div class="section-head">
                        <div class="section-title">
                            <span class="section-tag">Section 10</span>
                            <h2>LC</h2>
                        </div>
                        <div class="section-summary">
                            <strong>Control</strong>
                            <span>Check and prepare the LC step before Bill of Exchange.</span>
                        </div>
                    </div>
                    <div class="source-glance">
                        <div class="source-glance-item"><span>Matched Sales Order</span><strong data-bind="salesOrder">-</strong></div>
                        <div class="source-glance-item"><span>Customer PO</span><strong data-bind="customerPo">-</strong></div>
                        <div class="source-glance-item"><span>Buyer</span><strong data-bind="buyerName">-</strong></div>
                        <div class="source-glance-item"><span>Customer</span><strong data-bind="customerName">-</strong></div>
                    </div>
                    <div class="form-grid">
                        <div class="field span-6">
                            <label for="lcCheckStatus">LC Check Status</label>
                            <select id="lcCheckStatus" name="lcCheckStatus">
                                <option>Pending</option>
                                <option>Checked</option>
                                <option>Need Correction</option>
                                <option>Approved</option>
                            </select>
                        </div>
                        <div class="field span-6">
                            <label for="paymentTerms">Payment Terms</label>
                            <input id="paymentTerms" name="paymentTerms" placeholder="e.g. 120 Days LC">
                        </div>
                        <div class="field span-6">
                            <label for="shippingTerms">Shipping Terms</label>
                            <input id="shippingTerms" name="shippingTerms" placeholder="e.g. FOB Chittagong">
                        </div>
                        <div class="field span-6">
                            <label for="lcNumber">LC Number</label>
                            <input id="lcNumber" name="lcNumber" placeholder="LC number...">
                        </div>
                        <div class="field span-4">
                            <label for="lcDate">LC Date</label>
                            <input id="lcDate" name="lcDate" type="date">
                        </div>
                        <div class="field span-4">
                            <label for="lcReceivedDate">L/C Received Date</label>
                            <input id="lcReceivedDate" name="lcReceivedDate" type="date">
                        </div>
                        <div class="field span-4">
                            <label for="lcShipDate">L/C Ship Date</label>
                            <input id="lcShipDate" name="lcShipDate" type="date">
                        </div>
                        <div class="field span-6">
                            <label for="lcExpiryDate">L/C Expiry Date</label>
                            <input id="lcExpiryDate" name="lcExpiryDate" type="date">
                        </div>
                        <div class="field span-6">
                            <label for="lcDescription">Description</label>
                            <input id="lcDescription" name="lcDescription" placeholder="e.g. Export LC for approved order">
                        </div>
                        <div class="field span-6">
                            <label for="lcAmount">LC Amount (USD)</label>
                            <input id="lcAmount" name="lcAmount" type="number" step="0.01" placeholder="0.00">
                        </div>
                        <div class="field span-4">
                            <label for="docSendToBuyerDate">Doc send to the Buyer (date)</label>
                            <input id="docSendToBuyerDate" name="docSendToBuyerDate" type="date">
                        </div>
                        <div class="field span-4">
                            <label for="acceptanceDate">Acceptance Date</label>
                            <input id="acceptanceDate" name="acceptanceDate" type="date">
                        </div>
                        <div class="field span-4">
                            <label for="docSentToNegotiatingBank">Doc sent to the Negotiating Bank</label>
                            <input id="docSentToNegotiatingBank" name="docSentToNegotiatingBank" type="date">
                        </div>
                        <div class="field span-4">
                            <label for="maturityDate">Maturity Date</label>
                            <input id="maturityDate" name="maturityDate" type="date">
                        </div>
                        <div class="field span-4">
                            <label for="receivedAmount">Received Amount</label>
                            <input id="receivedAmount" name="receivedAmount" type="number" step="0.01" placeholder="0.00">
                        </div>
                        <div class="field span-4">
                            <label for="receivedDate">Received Date</label>
                            <input id="receivedDate" name="receivedDate" type="date">
                        </div>
                        <div class="field span-4">
                            <label for="lcIssuingBank">L/C Issuing Bank</label>
                            <textarea id="lcIssuingBank" name="lcIssuingBank" placeholder="Issuing bank details"></textarea>
                        </div>
                        <div class="field span-4">
                            <label for="reimbursementBank">Reimbursment Bank</label>
                            <textarea id="reimbursementBank" name="reimbursementBank" placeholder="Reimbursment bank details"></textarea>
                        </div>
                        <div class="field span-4">
                            <label for="negotiatingBeneficiaryBank">Negotiating / Benificiary Bank</label>
                            <textarea id="negotiatingBeneficiaryBank" name="negotiatingBeneficiaryBank" placeholder="Negotiating or benificiary bank details"></textarea>
                        </div>
                        <div class="field span-12">
                            <label for="lcNotes">LC Notes</label>
                            <textarea id="lcNotes" name="lcNotes" placeholder="Notes on LC check..."></textarea>
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="page-actions-left">
                            <button type="button" class="ghost-btn js-prev-page" data-prev-page="marketing">Previous</button>
                        </div>
                        <div class="page-actions-right">
                            <button type="button" class="primary-btn js-next-page" data-next-page="exchange">Next: Bill of Exchange</button>
                        </div>
                    </div>
                </section>

<script>
// Populate LC glance fields from saved sales page data
window.onOrderLoad = (function(_prev) {
    return function(res) {
        if (typeof _prev === 'function') _prev(res);
        const sales   = res.pages?.sales || {};
        const intake  = res.pages?.['marketing-intake'] || {};

        // Priority: pages.sales snapshot -> pis ERP data -> marketing-intake
        const allPis  = res.pis || [];
        const bestPi  = allPis.find(p => p.is_master) || allPis[0] || {};
        const erpPos  = bestPi.pos || [];
        const firstPo = sales.pos?.[0] || erpPos[0] || intake.pos?.[0] || {};

        const setGlance = (key, val) => {
            const el = document.querySelector(`[data-bind="${key}"]`);
            if (el && val) el.textContent = val;
        };

        setGlance('salesOrder', firstPo.salesOrder || firstPo.salesOrderNo || '-');
        setGlance('customerPo', firstPo.poNum || firstPo.customerPo || '-');
        setGlance('buyerName', firstPo.buyer || firstPo.endBuyer || '-');

        const custName = sales.customer || bestPi.customer || intake.customer || '';
        if (custName) {
            setGlance('customerName', custName);
        } else {
            const custId = intake.intakeCustomer;
            if (custId) {
                fetch(window.APP_BASE + '/api/customers.php?id=' + custId)
                    .then(r => r.json())
                    .then(c => { if (c?.company_name) setGlance('customerName', c.company_name); })
                    .catch(() => {});
            }
        }
    };
})(window.onOrderLoad);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
