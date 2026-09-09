<?php
$pageTitle    = 'Sales Contract';
$activePage   = 'sales-contract';
$navSection   = 'order';
$pageSubtitle = 'Prepare the buyer and supplier sales contract.';
include __DIR__ . '/../includes/header.php';
?>

<style>
.sc-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:14px 16px}
.sc-span-12{grid-column:span 12}.sc-span-6{grid-column:span 6}.sc-span-4{grid-column:span 4}
.sc-section{grid-column:span 12;margin-top:8px;padding:9px 12px;border-radius:9px;background:#eef2ff;color:#3730a3;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
.sc-help{display:block;margin-top:5px;color:#64748b;font-size:11px}
@media(max-width:760px){.sc-span-6,.sc-span-4{grid-column:span 12}}
</style>

<section class="form-card" data-page="sales-contract">
    <div class="section-head">
        <div class="section-title">
            <span class="section-tag">Sales Contract</span>
            <h2>Sales Contract</h2>
        </div>
        <div class="section-summary">
            <strong>Contract Details</strong>
            <span>Complete the contract, then generate the branded PDF or Excel file.</span>
        </div>
    </div>

    <div class="sc-grid">
        <div class="sc-section">Contract Reference</div>
        <div class="field sc-span-6">
            <label for="scContractNo">Contract No.</label>
            <input id="scContractNo" name="scContractNo" placeholder="ZZAL/TC/26/63517">
        </div>
        <div class="field sc-span-6">
            <label for="scContractDate">Contract Date</label>
            <input id="scContractDate" name="scContractDate" type="date">
        </div>

        <div class="sc-section">Buyer / Importer</div>
        <div class="field sc-span-4">
            <label for="scBuyerName">Buyer / Importer Name</label>
            <input id="scBuyerName" name="scBuyerName" placeholder="Buyer or importer name">
        </div>
        <div class="field sc-span-4">
            <label for="scBuyerAddress">Buyer / Importer Address</label>
            <textarea id="scBuyerAddress" name="scBuyerAddress" rows="3" placeholder="Full address"></textarea>
        </div>
        <div class="field sc-span-4">
            <label for="scBuyerBin">Buyer BIN No.</label>
            <input id="scBuyerBin" name="scBuyerBin" placeholder="BIN number">
        </div>
        <div class="field sc-span-6">
            <label for="scBuyerBankName">Buyer / Importer Bank</label>
            <textarea id="scBuyerBankName" name="scBuyerBankName" rows="2" placeholder="Bank name"></textarea>
        </div>
        <div class="field sc-span-6">
            <label for="scBuyerBankAddress">Buyer Bank Address</label>
            <textarea id="scBuyerBankAddress" name="scBuyerBankAddress" rows="2" placeholder="Bank address"></textarea>
        </div>
        <div class="field sc-span-6">
            <label for="scBuyerBankAccount">Buyer Bank Account No.</label>
            <input id="scBuyerBankAccount" name="scBuyerBankAccount" placeholder="Account number">
        </div>
        <div class="field sc-span-6">
            <label for="scBuyerBankSwift">Buyer Bank Swift-BIC No.</label>
            <input id="scBuyerBankSwift" name="scBuyerBankSwift" placeholder="Swift-BIC">
        </div>

        <div class="sc-section">Supplier</div>
        <div class="field sc-span-4">
            <label for="scSupplierName">Supplier Name</label>
            <input id="scSupplierName" name="scSupplierName" value="Zaber &amp; Zubair Accessories Ltd.">
        </div>
        <div class="field sc-span-4">
            <label for="scSupplierFactory">Factory Address</label>
            <textarea id="scSupplierFactory" name="scSupplierFactory" rows="2">Mawna, Sreepur, Gazipur.</textarea>
        </div>
        <div class="field sc-span-4">
            <label for="scSupplierOffice">Head Office Address</label>
            <textarea id="scSupplierOffice" name="scSupplierOffice" rows="2">115-120 Motijheel C/A, Dhaka-1000, Bangladesh.</textarea>
        </div>
        <div class="field sc-span-6">
            <label for="scSupplierBankName">Supplier Bank Name &amp; Address</label>
            <textarea id="scSupplierBankName" name="scSupplierBankName" rows="3">National Credit &amp; Commerce Bank Ltd.
Motijheel Main Branch, 6 Motijheel C/A, Dhaka-1000, Bangladesh.</textarea>
        </div>
        <div class="field sc-span-6">
            <label for="scSupplierBankDetails">Supplier Bank Account Details</label>
            <textarea id="scSupplierBankDetails" name="scSupplierBankDetails" rows="3">Bank Account No - 0002-0210023211
Routing No - 160274242
Swift - NCCLBDDHNBB</textarea>
        </div>

        <div class="sc-section">Goods and Commercial Terms</div>
        <div class="field sc-span-12">
            <label for="scGoodsDescription">Description of Goods</label>
            <textarea id="scGoodsDescription" name="scGoodsDescription" rows="2" placeholder="As per Proforma Invoice..."></textarea>
        </div>
        <div class="field sc-span-6">
            <label for="scBuyerContractNo">Buyer S/C No.</label>
            <input id="scBuyerContractNo" name="scBuyerContractNo" placeholder="Buyer sales contract number">
        </div>
        <div class="field sc-span-6">
            <label for="scBuyerContractDate">Buyer S/C Date</label>
            <input id="scBuyerContractDate" name="scBuyerContractDate" type="date">
        </div>
        <div class="field sc-span-6">
            <label for="scShipmentValidity">Shipment Validity</label>
            <input id="scShipmentValidity" name="scShipmentValidity" type="date">
        </div>
        <div class="field sc-span-6">
            <label for="scExpiryValidity">Expiry Validity</label>
            <input id="scExpiryValidity" name="scExpiryValidity" type="date">
        </div>
        <div class="field sc-span-12">
            <label for="scGoodsQuantity">Goods Quantity</label>
            <textarea id="scGoodsQuantity" name="scGoodsQuantity" rows="2" placeholder="Item quantities and total quantity"></textarea>
        </div>
        <div class="field sc-span-4">
            <label for="scPrice">Price</label>
            <input id="scPrice" name="scPrice" value="As per PI">
        </div>
        <div class="field sc-span-4">
            <label for="scTotalValue">Total Value (USD)</label>
            <input id="scTotalValue" name="scTotalValue" type="number" step="0.01" placeholder="0.00" oninput="updateSalesContractWords()">
        </div>
        <div class="field sc-span-4">
            <label for="scTotalWords">Total Value in Words</label>
            <input id="scTotalWords" name="scTotalWords" readonly>
        </div>
        <div class="field sc-span-4">
            <label for="scPaymentTerms">Terms of Payment</label>
            <input id="scPaymentTerms" name="scPaymentTerms" value="AT SIGHT (RTGS)">
        </div>
        <div class="field sc-span-4">
            <label for="scDeliveryTerms">Delivery Terms</label>
            <input id="scDeliveryTerms" name="scDeliveryTerms" value="CPT (Dhaka EPZ)">
        </div>
        <div class="field sc-span-4">
            <label for="scDeliveryTo">Delivery To</label>
            <input id="scDeliveryTo" name="scDeliveryTo" value="Applicant factory">
        </div>
        <div class="field sc-span-6">
            <label for="scHsCode">H.S Code</label>
            <input id="scHsCode" name="scHsCode" value="4819.10.00, 6217.10.00">
        </div>
        <div class="field sc-span-6">
            <label for="scOtherTerms">Other Terms &amp; Condition</label>
            <input id="scOtherTerms" name="scOtherTerms" value="As per mutual agreement">
        </div>
    </div>

    <div class="page-actions">
        <div class="page-actions-left">
            <button type="button" class="ghost-btn js-prev-page" data-prev-page="lc">Previous: Document Route</button>
        </div>
        <div class="page-actions-right">
            <button type="button" class="ghost-btn" onclick="openSalesContractDocument(true)">Download Excel</button>
            <button type="button" class="ghost-btn" onclick="openSalesContractDocument(false)">Print / Save PDF</button>
            <button type="button" class="primary-btn js-next-page" data-next-page="commercial">Next: Commercial Invoice</button>
        </div>
    </div>
</section>

<script>
function scSetIfEmpty(id, value) {
    const el = document.getElementById(id);
    if (el && !String(el.value || '').trim() && value != null) el.value = value;
}
function scDate(value) {
    const text = String(value || '').slice(0, 10);
    return /^\d{4}-\d{2}-\d{2}$/.test(text) ? text : '';
}
function scDisplayDate(value) {
    const date = scDate(value);
    if (!date) return '';
    const [y,m,d] = date.split('-');
    return `${d}/${m}/${y}`;
}
function scNumberWords(value) {
    let n = Math.floor(Number(value) || 0);
    if (!n) return 'ZERO US DOLLAR ONLY.';
    const one = ['','ONE','TWO','THREE','FOUR','FIVE','SIX','SEVEN','EIGHT','NINE','TEN','ELEVEN','TWELVE','THIRTEEN','FOURTEEN','FIFTEEN','SIXTEEN','SEVENTEEN','EIGHTEEN','NINETEEN'];
    const ten = ['','','TWENTY','THIRTY','FORTY','FIFTY','SIXTY','SEVENTY','EIGHTY','NINETY'];
    const chunk = x => x < 20 ? one[x] : x < 100 ? ten[Math.floor(x/10)] + (x%10 ? ' ' + one[x%10] : '') : one[Math.floor(x/100)] + ' HUNDRED' + (x%100 ? ' ' + chunk(x%100) : '');
    const scales = ['','THOUSAND','MILLION','BILLION'];
    const parts = [];
    for (let i=0; n>0; i++, n=Math.floor(n/1000)) if (n%1000) parts.unshift(chunk(n%1000) + (scales[i] ? ' ' + scales[i] : ''));
    return 'USD. ' + parts.join(' ') + ' ONLY.';
}
function updateSalesContractWords() {
    const total = document.getElementById('scTotalValue')?.value || 0;
    const words = document.getElementById('scTotalWords');
    if (words) words.value = scNumberWords(total);
}
async function saveSalesContract() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return false; }
    const response = await fetch(APP_BASE + '/api/save_page.php', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({order_id:orderId,page_name:'sales-contract',...collectPageFields()})
    });
    return response.ok;
}
async function openSalesContractDocument(excel) {
    try {
        if (!await saveSalesContract()) return;
        window.location.href = APP_BASE + '/pages/document-print.php?doc=sales-contract' + (excel ? '&excel=1' : '');
    } catch (_) { alert('Could not save the Sales Contract.'); }
}
window.onOrderLoad = (function(previous) {
    return function(res) {
        if (typeof previous === 'function') previous(res);
        const sales = res.pages?.sales || {};
        const intake = res.pages?.['marketing-intake'] || {};
        const resolved = window.atsResolveDisplayPos ? window.atsResolveDisplayPos(res) : {pos:sales.pos || []};
        const pos = resolved.pos || [];
        const pi = (res.pis || []).find(p => p.is_master) || (res.pis || [])[0] || {};
        const firstPo = pos[0] || {};
        const piNo = sales.piNum || pi.pi_number || '';
        const piDate = scDate(sales.piDate || pi.pi_date || res.order?.created_at);
        const contractNo = piNo ? piNo.replace('/PI/', '/TC/') : '';
        const buyerName = sales.customer || res.order?.customer_name || intake.customer || firstPo.customer || '';
        const buyerAddress = sales.buyerAddress || firstPo.sharedBuyerAddress || firstPo.shipToAddress || '';
        let total = 0;
        const quantityParts = [];
        pos.forEach(po => (po.items || []).forEach(item => {
            const qty = Number(item.qty || 0) || 0;
            const price = Number(item.price || item.unitPrice || 0) || 0;
            total += Number(item.total || 0) || qty * price;
            if (qty) quantityParts.push(`${item.desc || item.itemName || 'Goods'} - ${qty.toLocaleString()} Pcs`);
        }));
        const totalQty = pos.flatMap(po => po.items || []).reduce((sum,item) => sum + (Number(item.qty || 0) || 0), 0);
        if (totalQty && quantityParts.length) quantityParts.push(`Total = ${totalQty.toLocaleString()} Pcs`);

        scSetIfEmpty('scContractNo', contractNo);
        scSetIfEmpty('scContractDate', piDate || new Date().toISOString().slice(0,10));
        scSetIfEmpty('scBuyerName', buyerName);
        scSetIfEmpty('scBuyerAddress', buyerAddress);
        scSetIfEmpty('scBuyerBankName', sales.consigneeBank || '');
        scSetIfEmpty('scGoodsDescription', piNo ? `As per Proforma Invoice No. ${piNo}${piDate ? ' Date. ' + scDisplayDate(piDate) : ''}` : 'As per Proforma Invoice');
        scSetIfEmpty('scGoodsQuantity', quantityParts.join(', '));
        scSetIfEmpty('scTotalValue', total ? total.toFixed(2) : '');
        if (sales.hsCode) document.getElementById('scHsCode').value = sales.hsCode;
        updateSalesContractWords();
    };
})(window.onOrderLoad);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
