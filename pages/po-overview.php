<?php
$pageTitle   = 'PO Status';
$activePage  = 'po-overview';
$navSection  = 'order';
$pageSubtitle = 'Live PO status and production progress.';
include __DIR__ . '/../includes/header.php';
?>

                <section class="form-card" data-page="po-overview">
                    <div class="section-head">
                        <div class="section-title">
                            <span class="section-tag">Section 10A</span>
                            <h2>PO Status</h2>
                        </div>
                        <div class="section-summary">
                            <strong>Production</strong>
                            <span>Shows the PO, item rows, current production stage, produced quantity, and total quantity.</span>
                        </div>
                    </div>
                    <div class="subtable-card po-status-card">
                        <div class="subtable-head">
                            <div>
                                <strong>PO Tracking</strong>
                                <span>Live status view based on the matched PO and current production update.</span>
                            </div>
                            <div class="subtable-total">
                                <span>PO Number</span>
                                <strong id="poStatusPoText">—</strong>
                            </div>
                        </div>

                        <div class="po-status-summary">
                            <div class="po-status-pill">
                                <span>Current Stage</span>
                                <strong id="poStatusStageText">—</strong>
                            </div>
                            <div class="po-status-pill">
                                <span>Produced Qty</span>
                                <strong id="poStatusProducedText">—</strong>
                            </div>
                            <div class="po-status-pill">
                                <span>Total Qty</span>
                                <strong id="poStatusTotalText">—</strong>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="field span-6">
                                <label for="poStatusStage">Current Stage</label>
                                <input id="poStatusStage" name="poStatusStage" placeholder="e.g. Corrugation">
                            </div>
                            <div class="field span-6">
                                <label for="poStatusProducedQty">Completed / Produced Qty</label>
                                <input id="poStatusProducedQty" name="poStatusProducedQty" placeholder="0">
                            </div>
                        </div>

                        <div class="packing-items-wrap">
                            <table class="packing-items-table">
                                <thead>
                                    <tr>
                                        <th>SL No.</th>
                                        <th>Description of Goods</th>
                                        <th>Ply</th>
                                        <th>Order Qty</th>
                                        <th>Corrugation</th>
                                        <th>Delivery</th>
                                        <th>Produced Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="poStatusItemsBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="page-actions">
                        <div class="page-actions-left">
                            <button type="button" class="ghost-btn js-prev-page" data-prev-page="lc">Previous</button>
                        </div>
                        <div class="page-actions-right">
                            <button type="button" class="primary-btn js-next-page" data-next-page="exchange">Next: Bill of Exchange</button>
                        </div>
                    </div>
                </section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
