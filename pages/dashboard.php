<?php
$pageTitle    = 'Dashboard';
$activePage   = 'dashboard';
$navSection   = 'order';
$pageSubtitle = 'All orders tracked by ZNZ ID — click Load to resume any order.';
include __DIR__ . '/../includes/header.php';
?>

<section class="form-card page-screen active" data-page="dashboard">
    <div class="section-head">
        <div class="section-title">
            <span class="section-tag">Home</span>
            <h2>Order Tracking Dashboard</h2>
        </div>
        <div class="section-summary">
            <strong>All Orders</strong>
            <span>Every order gets an auto-generated ZNZ ID. Track which workflow step each order is currently on.</span>
        </div>
    </div>
    <div class="page-actions compact-actions">
        <div class="page-actions-left">
            <button type="button" class="primary-btn" id="dashNewOrderTop">+ New Order</button>
        </div>
    </div>
    <div class="packing-items-wrap">
        <table class="packing-items-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>PO Number</th>
                    <th>Sales Person</th>
                    <th>Buyer</th>
                    <th>Delivery Date</th>
                    <th>Items</th>
                    <th>Current Step</th>
                    <th>Last Saved</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="dashOrdersBody"></tbody>
        </table>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', async function () {
    await renderDashboard();

    // If nothing loaded after render, redirect to first step
    const body = document.getElementById('dashOrdersBody');
    if (body && body.querySelector('.dash-empty')) {
        window.location.href = APP_BASE + '/pages/marketing-intake.php';
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
