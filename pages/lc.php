<?php
$pageTitle   = 'LC';
$activePage  = 'lc';
$navSection  = 'order';
$pageSubtitle = 'Letter of Credit check and record LC details.';
include __DIR__ . '/../includes/header.php';
?>

<style>
.lc-up-table { width:100%; border-collapse:collapse; font-size:13px; margin-top:4px; }
.lc-up-table th { background:#f5f7ff; color:#4f46e5; font-size:11px; text-transform:uppercase; letter-spacing:.04em; padding:8px 10px; text-align:left; border:1px solid #e0e3ff; }
.lc-up-table td { padding:5px 6px; border:1px solid #eceffe; }
.lc-up-table tfoot td { background:#fafbff; }
.lc-up-inp { width:100%; border:none; border-bottom:1.5px solid #d1d5db; background:transparent; font-size:13px; padding:5px 4px; outline:none; box-sizing:border-box; }
.lc-up-inp:focus { border-bottom-color:#6366f1; background:#f5f7ff; }
.lc-up-rm { background:#fee2e2; border:none; border-radius:6px; color:#dc2626; width:28px; height:28px; font-size:16px; cursor:pointer; }
.lc-up-rm:hover { background:#fecaca; }
</style>

                <section class="form-card" data-page="lc">
                    <div class="section-head">
                        <div class="section-title">
                            <span class="section-tag">Section 10</span>
                            <h2>LC</h2>
                        </div>
                        <div class="section-summary">
                            <strong>Control</strong>
                            <span>Check and prepare the LC step before Bill of Exchange.</span>
                        </div>
                    </div>
                    <div id="lcApprovalNotice" style="display:none;background:#fef3c7;border:1px solid #fcd34d;color:#92400e;padding:12px 16px;border-radius:10px;margin-bottom:14px;font-size:13px;font-weight:600;">
                        ⚠ This order is still awaiting <strong>Marketing approval</strong>. Prepare the LC only after Marketing approves the PI.
                    </div>
                    <div class="source-glance">
                        <div class="source-glance-item"><span>Matched Sales Order</span><strong data-bind="salesOrder">-</strong></div>
                        <div class="source-glance-item"><span>Customer PO</span><strong data-bind="customerPo">-</strong></div>
                        <div class="source-glance-item"><span>Buyer</span><strong data-bind="buyerName">-</strong></div>
                        <div class="source-glance-item"><span>Customer</span><strong data-bind="customerName">-</strong></div>
                    </div>
                    <div class="form-grid">
                        <div class="field span-6">
                            <label for="lcCheckStatus">LC Check Status</label>
                            <select id="lcCheckStatus" name="lcCheckStatus">
                                <option>Pending</option>
                                <option>Checked</option>
                                <option>Need Correction</option>
                                <option>Approved</option>
                            </select>
                        </div>
                        <div class="field span-6">
                            <label for="paymentTerms">Payment Terms</label>
                            <input id="paymentTerms" name="paymentTerms" placeholder="e.g. 120 Days LC">
                        </div>
                        <div class="field span-6">
                            <label for="lcBuyer">LC Buyer</label>
                            <input id="lcBuyer" name="lcBuyer" placeholder="Buyer on the LC…">
                        </div>
                        <div class="field span-6">
                            <label for="shippingTerms">Shipping Terms</label>
                            <input id="shippingTerms" name="shippingTerms" placeholder="e.g. FOB Chittagong">
                        </div>
                        <div class="field span-6">
                            <label for="lcNumber">LC Number</label>
                            <input id="lcNumber" name="lcNumber" placeholder="LC number...">
                        </div>
                        <div class="field span-4">
                            <label for="lcDate">LC Date</label>
                            <input id="lcDate" name="lcDate" type="date">
                        </div>
                        <div class="field span-4">
                            <label for="lcReceivedDate">L/C Received Date</label>
                            <input id="lcReceivedDate" name="lcReceivedDate" type="date">
                        </div>
                        <div class="field span-4">
                            <label for="lcShipDate">L/C Ship Date</label>
                            <input id="lcShipDate" name="lcShipDate" type="date">
                        </div>
                        <div class="field span-6">
                            <label for="lcExpiryDate">L/C Expiry Date</label>
                            <input id="lcExpiryDate" name="lcExpiryDate" type="date">
                        </div>
                        <div class="field span-6">
                            <label for="lcDescription">Description</label>
                            <input id="lcDescription" name="lcDescription" placeholder="e.g. Export LC for approved order">
                        </div>
                        <div class="field span-6">
                            <label for="lcAmount">LC Amount (USD)</label>
                            <input id="lcAmount" name="lcAmount" type="number" step="0.01" placeholder="0.00">
                        </div>
                        <div class="field span-12">
                            <label>UP / Raw Material Details</label>
                            <table class="lc-up-table" id="lcUpTable">
                                <thead>
                                    <tr>
                                        <th style="width:22%;">Up No.</th>
                                        <th style="width:22%;">Up Date</th>
                                        <th style="width:24%;">Raw Material Qty</th>
                                        <th style="width:24%;">Raw Material Value</th>
                                        <th style="width:8%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="lcUpBody">
                                    <tr>
                                        <td><input class="lc-up-inp up-no" placeholder="UP number"></td>
                                        <td><input class="lc-up-inp up-date" type="date"></td>
                                        <td><input class="lc-up-inp up-qty" type="number" step="0.01" placeholder="0"></td>
                                        <td><input class="lc-up-inp up-val" type="number" step="0.01" placeholder="0.00"></td>
                                        <td><button type="button" class="lc-up-rm" onclick="lcRemoveUpRow(this)" title="Remove">&times;</button></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" style="text-align:right;font-weight:700;">Total</td>
                                        <td id="lcUpTotalQty" style="font-weight:700;">0</td>
                                        <td id="lcUpTotalVal" style="font-weight:700;">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <button type="button" class="ghost-btn" style="font-size:12px;padding:5px 14px;margin-top:8px;" onclick="lcAddUpRow()">+ Add Row</button>
                            <input type="hidden" id="lcUpTableData" name="lcUpTableData">
                        </div>
                        <div class="field span-4">
                            <label for="docSendToBuyerDate">Doc send to the Buyer (date)</label>
                            <input id="docSendToBuyerDate" name="docSendToBuyerDate" type="date">
                        </div>
                        <div class="field span-4">
                            <label for="acceptanceDate">Acceptance Date</label>
                            <input id="acceptanceDate" name="acceptanceDate" type="date">
                        </div>
                        <div class="field span-4">
                            <label for="docSentToNegotiatingBank">Doc sent to the Negotiating Bank</label>
                            <input id="docSentToNegotiatingBank" name="docSentToNegotiatingBank" type="date">
                        </div>
                        <div class="field span-4">
                            <label for="maturityDate">Maturity Date</label>
                            <input id="maturityDate" name="maturityDate" type="date">
                        </div>
                        <div class="field span-4">
                            <label for="receivedAmount">Received Amount</label>
                            <input id="receivedAmount" name="receivedAmount" type="number" step="0.01" placeholder="0.00">
                        </div>
                        <div class="field span-4">
                            <label for="receivedDate">Received Date</label>
                            <input id="receivedDate" name="receivedDate" type="date">
                        </div>
                        <div class="field span-4">
                            <label for="lcIssuingBank">L/C Issuing Bank</label>
                            <textarea id="lcIssuingBank" name="lcIssuingBank" placeholder="Issuing bank details"></textarea>
                        </div>
                        <div class="field span-4">
                            <label for="reimbursementBank">Reimbursment Bank</label>
                            <textarea id="reimbursementBank" name="reimbursementBank" placeholder="Reimbursment bank details"></textarea>
                        </div>
                        <div class="field span-4">
                            <label for="negotiatingBeneficiaryBank">Negotiating / Benificiary Bank</label>
                            <textarea id="negotiatingBeneficiaryBank" name="negotiatingBeneficiaryBank" placeholder="Negotiating or benificiary bank details"></textarea>
                        </div>
                        <div class="field span-4">
                            <label for="lcBeneficiaryName">Beneficiary Company Name</label>
                            <input id="lcBeneficiaryName" name="lcBeneficiaryName" placeholder="Beneficiary company name…">
                        </div>
                        <div class="field span-4">
                            <label for="lcBeneficiaryAddress">Head Office Address</label>
                            <input id="lcBeneficiaryAddress" name="lcBeneficiaryAddress" placeholder="Head office address…">
                        </div>
                        <div class="field span-4">
                            <label for="lcFactoryAddress">Factory Address</label>
                            <input id="lcFactoryAddress" name="lcFactoryAddress" placeholder="Factory address…">
                        </div>
                        <div class="field span-12">
                            <label for="lcNotes">LC Notes</label>
                            <textarea id="lcNotes" name="lcNotes" placeholder="Notes on LC check..."></textarea>
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="page-actions-left">
                            <button type="button" class="ghost-btn js-prev-page" data-prev-page="sales">Previous</button>
                        </div>
                        <div class="page-actions-right">
                            <button type="button" class="primary-btn js-next-page" data-next-page="exchange">Next: Bill of Exchange</button>
                        </div>
                    </div>
                </section>

<script>
// ── UP / Raw Material table ──────────────────────────────────────────────
function lcAddUpRow(data) {
    data = data || {};
    const tbody = document.getElementById('lcUpBody');
    const tr = document.createElement('tr');
    tr.innerHTML =
        '<td><input class="lc-up-inp up-no" placeholder="UP number"></td>' +
        '<td><input class="lc-up-inp up-date" type="date"></td>' +
        '<td><input class="lc-up-inp up-qty" type="number" step="0.01" placeholder="0"></td>' +
        '<td><input class="lc-up-inp up-val" type="number" step="0.01" placeholder="0.00"></td>' +
        '<td><button type="button" class="lc-up-rm" onclick="lcRemoveUpRow(this)" title="Remove">&times;</button></td>';
    tbody.appendChild(tr);
    tr.querySelector('.up-no').value   = data.upNo   || '';
    tr.querySelector('.up-date').value = data.upDate || '';
    tr.querySelector('.up-qty').value  = data.qty    || '';
    tr.querySelector('.up-val').value  = data.val    || '';
    tr.querySelectorAll('.lc-up-inp').forEach(inp => inp.addEventListener('input', lcSyncUpTable));
    lcSyncUpTable();
}

function lcRemoveUpRow(btn) {
    const tbody = document.getElementById('lcUpBody');
    if (tbody.rows.length <= 1) {
        // clear the last row instead of removing it
        btn.closest('tr').querySelectorAll('.lc-up-inp').forEach(i => i.value = '');
    } else {
        btn.closest('tr').remove();
    }
    lcSyncUpTable();
}

function lcSyncUpTable() {
    const rows = [];
    let totQty = 0, totVal = 0;
    document.querySelectorAll('#lcUpBody tr').forEach(tr => {
        const upNo   = tr.querySelector('.up-no')?.value.trim()   || '';
        const upDate = tr.querySelector('.up-date')?.value        || '';
        const qty    = tr.querySelector('.up-qty')?.value.trim()  || '';
        const val    = tr.querySelector('.up-val')?.value.trim()  || '';
        totQty += parseFloat(qty) || 0;
        totVal += parseFloat(val) || 0;
        if (upNo || upDate || qty || val) rows.push({ upNo, upDate, qty, val });
    });
    document.getElementById('lcUpTotalQty').textContent = totQty.toLocaleString();
    document.getElementById('lcUpTotalVal').textContent = totVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
    const hidden = document.getElementById('lcUpTableData');
    if (hidden) hidden.value = JSON.stringify(rows);
}

function lcRestoreUpTable(json) {
    let rows = [];
    try { rows = JSON.parse(json || '[]'); } catch (_) { rows = []; }
    const tbody = document.getElementById('lcUpBody');
    tbody.innerHTML = '';
    if (!rows.length) { lcAddUpRow(); return; }
    rows.forEach(r => lcAddUpRow(r));
}

document.addEventListener('DOMContentLoaded', function () {
    // wire the initial static row
    document.querySelectorAll('#lcUpBody .lc-up-inp').forEach(inp => inp.addEventListener('input', lcSyncUpTable));
});

// Populate LC glance fields from saved sales page data
window.onOrderLoad = (function(_prev) {
    return function(res) {
        if (typeof _prev === 'function') _prev(res);

        // Guard: warn if the order hasn't been approved by Marketing yet (step is before LC).
        const WF = ['marketing-intake','costing-review','sales','marketing','lc','exchange','commercial','packing','delivery','truck','origin','beneficiary','forwarding','bank-forwarding','po-status'];
        const step = res.order?.current_step || '';
        const notice = document.getElementById('lcApprovalNotice');
        if (notice) {
            const idx = WF.indexOf(step);
            notice.style.display = (idx > -1 && idx < WF.indexOf('lc')) ? 'block' : 'none';
        }

        // Restore the UP table from the saved LC page snapshot
        const lcSnap = res.pages?.lc || {};
        if (lcSnap.lcUpTableData) lcRestoreUpTable(lcSnap.lcUpTableData);
        const sales   = res.pages?.sales || {};
        const intake  = res.pages?.['marketing-intake'] || {};

        // Priority: pages.sales snapshot -> pis ERP data -> marketing-intake
        const allPis  = res.pis || [];
        const bestPi  = allPis.find(p => p.is_master) || allPis[0] || {};
        const erpPos  = bestPi.pos || [];
        const firstPo = sales.pos?.[0] || erpPos[0] || intake.pos?.[0] || {};

        const setGlance = (key, val) => {
            const el = document.querySelector(`[data-bind="${key}"]`);
            if (el && val) el.textContent = val;
        };

        setGlance('salesOrder', firstPo.salesOrder || firstPo.salesOrderNo || '-');
        setGlance('customerPo', firstPo.poNum || firstPo.customerPo || '-');
        const buyerVal = firstPo.buyer || firstPo.endBuyer || sales.buyer || '';
        setGlance('buyerName', buyerVal || '-');
        // Auto-fill the editable LC Buyer field (only if not already saved/typed).
        const lcBuyerEl = document.getElementById('lcBuyer');
        if (lcBuyerEl && !lcBuyerEl.value && buyerVal) lcBuyerEl.value = buyerVal;

        const custName = sales.customer || bestPi.customer || intake.customer || '';
        if (custName) {
            setGlance('customerName', custName);
        } else {
            const custId = intake.intakeCustomer;
            if (custId) {
                fetch(window.APP_BASE + '/api/customers.php?id=' + custId)
                    .then(r => r.json())
                    .then(c => { if (c?.company_name) setGlance('customerName', c.company_name); })
                    .catch(() => {});
            }
        }
    };
})(window.onOrderLoad);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
