<?php
$pageTitle   = 'Certificate of Origin';
$activePage  = 'origin';
$navSection  = 'order';
$pageSubtitle = 'Certificate of Origin — export document.';
include __DIR__ . '/../includes/header.php';
?>
<style>
.origin-footer { display:none !important; }
</style>

                <section class="form-card" data-page="origin">
                    <div class="section-head">
                        <div class="section-title">
                            <span class="section-tag">Section 8</span>
                            <h2>Certificate Of Origin</h2>
                        </div>
                        <div class="section-summary">
                            <strong>Origin</strong>
                            <span>This certificate reuses the LC, proforma invoice, applicant, contract, and HS code details.</span>
                        </div>
                    </div>
                    <div class="packing-sheet">
                        <div class="packing-sheet-header packing-sheet-header-centered">
                            <div class="packing-logo">ZZAL</div>
                            <div class="packing-sheet-title">
                                <h3>Zaber &amp; Zubair Accessories Ltd.</h3>
                                <strong>CERTIFICATE OF ORIGIN</strong>
                            </div>
                        </div>

                        <div class="origin-body">
                            <p id="originStatementText">—</p>
                            <p id="originApplicantsText">—</p>
                            <p id="originContractText">—</p>
                            <p>For and of behalf of,</p>
                        </div>

                        <div class="origin-footer">
                            <div class="signature-image" aria-hidden="true"></div>
                            <strong>For <span id="originFooterCompany">—</span></strong>
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="page-actions-left">
                            <button type="button" class="ghost-btn js-prev-page" data-prev-page="truck">Previous</button>
                        </div>
                        <div class="page-actions-right">
                            <button type="button" class="primary-btn js-next-page" data-next-page="beneficiary">Next: Beneficiary Certificate →</button>
                        </div>
                    </div>
                </section>

<script>
async function openOriginPrint() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'origin', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/document-print.php?doc=origin';
}
async function openOriginExcel() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'origin', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/document-print.php?doc=origin&excel=1';
}
document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.querySelector('.page-actions-right');
    if (wrap && !document.getElementById('btnOriginPrint')) {
        const xls = document.createElement('button');
        xls.type = 'button';
        xls.id = 'btnOriginExcel';
        xls.className = 'ghost-btn';
        xls.textContent = 'Download Origin Excel';
        xls.onclick = openOriginExcel;
        wrap.prepend(xls);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'btnOriginPrint';
        btn.className = 'ghost-btn';
        btn.textContent = 'Print Certificate of Origin';
        btn.onclick = openOriginPrint;
        wrap.prepend(btn);
    }
});
window.onOrderLoad = function(res) {
    const comm = res.pages?.commercial || {};
    const exch = res.pages?.exchange   || {};
    const lc   = res.pages?.lc         || {};

    const set = (id, val) => { const el = document.getElementById(id); if (el && val) el.textContent = val; };

    set('originFooterCompany', comm.commercialBeneficiaryName || '—');

    const lcNo   = exch.masterLcNo   || lc.lcNumber || '';
    const lcDate = exch.masterLcDate || lc.lcDate   || '';
    const pi     = comm.proformaNo   || '';
    const piDate = comm.proformaDate || '';

    if (lcNo || pi) {
        set('originStatementText',
            'This is to certify that the goods, which are delivered under' +
            (lcNo ? ' L/C No. ' + lcNo + (lcDate ? ' Dated ' + lcDate : '') : '') +
            (pi   ? ' as per Proforma Invoice No. ' + pi + (piDate ? ' Dated ' + piDate : '') : '') +
            ' is of Bangladesh Origin.'
        );
    }

    const irc = exch.applicantIrc || ''; const tin = exch.applicantTin || '';
    if (irc || tin) {
        const p = [];
        if (irc) p.push('IRC No. '+irc); if (tin) p.push('TIN No. '+tin);
        if (exch.applicantVatBin)   p.push('Vat/bin No. '+exch.applicantVatBin);
        if (exch.applicantBankBin)  p.push('Bank Bin No. '+exch.applicantBankBin);
        if (exch.bondLicenseNo)     p.push('Bond License no. '+exch.bondLicenseNo);
        if (exch.beneficiaryVatBin) p.push("Beneficiary's Vat/Bin: "+exch.beneficiaryVatBin);
        if (exch.hsCodeMaster)      p.push('H.S Code No: '+exch.hsCodeMaster);
        set('originApplicantsText', 'Applicants ' + p.join(', ') + '.');
    }

    const contract = exch.exportSalesContractNo   || '';
    const contDate = exch.exportSalesContractDate || '';
    set('originContractText', contract ? 'Sales Contract No : ' + contract + (contDate ? ' Dated ' + contDate : '') + '.' : '—');
};
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
