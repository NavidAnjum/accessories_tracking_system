<?php
/**
 * Remove one or more ERP sales orders from a work order (they were added wrongly).
 *
 * POST JSON: { work_order_id: "ORD-…", sale_order_nos: ["2600039034", …] }
 *
 * For each ERP number:
 *   1. Unlink it in erp_order_inbox (work_order_id → NULL) so it becomes reusable.
 *   2. Strip it from every PI's `pos[].salesOrder` for that work order; if a PO
 *      block was ONLY that ERP order (its salesOrder becomes empty), drop the
 *      whole PO block (its items came from that ERP order).
 *   3. Do the same to the `sales` page_data snapshot.
 * A PI whose PO blocks all disappear is deleted.
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/erp_order_inbox.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$me = currentUser();
$role = (string)($me['role'] ?? '');
if (!canManageErpOrderInbox($role)) {
    http_response_code(403);
    echo json_encode(['error' => 'Commercial access is required.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true) ?: [];
$workOrderId = trim((string)($body['work_order_id'] ?? ''));
$rawNumbers  = $body['sale_order_nos'] ?? ($body['sale_order_no'] ?? []);
if (!is_array($rawNumbers)) $rawNumbers = [$rawNumbers];

$targets = [];
foreach ($rawNumbers as $n) {
    $n = trim((string)$n);
    if ($n !== '') $targets[$n] = true;
}
$targets = array_keys($targets);

if ($workOrderId === '' || !$targets) {
    http_response_code(400);
    echo json_encode(['error' => 'work_order_id and at least one sale_order_no are required.']);
    exit;
}

/** Strip target ERP numbers from a decoded pos[] array; return [newPos, changed]. */
function erpStripPos(array $pos, array $targets): array
{
    $out = [];
    $changed = false;
    foreach ($pos as $po) {
        if (!is_array($po)) { $out[] = $po; continue; }

        // Collect this PO block's sales order numbers (array or comma string).
        $nums = [];
        if (!empty($po['salesOrders']) && is_array($po['salesOrders'])) {
            $nums = $po['salesOrders'];
        } elseif (isset($po['salesOrder'])) {
            $nums = preg_split('/\s*,\s*/', (string)$po['salesOrder'], -1, PREG_SPLIT_NO_EMPTY);
        }
        $nums = array_values(array_map('trim', $nums));

        $kept = array_values(array_filter($nums, static fn($v) => !in_array($v, $targets, true)));

        if (count($kept) !== count($nums)) {
            $changed = true;
            if (!$kept) {
                // This PO block was only the removed ERP order → drop it entirely.
                continue;
            }
            $po['salesOrder'] = implode(', ', $kept);
            if (isset($po['salesOrders'])) $po['salesOrders'] = $kept;
        }
        $out[] = $po;
    }
    return [$out, $changed];
}

try {
    $db = getDB();
    ensureErpOrderInboxTable($db);
    $db->beginTransaction();

    // 1. Unlink in the inbox (only rows owned by THIS work order).
    $in = implode(',', array_fill(0, count($targets), '?'));
    $unlink = $db->prepare("UPDATE erp_order_inbox
                            SET work_order_id = NULL, converted_by_id = NULL, converted_at = NULL
                            WHERE sale_order_no IN ($in) AND work_order_id = ?");
    $unlink->execute([...$targets, $workOrderId]);
    $unlinkedInbox = $unlink->rowCount();

    // 2. Strip from every PI of this work order.
    $piSel = $db->prepare('SELECT id, pos FROM pis WHERE order_id = ?');
    $piSel->execute([$workOrderId]);
    $piUpd = $db->prepare('UPDATE pis SET pos = ? WHERE id = ?');
    $piDel = $db->prepare('DELETE FROM pis WHERE id = ?');
    $pisChanged = 0; $pisDeleted = 0;
    foreach ($piSel->fetchAll() as $pi) {
        $pos = json_decode((string)($pi['pos'] ?? '[]'), true);
        if (!is_array($pos)) continue;
        [$newPos, $changed] = erpStripPos($pos, $targets);
        if (!$changed) continue;
        if (!$newPos) {
            $piDel->execute([$pi['id']]);
            $pisDeleted++;
        } else {
            $piUpd->execute([json_encode($newPos, JSON_UNESCAPED_UNICODE), $pi['id']]);
            $pisChanged++;
        }
    }

    // 3. Strip from the sales page_data snapshot.
    $pgSel = $db->prepare("SELECT data FROM page_data WHERE order_id = ? AND page_name = 'sales' LIMIT 1");
    $pgSel->execute([$workOrderId]);
    $pageData = json_decode((string)($pgSel->fetchColumn() ?: ''), true);
    $pageChanged = false;
    if (is_array($pageData) && is_array($pageData['pos'] ?? null)) {
        [$newPos, $changed] = erpStripPos($pageData['pos'], $targets);
        if ($changed) {
            $pageData['pos'] = $newPos;
            $pgUpd = $db->prepare("UPDATE page_data SET data = ? WHERE order_id = ? AND page_name = 'sales'");
            $pgUpd->execute([json_encode($pageData, JSON_UNESCAPED_UNICODE), $workOrderId]);
            $pageChanged = true;
        }
    }

    $db->commit();
    echo json_encode([
        'ok' => true,
        'work_order_id' => $workOrderId,
        'removed' => $targets,
        'unlinked_inbox' => $unlinkedInbox,
        'pis_updated' => $pisChanged,
        'pis_deleted' => $pisDeleted,
        'sales_snapshot_updated' => $pageChanged,
    ]);
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Could not remove the ERP order.', 'detail' => $e->getMessage()]);
}
