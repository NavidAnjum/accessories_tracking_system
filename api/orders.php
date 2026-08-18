<?php
/**
 * api/orders.php — REST endpoint for orders
 *
 * GET  (no params)          → all orders ordered by created_at DESC
 * GET  ?id=ZNZ000001        → single order + items array
 * POST (JSON body)          → upsert order + replace items
 * PUT  ?id=X&step=Y         → update current_step only
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/notifications.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    // ── GET ───────────────────────────────────────────────────────────────────
    if ($method === 'GET') {
        $id = $_GET['id'] ?? null;

        if ($id) {
            // Single order + items
            $stmt = $db->prepare('SELECT * FROM orders WHERE order_id = ?');
            $stmt->execute([$id]);
            $order = $stmt->fetch();
            if (!$order) {
                http_response_code(404);
                echo json_encode(['error' => 'Order not found']);
                exit;
            }
            $stmt2 = $db->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY sl_no');
            $stmt2->execute([$id]);
            $order['items'] = $stmt2->fetchAll();
            echo json_encode($order);
        } elseif (!empty($_GET['last'])) {
            // Most recently updated order (for auto-load on page open)
            $stmt = $db->query('SELECT order_id FROM orders ORDER BY updated_at DESC, created_at DESC LIMIT 1');
            $row  = $stmt->fetch();
            echo json_encode($row ?: null);
        } else {
            // All orders
            $stmt   = $db->query('SELECT * FROM orders ORDER BY created_at DESC');
            $orders = $stmt->fetchAll();

            // Enrich with data from the pis table — for PI-created orders the customer,
            // PO numbers, quantities and item counts live in `pis`, not the orders row.
            $piRows = $db->query('SELECT order_id, customer, pos, grand_qty, grand_val FROM pis WHERE order_id IS NOT NULL')->fetchAll();
            $agg = [];
            foreach ($piRows as $pi) {
                $oid = $pi['order_id'];
                if (!isset($agg[$oid])) $agg[$oid] = ['customer'=>'','pos'=>[],'buyer'=>'','qty'=>0,'val'=>0,'items'=>0];
                if (!$agg[$oid]['customer'] && trim($pi['customer'] ?? '')) $agg[$oid]['customer'] = trim($pi['customer']);
                $agg[$oid]['qty'] += (float)($pi['grand_qty'] ?? 0);
                $agg[$oid]['val'] += (float)($pi['grand_val'] ?? 0);
                foreach ((json_decode($pi['pos'] ?? '[]', true) ?: []) as $po) {
                    if (!empty($po['poNum'])) $agg[$oid]['pos'][$po['poNum']] = true;
                    if (!$agg[$oid]['buyer'] && !empty($po['buyer'])) $agg[$oid]['buyer'] = $po['buyer'];
                    if (is_array($po['items'] ?? null)) $agg[$oid]['items'] += count($po['items']);
                }
            }
            foreach ($orders as &$o) {
                $a = $agg[$o['order_id']] ?? null;
                if ($a) {
                    if (empty($o['customer_name']) && $a['customer']) $o['customer_name'] = $a['customer'];
                    if (empty($o['po_number'])    && $a['pos'])      $o['po_number']     = implode(', ', array_keys($a['pos']));
                    if (empty($o['to_buyer'])     && $a['buyer'])     $o['to_buyer']      = $a['buyer'];
                }
                $o['total_qty']  = $a['qty']   ?? 0;
                $o['total_val']  = $a['val']   ?? 0;
                $o['item_count'] = $a['items'] ?? 0;
            }
            unset($o);
            echo json_encode($orders);
        }
        exit;
    }

    // ── PUT — update step only ────────────────────────────────────────────────
    if ($method === 'PUT') {
        $id   = $_GET['id']   ?? null;
        $step = $_GET['step'] ?? null;
        if (!$id || !$step) {
            http_response_code(400);
            echo json_encode(['error' => 'id and step are required']);
            exit;
        }
        $curStmt = $db->prepare('SELECT current_step FROM orders WHERE order_id = ?');
        $curStmt->execute([$id]);
        $current = $curStmt->fetchColumn();

        // Optional order fields — only overwrite when a non-empty value is supplied,
        // so a plain step change never blanks out existing details.
        $sets   = ['current_step = ?', 'updated_at = CURRENT_TIMESTAMP'];
        $params = [$step];
        foreach (['customer' => 'customer_name', 'buyer' => 'to_buyer', 'po' => 'po_number', 'salesperson' => 'salesperson'] as $qkey => $col) {
            $val = isset($_GET[$qkey]) ? trim($_GET[$qkey]) : '';
            if ($val !== '') {
                $sets[]   = "$col = ?";
                $params[] = $val;
            }
        }
        $params[] = $id;
        $stmt = $db->prepare('UPDATE orders SET ' . implode(', ', $sets) . ' WHERE order_id = ?');
        $stmt->execute($params);

        if ($current !== $step) {
            createStepNotifications($db, $id, $step, (int)(currentUser()['id'] ?? 0) ?: null);
        }
        echo json_encode(['ok' => true, 'order_id' => $id, 'current_step' => $step]);
        exit;
    }

    // ── POST — upsert order + items ───────────────────────────────────────────
    if ($method === 'DELETE') {
        $id = $_GET['order_id'] ?? $_GET['id'] ?? null;
        $id = is_string($id) ? trim($id) : '';
        if ($id === '') {
            http_response_code(400);
            echo json_encode(['error' => 'order_id or id is required']);
            exit;
        }

        $exists = $db->prepare('SELECT order_id FROM orders WHERE order_id = ?');
        $exists->execute([$id]);
        if (!$exists->fetchColumn()) {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
            exit;
        }

        $db->beginTransaction();
        try {
            $db->prepare('DELETE FROM notifications WHERE order_id = ?')->execute([$id]);
            $db->prepare('DELETE FROM page_data WHERE order_id = ?')->execute([$id]);
            $db->prepare('DELETE FROM pis WHERE order_id = ?')->execute([$id]);
            $db->prepare('DELETE FROM order_items WHERE order_id = ?')->execute([$id]);
            $db->prepare('DELETE FROM orders WHERE order_id = ?')->execute([$id]);
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }

        echo json_encode(['ok' => true, 'order_id' => $id]);
        exit;
    }

    if ($method === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON body']);
            exit;
        }

        $orderId      = trim($body['orderId']      ?? '');
        $customerName = trim($body['customer']     ?? '');
        $salesperson  = trim($body['salesperson']  ?? '');
        $intakeDate   = $body['date']              ?? null;
        $poNumber     = trim($body['po']           ?? '');
        $trimsIpo     = trim($body['trims']        ?? '');
        $withoutArl   = !empty($body['withoutArl']) ? 1 : 0;
        $toBuyer      = trim($body['buyer']        ?? '');
        $subDesc      = trim($body['sub']          ?? '');
        $paperQuality = trim($body['paperQuality'] ?? '');
        $buyerEndBuyer= trim($body['buyerEndBuyer']?? '');
        $design       = trim($body['design']       ?? '');
        $orderNo      = trim($body['orderNo']      ?? '');
        $orderType    = trim($body['type']         ?? '');
        $deliveryDate = $body['deliveryDate']      ?? null;
        $notes        = trim($body['notes']        ?? '');
        $step         = trim($body['step']         ?? 'marketing-intake');
        $rows         = $body['rows']              ?? [];

        if (!$orderId) {
            http_response_code(400);
            echo json_encode(['error' => 'orderId is required']);
            exit;
        }

        // Normalise empty date strings to null
        $intakeDate   = ($intakeDate   && $intakeDate   !== '') ? $intakeDate   : null;
        $deliveryDate = ($deliveryDate && $deliveryDate !== '') ? $deliveryDate : null;

        $existingStmt = $db->prepare('SELECT current_step FROM orders WHERE order_id = ?');
        $existingStmt->execute([$orderId]);
        $existingStep = $existingStmt->fetchColumn();

        // Upsert orders
        $sql = '
            INSERT INTO orders
                (order_id, customer_name, salesperson, intake_date, po_number, trims_ipo,
                 without_arl, to_buyer, sub_description, paper_quality, buyer_end_buyer,
                 design, order_no, order_type, delivery_date, notes, current_step)
            VALUES
                (:order_id, :customer_name, :salesperson, :intake_date, :po_number, :trims_ipo,
                 :without_arl, :to_buyer, :sub_description, :paper_quality, :buyer_end_buyer,
                 :design, :order_no, :order_type, :delivery_date, :notes, :current_step)
            ON DUPLICATE KEY UPDATE
                customer_name   = VALUES(customer_name),
                salesperson     = VALUES(salesperson),
                intake_date     = VALUES(intake_date),
                po_number       = VALUES(po_number),
                trims_ipo       = VALUES(trims_ipo),
                without_arl     = VALUES(without_arl),
                to_buyer        = VALUES(to_buyer),
                sub_description = VALUES(sub_description),
                paper_quality   = VALUES(paper_quality),
                buyer_end_buyer = VALUES(buyer_end_buyer),
                design          = VALUES(design),
                order_no        = VALUES(order_no),
                order_type      = VALUES(order_type),
                delivery_date   = VALUES(delivery_date),
                notes           = VALUES(notes),
                current_step    = VALUES(current_step),
                updated_at      = CURRENT_TIMESTAMP
        ';

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':order_id'        => $orderId,
            ':customer_name'   => $customerName,
            ':salesperson'     => $salesperson,
            ':intake_date'     => $intakeDate,
            ':po_number'       => $poNumber,
            ':trims_ipo'       => $trimsIpo,
            ':without_arl'     => $withoutArl,
            ':to_buyer'        => $toBuyer,
            ':sub_description' => $subDesc,
            ':paper_quality'   => $paperQuality,
            ':buyer_end_buyer' => $buyerEndBuyer,
            ':design'          => $design,
            ':order_no'        => $orderNo,
            ':order_type'      => $orderType,
            ':delivery_date'   => $deliveryDate,
            ':notes'           => $notes,
            ':current_step'    => $step,
        ]);

        // Replace items — delete old, insert new
        $db->prepare('DELETE FROM order_items WHERE order_id = ?')->execute([$orderId]);

        if (!empty($rows)) {
            $itemSql = '
                INSERT INTO order_items
                    (order_id, sl_no, product_line, item_name, art_size, grade,
                     paper_combination, qty, unit, unit_price, amount)
                VALUES
                    (:order_id, :sl_no, :product_line, :item_name, :art_size, :grade,
                     :paper_combination, :qty, :unit, :unit_price, :amount)
            ';
            $itemStmt = $db->prepare($itemSql);
            foreach ($rows as $i => $row) {
                $qty       = is_numeric($row['qty'] ?? '')       ? (float)$row['qty']       : 0;
                $unitPrice = is_numeric($row['unitPrice'] ?? '') ? (float)$row['unitPrice'] : 0;
                $amount    = is_numeric($row['amount'] ?? '')    ? (float)$row['amount']    : ($qty * $unitPrice);
                $itemStmt->execute([
                    ':order_id'         => $orderId,
                    ':sl_no'            => $i + 1,
                    ':product_line'     => $row['productLine']     ?? '',
                    ':item_name'        => $row['item']            ?? ($row['itemName'] ?? ''),
                    ':art_size'         => $row['artSize']         ?? '',
                    ':grade'            => $row['grade']           ?? '',
                    ':paper_combination'=> $row['paperCombination']?? '',
                    ':qty'              => $qty,
                    ':unit'             => $row['unit']            ?? '',
                    ':unit_price'       => $unitPrice,
                    ':amount'           => $amount,
                ]);
            }
        }

        if ($existingStep === false || $existingStep !== $step) {
            createStepNotifications($db, $orderId, $step, (int)(currentUser()['id'] ?? 0) ?: null);
        }

        echo json_encode(['ok' => true, 'order_id' => $orderId]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
