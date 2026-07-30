<?php
/**
 * api/pi_excel_data.php
 * POST JSON → returns real cell-based XLS (HTML-as-Excel) for Single PI or Summary PI
 */
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

function piH($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_HTML5, 'UTF-8'); }
function piUSD($v) { return '$ ' . number_format((float)$v, 2); }

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) { http_response_code(400); exit('Invalid payload'); }

$type     = $payload['type']     ?? 'single';
$orderId  = $payload['orderId']  ?? 'document';
$piNum    = $payload['piNum']    ?? '';
$piDate   = $payload['piDate']   ?? '';
$buyer    = $payload['buyer']    ?? '';
$custName = $payload['custName'] ?? '';
$custAddr = $payload['custAddr'] ?? '';
$totalQty = (float)($payload['totalQty'] ?? 0);
$totalVal = (float)($payload['totalVal'] ?? 0);
$totalWords = $payload['totalWords'] ?? 'ZERO ONLY.';

$filename = ($type === 'summary' ? 'summary-pi-' : 'single-pi-') . $orderId;

// Bank details
$BANKS = [
    'ncc'  => ['name'=>'National Credit & Commerce Bank Plc.',
                'addr'=>'Motijheel main Branch, 6 Motijheel C/A Dhaka-1000 Bangladesh.',
                'acct'=>'0002-0259000092','swift'=>'NCCLBDDHNBB','routing'=>'160150137'],
    'dbbl' => ['name'=>'Dutch-Bangla Bank Plc.',
                'addr'=>'Local Office, 1, Dilkusha C/A, Dhaka-1000, Bangladesh.',
                'acct'=>'ERQ-101.117.1382','swift'=>'DBBLBDDHCTS','routing'=>'090273889'],
];
$bank = $BANKS[$payload['bank'] ?? 'ncc'] ?? $BANKS['ncc'];

// Terms (Single PI)
$days      = $payload['days']      ?? 'At Sight';
$tolerance = $payload['tolerance'] ?? '5';
$hsCode    = $payload['hsCode']    ?? '4819.10.00';
$docMust   = $payload['docMust']   ?? 'UD';
$terms = [
    "100% Irrevocable confirmed {$days} L/C to be opened in favour of Zaber & Zubair ACC. Ltd.",
    "P.I Validity : 45 Working days.",
    "Letter of Credit to allow acceptability of +/- {$tolerance}% tolerance in quantity and Value.",
    "Letter of Credit to allow for Partial Shipment.",
    "The Buyer should provide a copy of the master L/C and Garment Export UD before the delivery of mentioned goods.",
    "Where GSP certificate is required, applicant is requested to furnish full detail of the Master L/C in BBLC opened in favour of Zaber & Zubair ACC. Ltd.",
    "Prior to delivery- we will inform you full particulars of the consignment and forward the original delivery challan for the signature of the authorised signatory of your organisation. Please make arrangements to hand over the duly signed delivery challan at the time of delivery of goods.",
    "Payment to be made on Maturity in US Dollar and Maturity date will be counted {$days} from the date of DELIVERY Challan / Truck Receipt / This clause Will be integral Parts of L/C.",
    "Interest to be paid at LIBOR by the Buyer till Maturity. If payment is not made within maturity then interest @16% will be charged for overdue period and buyer's is liable to pay. This clause Must be appeared on the L/C",
    "Quality complaint, if any, should be notified to us prior before sewing.",
    "The above mention terms & condition will be the integral part of the BTB L/C & it must be mention in the BTB L/C.",
    "Beneficiary Bin No : 000230256-0103",
    "H.S. Code : {$hsCode}",
    "Total Gross Weight: Kgs",
    "Delivery Terms: CPT",
    "{$docMust} Mustbe",
    "Advising Bank : {$bank['name']}\n        {$bank['addr']}\n        Account No: {$bank['acct']}  |  Swift Code: {$bank['swift']}  |  Bank Routing No: {$bank['routing']}",
];

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '.xls"');
header('Cache-Control: no-store, no-cache, must-revalidate');

// Reusable styles
$hdrStyle  = 'background-color:#1a3a6e;color:white;font-weight:bold;text-align:center;border:1px solid #1a3a6e;padding:8px 12px;';
$cellStyle = 'border:1px solid #7a7a7a;padding:7px 12px;';
$noBorder  = 'border:none;padding:5px 2px;';
$footStyle = 'border:1px solid #000;text-align:center;font-size:9pt;padding:4px 8px;';

// Shared header / footer rows
function piHeaderRows($piNum, $piDate, $buyer, $custName, $custAddr, $title, $noBorder) {
    $sp    = '<tr height="6"><td colspan="6" style="border:none;"></td></tr>';
    $rows  = '<tr height="30"><td colspan="6" style="' . $noBorder . 'font-size:20pt;font-weight:bold;color:#1a3a6e;padding-bottom:2px;">Z &amp; Z ZZAL</td></tr>';
    $rows .= '<tr height="26"><td colspan="6" style="' . $noBorder . 'font-size:15pt;font-weight:bold;color:#1a3a6e;">Zaber &amp; Zubair Accessories Ltd.</td></tr>';
    $rows .= $sp;
    $rows .= '<tr height="28"><td colspan="6" style="font-size:13pt;font-weight:bold;letter-spacing:4pt;text-align:center;border-top:2px solid #1a3a6e;border-bottom:2px solid #1a3a6e;border-left:none;border-right:none;padding:7px 0;">' . piH($title) . '</td></tr>';
    $rows .= $sp;
    $rows .= '<tr height="20"><td colspan="4" style="' . $noBorder . '">' . piH("PROFOMA INVOICE NO : $piNum") . '</td><td colspan="2" style="' . $noBorder . 'text-align:right;">' . piH("Date : $piDate") . '</td></tr>';
    $rows .= '<tr height="20"><td colspan="6" style="' . $noBorder . '">' . piH("BUYER: $buyer") . '</td></tr>';
    $rows .= '<tr height="18"><td colspan="6" style="' . $noBorder . 'font-weight:bold;">TO</td></tr>';
    $rows .= '<tr height="20"><td colspan="6" style="' . $noBorder . 'font-weight:bold;">' . piH($custName) . '</td></tr>';
    if ($custAddr) $rows .= '<tr height="18"><td colspan="6" style="' . $noBorder . '">' . piH($custAddr) . '</td></tr>';
    $rows .= $sp;
    $rows .= '<tr height="20"><td colspan="6" style="' . $noBorder . '">WE CONFIRM HAVING SOLD TO YOU THE FOLLOWING MERCHANDISE AS PER TERMS AND CONDITION STATED BELOW.</td></tr>';
    $rows .= $sp;
    return $rows;
}

function piFooterRows($totalQty, $totalVal, $totalWords, $terms, $type, $cellStyle, $noBorder, $footStyle) {
    $sp    = '<tr height="6"><td colspan="6" style="border:none;"></td></tr>';
    $rows  = '<tr height="22">';
    $rows .= '<td colspan="3" style="' . $cellStyle . 'border-top:2px solid #1a3a6e;font-weight:bold;"></td>';
    $rows .= '<td style="' . $cellStyle . 'border-top:2px solid #1a3a6e;text-align:center;font-weight:bold;">' . number_format($totalQty) . '</td>';
    $rows .= '<td style="' . $cellStyle . 'border-top:2px solid #1a3a6e;"></td>';
    $rows .= '<td style="' . $cellStyle . 'border-top:2px solid #1a3a6e;text-align:right;font-weight:bold;">' . piUSD($totalVal) . '</td>';
    $rows .= '</tr>';
    $rows .= $sp;
    $rows .= '<tr height="22"><td colspan="6" style="' . $noBorder . 'font-weight:bold;border-top:1px dashed #333;border-bottom:1px dashed #333;padding:5px 2px;">' . piH("TOTAL AMOUNT : US DOLLER: $totalWords") . '</td></tr>';
    $rows .= $sp;

    if ($type === 'single') {
        $rows .= '<tr height="20"><td colspan="6" style="' . $noBorder . 'font-weight:bold;text-decoration:underline;">Terms &amp; Conditions:</td></tr>';
        $rows .= $sp;
        foreach ($terms as $i => $term) {
            $rows .= '<tr height="18"><td colspan="6" style="' . $noBorder . '">' . ($i+1) . '. ' . nl2br(piH($term)) . '</td></tr>';
        }
        $rows .= $sp;
    }

    $rows .= '<tr height="80">';
    $rows .= '<td colspan="3" style="' . $noBorder . 'vertical-align:bottom;font-weight:bold;">SIGNATURE OF BUYER</td>';
    $rows .= '<td colspan="3" style="' . $noBorder . 'vertical-align:bottom;font-weight:bold;text-align:right;">SIGNATURE OF SELLER</td>';
    $rows .= '</tr>';
    $rows .= $sp;
    $rows .= '<tr height="18"><td colspan="6" style="' . $footStyle . 'padding:5px 8px;">Corporate Office: Adamjee Court (4th &amp; 5th Floor), 115-120, motijheel C/A, Dhaka-1000, Bangladesh.</td></tr>';
    $rows .= '<tr height="18"><td colspan="6" style="' . $footStyle . 'padding:5px 8px;">Phone: +880-2-7176207-8, 7176356, 71766348, &nbsp;Fax: +880-2-9564252, 9565282, 7167293. &nbsp;Web: www.znzfab.com</td></tr>';
    $rows .= '<tr height="18"><td colspan="6" style="' . $footStyle . 'padding:5px 8px;">Factory: Mawna, Sreepur, Gazipur. &nbsp;E-mail: znzal@znzfab.com</td></tr>';
    return $rows;
}

echo '<!DOCTYPE html><html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="UTF-8"></head><body>';

echo '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse:collapse;font-family:Times New Roman,Times,serif;font-size:11pt;">';

// ── Single PI ──────────────────────────────────────────────────────────────
if ($type === 'single') {
    $orderRef = $payload['orderRef'] ?? '';
    $poNum    = $payload['poNum']    ?? '';
    $style    = $payload['style']    ?? '';
    $items    = $payload['items']    ?? [];

    echo piHeaderRows($piNum, $piDate, $buyer, $custName, $custAddr, 'PROFORMA     INVOICE', $noBorder);

    // Table header
    echo '<tr>';
    echo '<td style="' . $hdrStyle . 'width:45px;">SL NO</td>';
    echo '<td style="' . $hdrStyle . '">Description of goods</td>';
    echo '<td style="' . $hdrStyle . 'width:60px;">PLY</td>';
    echo '<td style="' . $hdrStyle . 'width:90px;">Quantity/</td>';
    echo '<td style="' . $hdrStyle . 'width:100px;">Unit Price</td>';
    echo '<td style="' . $hdrStyle . 'width:120px;">Total Amount</td>';
    echo '</tr>';
    echo '<tr>';
    foreach (['', '', '', 'Pcs/con', '', '(USD)'] as $h) {
        echo '<td style="' . $hdrStyle . '">' . $h . '</td>';
    }
    echo '</tr>';

    // ORDER REF row
    if ($orderRef || $poNum) {
        $ref = ($orderRef ? 'ORDER REF: ' . piH($orderRef) : '');
        if ($orderRef && $poNum) $ref .= '&nbsp;&nbsp;&nbsp;';
        $ref .= ($poNum ? 'PO # ' . piH($poNum) : '');
        if ($style) $ref .= '&nbsp;&nbsp; Style# ' . piH($style);
        echo '<tr height="20"><td style="' . $noBorder . '"></td><td colspan="5" style="' . $noBorder . 'font-weight:bold;">' . $ref . '</td></tr>';
    }

    // Item rows
    if (empty($items)) {
        echo '<tr><td colspan="6" style="text-align:center;color:#999;padding:12px;">No items saved yet.</td></tr>';
    } else {
        foreach ($items as $item) {
            $qty = (float)($item['qty'] ?? 0);
            $prc = (float)($item['prc'] ?? 0);
            $tot = (float)($item['tot'] ?? 0);
            echo '<tr>';
            echo '<td style="' . $cellStyle . 'text-align:center;">' . (int)($item['sl'] ?? 0) . '</td>';
            echo '<td style="' . $cellStyle . '">' . piH($item['desc'] ?? '') . '</td>';
            echo '<td style="' . $cellStyle . 'text-align:center;">' . piH($item['ply'] ?? '') . '</td>';
            echo '<td style="' . $cellStyle . 'text-align:center;">' . number_format($qty) . '</td>';
            echo '<td style="' . $cellStyle . 'text-align:right;">' . ($prc ? piUSD($prc) : '—') . '</td>';
            echo '<td style="' . $cellStyle . 'text-align:right;">' . ($tot ? piUSD($tot) : '—') . '</td>';
            echo '</tr>';
        }
    }

    echo piFooterRows($totalQty, $totalVal, $totalWords, $terms, 'single', $cellStyle, $noBorder, $footStyle);
}

// ── Summary PI ─────────────────────────────────────────────────────────────
elseif ($type === 'summary') {
    $piRows = $payload['piRows'] ?? [];

    echo piHeaderRows($piNum, $piDate, $buyer, $custName, $custAddr, 'PROFORMA   INVOICE   SUMMARY', $noBorder);

    // Table header
    echo '<tr>';
    echo '<td style="' . $hdrStyle . 'width:80px;">PI NO</td>';
    echo '<td style="' . $hdrStyle . '">Description of goods</td>';
    echo '<td style="' . $hdrStyle . 'width:60px;">PLY</td>';
    echo '<td style="' . $hdrStyle . 'width:90px;">Quantity/</td>';
    echo '<td style="' . $hdrStyle . 'width:100px;">Unit Price</td>';
    echo '<td style="' . $hdrStyle . 'width:120px;">Total Amount</td>';
    echo '</tr>';
    echo '<tr>';
    foreach (['', '', '', 'Pcs/con', '', '(USD)'] as $h) {
        echo '<td style="' . $hdrStyle . '">' . $h . '</td>';
    }
    echo '</tr>';

    foreach ($piRows as $piRow) {
        $shortNum  = piH($piRow['shortNum']  ?? '');
        $orderRef  = piH($piRow['orderRef']  ?? '');
        $rowPoNum  = piH($piRow['poNum']     ?? '');
        $rowStyle  = piH($piRow['style']     ?? '');
        $rowItems  = $piRow['items'] ?? [];
        $firstItem = true;

        // Ref row
        $refParts = array_filter([$orderRef ? 'ORDER REF: ' . $orderRef : '', $rowPoNum ? 'PO # ' . $rowPoNum : '']);
        if ($refParts) {
            echo '<tr class="ref-row">';
            echo '<td style="' . $cellStyle . 'font-weight:bold;vertical-align:top;">' . $shortNum . '</td>';
            echo '<td colspan="5" style="' . $cellStyle . 'font-weight:bold;">' . implode('<br>', $refParts) . '</td>';
            echo '</tr>';
            $firstItem = false;
        }

        foreach ($rowItems as $item) {
            $qty = (float)($item['qty'] ?? 0);
            $prc = (float)($item['prc'] ?? $item['price'] ?? $item['unitPrice'] ?? 0);
            $tot = (float)($item['tot'] ?? $item['total'] ?? ($qty * $prc));
            echo '<tr>';
            echo '<td style="' . $cellStyle . '">' . ($firstItem ? $shortNum : '') . '</td>';
            echo '<td style="' . $cellStyle . '">' . piH($item['desc'] ?? $item['itemName'] ?? '') . '</td>';
            echo '<td style="' . $cellStyle . 'text-align:center;">' . piH($item['ply'] ?? '') . '</td>';
            echo '<td style="' . $cellStyle . 'text-align:center;">' . number_format($qty) . '</td>';
            echo '<td style="' . $cellStyle . 'text-align:right;">' . ($prc ? piUSD($prc) : '—') . '</td>';
            echo '<td style="' . $cellStyle . 'text-align:right;">' . ($tot ? piUSD($tot) : '—') . '</td>';
            echo '</tr>';
            $firstItem = false;
        }
    }

    echo piFooterRows($totalQty, $totalVal, $totalWords, [], 'summary', $cellStyle, $noBorder, $footStyle);
}

echo '</table></body></html>';
