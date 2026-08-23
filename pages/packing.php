<?php
$pageTitle   = 'Packing List';
$activePage  = 'packing';
$navSection  = 'order';
$pageSubtitle = 'Packing List — item-level packing details.';
include __DIR__ . '/../includes/header.php';
?>
<style>
.packing-footer { display:none !important; }
</style>

                <section class="form-card" data-page="packing">
                    <div class="section-head">
                        <div class="section-title">
                            <span class="section-tag">Section 5</span>
                            <h2>Packing List</h2>
                        </div>
                        <div class="section-summary">
                            <strong>Packing</strong>
                            <span>Invoice goes to packing list, then checked details continue to LC and factory release.</span>
                        </div>
                    </div>
                    <div class="packing-sheet">
                        <div class="packing-sheet-header packing-sheet-header-centered">
                            <div class="packing-logo">ZZAL</div>
                            <div class="packing-sheet-title">
                                <h3>Zaber &amp; Zubair Accessories Ltd.</h3>
                                <strong>PACKING List</strong>
                            </div>
                        </div>

                        <div class="packing-grid">
                            <section class="packing-block">
                                <span class="packing-label">Beneficiary</span>
                                <strong id="packingBeneficiaryName">—</strong>
                                <p id="packingBeneficiaryAddress">—</p>
                                <p id="packingFactoryAddress">—</p>
                            </section>
                            <section class="packing-block">
                                <span class="packing-label">Consignee</span>
                                <strong id="packingConsigneeName">—</strong>
                                <p id="packingConsigneeAddress" data-pi-bind="buyerAddress">—</p>
                            </section>
                            <section class="packing-block">
                                <span class="packing-label">Advising Bank</span>
                                <p id="packingAdvisingBank" data-pi-bind="advisingBank">—</p>
                            </section>
                            <section class="packing-block">
                                <span class="packing-label">Consignee's Bank</span>
                                <p id="packingConsigneeBank" data-pi-bind="consigneeBank">—</p>
                            </section>
                        </div>

                        <div class="packing-items-wrap">
                            <table class="packing-items-table">
                                <thead>
                                    <tr>
                                        <th>SL No.</th>
                                        <th>Description of Goods</th>
                                        <th>Ply</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody id="packingItemsBody"></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3">Total</td>
                                        <td id="packingTotalQty">—</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="packing-notes">
                            <div class="packing-note-row"><span>Packing</span><strong id="packingDetailsText">Standard Poly Packing Rolls</strong></div>
                            <div class="packing-note-row"><span>L/C No.</span><strong id="packingLcText">—</strong></div>
                            <div class="packing-note-row full"><span>Applicants</span><strong id="packingApplicantsText">—</strong></div>
                            <div class="packing-note-row"><span>Sales Contract No</span><strong id="packingContractText">—</strong></div>
                            <div class="packing-note-row"><span>Proforma Invoice</span><strong id="packingProformaText">—</strong></div>
                            <div class="packing-note-row"><span>Carrier</span><strong id="packingCarrierText">—</strong></div>
                        </div>

                        <div class="packing-footer">
                            <div class="packing-sign">
                                <div class="signature-image" aria-hidden="true"></div>
                                <strong>For <span id="packingFooterCompany">—</span></strong>
                                <span>Authorized signature</span>
                            </div>
                            <div class="packing-sign">
                                <div class="signature-image" aria-hidden="true"></div>
                                <strong>Goods received in good condition</strong>
                                <span>Signature of Consignee with Seal</span>
                            </div>
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="page-actions-left">
                            <button type="button" class="ghost-btn js-prev-page" data-prev-page="commercial">Previous</button>
                        </div>
                        <div class="page-actions-right">
                            <button type="button" class="primary-btn js-next-page" data-next-page="delivery">Next: Delivery Challan →</button>
                        </div>
                    </div>
                </section>

<script>
async function openPackingPrint() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'packing', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/document-print.php?doc=packing';
}
async function openPackingExcel() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'packing', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/document-print.php?doc=packing&excel=1';
}
document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.querySelector('.page-actions-right');
    if (wrap && !document.getElementById('btnPackingPrint')) {
        const xls = document.createElement('button');
        xls.type = 'button';
        xls.id = 'btnPackingExcel';
        xls.className = 'ghost-btn';
        xls.textContent = 'Download Packing Excel';
        xls.onclick = openPackingExcel;
        wrap.prepend(xls);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'btnPackingPrint';
        btn.className = 'ghost-btn';
        btn.textContent = 'Print Packing List';
        btn.onclick = openPackingPrint;
        wrap.prepend(btn);
    }
});
window.onOrderLoad = function(res) {
    const order = res.order  || {};
    const comm  = res.pages?.commercial || {};
    const exch  = res.pages?.exchange   || {};
    const lc    = res.pages?.lc         || {};
    const sales = res.pages?.sales      || {};

    const set = (id, val) => { const el = document.getElementById(id); if (el && val) el.textContent = val; };
    const pick = (...vals) => {
        const found = vals.find(v => {
            const text = String(v || '').trim();
            return text.length > 0;
        });
        return found || '';
    };

    // Beneficiary from commercial page data
    set('packingBeneficiaryName',    comm.commercialBeneficiaryName    || '—');
    set('packingBeneficiaryAddress', comm.commercialBeneficiaryAddress || '—');
    set('packingFactoryAddress',     comm.commercialFactoryAddress     || '—');
    set('packingFooterCompany',      comm.commercialBeneficiaryName    || '—');

    // Consignee from order
    set('packingConsigneeName', order.customer_name || comm.commercialConsigneeName || '—');
    set('packingAdvisingBank', pick(
        comm.commercialAdvisingBank,
        exch.payToBankAddress,
        exch.payToBankName,
        lc.reimbursementBank,
        sales.advisingBank
    ));
    set('packingConsigneeBank', pick(
        comm.commercialConsigneeBankAddress,
        sales.consigneeBank,
        exch.negotiatingBankAddress,
        exch.beneficiaryBankAddress,
        lc.negotiatingBeneficiaryBank
    ));

    // Notes from exchange page data
    const lcNo   = exch.masterLcNo   || lc.lcNumber || '';
    const lcDate = exch.masterLcDate || lc.lcDate   || '';
    set('packingLcText', lcNo ? lcNo + (lcDate ? ' Dated ' + lcDate : '') : '—');
    set('packingDetailsText',  exch.packingDetailsMaster || '—');
    set('packingCarrierText',  exch.carrierNameMaster    || comm.commercialCarrier || '—');

    const contract = exch.exportSalesContractNo   || '';
    const contDate = exch.exportSalesContractDate || '';
    set('packingContractText', contract ? contract + (contDate ? ' Dated ' + contDate : '') : '—');

    const proforma     = comm.proformaNo   || '';
    const proformaDate = comm.proformaDate || '';
    set('packingProformaText', proforma ? proforma + (proformaDate ? ' Dated ' + proformaDate : '') : '—');

    // Applicants summary from exchange fields
    const irc  = exch.applicantIrc    || '';
    const tin  = exch.applicantTin    || '';
    const vat  = exch.applicantVatBin || '';
    const bbin = exch.applicantBankBin|| '';
    const bond = exch.bondLicenseNo   || '';
    const bvat = exch.beneficiaryVatBin || '';
    const hs   = exch.hsCodeMaster    || '';
    if (irc || tin) {
        const parts = [];
        if (irc)  parts.push('IRC No. ' + irc);
        if (tin)  parts.push('TIN No. ' + tin);
        if (vat)  parts.push('Vat/bin No. ' + vat);
        if (bbin) parts.push('Bank Bin No. ' + bbin);
        if (bond) parts.push('Bond License No. ' + bond);
        if (bvat) parts.push("Beneficiary's Vat/Bin: " + bvat);
        if (hs)   parts.push('H.S Code No: ' + hs);
        set('packingApplicantsText', 'Applicants ' + parts.join(', ') + '.');
    }

    // ── Populate item table from PI → sales ERP items ────────────────────────
    const resolved = window.atsResolveDisplayPos ? window.atsResolveDisplayPos(res) : { pos: res.pages?.sales?.pos || [] };
    const allPos  = resolved.pos || [];
    const tbody   = document.getElementById('packingItemsBody');
    const totalEl = document.getElementById('packingTotalQty');
    if (tbody && allPos.length) {
        tbody.innerHTML = '';
        let sl = 0, totalQty = 0;
        allPos.forEach(po => {
            (po.items || []).forEach(item => {
                sl++;
                const qty = parseFloat(item.qty || 0);
                totalQty += qty;
                const tr = document.createElement('tr');
                tr.innerHTML = `<td style="text-align:center;">${sl}</td>
                    <td>${item.desc || item.itemName || '—'}</td>
                    <td style="text-align:center;">${item.ply || '—'}</td>
                    <td style="text-align:right;">${qty.toLocaleString()}</td>`;
                tbody.appendChild(tr);
            });
        });
        if (totalEl) totalEl.textContent = totalQty.toLocaleString();
    }
};
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
