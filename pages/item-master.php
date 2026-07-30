<?php
$pageTitle   = 'Item Master';
$activePage  = 'item-master';
$navSection  = 'master';
$pageSubtitle = 'Maintain items, grades, and base prices.';
include __DIR__ . '/../includes/header.php';
?>

                <section class="form-card" data-page="item-master">
                    <div class="section-head">
                        <div class="section-title">
                            <span class="section-tag">Master 2</span>
                            <h2>Item Master</h2>
                        </div>
                        <div class="section-summary">
                            <strong>Master Table</strong>
                            <span>Maintain item, grade, and base price values used by Marketing and Costing.</span>
                        </div>
                    </div>
                    <div class="page-actions compact-actions">
                        <div class="page-actions-left">
                            <button type="button" class="ghost-btn" id="openItemModal">Add Item To Master</button>
                        </div>
                    </div>
                    <div class="packing-items-wrap">
                        <table class="packing-items-table">
                            <thead>
                                <tr>
                                    <th>Product Line</th>
                                    <th>Item Name</th>
                                    <th>Grade</th>
                                    <th>Paper Combination</th>
                                    <th>Base Price</th>
                                </tr>
                            </thead>
                            <tbody id="itemMasterBody"></tbody>
                        </table>
                    </div>
                </section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
