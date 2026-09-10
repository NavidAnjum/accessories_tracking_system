<?php
$pageTitle   = 'Truck Challan';
$activePage  = 'truck';
$navSection  = 'order';
$pageSubtitle = 'Truck Challan — transport document.';
include __DIR__ . '/../includes/header.php';
?>
<style>
.packing-footer { display:none !important; }
</style>

                <section class="form-card" data-page="truck">
                    <div class="section-head">
                        <div class="section-title">
                            <span class="section-tag">Section 7</span>
                            <h2>Truck Challan</h2>
                        </div>
                        <div class="section-summary">
                            <strong>Truck</strong>
                            <span>Truck challan reuses the same delivery, packing, bank, LC, and commercial details.</span>
                        </div>
                    </div>
                    <div class="packing-sheet">
                        <div class="packing-sheet-header packing-sheet-header-centered">
                            <div class="packing-logo">ZZAL</div>
                            <div class="packing-sheet-title">
                                <h3>Zaber &amp; Zubair Accessories Ltd.</h3>
                                <strong>TRUCK CHALLAN</strong>
                            </div>
                        </div>

                        <div class="packing-grid">
                            <section class="packing-block">
                                <span class="packing-label">Beneficiary</span>
                                <strong id="truckBeneficiaryName">—</strong>
                                <p id="truckBeneficiaryAddress">—</p>
                                <p id="truckFactoryAddress">—</p>
                            </section>
                            <section class="packing-block">
                                <span class="packing-label">Consignee</span>
                                <strong id="truckConsigneeName">—</strong>
                                <p id="truckConsigneeAddress" data-pi-bind="buyerAddress">—</p>
                            </section>
                            <section class="packing-block">
                                <span class="packing-label">Advising Bank</span>
                                <p id="truckAdvisingBank" data-pi-bind="advisingBank">—</p>
                            </section>
                            <section class="packing-block">
                                <span class="packing-label">Consignee's Bank</span>
                                <p id="truckConsigneeBank" data-pi-bind="consigneeBank">—</p>
                            </section>
                        </div>

                        <div class="delivery-buyer">
                            <span>Buyer:</span>
                            <strong id="truckBuyerName">—</strong>
                        </div>

                        <div class="packing-items-wrap">
                            <table class="packing-items-table">
                                <thead>
                                    <tr>
                                        <th>SL No.</th>
                                        <th>Description of Goods</th>
                                        <th>Ply</th>
                                        <th>Quantity/Cone</th>
                                    </tr>
                                </thead>
                                <tbody id="truckItemsBody"></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3">Total</td>
                                        <td id="truckTotalQty">—</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- EPZ weight/bundle summary (from LC page, EPZ zone only) -->
                        <div id="epzWeightBlock" style="display:none;margin-top:10px;border:1px solid #000;">
                            <table style="width:100%;border-collapse:collapse;font-size:9pt;">
                                <tr>
                                    <td style="border:1px solid #000;padding:5px 10px;font-weight:700;">Net Weight</td>
                                    <td style="border:1px solid #000;padding:5px 10px;text-align:right;" id="epzNetWeight">— Kgs</td>
                                    <td style="border:1px solid #000;padding:5px 10px;font-weight:700;">Gross Weight</td>
                                    <td style="border:1px solid #000;padding:5px 10px;text-align:right;" id="epzGrossWeight">— Kgs</td>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #000;padding:5px 10px;font-weight:700;">Total Bundle</td>
                                    <td style="border:1px solid #000;padding:5px 10px;text-align:right;" id="epzTotalBundle">— Pcs</td>
                                    <td style="border:1px solid #000;padding:5px 10px;font-weight:700;">Total Qty</td>
                                    <td style="border:1px solid #000;padding:5px 10px;text-align:right;" id="epzTotalQty">— Pcs</td>
                                </tr>
                            </table>
                        </div>

                        <div class="packing-notes">
                            <div class="packing-note-row"><span>Packing</span><strong id="truckPackingText">—</strong></div>
                            <div class="packing-note-row"><span>L/C No.</span><strong id="truckLcText">—</strong></div>
                            <div class="packing-note-row full"><span>Applicants</span><strong id="truckApplicantsText">—</strong></div>
                            <div class="packing-note-row"><span>Export LC No</span><strong id="truckContractText">—</strong></div>
                            <div class="packing-note-row"><span>Proforma Invoice No</span><strong id="truckProformaText">—</strong></div>
                            <div class="packing-note-row"><span>Carrier</span><strong id="truckCarrierText">—</strong></div>
                        </div>

                        <div class="packing-footer">
                            <div class="packing-sign">
                                <img src="<?= BASE_PATH ?>/AKM.png" alt="For Zaber & Zubair Accessories Ltd. — Authorised Signature" style="height:100px;max-width:300px;object-fit:contain;display:block;">
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
                            <button type="button" class="ghost-btn js-prev-page" data-prev-page="delivery">Previous</button>
                        </div>
                        <div class="page-actions-right">
                            <button type="button" class="primary-btn js-next-page" data-next-page="origin">Next: Certificate of Origin →</button>
                        </div>
                    </div>
                </section>

<script>
async function openTruckPrint() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'truck', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/document-print.php?doc=truck';
}
async function openTruckExcel() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'truck', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/document-print.php?doc=truck&excel=1';
}
document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.querySelector('.page-actions-right');
    if (wrap && !document.getElementById('btnTruckPrint')) {
        const xls = document.createElement('button');
        xls.type = 'button';
        xls.id = 'btnTruckExcel';
        xls.className = 'ghost-btn';
        xls.textContent = 'Download Truck Excel';
        xls.onclick = openTruckExcel;
        wrap.prepend(xls);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'btnTruckPrint';
        btn.className = 'ghost-btn';
        btn.textContent = 'Print Truck Challan';
        btn.onclick = openTruckPrint;
        wrap.prepend(btn);
    }
});
window.onOrderLoad = function(res) {
    const order = res.order  || {};
    const comm  = res.pages?.commercial || {};
    const exch  = res.pages?.exchange   || {};
    const lc    = res.pages?.lc         || {};

    const set = (id, val) => { const el = document.getElementById(id); if (el && val) el.textContent = val; };
    const pick = (...values) => values.find(value => String(value || '').trim()) || '';

    set('truckBeneficiaryName',    comm.commercialBeneficiaryName    || '—');
    set('truckBeneficiaryAddress', comm.commercialBeneficiaryAddress || '—');
    set('truckFactoryAddress',     comm.commercialFactoryAddress     || '—');
    set('truckFooterCompany',      comm.commercialBeneficiaryName    || '—');
    set('truckConsigneeName',      lc.lcApplicantName || order.customer_name || comm.commercialConsigneeName || '—');
    set('truckConsigneeAddress',   lc.lcApplicantAddress || comm.commercialConsigneeAddress || '—');
    set('truckAdvisingBank', pick(lc.reimbursementBank, comm.commercialAdvisingBank, exch.payToBankAddress, exch.payToBankName));
    set('truckConsigneeBank', pick(lc.negotiatingBeneficiaryBank, comm.commercialConsigneeBankAddress, exch.beneficiaryBankAddress));
    set('truckCarrierText',        exch.carrierNameMaster || comm.commercialCarrier || '—');
    set('truckPackingText',        exch.packingDetailsMaster || '—');

    const lcNo   = exch.masterLcNo   || lc.lcNumber || '';
    const lcDate = exch.masterLcDate || lc.lcDate   || '';
    set('truckLcText', lcNo ? lcNo + (lcDate ? ' Dated ' + lcDate : '') : '—');

    const contract = lc.lcExportSalesContractNo || exch.exportSalesContractNo || lc.lcNumber || '';
    const contDate = lc.lcExportSalesContractDate || exch.exportSalesContractDate || lc.lcDate || '';
    set('truckContractText', contract ? contract + (contDate ? ' Dated ' + contDate : '') : '—');

    const proforma     = comm.proformaNo   || '';
    const proformaDate = comm.proformaDate || '';
    set('truckProformaText', proforma ? proforma + (proformaDate ? ' Dated ' + proformaDate : '') : '—');

    const irc = exch.applicantIrc || ''; const tin = exch.applicantTin || '';
    const applicantName = lc.lcApplicantName || '';
    const applicantAddress = lc.lcApplicantAddress || '';
    if (applicantName || applicantAddress || irc || tin) {
        const p = [];
        if (applicantName) p.push(applicantName);
        if (applicantAddress) p.push(applicantAddress);
        if (irc) p.push('IRC No. '+irc); if (tin) p.push('TIN No. '+tin);
        if (exch.applicantVatBin)   p.push('Vat/bin No. '+exch.applicantVatBin);
        if (exch.applicantBankBin)  p.push('Bank Bin No. '+exch.applicantBankBin);
        if (exch.bondLicenseNo)     p.push('Bond License No. '+exch.bondLicenseNo);
        if (exch.beneficiaryVatBin) p.push("Beneficiary's Vat/Bin: "+exch.beneficiaryVatBin);
        if (exch.hsCodeMaster)      p.push('H.S Code No: '+exch.hsCodeMaster);
        set('truckApplicantsText', 'Applicants ' + p.join(', ') + '.');
    }

    // Buyer name
    const resolved = window.atsResolveDisplayPos ? window.atsResolveDisplayPos(res) : { pos: res.pages?.sales?.pos || [] };
    const allPos  = resolved.pos || [];
    const buyers  = [...new Set(allPos.map(p => p.buyer).filter(Boolean))].join(', ');
    set('truckBuyerName', buyers || order.buyer_name || '—');

    // ── Populate item table from PI → sales ERP items ────────────────────────
    const tbody   = document.getElementById('truckItemsBody');
    const totalEl = document.getElementById('truckTotalQty');
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
        atsFillEpzWeightBlock(lc, totalQty);
    }
};

// Populate the EPZ Net/Gross/Bundle/Qty block (only when the LC zone is EPZ).
function atsFillEpzWeightBlock(lc, totalQty) {
    const block = document.getElementById('epzWeightBlock');
    if (!block) return;
    if (!(lc && lc.lcZoneType === 'epz')) { block.style.display = 'none'; return; }
    block.style.display = 'block';
    const set = (id, val, unit) => { const el = document.getElementById(id); if (el) el.textContent = (val !== '' && val != null ? val : '—') + ' ' + unit; };
    set('epzNetWeight',   lc.lcNetWeight,   'Kgs');
    set('epzGrossWeight', lc.lcGrossWeight, 'Kgs');
    set('epzTotalBundle', lc.lcTotalBundle, 'Pcs');
    set('epzTotalQty',    (totalQty != null ? Number(totalQty).toLocaleString() : ''), 'Pcs');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
