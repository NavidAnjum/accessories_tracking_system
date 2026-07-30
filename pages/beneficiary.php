<?php
$pageTitle   = "Beneficiary's Certificate";
$activePage  = 'beneficiary';
$navSection  = 'order';
$pageSubtitle = "Beneficiary's Certificate — bank export document.";
include __DIR__ . '/../includes/header.php';
?>
<style>
.origin-footer { display:none !important; }
</style>

                <section class="form-card" data-page="beneficiary">
                    <div class="section-head">
                        <div class="section-title">
                            <span class="section-tag">Section 9</span>
                            <h2>Beneficiary's Certificate</h2>
                        </div>
                        <div class="section-summary">
                            <strong>Certificate</strong>
                            <span>This certificate reuses quantity, amount, invoice, LC, applicant, consignee, contract, and HS code details.</span>
                        </div>
                    </div>
                    <div class="packing-sheet">
                        <div class="packing-sheet-header packing-sheet-header-centered">
                            <div class="packing-logo">ZZAL</div>
                            <div class="packing-sheet-title">
                                <h3>Zaber &amp; Zubair Accessories Ltd.</h3>
                                <strong>BENEFICIARY'S CERTIFICATE</strong>
                            </div>
                        </div>

                        <div class="origin-body">
                            <p id="beneficiaryStatementOne">—</p>
                            <p id="beneficiaryStatementTwo">—</p>
                        </div>

                        <div class="origin-footer">
                            <div class="signature-image" aria-hidden="true"></div>
                            <span>For &amp; on behalf of</span>
                            <strong>For <span id="beneficiaryFooterCompany">—</span></strong>
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="page-actions-left">
                            <button type="button" class="ghost-btn js-prev-page" data-prev-page="origin">Previous</button>
                        </div>
                        <div class="page-actions-right">
                            <button type="button" class="primary-btn js-next-page" data-next-page="forwarding">Next: Forwarding →</button>
                        </div>
                    </div>
                </section>

<script>
async function openBeneficiaryPrint() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'beneficiary', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/document-print.php?doc=beneficiary';
}
async function openBeneficiaryExcel() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'beneficiary', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/document-print.php?doc=beneficiary&excel=1';
}
document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.querySelector('.page-actions-right');
    if (wrap && !document.getElementById('btnBeneficiaryPrint')) {
        const xls = document.createElement('button');
        xls.type = 'button';
        xls.id = 'btnBeneficiaryExcel';
        xls.className = 'ghost-btn';
        xls.textContent = 'Download Beneficiary Excel';
        xls.onclick = openBeneficiaryExcel;
        wrap.prepend(xls);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'btnBeneficiaryPrint';
        btn.className = 'ghost-btn';
        btn.textContent = 'Print Beneficiary Certificate';
        btn.onclick = openBeneficiaryPrint;
        wrap.prepend(btn);
    }
});
window.onOrderLoad = function(res) {
    const order = res.order  || {};
    const comm  = res.pages?.commercial || {};
    const exch  = res.pages?.exchange   || {};
    const lc    = res.pages?.lc         || {};

    const set = (id, val) => { const el = document.getElementById(id); if (el && val) el.textContent = val; };

    set('beneficiaryFooterCompany', comm.commercialBeneficiaryName || '—');

    const totalQty  = comm.commercialTotalQty    || '';
    const totalAmt  = comm.commercialTotalAmount || exch.exchangeAmount || '';
    const pi        = comm.proformaNo   || '';
    const piDate    = comm.proformaDate || '';
    const customer  = order.customer_name || comm.commercialConsigneeName || '';
    const bank      = sessionStorage.getItem('ats_consignee_bank') || '';
    const lcNo      = exch.masterLcNo   || lc.lcNumber || '';
    const lcDate    = exch.masterLcDate || lc.lcDate   || '';
    const contract  = exch.exportSalesContractNo   || '';
    const contDate  = exch.exportSalesContractDate || '';

    const irc = exch.applicantIrc || ''; const tin = exch.applicantTin || '';
    const p = [];
    if (irc) p.push('IRC No. '+irc); if (tin) p.push('TIN No. '+tin);
    if (exch.applicantVatBin)   p.push('Vat/bin No. '+exch.applicantVatBin);
    if (exch.applicantBankBin)  p.push('Bank Bin No. '+exch.applicantBankBin);
    if (exch.bondLicenseNo)     p.push('Bond License no. '+exch.bondLicenseNo);
    if (exch.beneficiaryVatBin) p.push("Beneficiary's Vat/Bin: "+exch.beneficiaryVatBin);
    if (exch.hsCodeMaster)      p.push('H.S Code No: '+exch.hsCodeMaster);
    const applicants = p.length ? 'Applicants ' + p.join(', ') + '.' : '';

    set('beneficiaryStatementOne',
        'We hereby confirm that we have supplied Accessories for 100% export oriented garments industry' +
        (totalQty ? ': ' + totalQty + ' cones / pcs' : '') +
        (totalAmt ? ' total amount of US $ ' + totalAmt : '') +
        (pi ? ' all other details as per pro-forma invoice No. ' + pi + (piDate ? ' Dated ' + piDate : '') : '') +
        (customer ? '. To The ' + customer : '') +
        (bank ? ' against their ' + bank.split('.')[0] : '') +
        (lcNo ? ' L/C No. ' + lcNo + (lcDate ? ' Dated ' + lcDate : '') : '') +
        (applicants ? ' under ' + applicants : '') +
        (contract ? ' Sales Contract No: ' + contract + (contDate ? ' Dated ' + contDate : '') : '') +
        ' for exporting readymade garments.'
    );

    set('beneficiaryStatementTwo',
        "We do hereby undertake that the said accessories shipment from : Beneficiary's factory to applicant factory warehouse. We also certified that quantity, quality, rate specification & all other terms & conditions are as per suppliers" +
        (pi ? ' pro-forma invoice No. ' + pi + (piDate ? ' Dated ' + piDate : '') : ' pro-forma invoice') +
        ' any short and defective goods to be replaced by us on free of cost.'
    );
};
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
