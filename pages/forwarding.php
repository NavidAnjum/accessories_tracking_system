<?php
$pageTitle   = 'Forwarding';
$activePage  = 'forwarding';
$navSection  = 'order';
$pageSubtitle = 'Forwarding — document check list before bank submission.';
include __DIR__ . '/../includes/header.php';
?>

                <section class="form-card" data-page="forwarding">
                    <div class="section-head">
                        <div class="section-title">
                            <span class="section-tag">Section 12</span>
                            <h2>Forwarding</h2>
                        </div>
                        <div class="section-summary">
                            <strong>Checklist</strong>
                            <span>Check the document pack before sending it to the bank.</span>
                        </div>
                    </div>

                    <div class="packing-sheet">
                        <div class="packing-sheet-header packing-sheet-header-centered">
                            <div class="packing-logo">ZZAL</div>
                            <div class="packing-sheet-title">
                                <h3>Zaber &amp; Zubair Accessories Ltd.</h3>
                                <strong>DOCUMENT CHECK LIST</strong>
                            </div>
                        </div>

                        <div class="forwarding-meta">
                            <div><span>Document Submit Date:</span><strong><input id="forwardingSubmitDate" name="forwardingSubmitDate" type="date"></strong></div>
                        </div>

                        <div class="packing-notes" style="margin-bottom:18px;">
                            <div class="packing-note-row"><span>Customer Name</span><strong id="forwardingCustomerName">—</strong></div>
                            <div class="packing-note-row"><span>LC No.</span><strong id="forwardingLcNo">—</strong></div>
                            <div class="packing-note-row"><span>LC Date</span><strong id="forwardingLcDate">—</strong></div>
                            <div class="packing-note-row"><span>Value</span><strong id="forwardingValue">—</strong></div>
                            <div class="packing-note-row"><span>Document Value</span><strong id="forwardingDocumentValue">—</strong></div>
                        </div>

                        <div class="packing-items-wrap">
                            <table class="packing-items-table">
                                <thead>
                                    <tr>
                                        <th>SL</th>
                                        <th>Description</th>
                                        <th>Requirement Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>1</td><td>Bill of Exchange</td><td><input id="forwardingQty1" name="forwardingQty1" class="table-entry" value="2 Copies"></td></tr>
                                    <tr><td>2</td><td>Commercial Invoice</td><td><input id="forwardingQty2" name="forwardingQty2" class="table-entry" value="1 Copy"></td></tr>
                                    <tr><td>3</td><td>Packing List</td><td><input id="forwardingQty3" name="forwardingQty3" class="table-entry" value="1 Copy"></td></tr>
                                    <tr><td>4</td><td>Delivery Challan</td><td><input id="forwardingQty4" name="forwardingQty4" class="table-entry" value="1 Copy"></td></tr>
                                    <tr><td>5</td><td>Certificate of Origin</td><td><input id="forwardingQty5" name="forwardingQty5" class="table-entry" value="1 Copy"></td></tr>
                                    <tr><td>6</td><td>Beneficiary Certificate</td><td><input id="forwardingQty6" name="forwardingQty6" class="table-entry" value="1 Copy"></td></tr>
                                    <tr><td>7</td><td>Truck Challan</td><td><input id="forwardingQty7" name="forwardingQty7" class="table-entry" value="1 Copy"></td></tr>
                                    <tr><td>8</td><td>Delivery Challan Original Copy</td><td><input id="forwardingQty8" name="forwardingQty8" class="table-entry" value="1 Copy"></td></tr>
                                    <tr><td>9</td><td>L/C Copy &amp; PI</td><td><input id="forwardingQty9" name="forwardingQty9" class="table-entry" value="1 Copy"></td></tr>
                                    <tr><td>10</td><td><input id="forwardingExtraDesc1" name="forwardingExtraDesc1" class="table-entry" placeholder="Additional field 1"></td><td><input id="forwardingExtraQty1" name="forwardingExtraQty1" class="table-entry" placeholder="Qty / note"></td></tr>
                                    <tr><td>11</td><td><input id="forwardingExtraDesc2" name="forwardingExtraDesc2" class="table-entry" placeholder="Additional field 2"></td><td><input id="forwardingExtraQty2" name="forwardingExtraQty2" class="table-entry" placeholder="Qty / note"></td></tr>
                                    <tr><td>12</td><td><input id="forwardingExtraDesc3" name="forwardingExtraDesc3" class="table-entry" placeholder="Additional field 3"></td><td><input id="forwardingExtraQty3" name="forwardingExtraQty3" class="table-entry" placeholder="Qty / note"></td></tr>
                                    <tr><td>13</td><td><input id="forwardingExtraDesc4" name="forwardingExtraDesc4" class="table-entry" placeholder="Additional field 4"></td><td><input id="forwardingExtraQty4" name="forwardingExtraQty4" class="table-entry" placeholder="Qty / note"></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="page-actions">
                        <div class="page-actions-left">
                            <button type="button" class="ghost-btn js-prev-page" data-prev-page="beneficiary">Previous</button>
                        </div>
                        <div class="page-actions-right">
                            <button type="button" class="primary-btn js-next-page" data-next-page="bank-forwarding">Next: Bank Forwarding →</button>
                        </div>
                    </div>
                </section>

<script>
async function openForwardingPrint() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'forwarding', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/document-print.php?doc=forwarding';
}
async function openForwardingExcel() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'forwarding', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/document-print.php?doc=forwarding&excel=1';
}
document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.querySelector('.page-actions-right');
    if (wrap && !document.getElementById('btnForwardingPrint')) {
        const xls = document.createElement('button');
        xls.type = 'button';
        xls.id = 'btnForwardingExcel';
        xls.className = 'ghost-btn';
        xls.textContent = 'Download Forwarding Excel';
        xls.onclick = openForwardingExcel;
        wrap.prepend(xls);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'btnForwardingPrint';
        btn.className = 'ghost-btn';
        btn.textContent = 'Print Forwarding';
        btn.onclick = openForwardingPrint;
        wrap.prepend(btn);
    }
});
window.onOrderLoad = function(res) {
    const order = res.order || {};
    const comm  = res.pages?.commercial || {};
    const exch  = res.pages?.exchange || {};
    const lc    = res.pages?.lc || {};

    const set = (id, val) => {
        const el = document.getElementById(id);
        if (!el || !val) return;
        el.textContent = val;
    };

    const lcNo = exch.masterLcNo || lc.lcNumber || '—';
    const lcDate = exch.masterLcDate || lc.lcDate || '—';
    const value = exch.exchangeAmount || comm.commercialTotalAmount || '—';
    const customer = order.customer_name || comm.commercialConsigneeName || '—';

    set('forwardingCustomerName', customer);
    set('forwardingLcNo', lcNo);
    set('forwardingLcDate', lcDate);
    set('forwardingValue', value);
    set('forwardingDocumentValue', value);
};
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
