<?php
$pageTitle   = 'Delivery Challan';
$activePage  = 'delivery';
$navSection  = 'order';
$pageSubtitle = 'Delivery Challan — dispatch document.';
include __DIR__ . '/../includes/header.php';
?>
<style>
.packing-footer { display:none !important; }
</style>

                <section class="form-card" data-page="delivery">
                    <div class="section-head">
                        <div class="section-title">
                            <span class="section-tag">Section 6</span>
                            <h2>Delivery Challan</h2>
                        </div>
                        <div class="section-summary">
                            <strong>Delivery</strong>
                            <span>Delivery challan reuses order, packing, bank, LC, and commercial details.</span>
                        </div>
                    </div>
                    <div class="packing-sheet">
                        <div class="packing-sheet-header packing-sheet-header-centered">
                            <div class="packing-logo">ZZAL</div>
                            <div class="packing-sheet-title">
                                <h3>Zaber &amp; Zubair Accessories Ltd.</h3>
                                <strong>DELIVERY CHALLAN</strong>
                            </div>
                        </div>

                        <div class="delivery-meta">
                            <div><span>No:</span><strong id="deliveryInvoiceNo">—</strong></div>
                            <div><span>Date:</span><strong id="deliveryDateText">—</strong></div>
                        </div>

                        <div class="packing-grid">
                            <section class="packing-block">
                                <span class="packing-label">Beneficiary</span>
                                <strong id="deliveryBeneficiaryName">—</strong>
                                <p id="deliveryBeneficiaryAddress">—</p>
                                <p id="deliveryFactoryAddress">—</p>
                            </section>
                            <section class="packing-block">
                                <span class="packing-label">Consignee</span>
                                <strong id="deliveryConsigneeName">—</strong>
                                <p id="deliveryConsigneeAddress" data-pi-bind="buyerAddress">—</p>
                            </section>
                            <section class="packing-block">
                                <span class="packing-label">Advising Bank</span>
                                <p id="deliveryAdvisingBank" data-pi-bind="advisingBank">—</p>
                            </section>
                            <section class="packing-block">
                                <span class="packing-label">Consignee's Bank</span>
                                <p id="deliveryConsigneeBank" data-pi-bind="consigneeBank">—</p>
                            </section>
                        </div>

                        <div class="delivery-buyer">
                            <span>Buyer:</span>
                            <strong id="deliveryBuyerName">—</strong>
                        </div>

                        <div class="packing-items-wrap">
                            <table class="packing-items-table">
                                <thead>
                                    <tr>
                                        <th>SL No.</th>
                                        <th>Description of Goods</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody id="deliveryItemsBody"></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2">Total</td>
                                        <td id="deliveryTotalQty">—</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="delivery-freight">Freight prepaid</div>

                        <div class="packing-notes">
                            <div class="packing-note-row"><span>Packing</span><strong id="deliveryPackingText">—</strong></div>
                            <div class="packing-note-row"><span>L/C No.</span><strong id="deliveryLcText">—</strong></div>
                            <div class="packing-note-row full"><span>Applicants</span><strong id="deliveryApplicantsText">—</strong></div>
                            <div class="packing-note-row"><span>Export LC No</span><strong id="deliveryContractText">—</strong></div>
                            <div class="packing-note-row"><span>Proforma Invoice No</span><strong id="deliveryProformaText">—</strong></div>
                            <div class="packing-note-row"><span>Carrier</span><strong id="deliveryCarrierText">—</strong></div>
                        </div>

                        <div class="packing-footer">
                            <div class="packing-sign">
                                <div class="signature-image" aria-hidden="true"></div>
                                <strong>For <span id="deliveryFooterCompany">—</span></strong>
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
                            <button type="button" class="ghost-btn js-prev-page" data-prev-page="packing">Previous</button>
                        </div>
                        <div class="page-actions-right">
                            <button type="button" class="primary-btn js-next-page" data-next-page="truck">Next: Truck Challan →</button>
                        </div>
                    </div>
                </section>

<script>
async function openDeliveryPrint() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'delivery', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/document-print.php?doc=delivery';
}
async function openDeliveryExcel() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'delivery', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/document-print.php?doc=delivery&excel=1';
}
document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.querySelector('.page-actions-right');
    if (wrap && !document.getElementById('btnDeliveryPrint')) {
        const xls = document.createElement('button');
        xls.type = 'button';
        xls.id = 'btnDeliveryExcel';
        xls.className = 'ghost-btn';
        xls.textContent = 'Download Delivery Excel';
        xls.onclick = openDeliveryExcel;
        wrap.prepend(xls);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'btnDeliveryPrint';
        btn.className = 'ghost-btn';
        btn.textContent = 'Print Delivery Challan';
        btn.onclick = openDeliveryPrint;
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
    const setDate = (id, val) => {
        if (!val) return;
        const el = document.getElementById(id);
        if (!el) return;
        const raw = String(val).trim().slice(0, 10);
        if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
            el.textContent = `${raw.slice(8, 10)}.${raw.slice(5, 7)}.${raw.slice(0, 4)}`;
        } else {
            el.textContent = raw;
        }
    };
    const pick = (...vals) => {
        const found = vals.find(v => {
            const text = String(v || '').trim();
            return text.length > 0;
        });
        return found || '';
    };

    set('deliveryBeneficiaryName',    comm.commercialBeneficiaryName    || '—');
    set('deliveryBeneficiaryAddress', comm.commercialBeneficiaryAddress || '—');
    set('deliveryFactoryAddress',     comm.commercialFactoryAddress     || '—');
    set('deliveryFooterCompany',      comm.commercialBeneficiaryName    || '—');
    set('deliveryConsigneeName',      order.customer_name || comm.commercialConsigneeName || '—');
    set('deliveryInvoiceNo',          comm.invoiceNo   || comm.proformaNo || '—');
    set('deliveryAdvisingBank', pick(
        comm.commercialAdvisingBank,
        exch.payToBankAddress,
        exch.payToBankName,
        lc.reimbursementBank
    ));
    set('deliveryConsigneeBank', pick(
        comm.commercialConsigneeBankAddress,
        sales.consigneeBank,
        exch.negotiatingBankAddress,
        exch.beneficiaryBankAddress,
        lc.negotiatingBeneficiaryBank
    ));
    set('deliveryCarrierText',        exch.carrierNameMaster || comm.commercialCarrier || '—');
    set('deliveryPackingText',        'Standard Poly Packing Rolls');
    setDate('deliveryDateText', pick(comm.invoiceDate, comm.proformaDate, comm.certificateDate, comm.originDate, order.created_at));

    const lcNo   = exch.masterLcNo   || lc.lcNumber || '';
    const lcDate = exch.masterLcDate || lc.lcDate   || '';
    set('deliveryLcText', lcNo ? lcNo + (lcDate ? ' Dated ' + lcDate : '') : '—');

    const contract = exch.exportSalesContractNo   || '';
    const contDate = exch.exportSalesContractDate || '';
    set('deliveryContractText', contract ? contract + (contDate ? ' Dated ' + contDate : '') : '—');

    const proforma     = comm.proformaNo   || '';
    const proformaDate = comm.proformaDate || '';
    set('deliveryProformaText', proforma ? proforma + (proformaDate ? ' Dated ' + proformaDate : '') : '—');

    const irc = exch.applicantIrc || ''; const tin = exch.applicantTin || '';
    if (irc || tin) {
        const p = [];
        if (irc) p.push('IRC No. '+irc); if (tin) p.push('TIN No. '+tin);
        if (exch.applicantVatBin)  p.push('Vat/bin No. '+exch.applicantVatBin);
        if (exch.applicantBankBin) p.push('Bank Bin No. '+exch.applicantBankBin);
        if (exch.bondLicenseNo)    p.push('Bond License No. '+exch.bondLicenseNo);
        if (exch.beneficiaryVatBin) p.push("Beneficiary's Vat/Bin: "+exch.beneficiaryVatBin);
        if (exch.hsCodeMaster)     p.push('H.S Code No: '+exch.hsCodeMaster);
        set('deliveryApplicantsText', 'Applicants ' + p.join(', ') + '.');
    }

    // ── Populate item table from PI → sales ERP items ────────────────────────
    const resolved = window.atsResolveDisplayPos ? window.atsResolveDisplayPos(res) : { pos: res.pages?.sales?.pos || [] };
    const allPos  = resolved.pos || [];
    const tbody   = document.getElementById('deliveryItemsBody');
    const totalEl = document.getElementById('deliveryTotalQty');
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
                    <td style="text-align:right;">${qty.toLocaleString()}</td>`;
                tbody.appendChild(tr);
            });
        });
        if (totalEl) totalEl.textContent = totalQty.toLocaleString();
    }
};
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
