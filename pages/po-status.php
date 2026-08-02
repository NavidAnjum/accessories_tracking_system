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
                            <input id="challanPiSearch" class="toolbar-input" placeholder="Enter PI number…">
                            <button type="button" class="primary-btn" id="searchChallanPi">Search PI</button>
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
                </section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
