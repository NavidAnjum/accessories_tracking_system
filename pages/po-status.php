<?php
$pageTitle   = 'Challan Sheet';
$activePage  = 'po-status';
$navSection  = 'order';
$pageSubtitle = 'Challan Sheet — delivery and inspection records.';
include __DIR__ . '/../includes/header.php';
?>

<style>
@media print {
    /* Hide app chrome — print only the challan sheet */
    nav.page-nav, .order-id-bar, .section-head, .erp-banner,
    .challan-search-banner, .page-actions, .no-print,
    .nav-user-bar, .modal-shell, #sharedOrderItemsPanel { display: none !important; }
    html, body, .app-shell, .form-stack, .form-card { background: #fff !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; }
    .challan-sheet { border: 1.5px solid #1e3a8a !important; box-shadow: none !important; margin: 0 !important; }
    /* Force table borders/colors to render in print */
    .challan-table th, .challan-table td, .challan-qa-card, .challan-sheet * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    @page { size: A4 landscape; margin: 8mm; }
}
</style>

                <section class="form-card" data-page="po-status">
                    <div class="section-head">
                        <div class="section-title">
                            <span class="section-tag">Section 11</span>
                            <h2>Challan Sheet</h2>
                        </div>
                        <div class="section-summary">
                            <strong>ERP Output</strong>
                            <span>This challan sheet comes from ERP using the PI number and keeps the delivery rows for QA review.</span>
                        </div>
                    </div>
                    <div class="erp-banner challan-search-banner">
                        <div>
                            <strong>Challan PI Search</strong>
                            <span>Search by PI number to load challan data.</span>
                        </div>
                        <div class="challan-search-tools">
                            <input id="challanPiSearch" class="toolbar-input" placeholder="Enter PI number..." onkeydown="if(event.key==='Enter'){event.preventDefault();window.runChallanSearch(this.value);}">
                            <button type="button" class="primary-btn" id="searchChallanPi" onclick="window.runChallanSearch((document.getElementById('challanPiSearch') && document.getElementById('challanPiSearch').value) || '')">Search PI</button>
                        </div>
                    </div>
                    <div class="challan-sheet">
                        <div class="challan-brand-row">
                            <div class="challan-brand-mark">ZZAL</div>
                            <h3>Zaber &amp; Zubair Accessories Ltd.</h3>
                        </div>
                        <div class="challan-title-row">
                            <div>
                                <strong id="challanCustomerText">—</strong>
                                <span>Quality Certificate From</span>
                            </div>
                            <div class="challan-date-row">
                                <span>Date:</span>
                                <strong id="challanSheetDateText">—</strong>
                            </div>
                        </div>
                        <div class="packing-items-wrap">
                            <table class="packing-items-table challan-table">
                                <thead>
                                    <tr>
                                        <th>PI NO</th>
                                        <th>Order Ref#</th>
                                        <th>Description</th>
                                        <th>Delivery Date</th>
                                        <th>Qty</th>
                                        <th>Challan No</th>
                                        <th>Inspection Result</th>
                                    </tr>
                                </thead>
                                <tbody id="challanItemsBody">
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td class="challan-total-label">Total=</td>
                                        <td></td>
                                        <td id="challanTotalQty">—</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="challan-qa-grid">
                            <div class="challan-qa-card">
                                <div class="challan-qa-row"><strong>Quality Assurance Deperment</strong></div>
                                <div class="challan-qa-row"><span id="challanQaCustomerLeft">—</span></div>
                                <div class="challan-qa-row"><span>Comment:</span></div>
                                <div class="challan-qa-row"><span>Pass: Yes/No</span></div>
                                <div class="challan-qa-row"><span>Fail: Yes/No</span></div>
                                <div class="challan-qa-row"><span>Signeture:</span></div>
                                <div class="challan-qa-row"><span>Date:</span></div>
                            </div>
                            <div class="challan-qa-card">
                                <div class="challan-qa-row"><strong>Quality Assurance Department</strong></div>
                                <div class="challan-qa-row"><span>Supplier Name:</span></div>
                                <div class="challan-qa-row"><span>Comment:</span></div>
                                <div class="challan-qa-row"><span>Pass: Yes/No</span></div>
                                <div class="challan-qa-row"><span>Fail: Yes/No</span></div>
                                <div class="challan-qa-row"><span>Signeture:</span></div>
                                <div class="challan-qa-row"><span>Date:</span></div>
                            </div>
                        </div>
                        <div class="challan-footer-text">
                            Corporate Office: Adamjee Court (4th &amp; 5th Floor), 115-120, Motijheel C/A, Dhaka-1000, Bangladesh. Phone: +880-2-7176207-8, 7176356, 71766348, Fax: +880-2-9564252, 9565282, 7167293. Web: www.znzfab.com Factory: Mawna, Sreepur, Gazipur. E-mail: znzal@znzfab.com
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="page-actions-left">
                            <button type="button" class="ghost-btn js-prev-page" data-prev-page="bank-forwarding">Previous</button>
                            <button type="button" class="ghost-btn workflow-btn" data-action="generate-challan">Generate Challan Sheet</button>
                            <button type="button" class="ghost-btn no-print" onclick="window.print()" style="color:#4f46e5;border-color:#c7d2fe;">&#128424; Download PDF</button>
                        </div>
                        <div class="page-actions-right">
                            <button type="button" class="primary-btn workflow-btn" data-action="generate-docs">Finish Pack</button>
                        </div>
                    </div>
<script>
function challanEscape(val) {
    return String(val ?? '-')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function challanFormatDate(val) {
    const raw = String(val || '').trim();
    if (!raw) return '-';
    const iso = raw.replace(' ', 'T');
    const parsed = new Date(iso);
    if (!Number.isNaN(parsed.getTime())) {
        return parsed.toLocaleDateString('en-GB');
    }
    return raw;
}

function resetChallanSheet(message) {
    const body = document.getElementById('challanItemsBody');
    const total = document.getElementById('challanTotalQty');
    const customer = document.getElementById('challanCustomerText');
    const qaLeft = document.getElementById('challanQaCustomerLeft');
    const date = document.getElementById('challanSheetDateText');
    if (body) {
        body.innerHTML = message
            ? `<tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:18px;">${challanEscape(message)}</td></tr>`
            : '';
    }
    if (total) total.textContent = '0';
    if (customer) customer.textContent = '-';
    if (qaLeft) qaLeft.textContent = '-';
    if (date) date.textContent = '-';
}

function renderChallanSheet(data) {
    const body = document.getElementById('challanItemsBody');
    const total = document.getElementById('challanTotalQty');
    const customer = document.getElementById('challanCustomerText');
    const qaLeft = document.getElementById('challanQaCustomerLeft');
    const date = document.getElementById('challanSheetDateText');
    if (!body) return;

    const rows = Array.isArray(data.rows) ? data.rows : [];
    if (!rows.length) {
        resetChallanSheet('No challan rows found for this PI.');
        return;
    }

    body.innerHTML = rows.map(row => `
        <tr>
            <td>${challanEscape(row.piNo || data.pi || '-')}</td>
            <td>${challanEscape(row.orderRef || '-')}</td>
            <td>${challanEscape(row.description || '-')}</td>
            <td>${challanEscape(challanFormatDate(row.deliveryDate))}</td>
            <td style="text-align:right;">${Number(row.qty || 0).toLocaleString()}</td>
            <td>${challanEscape(row.challanNo || '-')}</td>
            <td>${challanEscape(row.inspectionResult || '-')}</td>
        </tr>
    `).join('');

    if (total) total.textContent = Number(data.totalQty || 0).toLocaleString();
    if (customer) customer.textContent = data.customer || '-';
    if (qaLeft) qaLeft.textContent = data.customer || '-';
    const firstDeliveryDate = rows.length ? rows[0].deliveryDate : '';
    if (date) date.textContent = challanFormatDate(data.sheetDate || firstDeliveryDate || '');
}

async function searchChallanByPi(piValue) {
    const pi = String(piValue || '').trim();
    const input = document.getElementById('challanPiSearch');
    const btn = document.getElementById('searchChallanPi');
    if (!pi) {
        alert('Please enter a PI number.');
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Searching...';
    }
    resetChallanSheet('Loading challan data...');

    try {
        const res = await fetch(APP_BASE + '/api/challan_proxy.php?pi=' + encodeURIComponent(pi));
        const json = await res.json();
        if (json.error) {
            resetChallanSheet('ERP error: ' + json.error + (json.detail ? ' (' + json.detail + ')' : ''));
            return;
        }
        if (!json.found) {
            resetChallanSheet('No challan found for PI ' + pi + '.');
            return;
        }
        renderChallanSheet(json);
        if (input) input.value = json.pi || pi;
    } catch (e) {
        resetChallanSheet('Could not reach ERP challan service.');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Search PI';
        }
    }
}

window.searchChallanByPi = searchChallanByPi;
window.runChallanSearch = function (piValue) {
    console.log('runChallanSearch hit', piValue);
    return searchChallanByPi(piValue);
};

document.addEventListener('DOMContentLoaded', function () {
    resetChallanSheet('Search by PI number to load challan data.');

    var challanBtn = document.getElementById('searchChallanPi');
    if (challanBtn) {
        challanBtn.addEventListener('click', function () {
            var challanInput = document.getElementById('challanPiSearch');
            console.log('Search PI button clicked', challanInput ? challanInput.value : '');
            console.log('search fn type', typeof window.runChallanSearch, typeof searchChallanByPi);
            window.runChallanSearch(challanInput ? challanInput.value : '');
        });
    }

    var challanInput = document.getElementById('challanPiSearch');
    if (challanInput) {
        challanInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                window.runChallanSearch(event.target.value || '');
            }
        });
    }
});

window.onOrderLoad = function (res) {
    const sales = res.pages?.sales || {};
    const piNum = sales.piNum || '';
    const input = document.getElementById('challanPiSearch');
    if (input && piNum) {
        input.value = piNum;
        window.runChallanSearch(piNum);
    } else {
        resetChallanSheet('Search by PI number to load challan data.');
    }
};
</script>

                </section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
