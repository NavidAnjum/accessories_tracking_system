<?php
$pageTitle   = 'Commercial Invoice';
$activePage  = 'commercial';
$navSection  = 'order';
$pageSubtitle = 'Commercial Invoice — finalize invoice details.';
include __DIR__ . '/../includes/header.php';
?>

                <section class="form-card" data-page="commercial">
                    <div class="section-head">
                        <div class="section-title">
                            <span class="section-tag">Section 4</span>
                            <h2>Commercial Invoice</h2>
                        </div>
                        <div class="section-summary">
                            <strong>Owner</strong>
                            <span>Commercial invoice sheet built from PO result, PI, LC, and item details.</span>
                        </div>
                    </div>
                    <div class="source-glance">
                        <div class="source-glance-item"><span>Matched Sales Order</span><strong data-bind="salesOrder">—</strong></div>
                        <div class="source-glance-item"><span>Customer PO</span><strong data-bind="customerPo">—</strong></div>
                        <div class="source-glance-item"><span>Buyer</span><strong data-bind="buyerName">—</strong></div>
                        <div class="source-glance-item"><span>Customer</span><strong data-bind="customerName">—</strong></div>
                    </div>
                    <div class="packing-sheet commercial-sheet">
                        <div class="packing-sheet-header commercial-sheet-header">
                            <div class="packing-logo">ZZAL</div>
                            <div class="commercial-sheet-title">
                                <h3>Zaber &amp; Zubair Accessories Ltd.</h3>
                                <strong>COMMERCIAL INVOICE</strong>
                            </div>
                        </div>

                        <div class="commercial-grid">
                            <section class="packing-block">
                                <span class="packing-label">Beneficiary</span>
                                <input id="commercialBeneficiaryName" name="commercialBeneficiaryName" placeholder="Beneficiary company name…">
                                <input id="commercialBeneficiaryAddress" name="commercialBeneficiaryAddress" placeholder="Head office address…">
                                <input id="commercialFactoryAddress" name="commercialFactoryAddress" placeholder="Factory address…">
                            </section>
                            <section class="packing-block commercial-meta-block">
                                <div class="commercial-meta-row">
                                    <span>Invoice No</span>
                                    <strong><input id="invoiceNo" name="invoiceNo" placeholder="e.g. ZZAL/PI/26/52017"></strong>
                                </div>
                                <div class="commercial-meta-row">
                                    <span>Date</span>
                                    <strong><input id="invoiceDate" name="invoiceDate" type="date"></strong>
                                </div>
                                <div class="commercial-meta-row">
                                    <span>L/C No</span>
                                    <strong><input id="commercialLcNo" name="commercialLcNo" placeholder="LC number from LC page"></strong>
                                </div>
                                <div class="commercial-meta-row">
                                    <span>Dated</span>
                                    <strong><input id="commercialLcDate" name="commercialLcDate" type="date"></strong>
                                </div>
                                <div class="commercial-meta-row">
                                    <span>Proforma Invoice</span>
                                    <strong><input id="proformaNo" name="proformaNo" placeholder="e.g. ZZAL/PI/26/52017"></strong>
                                </div>
                                <div class="commercial-meta-row">
                                    <span>Dated</span>
                                    <strong><input id="proformaDate" name="proformaDate" type="date"></strong>
                                </div>
                                <div class="commercial-meta-row">
                                    <span>L/C issuing Bank</span>
                                    <strong><input id="commercialIssuingBankName" name="commercialIssuingBankName" placeholder="Bank name"></strong>
                                </div>
                                <div class="commercial-meta-row full">
                                    <span>Bank Address</span>
                                    <strong><textarea id="commercialIssuingBankAddress" name="commercialIssuingBankAddress" rows="2" placeholder="Full bank address"></textarea></strong>
                                </div>
                                <div class="commercial-meta-row">
                                    <span>Place of Loading</span>
                                    <strong><input id="placeLoading" name="placeLoading" value="Supplier's Factory" readonly title="Fixed value"></strong>
                                </div>
                                <div class="commercial-meta-row">
                                    <span>Place of Delivery</span>
                                    <strong><input id="placeDelivery" name="placeDelivery" value="Opener's Factory" readonly title="Fixed value"></strong>
                                </div>
                                <div class="commercial-meta-row">
                                    <span>Carrier</span>
                                    <strong><input id="commercialCarrier" name="commercialCarrier" value="Bangladesh, By Truck" readonly title="Fixed value"></strong>
                                </div>
                            </section>

                            <section class="packing-block">
                                <span class="packing-label">Advising Bank</span>
                                <textarea id="commercialAdvisingBank" name="commercialAdvisingBank" rows="3"
                                    data-pi-bind="advisingBank"
                                    placeholder="Auto-filled from PI advising bank…"></textarea>
                            </section>
                            <section class="packing-block">
                                <span class="packing-label">Consignee</span>
                                <input id="commercialConsigneeName" name="commercialConsigneeName" placeholder="Consignee / customer name">
                                <textarea id="commercialConsigneeAddress" name="commercialConsigneeAddress" rows="3"
                                    data-pi-bind="buyerAddress"
                                    placeholder="Auto-filled from PI buyer address…"></textarea>
                            </section>

                            <section class="packing-block">
                                <span class="packing-label">Consignee's Bank</span>
                                <textarea id="commercialConsigneeBankAddress" name="commercialConsigneeBankAddress" rows="3"
                                    data-pi-bind="consigneeBank"
                                    placeholder="Auto-filled from PI consignee bank…"></textarea>
                            </section>
                        </div>

                        <div class="delivery-buyer">
                            <span>Buyer:</span>
                            <strong id="commercialBuyerName">—</strong>
                        </div>

                        <div class="packing-items-wrap">
                            <table class="packing-items-table commercial-items-table">
                                <thead>
                                    <tr>
                                        <th>SL No.</th>
                                        <th>Description of Goods</th>
                                        <th>Quantity</th>
                                        <th>Price $</th>
                                        <th>Amount $</th>
                                    </tr>
                                </thead>
                                <tbody id="commercialItemsBody"></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2">Total</td>
                                        <td id="commercialTotalQty">—</td>
                                        <td></td>
                                        <td id="commercialTotalAmount">—</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="delivery-freight">Freight prepaid</div>

                        <div class="commercial-footer-note">
                            <textarea id="commercialApplicantsText" name="commercialApplicantsText" rows="3"
                                placeholder="Export Sales Contract No., Applicants IRC No., TIN, Vat/bin, Bank Bin, Bond License, H.S Code…"></textarea>
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="page-actions-left">
                            <button type="button" class="ghost-btn js-prev-page" data-prev-page="exchange">Previous</button>
                        </div>
                        <div class="page-actions-right">
                            <button type="button" class="ghost-btn" onclick="openCommercialExcel()">Download Commercial Excel</button>
                            <button type="button" class="ghost-btn" onclick="openCommercialPrint()">Print Commercial Invoice</button>
                            <button type="button" class="primary-btn js-next-page" data-next-page="packing">Next: Packing List →</button>
                        </div>
                    </div>
                </section>

<script>
const COMMERCIAL_BANKS = {
    ncc: {
        name: 'National Credit & Commerce Bank Plc.',
        address: 'Motijheel main Branch, 6 Motijheel C/A, Dhaka-1000, Bangladesh.',
        account: '0002-0259000092',
        swift: 'NCCLBDDHNBB',
        routing: '160150137'
    },
    dbbl: {
        name: 'Dutch-Bangla Bank Plc.',
        address: 'Local Office, 1, Dilkusha C/A, Dhaka-1000, Bangladesh.',
        account: 'ERQ-101.117.1382',
        swift: 'DBBLBDDHCTS',
        routing: '090273889'
    }
};

function commercialBankBlock(key) {
    const bank = COMMERCIAL_BANKS[key] || COMMERCIAL_BANKS.ncc;
    return [bank.name, bank.address, `Account No: ${bank.account}`, `Swift Code: ${bank.swift}`, `Bank Routing No: ${bank.routing}`].join('\n');
}

function commercialNormalizeBank(text, fallbackKey = 'ncc') {
    const raw = String(text ?? '').trim();
    const lower = raw.toLowerCase();
    const looksLikeLabel = !raw || /^(reimbursment|reimbursement|negotiating|benificiary|beneficiary)\b/i.test(raw) || /bank details|auto-filled|full bank/i.test(lower);
    if (looksLikeLabel) {
        return commercialBankBlock(fallbackKey in COMMERCIAL_BANKS ? fallbackKey : 'ncc');
    }

    if (lower.includes('dutch-bangla') || lower.includes('dbbl')) {
        return commercialBankBlock('dbbl');
    }
    if (lower.includes('national credit') || lower.includes('ncc')) {
        return commercialBankBlock('ncc');
    }

    return raw;
}

async function openCommercialPrint() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'commercial', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/commercial-print.php';
}
async function openCommercialExcel() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) { alert('Load an order first.'); return; }
    try {
        await fetch(APP_BASE + '/api/save_page.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, page_name: 'commercial', ...collectPageFields() })
        });
    } catch (_) {}
    window.location.href = APP_BASE + '/pages/commercial-print.php?excel=1';
}

window.onOrderLoad = function(res) {
    const order = res.order || {};
    const sales = res.pages?.sales || {};
    const lc    = res.pages?.lc    || {};
    const exch  = res.pages?.exchange || {};

    const fill = (id, val) => { const el = document.getElementById(id); if (el && val && !el.value) el.value = val; };

    // ── Carry Beneficiary details from the LC page ──
    fill('commercialBeneficiaryName',    lc.lcBeneficiaryName);
    fill('commercialBeneficiaryAddress', lc.lcBeneficiaryAddress);
    fill('commercialFactoryAddress',     lc.lcFactoryAddress);

    // ── Populate source-glance reference fields (salesOrder, customerPo, buyer, customer) ──
    const allPis   = res.pis || [];
    const resolved = window.atsResolveDisplayPos ? window.atsResolveDisplayPos(res) : { pos: sales?.pos || [] };
    const bestPi   = allPis.find(p => p.is_master) || allPis[0];
    const allPos   = resolved.pos || [];
    const uniq     = key => [...new Set(allPos.map(p => p[key]).filter(Boolean))].join(', ');
    const uniqAny  = (...keys) => {
        for (const key of keys) {
            const val = uniq(key);
            if (val) return val;
        }
        return '';
    };
    const buyerVal = lc.lcBuyer
        || uniqAny('buyer', 'buyerName', 'sharedBuyer', 'endBuyer')
        || sales.buyer
        || order.to_buyer
        || order.buyer_end_buyer
        || bestPi?.buyer
        || order.buyer_name
        || '';
    const binds = {
        salesOrder:   uniq('salesOrder') || order.sales_order_no || '',
        customerPo:   uniq('poNum')      || order.customer_po    || '',
        buyerName:    buyerVal,
        customerName: order.customer_name || sales.customer || resolved.customer || bestPi?.customer || '',
    };
    document.querySelectorAll('[data-bind]').forEach(el => {
        const val = binds[el.dataset.bind];
        if (val) el.textContent = val;
    });

    // Consignee name from order customer
    if (order.customer_name) {
        const cn = document.getElementById('commercialConsigneeName');
        if (cn && !cn.value) cn.value = order.customer_name;
    }

    // LC details from lc page
    if (lc.lcNumber) {
        const el = document.getElementById('commercialLcNo');
        if (el && !el.value) el.value = lc.lcNumber;
    }

    // Issuing bank from exchange page
    const issuingBankVal = commercialNormalizeBank(exch.applicantBank || lc.lcIssuingBank || '', 'ncc');
    const issuingBankName = issuingBankVal.split('\n')[0] || '';
    const issuingBankAddr = issuingBankVal;
    const issuingAddrEl = document.getElementById('commercialIssuingBankAddress');
    if (issuingAddrEl && (!issuingAddrEl.value || /bank address/i.test(issuingAddrEl.value) || /issuing bank/i.test(issuingAddrEl.value))) {
        issuingAddrEl.value = issuingBankAddr;
    }
    const nameEl = document.getElementById('commercialIssuingBankName');
    if (nameEl && (!nameEl.value || /bank name/i.test(nameEl.value))) {
        nameEl.value = issuingBankName;
    }

    // Restore bank fields directly from Exchange / LC like before
    const advisingBankVal = commercialNormalizeBank(
        exch.payToBankAddress ||
        exch.payToBankName ||
        lc.reimbursementBank ||
        '',
        'ncc'
    );
    const consigneeBankVal = commercialNormalizeBank(
        exch.negotiatingBankAddress ||
        exch.beneficiaryBankAddress ||
        lc.negotiatingBeneficiaryBank ||
        '',
        'dbbl'
    );

    const advisingEl = document.getElementById('commercialAdvisingBank');
    if (advisingEl && (!advisingEl.value || /auto-filled|bank/i.test(advisingEl.value))) {
        advisingEl.value = advisingBankVal;
    }

    const consigneeBankEl = document.getElementById('commercialConsigneeBankAddress');
    if (consigneeBankEl && (!consigneeBankEl.value || /auto-filled|bank/i.test(consigneeBankEl.value))) {
        consigneeBankEl.value = consigneeBankVal;
    }

    // Buyer name (first PO across master PI or all PIs)
    const buyerDisplay = buyerVal || '';
    const bn = document.getElementById('commercialBuyerName');
    if (bn && buyerDisplay) bn.textContent = buyerDisplay;

    // Proforma invoice no + date from saved PI
    fill('proformaNo',   sales.piNum  || resolved.piNum  || bestPi?.pi_number || '');
    fill('proformaDate', sales.piDate || resolved.piDate || bestPi?.pi_date   || '');
    fill('invoiceNo',    sales.piNum  || resolved.piNum  || bestPi?.pi_number || '');

    // LC date from lc page
    fill('commercialLcDate', lc.lcDate || '');

    // ── Populate item table from PI/sales ERP items (authoritative source) ──────
    const itemsBody = document.getElementById('commercialItemsBody');
    if (itemsBody && allPos.length) {
        itemsBody.innerHTML = '';
        let totalQty = 0, totalAmt = 0, sl = 0;

        const grouped = {};
        allPos.forEach(po => {
            const buyer = po.buyer || po.endBuyer || uniq('buyer') || '—';
            if (!grouped[buyer]) grouped[buyer] = [];
            (po.items || []).forEach(item => grouped[buyer].push(item));
        });

        Object.entries(grouped).forEach(([buyer, items]) => {
            // Buyer sub-header row
            const hdr = document.createElement('tr');
            hdr.innerHTML = `<td colspan="5" style="font-weight:700;padding:6px 8px;background:#f8f9ff;">BUYER: ${buyer}</td>`;
            itemsBody.appendChild(hdr);

            items.forEach(item => {
                sl++;
                const desc  = item.desc  || item.itemName || '—';
                const qty   = parseFloat(item.qty   || 0);
                const price = parseFloat(item.price || item.unitPrc || 0);
                const amt   = parseFloat(item.total || (qty * price)) || 0;
                totalQty += qty; totalAmt += amt;
                const tr = document.createElement('tr');
                tr.innerHTML = `<td style="text-align:center;">${sl}</td>
                    <td>${desc}</td>
                    <td style="text-align:right;">${qty.toLocaleString()}</td>
                    <td style="text-align:right;">$${price.toFixed(4)}</td>
                    <td style="text-align:right;font-weight:700;">$${amt.toFixed(2)}</td>`;
                itemsBody.appendChild(tr);
            });
        });

        const tq = document.getElementById('commercialTotalQty');
        const ta = document.getElementById('commercialTotalAmount');
        if (tq) tq.textContent = totalQty.toLocaleString();
        if (ta) ta.textContent = '$' + totalAmt.toFixed(2);
    }

    // ── Auto-build applicants footer text from exchange page data ─────────────
    // Always rebuild from exchange data so it reflects the latest values entered there
    const ex = res.pages?.exchange || {};
    const footerEl = document.getElementById('commercialApplicantsText');
    if (footerEl) {
        const f = v => ex[v] || '';
        // Only rebuild if exchange has any data, otherwise keep manually saved value
        const hasExchangeData = Object.values(ex).some(v => v && String(v).trim());
        if (hasExchangeData || !footerEl.value) {
            footerEl.value = `Export Sales Contract No. ${f('exportSalesContractNo')} Dated ${f('exportSalesContractDate')}, `
                + `Applicants IRC No. ${f('applicantIrc')}, `
                + `Applicants TIN No. ${f('applicantTin')}, `
                + `Applicants Vat/bin No. ${f('applicantVatBin')}, `
                + `Applicants Bank Bin No. ${f('applicantBankBin')}, `
                + `Bond License no. ${f('bondLicenseNo')}, `
                + `Beneficiary's Vat/Bin: ${f('beneficiaryVatBin')} and `
                + `H.S Code No: ${f('hsCodeMaster')}.`;
        }
    }
};
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
