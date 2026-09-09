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
.lc-zone-options { display:flex; gap:12px; flex-wrap:wrap; margin-top:3px; }
.lc-zone-option { display:flex; align-items:center; gap:8px; min-width:130px; padding:10px 14px; border:1.5px solid #c7d2fe; border-radius:10px; background:#f8faff; color:#3730a3; font-weight:700; cursor:pointer; }
.lc-zone-option input { width:17px; height:17px; accent-color:#4f46e5; }
.lc-pi-picker { margin:14px 0 18px; padding:14px; border:1.5px solid #c7d2fe; border-radius:12px; background:#f8faff; }
.lc-pi-picker-title { color:#3730a3; font-size:12px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; margin-bottom:8px; }
.lc-pi-search-row { display:flex; gap:8px; }
.lc-pi-search-row input { flex:1; min-width:0; }
.lc-pi-add-btn { border:0; border-radius:9px; padding:0 18px; background:#4f46e5; color:#fff; font-weight:800; cursor:pointer; white-space:nowrap; }
.lc-pi-message { min-height:18px; margin-top:7px; color:#64748b; font-size:12px; }
.lc-pi-message.error { color:#dc2626; }
.lc-pi-message.ok { color:#15803d; }
.lc-pi-list { display:flex; flex-wrap:wrap; gap:8px; margin-top:6px; }
.lc-pi-chip { display:flex; align-items:center; gap:9px; padding:7px 9px 7px 11px; border:1px solid #c7d2fe; border-radius:999px; background:#fff; color:#312e81; font-size:12px; font-weight:800; }
.lc-pi-chip small { color:#64748b; font-weight:600; }
.lc-pi-chip button { width:21px; height:21px; border:0; border-radius:50%; background:#fee2e2; color:#dc2626; cursor:pointer; font-size:15px; line-height:19px; }
@media (max-width:640px) { .lc-pi-search-row { flex-direction:column; } .lc-pi-add-btn { min-height:42px; } }

/* ── LC preview overlay ── */
#lcPreviewOverlay { position:fixed; inset:0; background:rgba(15,23,42,.55); z-index:1000; display:none; overflow:auto; padding:22px 0 40px; }
#lcPreviewOverlay.open { display:block; }
.lc-prev-toolbar { display:flex; justify-content:center; gap:10px; margin-bottom:16px; }
.lc-prev-toolbar button { padding:9px 22px; border:none; border-radius:8px; font-weight:700; font-size:13px; cursor:pointer; }
.lc-prev-print { background:#22c55e; color:#fff; }
.lc-prev-close { background:#e2e8f0; color:#334155; }
.lc-doc {
    width:210mm; max-width:calc(100% - 24px); margin:0 auto; background:#fff; box-sizing:border-box;
    padding:16mm 15mm; box-shadow:0 10px 44px rgba(0,0,0,.35);
    font-family:'Times New Roman',Times,serif; color:#111; font-size:11pt;
}
.lc-doc h1 { text-align:center; font-size:17pt; margin:0; color:#1a3a6e; letter-spacing:1px; }
.lc-doc .lc-doc-sub { text-align:center; font-size:10.5pt; color:#444; margin:2px 0 12px; border-bottom:2px solid #1a3a6e; padding-bottom:8px; }
.lc-doc h3 { font-size:10.5pt; color:#1a3a6e; margin:14px 0 5px; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #cbd5e1; padding-bottom:3px; }
.lc-doc table.lc-kv { width:100%; border-collapse:collapse; font-size:10pt; }
.lc-doc table.lc-kv th { text-align:left; width:33%; padding:4px 8px; vertical-align:top; color:#334155; font-weight:700; border:1px solid #e2e8f0; background:#f8fafc; }
.lc-doc table.lc-kv td { padding:4px 8px; border:1px solid #e2e8f0; vertical-align:top; white-space:pre-wrap; }
.lc-doc table.lc-up { width:100%; border-collapse:collapse; font-size:9.5pt; margin-top:4px; }
.lc-doc table.lc-up th, .lc-doc table.lc-up td { border:1px solid #cbd5e1; padding:4px 8px; text-align:left; }
.lc-doc table.lc-up th { background:#f1f5f9; }
.lc-doc table.lc-up tfoot td { font-weight:700; background:#fafbff; }
@media print {
    body.lc-printing > *:not(#lcPreviewOverlay) { display:none !important; }
    #lcPreviewOverlay { position:static !important; background:#fff !important; padding:0 !important; overflow:visible !important; }
    .lc-prev-toolbar { display:none !important; }
    .lc-doc { box-shadow:none !important; width:auto !important; max-width:none !important; margin:0 !important; padding:0 !important; }
    @page { size:A4 portrait; margin:12mm; }
}
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
                    <div class="lc-pi-picker">
                        <div class="lc-pi-picker-title">Add Existing PI From Another Sales Order</div>
                        <div class="lc-pi-search-row">
                            <input id="lcPiSearchInput" placeholder="Enter PI number, ERP sales order or customer PO"
                                   onkeydown="if(event.key==='Enter'){event.preventDefault();lcAddExistingPi();}">
                            <button type="button" class="lc-pi-add-btn" id="lcPiAddButton" onclick="lcAddExistingPi()">+ Add PI</button>
                        </div>
                        <div id="lcPiSearchMessage" class="lc-pi-message">The selected PI remains under its original Work Order.</div>
                        <div id="lcIncludedPiList" class="lc-pi-list"></div>
                        <input type="hidden" id="lcIncludedPis" name="lcIncludedPis" value="[]">
                    </div>
                    <div class="form-grid">
                        <div class="field span-12">
                            <label for="documentRoute">Document Route</label>
                            <select id="documentRoute" name="documentRoute" onchange="updateLcDocumentRoute()">
                                <option value="lc" selected>LC</option>
                                <option value="sales_contract">Sales Contract</option>
                            </select>
                            <small style="color:#64748b;">LC is selected by default. Choose Sales Contract to use the shorter document workflow.</small>
                        </div>
                        <div class="field span-12">
                            <label for="marketingApprovalDateTime">Marketing Approval Date &amp; Time</label>
                            <input id="marketingApprovalDateTime" readonly
                                   placeholder="Waiting for Marketing approval"
                                   style="background:#f8fafc;color:#334155;font-weight:700;">
                        </div>
                        <div class="field span-12">
                            <label>EPZ / Non-EPZ Selection</label>
                            <div class="lc-zone-options">
                                <label class="lc-zone-option">
                                    <input type="checkbox" id="lcZoneEpz" onchange="setLcZoneType('epz')">
                                    EPZ
                                </label>
                                <label class="lc-zone-option">
                                    <input type="checkbox" id="lcZoneNonEpz" checked onchange="setLcZoneType('non_epz')">
                                    Non-EPZ
                                </label>
                            </div>
                            <input type="hidden" id="lcZoneType" name="lcZoneType" value="non_epz">
                        </div>
                        <div class="field span-6 lc-epz-field" style="display:none;">
                            <label for="lcExpNo">EXP No.</label>
                            <input id="lcExpNo" name="lcExpNo" placeholder="EXP number">
                        </div>
                        <div class="field span-6 lc-epz-field" style="display:none;">
                            <label for="lcExpDate">EXP Date</label>
                            <input id="lcExpDate" name="lcExpDate" type="date">
                        </div>
                        <div class="field span-6 lc-epz-field" style="display:none;">
                            <label for="lcIpNo">IP No.</label>
                            <input id="lcIpNo" name="lcIpNo" placeholder="IP number">
                        </div>
                        <div class="field span-6 lc-epz-field" style="display:none;">
                            <label for="lcIpDate">IP Date</label>
                            <input id="lcIpDate" name="lcIpDate" type="date">
                        </div>
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
                            <label for="lcApplicantName">Applicant Name</label>
                            <input id="lcApplicantName" name="lcApplicantName" placeholder="LC applicant / importer name">
                        </div>
                        <div class="field span-6">
                            <label for="lcApplicantAddress">Applicant Address</label>
                            <textarea id="lcApplicantAddress" name="lcApplicantAddress" placeholder="LC applicant address"></textarea>
                        </div>
                        <div class="field span-6">
                            <label for="lcNumber">LC Number</label>
                            <input id="lcNumber" name="lcNumber" placeholder="LC number...">
                        </div>
                        <div class="field span-6">
                            <label for="lcExportSalesContractNo">Export S/C No.</label>
                            <input id="lcExportSalesContractNo" name="lcExportSalesContractNo" placeholder="Export sales contract number">
                        </div>
                        <div class="field span-6">
                            <label for="lcExportSalesContractDate">Export S/C Date</label>
                            <input id="lcExportSalesContractDate" name="lcExportSalesContractDate" type="date">
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
                        <div class="field span-4">
                            <label for="lcAmount">LC Amount (USD)</label>
                            <input id="lcAmount" name="lcAmount" type="number" step="0.01" placeholder="0.00">
                        </div>
                        <div class="field span-4">
                            <label for="piNumbersIncluded">PI Numbers Included</label>
                            <input id="piNumbersIncluded" readonly placeholder="Loaded from Work Order"
                                   style="background:#f8fafc;color:#334155;font-weight:700;">
                        </div>
                        <div class="field span-4">
                            <label for="piTotalValue">PI Total Value (USD)</label>
                            <input id="piTotalValue" readonly placeholder="Loaded from PI"
                                   style="background:#f8fafc;color:#334155;font-weight:700;">
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
                            <label for="lcBeneficiaryAddress">Beneficiary Company Address</label>
                            <input id="lcBeneficiaryAddress" name="lcBeneficiaryAddress" placeholder="Beneficiary company address…">
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
                            <button type="button" class="ghost-btn" onclick="openLcPreview()">👁 Preview LC</button>
                        </div>
                        <div class="page-actions-right">
                            <button type="button" class="primary-btn js-next-page" id="lcNextButton" data-next-page="exchange">Next: Bill of Exchange</button>
                        </div>
                    </div>
                </section>

<!-- LC preview overlay (rendered from the data currently in the form) -->
<div id="lcPreviewOverlay" onclick="if(event.target===this)closeLcPreview()">
    <div class="lc-prev-toolbar">
        <button type="button" class="lc-prev-print" onclick="printLcPreview()">🖨 Print / Save PDF</button>
        <button type="button" class="lc-prev-close" onclick="closeLcPreview()">Close</button>
    </div>
    <div class="lc-doc" id="lcPreviewDoc"></div>
</div>

<script>
let lcBasePis = [];
let lcExtraPis = [];
let lcLoadedResponse = null;

function lcPiKey(pi) {
    const id = Number(pi?.id || 0);
    return id > 0 ? 'id:' + id : 'num:' + String(pi?.pi_number || '').trim().toLowerCase();
}

function lcSetPiMessage(text, type) {
    const el = document.getElementById('lcPiSearchMessage');
    if (!el) return;
    el.textContent = text || '';
    el.className = 'lc-pi-message' + (type ? ' ' + type : '');
}

function lcRefreshPiSummary() {
    if (!lcLoadedResponse) return;
    const seen = new Set();
    lcLoadedResponse.pis = [...lcBasePis, ...lcExtraPis].filter(pi => {
        const key = lcPiKey(pi);
        if (!key || seen.has(key)) return false;
        seen.add(key);
        return true;
    });
    const summary = typeof window.atsResolveOrderPiSummary === 'function'
        ? window.atsResolveOrderPiSummary(lcLoadedResponse)
        : {numbers:lcLoadedResponse.pis.map(pi => pi.pi_number).filter(Boolean), total:lcResolvePiTotal(lcLoadedResponse)};
    const numbers = document.getElementById('piNumbersIncluded');
    const total = document.getElementById('piTotalValue');
    if (numbers) numbers.value = (summary.numbers || []).join(' / ');
    if (total) total.value = summary.total == null ? '' : Number(summary.total).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    if (typeof window._renderSharedPiSummary === 'function') window._renderSharedPiSummary(lcLoadedResponse);
    if (typeof window._renderSharedItemsPanel === 'function') window._renderSharedItemsPanel(lcLoadedResponse);
}

function lcSyncIncludedPis(persist) {
    const refs = lcExtraPis.map(pi => ({id:Number(pi.id || 0), pi_number:String(pi.pi_number || '')}));
    const hidden = document.getElementById('lcIncludedPis');
    if (hidden) hidden.value = JSON.stringify(refs);
    const list = document.getElementById('lcIncludedPiList');
    if (list) {
        list.innerHTML = lcExtraPis.map(pi => {
            const key = lcEsc(lcPiKey(pi));
            const value = Number.parseFloat(pi.grand_val || 0) || 0;
            return '<span class="lc-pi-chip">' + lcEsc(pi.pi_number || 'PI') +
                ' <small>$' + value.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}) + '</small>' +
                '<button type="button" title="Remove PI" onclick="lcRemoveIncludedPi(\'' + key + '\')">&times;</button></span>';
        }).join('');
    }
    lcRefreshPiSummary();
    if (persist) lcPersistIncludedPis();
}

async function lcPersistIncludedPis() {
    const orderId = window.getCurrentOrderId ? window.getCurrentOrderId() : '';
    if (!orderId) return;
    try {
        const response = await fetch(window.APP_BASE + '/api/save_page.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body:JSON.stringify({order_id:orderId, page_name:'lc', ...collectPageFields()})
        });
        const json = await response.json();
        if (!response.ok || json.error) throw new Error(json.error || 'Could not save PI link.');
        lcSetPiMessage('PI list saved for this LC.', 'ok');
    } catch (error) {
        lcSetPiMessage(error.message || 'Could not save PI list.', 'error');
    }
}

async function lcAddExistingPi() {
    const input = document.getElementById('lcPiSearchInput');
    const button = document.getElementById('lcPiAddButton');
    const term = String(input?.value || '').trim();
    if (!window.getCurrentOrderId?.()) { lcSetPiMessage('Load a Work Order first.', 'error'); return; }
    if (!term) { lcSetPiMessage('Enter a PI number, ERP sales order or customer PO.', 'error'); return; }
    if (button) { button.disabled = true; button.textContent = 'Searching...'; }
    try {
        const response = await fetch(window.APP_BASE + '/api/pis.php?q=' + encodeURIComponent(term));
        const result = await response.json();
        const pi = result?.pi;
        if (!response.ok || !pi) throw new Error(result?.error || 'No existing PI found.');
        let relatedPis = [pi];
        const relatedOrderId = String(pi.order_id || '').trim();
        if (relatedOrderId) {
            const relatedResponse = await fetch(window.APP_BASE + '/api/pis.php?order_id=' + encodeURIComponent(relatedOrderId));
            const relatedResult = await relatedResponse.json();
            if (relatedResponse.ok && Array.isArray(relatedResult)) {
                if (relatedResult.length) relatedPis = relatedResult;
            }
        }
        const existingKeys = new Set([...lcBasePis, ...lcExtraPis].map(lcPiKey));
        const newPis = relatedPis.filter(item => !existingKeys.has(lcPiKey(item)));
        if (!newPis.length) throw new Error('All PIs from this Work Order are already included.');
        newPis.forEach(item => { item.linked_via_lc = 1; lcExtraPis.push(item); });
        if (input) input.value = '';
        lcSyncIncludedPis(true);
        lcSetPiMessage(
            newPis.length > 1
                ? 'Added all ' + newPis.length + ' PIs from Work Order ' + relatedOrderId + '.'
                : 'Added ' + String(newPis[0]?.pi_number || 'PI') + '.',
            'ok'
        );
    } catch (error) {
        lcSetPiMessage(error.message || 'Unable to add this PI.', 'error');
    } finally {
        if (button) { button.disabled = false; button.textContent = '+ Add PI'; }
    }
}

function lcRemoveIncludedPi(key) {
    lcExtraPis = lcExtraPis.filter(pi => lcPiKey(pi) !== key);
    lcSyncIncludedPis(true);
}

function updateLcDocumentRoute() {
    const route = document.getElementById('documentRoute')?.value || 'lc';
    const next = document.getElementById('lcNextButton');
    if (!next) return;
    if (route === 'sales_contract') {
        next.dataset.nextPage = 'sales-contract';
        next.textContent = 'Next: Sales Contract';
    } else {
        next.dataset.nextPage = 'exchange';
        next.textContent = 'Next: Bill of Exchange';
    }
}

function setLcZoneType(type) {
    const zone = type === 'epz' ? 'epz' : 'non_epz';
    const epz = document.getElementById('lcZoneEpz');
    const nonEpz = document.getElementById('lcZoneNonEpz');
    const hidden = document.getElementById('lcZoneType');
    if (epz) epz.checked = zone === 'epz';
    if (nonEpz) nonEpz.checked = zone === 'non_epz';
    if (hidden) hidden.value = zone;
    document.querySelectorAll('.lc-epz-field').forEach(field => {
        field.style.display = zone === 'epz' ? '' : 'none';
    });
}

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
    updateLcDocumentRoute();
    setLcZoneType(document.getElementById('lcZoneType')?.value || 'non_epz');
});

// ── LC preview (built from the values currently in the form) ─────────────
function lcEsc(s) {
    return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
function lcFieldVal(id) {
    const el = document.getElementById(id);
    return el && String(el.value).trim() ? String(el.value).trim() : '';
}
function lcGlanceVal(key) {
    const el = document.querySelector('[data-bind="' + key + '"]');
    const t = el ? el.textContent.trim() : '';
    return t === '-' ? '' : t;
}
function lcResolvePiTotal(res) {
    if (typeof window.atsResolveOrderPiSummary === 'function') {
        return window.atsResolveOrderPiSummary(res).total;
    }
    const sales = res?.pages?.sales || {};
    const pis = Array.isArray(res?.pis) ? res.pis : [];
    const standalone = pis.filter(pi => Number(pi?.is_master || 0) !== 1);
    if (standalone.length) {
        return standalone.reduce((sum, pi) => sum + (parseFloat(pi?.grand_val) || 0), 0);
    }
    const direct = parseFloat(String(sales.grandVal ?? '').replace(/[^\d.-]/g, ''));
    if (Number.isFinite(direct)) return direct;

    const pos = Array.isArray(sales.pos) ? sales.pos : [];
    if (pos.length) {
        return pos.reduce((sum, po) => {
            const poValue = parseFloat(String(po?.val ?? '').replace(/[^\d.-]/g, ''));
            if (Number.isFinite(poValue)) return sum + poValue;
            return sum + (po?.items || []).reduce((itemSum, item) => {
                const total = parseFloat(item?.total);
                if (Number.isFinite(total)) return itemSum + total;
                return itemSum + ((parseFloat(item?.qty) || 0) * (parseFloat(item?.price) || 0));
            }, 0);
        }, 0);
    }

    const source = pis.slice(0, 1);
    if (!source.length) return null;
    return source.reduce((sum, pi) => sum + (parseFloat(pi?.grand_val) || 0), 0);
}
function lcKvRows(pairs) {
    return pairs.map(([label, val]) =>
        '<tr><th>' + lcEsc(label) + '</th><td>' + (val ? lcEsc(val) : '—') + '</td></tr>'
    ).join('');
}
function lcBuildPreview() {
    if (typeof lcSyncUpTable === 'function') lcSyncUpTable(); // refresh UP totals/JSON first
    const orderId = (window.getCurrentOrderId ? window.getCurrentOrderId() : '') || '';
    const amount  = lcFieldVal('lcAmount');
    const amountFmt = amount ? Number(amount).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) : '';

    // UP / Raw material rows from the hidden JSON (kept in sync by lcSyncUpTable)
    let upRows = [];
    try { upRows = JSON.parse(document.getElementById('lcUpTableData')?.value || '[]'); } catch (_) { upRows = []; }
    let upQty = 0, upVal = 0;
    const upBody = upRows.map(r => {
        upQty += parseFloat(r.qty) || 0;
        upVal += parseFloat(r.val) || 0;
        return '<tr><td>' + lcEsc(r.upNo || '—') + '</td><td>' + lcEsc(r.upDate || '—') +
               '</td><td>' + lcEsc(r.qty || '0') + '</td><td>' + lcEsc(r.val || '0') + '</td></tr>';
    }).join('');
    const upTable = upRows.length ? (
        '<h3>UP / Raw Material Details</h3>' +
        '<table class="lc-up"><thead><tr><th>Up No.</th><th>Up Date</th><th>Raw Material Qty</th><th>Raw Material Value</th></tr></thead>' +
        '<tbody>' + upBody + '</tbody>' +
        '<tfoot><tr><td colspan="2" style="text-align:right;">Total</td><td>' +
        upQty.toLocaleString() + '</td><td>' +
        upVal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '</td></tr></tfoot></table>'
    ) : '';

    const notes = lcFieldVal('lcNotes');

    document.getElementById('lcPreviewDoc').innerHTML =
        '<h1>LETTER OF CREDIT</h1>' +
        '<div class="lc-doc-sub">LC Details' + (orderId ? ' &nbsp;·&nbsp; Work Order ' + lcEsc(orderId) : '') + '</div>' +

        '<h3>Order Reference</h3>' +
        '<table class="lc-kv">' + lcKvRows([
            ['Matched Sales Order', lcGlanceVal('salesOrder')],
            ['Customer PO',         lcGlanceVal('customerPo')],
            ['Buyer',               lcGlanceVal('buyerName')],
            ['Customer',            lcGlanceVal('customerName')],
            ['Marketing Approval Date & Time', lcFieldVal('marketingApprovalDateTime')],
        ]) + '</table>' +

        '<h3>LC Details</h3>' +
        '<table class="lc-kv">' + lcKvRows([
            ['LC Number',       lcFieldVal('lcNumber')],
            ['LC Date',         lcFieldVal('lcDate')],
            ['Export S/C No.',  lcFieldVal('lcExportSalesContractNo')],
            ['Export S/C Date', lcFieldVal('lcExportSalesContractDate')],
            ['LC Check Status', lcFieldVal('lcCheckStatus')],
            ['Zone Type',       lcFieldVal('lcZoneType') === 'non_epz' ? 'Non-EPZ' : 'EPZ'],
            ...(lcFieldVal('lcZoneType') === 'epz' ? [
                ['EXP No.',     lcFieldVal('lcExpNo')],
                ['EXP Date',    lcFieldVal('lcExpDate')],
                ['IP No.',      lcFieldVal('lcIpNo')],
                ['IP Date',     lcFieldVal('lcIpDate')],
            ] : []),
            ['LC Buyer',        lcFieldVal('lcBuyer')],
            ['Applicant Name',  lcFieldVal('lcApplicantName')],
            ['Applicant Address', lcFieldVal('lcApplicantAddress')],
            ['Payment Terms',   lcFieldVal('paymentTerms')],
            ['Shipping Terms',  lcFieldVal('shippingTerms')],
            ['PI Numbers Included', lcFieldVal('piNumbersIncluded')],
            ['PI Total Value (USD)', lcFieldVal('piTotalValue')],
            ['LC Amount (USD)', amountFmt],
            ['Description',     lcFieldVal('lcDescription')],
            ['L/C Received Date', lcFieldVal('lcReceivedDate')],
            ['L/C Ship Date',   lcFieldVal('lcShipDate')],
            ['L/C Expiry Date', lcFieldVal('lcExpiryDate')],
        ]) + '</table>' +

        '<h3>Banks</h3>' +
        '<table class="lc-kv">' + lcKvRows([
            ['L/C Issuing Bank',            lcFieldVal('lcIssuingBank')],
            ['Reimbursement Bank',          lcFieldVal('reimbursementBank')],
            ['Negotiating / Beneficiary Bank', lcFieldVal('negotiatingBeneficiaryBank')],
        ]) + '</table>' +

        '<h3>Beneficiary</h3>' +
        '<table class="lc-kv">' + lcKvRows([
            ['Beneficiary Company Name', lcFieldVal('lcBeneficiaryName')],
            ['Beneficiary Company Address', lcFieldVal('lcBeneficiaryAddress')],
            ['Factory Address',          lcFieldVal('lcFactoryAddress')],
        ]) + '</table>' +

        '<h3>Processing &amp; Payment</h3>' +
        '<table class="lc-kv">' + lcKvRows([
            ['Doc send to the Buyer',        lcFieldVal('docSendToBuyerDate')],
            ['Acceptance Date',              lcFieldVal('acceptanceDate')],
            ['Doc sent to Negotiating Bank', lcFieldVal('docSentToNegotiatingBank')],
            ['Maturity Date',                lcFieldVal('maturityDate')],
            ['Received Amount',              lcFieldVal('receivedAmount')],
            ['Received Date',                lcFieldVal('receivedDate')],
        ]) + '</table>' +

        upTable +
        (notes ? '<h3>LC Notes</h3><table class="lc-kv"><tr><td>' + lcEsc(notes) + '</td></tr></table>' : '');
}
function openLcPreview() {
    lcBuildPreview();
    const overlay = document.getElementById('lcPreviewOverlay');
    // Hoist to <body> so the print rule can hide everything else cleanly.
    if (overlay.parentNode !== document.body) document.body.appendChild(overlay);
    overlay.classList.add('open');
}
function closeLcPreview() {
    document.getElementById('lcPreviewOverlay').classList.remove('open');
}
function printLcPreview() {
    document.body.classList.add('lc-printing');
    window.print();
}
window.addEventListener('afterprint', function () { document.body.classList.remove('lc-printing'); });
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeLcPreview(); });

// Populate LC glance fields from saved sales page data
window.onOrderLoad = (function(_prev) {
    return function(res) {
        if (typeof _prev === 'function') _prev(res);

        // Guard: warn only if Marketing hasn't approved the PI yet. The order is
        // returned to the Sales step after approval, so checking step position
        // alone would wrongly flag an approved order as "awaiting approval".
        const WF = ['marketing-intake','costing-review','sales','marketing','lc','exchange','commercial','packing','delivery','truck','origin','beneficiary','forwarding','bank-forwarding','po-status'];
        const step = res.order?.current_step || '';
        const marketingSnap = res.pages?.marketing || {};
        const marketingApproved = marketingSnap.marketingApproved === true
            || marketingSnap.marketingApproved === 'true'
            || marketingSnap.piApprovalStatus === 'approved';
        const approvalField = document.getElementById('marketingApprovalDateTime');
        if (approvalField) {
            const rawApprovalTime = String(marketingSnap.approvedAt || '').trim();
            let displayApprovalTime = '';
            if (rawApprovalTime) {
                const approvalDate = new Date(rawApprovalTime);
                displayApprovalTime = Number.isNaN(approvalDate.getTime())
                    ? rawApprovalTime
                    : approvalDate.toLocaleString('en-GB', {
                        timeZone:'Asia/Dhaka', day:'2-digit', month:'2-digit', year:'numeric',
                        hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true
                    });
            }
            approvalField.value = displayApprovalTime;
        }
        const notice = document.getElementById('lcApprovalNotice');
        if (notice) {
            const idx = WF.indexOf(step);
            notice.style.display = (!marketingApproved && idx > -1 && idx < WF.indexOf('lc')) ? 'block' : 'none';
        }

        // Restore the UP table from the saved LC page snapshot
        const lcSnap = res.pages?.lc || {};
        lcLoadedResponse = res;
        lcBasePis = (res.pis || []).filter(pi => Number(pi?.linked_via_lc || 0) !== 1);
        lcExtraPis = (res.pis || []).filter(pi => Number(pi?.linked_via_lc || 0) === 1);
        lcSyncIncludedPis(false);
        const routeSelect = document.getElementById('documentRoute');
        if (routeSelect) routeSelect.value = lcSnap.documentRoute === 'sales_contract' ? 'sales_contract' : 'lc';
        updateLcDocumentRoute();
        setLcZoneType(lcSnap.lcZoneType || 'non_epz');
        const salesContractSnap = res.pages?.['sales-contract'] || {};
        const exportScNoEl = document.getElementById('lcExportSalesContractNo');
        const exportScDateEl = document.getElementById('lcExportSalesContractDate');
        if (exportScNoEl && !exportScNoEl.value) {
            exportScNoEl.value = lcSnap.lcExportSalesContractNo || salesContractSnap.scContractNo || '';
        }
        if (exportScDateEl && !exportScDateEl.value) {
            exportScDateEl.value = lcSnap.lcExportSalesContractDate || salesContractSnap.scContractDate || '';
        }
        if (lcSnap.lcUpTableData) lcRestoreUpTable(lcSnap.lcUpTableData);
        const sales   = res.pages?.sales || {};
        const intake  = res.pages?.['marketing-intake'] || {};
        const piSummary = typeof window.atsResolveOrderPiSummary === 'function'
            ? window.atsResolveOrderPiSummary(res)
            : {numbers:(res.pis || []).filter(pi => Number(pi?.is_master || 0) !== 1).map(pi => pi.pi_number).filter(Boolean), total:lcResolvePiTotal(res)};
        const piTotal = piSummary.total;
        const piNumbersField = document.getElementById('piNumbersIncluded');
        if (piNumbersField) piNumbersField.value = (piSummary.numbers || []).join(' / ');
        const piTotalField = document.getElementById('piTotalValue');
        if (piTotalField) {
            piTotalField.value = piTotal == null
                ? ''
                : piTotal.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
        }

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
        const applicantNameEl = document.getElementById('lcApplicantName');
        if (applicantNameEl && !applicantNameEl.value) {
            applicantNameEl.value = res.order?.customer_name || sales.customer || bestPi.customer || '';
        }
        const applicantAddressEl = document.getElementById('lcApplicantAddress');
        if (applicantAddressEl && !applicantAddressEl.value) {
            applicantAddressEl.value = firstPo.buyerAddress || firstPo.customerAddress || sales.buyerAddress || res.order?.customer_address || '';
        }
    };
})(window.onOrderLoad);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
