<?php
$pageTitle   = 'Bank Forwarding';
$activePage  = 'bank-forwarding';
$navSection  = 'order';
$pageSubtitle = 'Bank Forwarding — final document pack cover letter.';
include __DIR__ . '/../includes/header.php';
?>

                <section class="form-card" data-page="bank-forwarding">
                    <div class="section-head">
                        <div class="section-title">
                            <span class="section-tag">Section 13</span>
                            <h2>Bank Forwarding</h2>
                        </div>
                        <div class="section-summary">
                            <strong>Bank Pack</strong>
                            <span>Negotiation document cover letter prepared before factory handoff.</span>
                        </div>
                    </div>
                    <div class="packing-sheet">
                        <div class="packing-sheet-header packing-sheet-header-centered">
                            <div class="packing-logo">ZZAL</div>
                            <div class="packing-sheet-title">
                                <h3>Zaber &amp; Zubair Accessories Ltd.</h3>
                                <strong>BANK FORWARDING</strong>
                            </div>
                        </div>

                        <div class="forwarding-meta">
                            <div><span>Date:</span><input id="forwardingDate" name="forwardingDate" type="date" style="border:none;border-bottom:1.5px solid #d1d5db;font:inherit;font-weight:700;padding:2px 4px;outline:none;"></div>
                            <div><span>Reference No.:</span><strong id="forwardingReferenceText">—</strong></div>
                        </div>

                        <div class="forwarding-address">
                            <p>To</p>
                            <strong id="forwardingManagerText">The Manager,</strong>
                            <strong id="forwardingBankNameText">—</strong>
                            <strong id="forwardingBankAddressText">—</strong>
                        </div>

                        <div class="forwarding-subject">
                            <strong>Subject: Application for the following negotiation documents for US $ <span id="forwardingAmountText">—</span> Against</strong>
                            <strong>Letter of Credit No. <span id="forwardingLcNoText">—</span> Dated <span id="forwardingLcDateText">—</span> of <span id="forwardingCustomerText">—</span></strong>
                        </div>

                        <div class="forwarding-body">
                            <p>Dear Sir,</p>
                            <p>We hereby submit the following documents for negotiation of US $ <span id="forwardingBodyAmountText">—</span> (<span id="forwardingAmountWords">—</span>) delivery of garments accessories as per proforma Invoice No. <span id="forwardingProformaText">—</span> Dated <span id="forwardingProformaDateText">—</span>.</p>
                        </div>

                        <div class="packing-items-wrap">
                            <table class="packing-items-table">
                                <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>Description</th>
                                        <th>Advising Bank</th>
                                        <th>Consignee's Bank</th>
                                    </tr>
                                </thead>
                                <tbody id="forwardingItemsBody">
                                    <tr><td>1</td><td>Bill of Exchange</td><td><div class="copy-entry"><input class="table-entry table-entry-count" value="2"><span>Copies</span></div></td><td><div class="copy-entry"><input class="table-entry table-entry-count" value=""><span>Copies</span></div></td></tr>
                                    <tr><td>2</td><td>Commercial Invoice</td><td><div class="copy-entry"><input class="table-entry table-entry-count" value="1"><span>Copy</span></div></td><td><div class="copy-entry"><input class="table-entry table-entry-count" value=""><span>Copies</span></div></td></tr>
                                    <tr><td>3</td><td>Packing List</td><td><div class="copy-entry"><input class="table-entry table-entry-count" value="1"><span>Copy</span></div></td><td><div class="copy-entry"><input class="table-entry table-entry-count" value=""><span>Copies</span></div></td></tr>
                                    <tr><td>4</td><td>Delivery Challan</td><td><div class="copy-entry"><input class="table-entry table-entry-count" value="1"><span>Copy</span></div></td><td><div class="copy-entry"><input class="table-entry table-entry-count" value=""><span>Copies</span></div></td></tr>
                                    <tr><td>5</td><td>Certificate of Origin</td><td><div class="copy-entry"><input class="table-entry table-entry-count" value="1"><span>Copy</span></div></td><td><div class="copy-entry"><input class="table-entry table-entry-count" value=""><span>Copies</span></div></td></tr>
                                    <tr><td>6</td><td>Beneficiary Certificate</td><td><div class="copy-entry"><input class="table-entry table-entry-count" value="1"><span>Copy</span></div></td><td><div class="copy-entry"><input class="table-entry table-entry-count" value=""><span>Copies</span></div></td></tr>
                                    <tr><td>7</td><td>Truck Challan</td><td><div class="copy-entry"><input class="table-entry table-entry-count" value="1"><span>Copy</span></div></td><td><div class="copy-entry"><input class="table-entry table-entry-count" value=""><span>Copies</span></div></td></tr>
                                    <tr><td>8</td><td>Mushok Challan 6.3</td><td><div class="copy-entry"><input class="table-entry table-entry-count" value="1"><span>Copy</span></div></td><td><div class="copy-entry"><input class="table-entry table-entry-count" value=""><span>Copies</span></div></td></tr>
                                    <tr><td>9</td><td>Others</td><td><div class="copy-entry"><input class="table-entry table-entry-count" value="1"><span>Copy</span></div></td><td><div class="copy-entry"><input class="table-entry table-entry-count" value=""><span>Copy</span></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="page-actions-left">
                            <button type="button" class="ghost-btn js-prev-page" data-prev-page="forwarding">Previous</button>
                        </div>
                        <div class="page-actions-right">
                            <button type="button" class="primary-btn js-next-page" data-next-page="po-status">Next: Challan Sheet →</button>
                        </div>
                    </div>
                </section>

<script>
async function openBankForwardingPrint() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'bank-forwarding', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/document-print.php?doc=bank-forwarding';
}
async function openBankForwardingExcel() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'bank-forwarding', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/document-print.php?doc=bank-forwarding&excel=1';
}
document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.querySelector('.page-actions-right');
    if (wrap && !document.getElementById('btnBankForwardingPrint')) {
        const xls = document.createElement('button');
        xls.type = 'button';
        xls.id = 'btnBankForwardingExcel';
        xls.className = 'ghost-btn';
        xls.textContent = 'Download Bank Forwarding Excel';
        xls.onclick = openBankForwardingExcel;
        wrap.prepend(xls);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'btnBankForwardingPrint';
        btn.className = 'ghost-btn';
        btn.textContent = 'Print Bank Forwarding';
        btn.onclick = openBankForwardingPrint;
        wrap.prepend(btn);
    }
});
window.onOrderLoad = function(res) {
    const order = res.order || {};
    const comm  = res.pages?.commercial || {};
    const exch  = res.pages?.exchange || {};
    const lc    = res.pages?.lc || {};

    const set = (id, val) => { const el = document.getElementById(id); if (el && val) el.textContent = val; };

    const lcNo    = exch.masterLcNo || lc.lcNumber || '';
    const lcDate  = exch.masterLcDate || lc.lcDate || '';
    const pi      = comm.proformaNo || '';
    const piDate  = comm.proformaDate || '';
    const amount  = exch.exchangeAmount || comm.commercialTotalAmount || '';
    const words   = exch.tenorWordsMaster || '';
    const customer = order.customer_name || comm.commercialConsigneeName || '';
    const bankName = exch.payToBankName || sessionStorage.getItem('ats_advising_bank') || '';
    const invNo   = comm.invoiceNo || pi || '';

    set('forwardingReferenceText', invNo || '—');
    set('forwardingBankNameText', bankName || '—');
    set('forwardingBankAddressText', exch.payToBankAddress || '—');
    set('forwardingAmountText', amount || '—');
    set('forwardingBodyAmountText', amount || '—');
    set('forwardingAmountWords', words || '—');
    set('forwardingLcNoText', lcNo || '—');
    set('forwardingLcDateText', lcDate || '—');
    set('forwardingCustomerText', customer ? customer + '.' : '—');
    set('forwardingProformaText', pi || '—');
    set('forwardingProformaDateText', piDate || '—');
};
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
