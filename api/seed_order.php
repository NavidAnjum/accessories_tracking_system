<?php
/**
 * Seed / quick-fill all pages for a given order.
 * Usage: http://localhost/ed_module/api/seed_order.php?order_id=ORD-2026-0018
 *
 * Uses the ERP data already saved in the PI for this order, or the
 * hard-coded ERP items below as fallback.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$orderId = trim($_GET['order_id'] ?? '');
if (!$orderId) { http_response_code(400); echo json_encode(['error' => 'order_id required']); exit; }

$db = getDB();

// ── Load order ───────────────────────────────────────────────────────────────
$stmt = $db->prepare('SELECT * FROM orders WHERE order_id = ?');
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) { http_response_code(404); echo json_encode(['error' => 'Order not found']); exit; }

// ── Load saved PI for this order (to get ERP items) ─────────────────────────
$piStmt = $db->prepare('SELECT * FROM proforma_invoices WHERE order_id = ? ORDER BY created_at ASC LIMIT 1');
$piStmt->execute([$orderId]);
$pi = $piStmt->fetch(PDO::FETCH_ASSOC);
$piPos  = $pi ? json_decode($pi['pos_json'] ?? '[]', true) : [];
$piNum  = $pi['pi_number'] ?? ($orderId . '-PI');
$customer = $pi['customer'] ?? $order['customer_name'] ?? 'Alpha Garments Ltd';

// ── ERP items from the screenshot (fallback if no PI saved) ─────────────────
$erpItems = [
    ['desc'=>'A.M INTERNATIONAL Bath Towel Solid Dyed -Terry Towel 100% Cotton',   'ply'=>'PCS','qty'=>850,     'price'=>290.6,  'total'=>247010.00],
    ['desc'=>'A.M INTERNATIONAL Hand Towel Solid Dyed -Terry Towel 100% Cotton',   'ply'=>'PCS','qty'=>1250,    'price'=>85.64,  'total'=>107050.00],
    ['desc'=>'A.M INTERNATIONAL Bath Mat Solid Dyed -Terry Towel 100% Cotton',     'ply'=>'PCS','qty'=>1000,    'price'=>121.51, 'total'=>121510.00],
    ['desc'=>'Aman Graphics Meter Fabric Yarn Dyed Terry 100% Organic Cotton',     'ply'=>'KGS','qty'=>1661,    'price'=>8.83,   'total'=>14666.63],
    ['desc'=>'Aman Graphics Meter Fabric Yarn Dyed Terry 100% Organic Cotton',     'ply'=>'KGS','qty'=>1662,    'price'=>8.85,   'total'=>14708.70],
    ['desc'=>'Aman Graphics Meter Fabric Yarn Dyed Terry 100% Organic Cotton',     'ply'=>'KGS','qty'=>1662,    'price'=>8.85,   'total'=>14708.70],
    ['desc'=>'Aman Graphics Meter Fabric Yarn Dyed Terry 100% Organic Cotton',     'ply'=>'KGS','qty'=>1661,    'price'=>8.83,   'total'=>14666.63],
    ['desc'=>'Aman Graphics Meter Fabric Yarn Dyed Terry 100% Organic Cotton',     'ply'=>'KGS','qty'=>1662.12, 'price'=>5.4,    'total'=>8975.45],
    ['desc'=>'Aman Graphics Meter Fabric Yarn Dyed Terry 100% Organic Cotton',     'ply'=>'KGS','qty'=>1357.78, 'price'=>5.4,    'total'=>7332.01],
];

// Use PI items if available, else use the hardcoded ERP list
$items = !empty($piPos) ? ($piPos[0]['items'] ?? $erpItems) : $erpItems;
$poNum  = !empty($piPos) ? ($piPos[0]['poNum'] ?? 'NTTTML/AMAN2025-01') : 'NTTTML/AMAN2025-01';
$buyer  = !empty($piPos) ? ($piPos[0]['buyer'] ?? 'A.M INTERNATIONAL') : 'A.M INTERNATIONAL';

$today = date('Y-m-d');

// ── Map ERP items to marketing-intake rows ───────────────────────────────────
$intakeRows = array_map(function($item, $i) {
    return [
        'prodLine'  => 'Offset',
        'itemName'  => $item['desc'],
        'seg2'      => $item['desc'],
        'artSize'   => '',
        'spec1'     => $item['ply'],
        'spec2'     => '',
        'spec3'     => '',
        'spec4'     => '',
        'qty'       => $item['qty'],
        'unit'      => $item['ply'],
        'unitPrc'   => $item['price'],
        'detailExtra'=> ['pn' => $item['desc']],
    ];
}, $items, array_keys($items));

// ── Helper: save one page ────────────────────────────────────────────────────
function savePage(PDO $db, string $orderId, string $page, array $data): void {
    $db->prepare('
        INSERT INTO page_data (order_id, page_name, data)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = CURRENT_TIMESTAMP
    ')->execute([$orderId, $page, json_encode($data)]);
}

$saved = [];

// ── 1. marketing-intake ──────────────────────────────────────────────────────
savePage($db, $orderId, 'marketing-intake', [
    'customer'     => $customer,
    'salesPerson'  => $order['salesperson'] ?? '',
    'intakeDate'   => $today,
    'subject'      => 'Order for ' . $poNum,
    'paperQuality' => '',
    'notes'        => '',
    'pos'          => [[
        'poNum'      => $poNum,
        'endBuyer'   => $buyer,
        'trims'      => $poNum,
        'design'     => '',
        'orderNo'    => $poNum,
        'type'       => 'Export',
        'delivery'   => '',
        'withoutArl' => false,
        'rows'       => $intakeRows,
    ]],
]);
$saved[] = 'marketing-intake';

// Update orders table customer
$db->prepare('UPDATE orders SET customer_name=?, intake_date=?, current_step=? WHERE order_id=?')
   ->execute([$customer, $today, 'marketing-intake', $orderId]);

// ── 2. costing-review ───────────────────────────────────────────────────────
savePage($db, $orderId, 'costing-review', [
    'costingNotes' => '',
    'revisedByRow' => [],
]);
$saved[] = 'costing-review';

// ── 3. sales (PI) ───────────────────────────────────────────────────────────
savePage($db, $orderId, 'sales', [
    'piNum'        => $piNum,
    'piDate'       => $today,
    'customer'     => $customer,
    'productLine'  => '',
    'buyerAddress' => '',
    'consigneeBank'=> '',
    'advisingBank' => '',
    'pos'          => [[
        'salesOrder' => '',
        'poNum'      => $poNum,
        'buyer'      => $buyer,
        'reqDate'    => '',
        'status'     => 'Booked',
        'items'      => $items,
    ]],
]);
$saved[] = 'sales';

// ── 4. marketing ────────────────────────────────────────────────────────────
savePage($db, $orderId, 'marketing', [
    'marketingOwner' => '',
    'requestedDate'  => $today,
    'deliveryDate'   => '',
    'marketingNotes' => '',
]);
$saved[] = 'marketing';

// ── 5. lc ───────────────────────────────────────────────────────────────────
savePage($db, $orderId, 'lc', [
    'lcCheckStatus'        => 'Pending',
    'paymentTerms'         => '',
    'shippingTerms'        => '',
    'lcNumber'             => '',
    'lcDate'               => '',
    'lcReceivedDate'       => '',
    'lcShipDate'           => '',
    'lcExpiryDate'         => '',
    'lcDescription'        => '',
    'lcAmount'             => '',
    'lcIssuingBank'        => '',
    'reimbursementBank'    => '',
    'negotiatingBeneficiaryBank' => '',
    'lcNotes'              => '',
]);
$saved[] = 'lc';

// ── 6. exchange (Bill of Exchange) ──────────────────────────────────────────
savePage($db, $orderId, 'exchange', [
    'salesOrder'                   => '',
    'customerPo'                   => '',
    'buyerName'                    => '',
    'customerName'                 => '',
    'masterLcNo'                   => '',
    'masterLcDate'                 => '',
    'applicantBank'                => '',
    'exchangeAmount'               => '',
    'exchangeDate'                 => '',
    'lcTenorMaster'                => '',
    'payToBankName'                => '',
    'payToBankAddress'             => '',
    'beneficiaryBankAddress'       => '',
    'negotiatingBankAddress'       => '',
    'docSendToBuyerDate'           => '',
    'acceptanceDate'               => '',
    'docSentToNegotiatingBankDate' => '',
    'maturityDate'                 => '',
    'receivedAmount'               => '',
    'receivedDate'                 => '',
    'tenorWordsMaster'             => '',
    'exportSalesContractNo'        => '',
    'exportSalesContractDate'      => '',
    'applicantIrc'                 => '',
    'applicantTin'                 => '',
    'applicantVatBin'              => '',
    'applicantBankBin'             => '',
    'bondLicenseNo'                => '',
    'beneficiaryVatBin'            => '',
    'hsCodeMaster'                 => '',
    'exchangePreviewText'          => '',
    'applicantName'                => '',
    'factoryAddress'               => '',
    'beneficiaryOfficeAddress'     => '',
    'advisingBankAddress'          => '',
    'packingDetailsMaster'         => '',
    'carrierNameMaster'            => '',
]);
$saved[] = 'exchange';

// ── 7. commercial ───────────────────────────────────────────────────────────
savePage($db, $orderId, 'commercial', []);
$saved[] = 'commercial';

// ── 8. packing ──────────────────────────────────────────────────────────────
savePage($db, $orderId, 'packing', []);
$saved[] = 'packing';

// ── 9. delivery ─────────────────────────────────────────────────────────────
savePage($db, $orderId, 'delivery', []);
$saved[] = 'delivery';

// ── 10. truck ───────────────────────────────────────────────────────────────
savePage($db, $orderId, 'truck', []);
$saved[] = 'truck';

// ── 11. origin ──────────────────────────────────────────────────────────────
savePage($db, $orderId, 'origin', []);
$saved[] = 'origin';

// ── 12. beneficiary ─────────────────────────────────────────────────────────
savePage($db, $orderId, 'beneficiary', []);
$saved[] = 'beneficiary';

// ── 13. forwarding ──────────────────────────────────────────────────────────
savePage($db, $orderId, 'forwarding', []);
$saved[] = 'forwarding';

savePage($db, $orderId, 'bank-forwarding', []);
$saved[] = 'bank-forwarding';

// ── Done ─────────────────────────────────────────────────────────────────────
echo json_encode([
    'ok'       => true,
    'order_id' => $orderId,
    'saved'    => $saved,
    'items'    => count($items),
    'poNum'    => $poNum,
    'customer' => $customer,
    'message'  => 'All ' . count($saved) . ' pages seeded. Reload any page with the order loaded to see the data.',
]);
