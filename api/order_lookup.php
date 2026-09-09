<?php
/**
 * api/order_lookup.php
 *
 * GET  ?id=ORD-2026-0001  → full order snapshot (orders + page_data + pis)
 * POST (no body)           → create new order, returns { order_id }
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/erp_order_inbox.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();
    $orderColumns = null;
    $getOrderColumns = static function () use ($db, &$orderColumns): array {
        if ($orderColumns !== null) {
            return $orderColumns;
        }
        $orderColumns = [];
        foreach ($db->query("SHOW COLUMNS FROM orders")->fetchAll() as $col) {
            if (!empty($col['Field'])) {
                $orderColumns[$col['Field']] = true;
            }
        }
        return $orderColumns;
    };

    // ── POST — create new order ───────────────────────────────────────────────
    if ($method === 'POST') {
        // Optional starting step (e.g. 'sales' to start directly from PI)
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $startStep = $input['step'] ?? ($_GET['step'] ?? 'marketing-intake');
        if (!in_array($startStep, ['marketing-intake', 'sales'], true)) {
            $startStep = 'marketing-intake';
        }

        // Generate the next monthly ID across both real orders and IDs already
        // claimed in the ERP inbox. COUNT(*) can reuse a number after gaps and
        // does not see inbox claims, causing uq_erp_order_inbox_work_order 1062.
        $yearMonth = date('Y-m');
        $prefix = "ORD-{$yearMonth}-";
        ensureErpOrderInboxTable($db);

        $stmt = $db->prepare('SELECT order_id FROM orders WHERE order_id LIKE ? ORDER BY order_id DESC LIMIT 1');
        $stmt->execute([$prefix . '%']);
        $lastOrderNum = (int)substr((string)($stmt->fetchColumn() ?: ''), strlen($prefix));

        $stmt = $db->prepare('SELECT work_order_id FROM erp_order_inbox WHERE work_order_id LIKE ? ORDER BY work_order_id DESC LIMIT 1');
        $stmt->execute([$prefix . '%']);
        $lastInboxNum = (int)substr((string)($stmt->fetchColumn() ?: ''), strlen($prefix));

        $orderId = sprintf('%s%05d', $prefix, max($lastOrderNum, $lastInboxNum) + 1);

        // Stamp who created the order
        $me     = currentUser();
        $byId   = $me['id']   ?? null;
        $byName = $me['name'] ?? null;

        $cols = $getOrderColumns();
        $insertCols = ['order_id', 'current_step'];
        $insertVals = [$orderId, $startStep];
        if (isset($cols['created_by_id'])) {
            $insertCols[] = 'created_by_id';
            $insertVals[] = $byId;
        }
        if (isset($cols['created_by_name'])) {
            $insertCols[] = 'created_by_name';
            $insertVals[] = $byName;
        }
        $placeholders = implode(', ', array_fill(0, count($insertCols), '?'));
        $sql = 'INSERT INTO orders (' . implode(', ', $insertCols) . ') VALUES (' . $placeholders . ')';
        $db->prepare($sql)->execute($insertVals);

        echo json_encode(['ok' => true, 'order_id' => $orderId, 'current_step' => $startStep, 'created_by' => $byName]);
        exit;
    }

    // ── GET — look up full order ─────────────────────────────────────────────
    if ($method === 'GET') {
        $id = trim($_GET['id'] ?? '');
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'id required']); exit; }

        // New PI records link directly to their Work Order. The legacy Sales
        // snapshot fallback below keeps older PI numbers searchable as well.
        $hasMigCols = false;
        try { $db->query('SELECT order_id FROM pis LIMIT 0'); $hasMigCols = true; } catch (PDOException $_) {}
        $matchedBy = 'work_order';

        // Search by full match first, then partial
        $stmt = $db->prepare("SELECT * FROM orders WHERE order_id = ? LIMIT 1");
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        if (!$order) {
            $stmt = $db->prepare("SELECT * FROM orders WHERE order_id LIKE ? LIMIT 1");
            $stmt->execute(['%' . $id . '%']);
            $order = $stmt->fetch();
        }

        // Not a Work Order ID: resolve the value as a PI number. Exact match is
        // preferred; partial PI numbers are accepted for convenient searching.
        if (!$order && $hasMigCols) {
            $stmt = $db->prepare("SELECT order_id FROM pis WHERE pi_number = ? AND order_id IS NOT NULL AND order_id <> '' ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$id]);
            $piOrderId = trim((string)($stmt->fetchColumn() ?: ''));
            if ($piOrderId === '') {
                $stmt = $db->prepare("SELECT order_id FROM pis WHERE pi_number LIKE ? AND order_id IS NOT NULL AND order_id <> '' ORDER BY created_at DESC LIMIT 1");
                $stmt->execute(['%' . $id . '%']);
                $piOrderId = trim((string)($stmt->fetchColumn() ?: ''));
            }
            if ($piOrderId !== '') {
                $stmt = $db->prepare("SELECT * FROM orders WHERE order_id = ? LIMIT 1");
                $stmt->execute([$piOrderId]);
                $order = $stmt->fetch();
                if ($order) $matchedBy = 'pi_number';
            }
        }

        if (!$order) {
            $stmt = $db->prepare("SELECT o.* FROM orders o JOIN page_data p ON p.order_id = o.order_id WHERE p.page_name = 'sales' AND p.data LIKE ? ORDER BY o.updated_at DESC LIMIT 1");
            $stmt->execute(['%' . $id . '%']);
            $order = $stmt->fetch();
            if ($order) $matchedBy = 'pi_number';
        }

        if (!$order) {
            echo json_encode(['found' => false]);
            exit;
        }

        // Fetch all page_data blobs for this order
        $stmt = $db->prepare("SELECT page_name, data FROM page_data WHERE order_id = ?");
        $stmt->execute([$order['order_id']]);
        $pages = [];
        foreach ($stmt->fetchAll() as $row) {
            $pages[$row['page_name']] = json_decode($row['data'], true) ?? [];
        }

        // Fetch linked PIs
        $pis = [];
        if ($hasMigCols) {
            $stmt = $db->prepare("SELECT * FROM pis WHERE order_id = ? ORDER BY is_master ASC, created_at DESC");
            $stmt->execute([$order['order_id']]);
            $pis = $stmt->fetchAll();
        }
        if (!$pis) {
            $stmt = $db->prepare("SELECT * FROM pis WHERE pi_number LIKE ? ORDER BY created_at DESC");
            $stmt->execute(['%' . $order['order_id'] . '%']);
            $pis = $stmt->fetchAll();
        }

        // Commercial can attach already-created PIs from other Work Orders to
        // this LC. Keep the PI's original order_id intact; the LC snapshot only
        // stores lightweight references and this response merges those records.
        foreach ($pis as &$ownedPi) {
            $ownedPi['linked_via_lc'] = 0;
        }
        unset($ownedPi);

        $lcIncludedRaw = $pages['lc']['lcIncludedPis'] ?? [];
        if (is_string($lcIncludedRaw)) {
            $lcIncludedRaw = json_decode($lcIncludedRaw, true) ?: [];
        }
        if (is_array($lcIncludedRaw) && $lcIncludedRaw) {
            $extraIds = [];
            $extraNumbers = [];
            foreach ($lcIncludedRaw as $ref) {
                if (is_array($ref)) {
                    $refId = (int)($ref['id'] ?? 0);
                    $refNumber = trim((string)($ref['pi_number'] ?? ''));
                } else {
                    $refId = is_numeric($ref) ? (int)$ref : 0;
                    $refNumber = $refId ? '' : trim((string)$ref);
                }
                if ($refId > 0) $extraIds[$refId] = $refId;
                if ($refNumber !== '') $extraNumbers[strtolower($refNumber)] = $refNumber;
            }

            $where = [];
            $params = [];
            if ($extraIds) {
                $where[] = 'id IN (' . implode(',', array_fill(0, count($extraIds), '?')) . ')';
                array_push($params, ...array_values($extraIds));
            }
            if ($extraNumbers) {
                $where[] = 'pi_number IN (' . implode(',', array_fill(0, count($extraNumbers), '?')) . ')';
                array_push($params, ...array_values($extraNumbers));
            }

            if ($where) {
                $stmt = $db->prepare('SELECT * FROM pis WHERE (' . implode(' OR ', $where) . ') ORDER BY created_at DESC');
                $stmt->execute($params);
                $extraRows = $stmt->fetchAll();

                // Selecting any individual PI means selecting the individual PI
                // set belonging to that PI's original Work Order. This ensures
                // that a Work Order with PI-1 and PI-2 always shows both in LC.
                if ($hasMigCols && $extraRows) {
                    $relatedOrderIds = [];
                    foreach ($extraRows as $extraRow) {
                        $relatedOrderId = trim((string)($extraRow['order_id'] ?? ''));
                        if ($relatedOrderId !== '') $relatedOrderIds[$relatedOrderId] = $relatedOrderId;
                    }
                    if ($relatedOrderIds) {
                        $relatedStmt = $db->prepare(
                            'SELECT * FROM pis WHERE order_id IN (' .
                            implode(',', array_fill(0, count($relatedOrderIds), '?')) .
                            ') ORDER BY created_at ASC'
                        );
                        $relatedStmt->execute(array_values($relatedOrderIds));
                        $extraRows = $relatedStmt->fetchAll();
                    }
                }
                $existingPiIds = [];
                $existingPiNumbers = [];
                foreach ($pis as $pi) {
                    $existingPiIds[(int)($pi['id'] ?? 0)] = true;
                    $existingPiNumbers[strtolower(trim((string)($pi['pi_number'] ?? '')))] = true;
                }
                foreach ($extraRows as $extraPi) {
                    $extraId = (int)($extraPi['id'] ?? 0);
                    $extraNumberKey = strtolower(trim((string)($extraPi['pi_number'] ?? '')));
                    if (isset($existingPiIds[$extraId]) || ($extraNumberKey !== '' && isset($existingPiNumbers[$extraNumberKey]))) {
                        continue;
                    }
                    $extraPi['linked_via_lc'] = 1;
                    $pis[] = $extraPi;
                    $existingPiIds[$extraId] = true;
                    if ($extraNumberKey !== '') $existingPiNumbers[$extraNumberKey] = true;
                }
            }
        }
        foreach ($pis as &$pi) {
            $pi['pos']          = json_decode($pi['pos'],          true) ?? [];
            $pi['included_pis'] = json_decode($pi['included_pis'] ?? 'null', true) ?? [];
        }

        echo json_encode([
            'found'   => true,
            'order'   => $order,
            'pages'   => $pages,
            'pis'     => $pis,
            'matched_by' => $matchedBy,
            'lookup_value' => $id,
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
