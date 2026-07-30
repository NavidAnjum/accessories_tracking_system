<?php
$pageTitle   = 'Buyer Master';
$activePage  = 'buyer-master';
$navSection  = 'master';
$pageSubtitle = 'Maintain the buyer list used across all forms.';
include __DIR__ . '/../includes/header.php';
?>

                <section class="form-card" data-page="buyer-master">
                    <div class="section-head">
                        <div class="section-title">
                            <span class="section-tag">Master 1</span>
                            <h2>Buyer Master</h2>
                        </div>
                        <div class="section-summary">
                            <strong>Master Table</strong>
                            <span>Maintain the buyer list used by Marketing intake. If a buyer is missing, create it here or from the intake popup.</span>
                        </div>
                    </div>
                    <div class="page-actions compact-actions">
                        <div class="page-actions-left">
                            <button type="button" class="ghost-btn" id="openBuyerMasterModal">Add Buyer To Master</button>
                        </div>
                    </div>
                    <div class="packing-items-wrap">
                        <table class="packing-items-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Buyer Code</th>
                                    <th>Buyer Name</th>
                                    <th>Address</th>
                                </tr>
                            </thead>
                            <tbody id="buyerMasterBody"></tbody>
                        </table>
                    </div>
                </section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
