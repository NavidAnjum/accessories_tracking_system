<?php
$pageTitle    = 'Bill / Sales Contract';
$activePage   = 'bill';
$navSection   = 'order';
$pageSubtitle = 'Optional bill / sales-contract document — print only, nothing to submit.';
include __DIR__ . '/../includes/header.php';
?>

<style>
.bill-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:14px 16px}
.bill-span-6{grid-column:span 6}.bill-span-12{grid-column:span 12}
.bill-note{grid-column:span 12;padding:10px 14px;border-radius:9px;background:#f1f5f9;color:#475569;font-size:12px}
@media(max-width:760px){.bill-span-6{grid-column:span 12}}
</style>

<section class="form-card" data-page="bill">
    <div class="section-head">
        <div class="section-title">
            <span class="section-tag">Bill</span>
            <h2>Bill / Sales Contract</h2>
        </div>
        <div class="section-summary">
            <strong>Optional Step</strong>
            <span>Print the bill after the PI. This step is optional — nothing is saved or submitted.</span>
        </div>
    </div>

    <div class="bill-grid">
        <div class="bill-note">This document mirrors the PI. Only the <strong>Bill No.</strong> can be adjusted before printing — it defaults to the PI number.</div>
        <div class="field bill-span-6">
            <label for="billNo">Bill No.</label>
            <input id="billNo" name="billNo" placeholder="26/1001">
        </div>
    </div>

    <div class="page-actions">
        <div class="page-actions-left">
            <button type="button" class="ghost-btn js-prev-page" data-prev-page="sales">Previous: PI</button>
        </div>
        <div class="page-actions-right">
            <button type="button" class="ghost-btn" onclick="openBillDocument(true)">Download Excel</button>
            <button type="button" class="ghost-btn" onclick="openBillDocument(false)">Print / Save PDF</button>
            <button type="button" class="primary-btn js-next-page" data-next-page="lc">Next: LC / Sales Contract</button>
        </div>
    </div>
</section>

<script>
function billSetIfEmpty(id, value) {
    const el = document.getElementById(id);
    if (el && !String(el.value || '').trim() && value != null) el.value = value;
}
function openBillDocument(excel) {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    const billNo = String(document.getElementById('billNo')?.value || '').trim();
    let url = APP_BASE + '/pages/document-print.php?doc=bill';
    if (billNo) url += '&bill_no=' + encodeURIComponent(billNo);
    if (excel) url += '&excel=1';
    window.location.href = url;
}
window.onOrderLoad = (function(previous) {
    return function(res) {
        if (typeof previous === 'function') previous(res);
        const sales = res.pages?.sales || {};
        const pi = (res.pis || []).find(p => p.is_master) || (res.pis || [])[0] || {};
        const piNo = sales.piNum || pi.pi_number || res.pages?.commercial?.proformaNo || '';
        billSetIfEmpty('billNo', piNo);
    };
})(window.onOrderLoad);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
