<?php
// customer-pdf.php - printable Customer Profile (Save as PDF via browser print)
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/print-brand.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); echo 'Missing customer id.'; exit; }

try {
    $db   = getDB();
    $stmt = $db->prepare('SELECT * FROM customers WHERE id = ?');
    $stmt->execute([$id]);
    $c = $stmt->fetch();
} catch (Throwable $e) {
    http_response_code(500); echo 'Database error.'; exit;
}
if (!$c) { http_response_code(404); echo 'Customer not found.'; exit; }

$extra = [];
if (!empty($c['extra_data'])) {
    $extra = json_decode($c['extra_data'], true) ?: [];
}
$sigs = [];
if (!empty($c['signatures'])) {
    $sigs = json_decode($c['signatures'], true) ?: [];
}

function h($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function val($v) { $v = trim((string)($v ?? '')); return $v === '' ? '-' : htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function ex($extra, $k) { return val($extra[$k] ?? ''); }
$stageLabels = [
    'sales_person' => 'Sales Person',
    'team_leader'  => 'Team Lead',
    'finance'      => 'Finance',
    'commercial'   => 'Commercial',
];

$company = $c['company_name'] ?: 'Customer Profile';
$custCode = $extra['customerCode'] ?? '';
$productInterest = is_array($extra['productInterest'] ?? null) ? $extra['productInterest'] : [];
$certifications  = is_array($extra['factoryCertifications'] ?? null)  ? $extra['factoryCertifications']  : [];
$leadTimes       = is_array($extra['leadTimes'] ?? null)       ? $extra['leadTimes']       : [];
$priceMatrix     = is_array($extra['priceMatrix'] ?? null)     ? $extra['priceMatrix']     : [];
$docChecklist    = is_array($extra['docChecklist'] ?? null)    ? $extra['docChecklist']    : [];
$competitor      = is_array($extra['competitorAnalysis'] ?? null) ? $extra['competitorAnalysis'] : [];
$risk            = is_array($extra['riskAssessment'] ?? null) ? $extra['riskAssessment'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($company) ?> - Customer Profile</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; color: #1e1e2e; margin: 0; background: #e5e7eb; }
    .sheet { position: relative; width: 210mm; min-height: 297mm; height: auto; max-width: 820px; margin: 20px auto; padding: 14mm 14mm 30mm; background: #fff; box-shadow: 0 4px 24px rgba(0,0,0,.12); display:flex; flex-direction:column; overflow: visible; }
    .brand-header { margin-bottom: 10px; }
    .brand-footer { position: static; left: auto; right: auto; bottom: auto; margin-top: 18px; }
    .doc-head { display: flex; align-items: center; justify-content: space-between; border-bottom: 3px solid #4f46e5; padding-bottom: 14px; margin-bottom: 8px; }
    .doc-head h1 { font-size: 22px; margin: 0; color: #1e1e2e; }
    .doc-head .sub { font-size: 12px; color: #6b7280; margin-top: 3px; }
    .doc-code { text-align: right; font-size: 12px; color: #4f46e5; font-weight: 700; }
    .sec { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #4f46e5; border-bottom: 1.5px solid #e0e3ff; padding-bottom: 5px; margin: 20px 0 12px; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 28px; }
    .grid.three { grid-template-columns: 1fr 1fr 1fr; }
    .item label { display: block; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #9ca3af; margin-bottom: 2px; }
    .item span { font-size: 13px; font-weight: 600; color: #1e1e2e; }
    .badges { display: flex; flex-wrap: wrap; gap: 6px; }
    .badge { background: #e0e7ff; color: #4f46e5; padding: 3px 11px; border-radius: 999px; font-size: 11.5px; font-weight: 600; }
    table.matrix { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 4px; }
    table.matrix th { background: #f5f7ff; color: #4f46e5; font-size: 10.5px; text-transform: uppercase; letter-spacing: .04em; padding: 7px 9px; border: 1px solid #e0e3ff; text-align: left; }
    table.matrix td { padding: 6px 9px; border: 1px solid #eceffe; }
    .sigs { display: flex; gap: 40px; flex-wrap: wrap; margin-top: 12px; }
    .sig { text-align: center; min-width: 180px; }
    .sig img { max-height: 60px; max-width: 170px; display: block; margin: 0 auto 4px; }
    .sig .line { border-top: 1.5px solid #374151; padding-top: 5px; font-size: 12px; font-weight: 700; color: #374151; }
    .sig .empty { height: 60px; display: flex; align-items: flex-end; justify-content: center; color: #cbd5e1; font-size: 11px; padding-bottom: 4px; }
    .toolbar { max-width: 820px; margin: 16px auto 0; display: flex; gap: 10px; justify-content: flex-end; }
    .btn { background: #4f46e5; color: #fff; border: none; border-radius: 8px; padding: 10px 22px; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-block; }
    .btn.ghost { background: #fff; color: #4f46e5; border: 1.5px solid #4f46e5; }
    @media print {
        @page { size: A4; margin: 12mm 10mm 14mm; }
        body { background: #fff; }
        .toolbar { display: none; }
        .sheet { box-shadow: none; margin: 0; max-width: 100%; width: 100%; min-height: 0; height: auto; overflow: visible; padding: 0; }
        .sec { break-after: avoid; }
        .brand-footer { margin-top: 10px; }
    }
</style>
</head>
<body>

<div class="toolbar">
    <a class="btn ghost" href="customer-profile.php">&#8592; Back</a>
    <button class="btn" onclick="window.print()">&#128424; Download / Print PDF</button>
</div>

<div class="sheet">
    <div class="brand-header"><?= zzal_print_brand_header() ?></div>
    <div class="doc-head">
        <div>
            <h1><?= h($company) ?></h1>
            <div class="sub">Customer Profile &amp; Commercial Assessment</div>
        </div>
        <div class="doc-code">
            <?php if ($custCode): ?><?= h($custCode) ?><br><?php endif; ?>
            <?= h($c['customer_type'] ?? 'Regular') ?><br>
            <span style="color:#9ca3af;font-weight:500;"><?= $c['created_at'] ? h(date('d M Y', strtotime($c['created_at']))) : '' ?></span>
        </div>
    </div>

    <div class="sec">1. Customer Information</div>
    <div class="grid">
        <div class="item"><label>Customer Category</label><span><?= ex($extra,'customerCategory') ?></span></div>
        <div class="item"><label>Customer Code</label><span><?= val($custCode) ?></span></div>
        <div class="item"><label>Industry</label><span><?= ex($extra,'industry') ?></span></div>
        <div class="item"><label>Website</label><span><?= ex($extra,'website') ?></span></div>
        <div class="item"><label>Head Office Address</label><span><?= val($c['address_head_office']) ?></span></div>
        <div class="item"><label>Factory Address</label><span><?= val($c['factory_address']) ?></span></div>
        <div class="item"><label>Chairman / MD (<?= val($c['chairman_role'] ?? ($extra['chairmanRole'] ?? '')) ?>)</label><span><?= val($c['chairman_name']) ?></span></div>
        <div class="item"><label>Chairman Phone</label><span><?= val($c['chairman_mobile']) ?></span></div>
        <div class="item"><label>Commercial Contact</label><span><?= ex($extra,'commercialName') ?> | <?= ex($extra,'commercialNumber') ?></span></div>
        <div class="item"><label>Merchandiser</label><span><?= ex($extra,'merchandiserContact') ?> | <?= ex($extra,'merchandiserMobile') ?></span></div>
        <div class="item"><label>Email</label><span><?= ex($extra,'email') ?></span></div>
    </div>

    <div class="sec">2. Business &amp; Compliance</div>
    <div class="grid three">
        <div class="item"><label>Trade License</label><span><?= ex($extra,'tradelicense') ?></span></div>
        <div class="item"><label>BIN</label><span><?= ex($extra,'bin') ?></span></div>
        <div class="item"><label>TIN</label><span><?= ex($extra,'tin') ?></span></div>
        <div class="item"><label>Bond License</label><span><?= ex($extra,'bondLicense') ?></span></div>
        <div class="item"><label>Bond Expiry</label><span><?= ex($extra,'bondLicenseExpiry') ?></span></div>
        <div class="item"><label>Compliance Status</label><span><?= ex($extra,'complianceStatus') ?></span></div>
        <div class="item"><label>Factory Building</label><span><?= ex($extra,'factoryBuilding') ?></span></div>
        <div class="item"><label>Political Exposure</label><span><?= !empty($c['politics_yes']) ? 'Yes' : 'No' ?></span></div>
        <div class="item"><label>Bank Name &amp; Branch</label><span><?= ex($extra,'bankName') ?></span></div>
    </div>
    <div class="item" style="margin-top:10px;"><label>Factory Certifications</label>
        <div class="badges"><?php if ($certifications): foreach ($certifications as $cert): ?><span class="badge"><?= h($cert) ?></span><?php endforeach; else: ?><span style="color:#9ca3af;font-size:12px;">None</span><?php endif; ?></div>
    </div>

    <div class="sec">3. Production Capability</div>
    <div class="grid three">
        <div class="item"><label>Factory Type</label><span><?= ex($extra,'factoryType') ?></span></div>
        <div class="item"><label>Monthly Capacity</label><span><?= ex($extra,'monthlyCapacity') ?></span></div>
        <div class="item"><label>Daily Production</label><span><?= ex($extra,'dailyProduction') ?></span></div>
        <div class="item"><label>No. of Machines</label><span><?= ex($extra,'noOfMachines') ?></span></div>
        <div class="item"><label>No. of Lines</label><span><?= ex($extra,'noOfLines') ?></span></div>
        <div class="item"><label>Peak Capacity</label><span><?= ex($extra,'peakCapacity') ?></span></div>
        <div class="item"><label>Major Buyers</label><span><?= ex($extra,'majorBuyers') ?></span></div>
        <div class="item"><label>Major Products</label><span><?= ex($extra,'majorProducts') ?></span></div>
        <div class="item"><label>Subcontract Factory</label><span><?= ex($extra,'subcontractFactory') ?></span></div>
    </div>

    <div class="sec">4. Commercial Assessment</div>
    <div class="grid three">
        <div class="item"><label>Expected Monthly Business</label><span><?= ex($extra,'expectedMonthlyBiz') ?></span></div>
        <div class="item"><label>Average Monthly Order</label><span><?= ex($extra,'avgMonthlyOrder') ?></span></div>
        <div class="item"><label>Credit Facility</label><span><?= ex($extra,'creditFacility') ?></span></div>
        <div class="item"><label>Payment Currency</label><span><?= ex($extra,'paymentCurrency') ?></span></div>
        <div class="item"><label>LC Terms</label><span><?= ex($extra,'lcTerms') ?></span></div>
        <div class="item"><label>BBLC Terms</label><span><?= val($extra['bbkTerms'] ?? ($extra['bblcTerms'] ?? '')) ?></span></div>
        <div class="item"><label>Delivery Terms</label><span><?= ex($extra,'deliveryTerms') ?></span></div>
        <div class="item"><label>UD Required</label><span><?= ex($extra,'udRequired') ?></span></div>
        <div class="item"><label>Zone</label><span><?= ex($extra,'zone') ?></span></div>
    </div>

    <div class="sec">5. Product Interest &amp; Lead Time</div>
    <div class="badges" style="margin-bottom:8px;">
        <?php if ($productInterest): foreach ($productInterest as $pi): ?>
            <span class="badge"><?= h($pi) ?><?php if (!empty($leadTimes[$pi])): ?> | <?= h($leadTimes[$pi]) ?>d<?php endif; ?></span>
        <?php endforeach; else: ?><span style="color:#9ca3af;font-size:12px;">None selected</span><?php endif; ?>
    </div>

    <div class="sec">6. Competitor Analysis</div>
    <div class="grid">
        <div class="item"><label>Existing Supplier</label><span><?= val($competitor['supplier'] ?? ($extra['compSupplier'] ?? '')) ?></span></div>
        <div class="item"><label>Current Price</label><span><?= val($competitor['currentPrice'] ?? ($extra['compCurrentPrice'] ?? '')) ?></span></div>
        <div class="item"><label>Strength</label><span><?= val($competitor['strength'] ?? ($extra['compStrength'] ?? '')) ?></span></div>
        <div class="item"><label>Weakness</label><span><?= val($competitor['weakness'] ?? ($extra['compWeakness'] ?? '')) ?></span></div>
        <div class="item" style="grid-column:1/-1;"><label>Reason for Change</label><span><?= val($competitor['reasonForChange'] ?? ($extra['compReasonForChange'] ?? '')) ?></span></div>
    </div>

    <div class="sec">7. Risk Assessment</div>
    <div class="grid">
        <div class="item"><label>Financial Risk</label><span><?= val($risk['financialRisk'] ?? ($extra['financialRisk'] ?? '')) ?></span></div>
        <div class="item"><label>Payment History</label><span><?= val($risk['paymentHistory'] ?? ($extra['paymentHistory'] ?? '')) ?></span></div>
        <div class="item"><label>Credit Limit Recommended</label><span><?= val($risk['creditLimitRec'] ?? ($extra['creditLimitRec'] ?? '')) ?></span></div>
        <div class="item"><label>Remarks</label><span><?= val($risk['remarks'] ?? ($extra['riskRemarks'] ?? '')) ?></span></div>
    </div>

    <div class="sec">8. Price Approval Matrix</div>
    <table class="matrix">
        <thead><tr><th>Product</th><th>Existing</th><th>Target</th><th>Approved</th><th>Commission</th></tr></thead>
        <tbody>
        <?php if ($priceMatrix): foreach ($priceMatrix as $row): ?>
            <tr>
                <td><?= val($row['product'] ?? '') ?></td>
                <td><?= val($row['existingPrice'] ?? '') ?></td>
                <td><?= val($row['targetPrice'] ?? '') ?></td>
                <td><?= val($row['approvedPrice'] ?? '') ?></td>
                <td><?= val($row['commission'] ?? '') ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="5" style="color:#9ca3af;text-align:center;">No price approval rows saved.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="sec">9. Document Checklist</div>
    <div class="badges"><?php if ($docChecklist): foreach ($docChecklist as $doc): ?><span class="badge">&#10003; <?= h($doc) ?></span><?php endforeach; else: ?><span style="color:#9ca3af;font-size:12px;">No documents checked.</span><?php endif; ?></div>

    <div class="sec">Approvals &amp; Signatures</div>
    <div class="sigs">
        <?php foreach ($stageLabels as $role => $label): ?>
            <div class="sig">
                <?php if (!empty($sigs[$role])): ?>
                    <img src="<?= h($sigs[$role]) ?>" alt="<?= h($label) ?> signature">
                <?php else: ?>
                    <div class="empty">Pending</div>
                <?php endif; ?>
                <div class="line"><?= h($label) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="brand-footer"><?= zzal_print_brand_footer() ?></div>
</div>

<script>
window.addEventListener('load', function () {
    setTimeout(function () { window.print(); }, 400);
});
</script>
</body>
</html>
