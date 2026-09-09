<?php
/**
 * api/pis.php
 *
 * GET  ?all=1           → all saved PIs (for Master PI modal)
 * GET  ?q=<term>        → search by PI number or PO number within pos JSON
 * GET  ?id=<n>          → single PI record
 * POST (JSON)           → save / upsert PI
 * DELETE ?id=<n>        → delete PI
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

// PI-number reservations let two+ people create PIs at once without collisions.
// A reservation holds the next free number for one user; it frees when the user
// cancels (Clear/New Order) OR auto-expires after 15 min of no heartbeat, so an
// abandoned number becomes reusable.
const PI_RESERVE_TTL_MIN = 15;

function ensurePiReservationsTable(PDO $db): void
{
    static $done = false;
    if ($done) return;
    $db->exec("CREATE TABLE IF NOT EXISTS pi_number_reservations (
        pi_number   VARCHAR(80) NOT NULL,
        token       VARCHAR(64) NOT NULL,
        reserved_by VARCHAR(120) NULL,
        reserved_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        expires_at  DATETIME NOT NULL,
        PRIMARY KEY (pi_number),
        KEY idx_pi_res_token (token),
        KEY idx_pi_res_exp (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $done = true;
}

function purgeExpiredPiReservations(PDO $db): void
{
    $db->prepare('DELETE FROM pi_number_reservations WHERE expires_at < NOW()')->execute();
}

try {
    $db = getDB();

    // Detect whether migration columns exist
    $hasMigrationCols = false;
    try {
        $db->query('SELECT order_id FROM pis LIMIT 0');
        $hasMigrationCols = true;
    } catch (PDOException $_) {}

    // Self-migrate a history column that keeps prior versions of an edited PI
    // (kept for the record, not shown to anyone yet). Detect + add if missing.
    $hasHistoryCol = false;
    try {
        $db->query('SELECT history_json FROM pis LIMIT 0');
        $hasHistoryCol = true;
    } catch (PDOException $_) {
        try {
            $db->exec('ALTER TABLE pis ADD COLUMN history_json LONGTEXT NULL');
            $hasHistoryCol = true;
        } catch (PDOException $__) { /* leave off if we cannot add it */ }
    }

    // Self-migrate a flag marking a PI whose number was entered MANUALLY
    // (vs auto-generated), so manual PIs can be tracked/audited.
    $hasManualCol = false;
    try {
        $db->query('SELECT pi_number_manual FROM pis LIMIT 0');
        $hasManualCol = true;
    } catch (PDOException $_) {
        try {
            $db->exec('ALTER TABLE pis ADD COLUMN pi_number_manual TINYINT(1) NOT NULL DEFAULT 0');
            $hasManualCol = true;
        } catch (PDOException $__) { /* leave off if we cannot add it */ }
    }

    $manualSel = $hasManualCol ? ', pi_number_manual' : '';
    $piSelect = ($hasMigrationCols
        ? 'id, order_id, is_master, included_pis, pi_number, customer, product_line, pi_date, status, grand_qty, grand_val, created_at, pos'
        : 'id, pi_number, customer, product_line, pi_date, status, grand_qty, grand_val, created_at, pos') . $manualSel;

    function decodePiRow(&$r, bool $hasMigrationCols): void {
        $r['pos']          = json_decode($r['pos'] ?? 'null', true) ?? [];
        $r['is_master']    = $hasMigrationCols ? (int)($r['is_master'] ?? 0) : 0;
        $r['order_id']     = $r['order_id'] ?? null;
        $r['included_pis'] = json_decode($r['included_pis'] ?? 'null', true) ?? [];
    }

    // ── GET ───────────────────────────────────────────────────────────────────
    if ($method === 'GET') {

        // Single PI by id
        if (!empty($_GET['id'])) {
            $stmt = $db->prepare('SELECT * FROM pis WHERE id = ?');
            $stmt->execute([(int)$_GET['id']]);
            $row = $stmt->fetch();
            if ($row) $row['pos'] = json_decode($row['pos'], true) ?? [];
            echo json_encode($row ?: null);
            exit;
        }

        // Release a reservation (Clear / New Order) — free the number immediately.
        if (isset($_GET['release_num'])) {
            ensurePiReservationsTable($db);
            $tok = trim((string)($_GET['token'] ?? ''));
            $num = trim((string)($_GET['pi_number'] ?? ''));
            if ($tok !== '') {
                $del = $db->prepare('DELETE FROM pi_number_reservations WHERE token = ?' . ($num !== '' ? ' AND pi_number = ?' : ''));
                $del->execute($num !== '' ? [$tok, $num] : [$tok]);
            }
            echo json_encode(['ok' => true]);
            exit;
        }

        // Heartbeat — keep a reservation alive while the PI page is open.
        if (isset($_GET['reserve_heartbeat'])) {
            ensurePiReservationsTable($db);
            $tok = trim((string)($_GET['token'] ?? ''));
            $ttl = PI_RESERVE_TTL_MIN;
            $ok = false;
            if ($tok !== '') {
                $up = $db->prepare("UPDATE pi_number_reservations SET expires_at = DATE_ADD(NOW(), INTERVAL {$ttl} MINUTE) WHERE token = ?");
                $up->execute([$tok]);
                $ok = $up->rowCount() > 0;
            }
            echo json_encode(['ok' => $ok]);
            exit;
        }

        // Next PI number: ZZAL/PI/YY/N — atomically RESERVE the lowest free number
        // so concurrent users never collide, and abandoned numbers are reusable.
        if (isset($_GET['next_num'])) {
            ensurePiReservationsTable($db);
            $yy = date('y'); // 2-digit year, e.g. "26"
            $prefix = 'ZZAL/PI/' . $yy . '/';
            $token = bin2hex(random_bytes(16));
            $who = '';
            if (function_exists('currentUser')) {
                $u = currentUser();
                $who = (string)($u['name'] ?? $u['username'] ?? $u['id'] ?? '');
            }
            $ttl = PI_RESERVE_TTL_MIN;

            $piNumber = null;
            for ($attempt = 0; $attempt < 200; $attempt++) {
                $db->beginTransaction();
                try {
                    purgeExpiredPiReservations($db);

                    // All sequence numbers already SAVED in pis for this year.
                    $s = $db->prepare("SELECT CAST(SUBSTRING_INDEX(pi_number, '/', -1) AS UNSIGNED) AS n
                                       FROM pis WHERE pi_number LIKE ?");
                    $s->execute([$prefix . '%']);
                    $taken = [];
                    foreach ($s->fetchAll(PDO::FETCH_COLUMN) as $n) { $taken[(int)$n] = true; }

                    // All currently-reserved sequence numbers for this year (locked).
                    $r = $db->prepare("SELECT CAST(SUBSTRING_INDEX(pi_number, '/', -1) AS UNSIGNED) AS n
                                       FROM pi_number_reservations WHERE pi_number LIKE ? FOR UPDATE");
                    $r->execute([$prefix . '%']);
                    foreach ($r->fetchAll(PDO::FETCH_COLUMN) as $n) { $taken[(int)$n] = true; }

                    // Smallest number that is neither saved nor reserved. This reuses
                    // gaps left by abandoned reservations (your "start 52 again" case).
                    $candidate = 0;
                    while (isset($taken[$candidate])) { $candidate++; }

                    $piNumber = $prefix . $candidate;
                    $ins = $db->prepare("INSERT INTO pi_number_reservations (pi_number, token, reserved_by, expires_at)
                                         VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL {$ttl} MINUTE))");
                    $ins->execute([$piNumber, $token, $who]);
                    $db->commit();
                    break; // reserved successfully
                } catch (PDOException $e) {
                    if ($db->inTransaction()) $db->rollBack();
                    // Duplicate key = someone grabbed it first; retry with next number.
                    if (($e->errorInfo[1] ?? 0) === 1062) { $piNumber = null; continue; }
                    throw $e;
                }
            }

            echo json_encode(['pi_number' => $piNumber, 'reserve_token' => $token]);
            exit;
        }

        // Search by PI number or PO number
        if (isset($_GET['q']) && $_GET['q'] !== '') {
            $q = trim($_GET['q']);

            // 1. Exact PI number match
            $stmt = $db->prepare('SELECT * FROM pis WHERE LOWER(pi_number) = LOWER(?) LIMIT 1');
            $stmt->execute([$q]);
            $row = $stmt->fetch();
            if ($row) {
                $row['pos'] = json_decode($row['pos'], true) ?? [];
                echo json_encode(['match' => 'pi', 'pi' => $row]);
                exit;
            }

            // 2. PI number partial match
            $stmt = $db->prepare('SELECT * FROM pis WHERE pi_number LIKE ? ORDER BY created_at DESC LIMIT 1');
            $stmt->execute(['%' . $q . '%']);
            $row = $stmt->fetch();
            if ($row) {
                $row['pos'] = json_decode($row['pos'], true) ?? [];
                echo json_encode(['match' => 'pi', 'pi' => $row]);
                exit;
            }

            // 3. Search within pos JSON for a matching PO number
            // MySQL JSON_SEARCH across array of objects isn't straightforward;
            // fetch all and search in PHP (PIs are typically few hundred rows max)
            $stmt = $db->query('SELECT * FROM pis ORDER BY created_at DESC');
            $rows = $stmt->fetchAll();
            foreach ($rows as $pi) {
                $pos = json_decode($pi['pos'], true) ?? [];
                foreach ($pos as $po) {
                    // Match the customer PO or the ERP sales order so a Summary PI
                    // can be assembled by typing either identifier.
                    $haystacks = [
                        $po['poNum']        ?? '',
                        $po['customerPo']   ?? '',
                        $po['salesOrder']   ?? '',
                        $po['salesOrderNo'] ?? '',
                    ];
                    foreach ($haystacks as $hay) {
                        if ($hay !== '' && stripos((string)$hay, $q) !== false) {
                            $pi['pos'] = $pos;
                            echo json_encode(['match' => 'po', 'poNum' => $po['poNum'] ?? '', 'po' => $po, 'pi' => $pi]);
                            exit;
                        }
                    }
                }
            }

            // Not found
            echo json_encode(['match' => null]);
            exit;
        }

        // PIs for a specific order
        if (!empty($_GET['order_id'])) {
            $oid = trim($_GET['order_id']);
            if ($hasMigrationCols) {
                $stmt = $db->prepare("SELECT {$piSelect} FROM pis WHERE order_id = ? ORDER BY is_master ASC, created_at ASC");
                $stmt->execute([$oid]);
                $rows = $stmt->fetchAll();
            } else {
                // Fallback: search by PI number pattern
                $stmt = $db->prepare("SELECT {$piSelect} FROM pis WHERE pi_number LIKE ? ORDER BY created_at ASC");
                $stmt->execute(['%' . $oid . '%']);
                $rows = $stmt->fetchAll();
            }
            foreach ($rows as &$r) decodePiRow($r, $hasMigrationCols);
            echo json_encode($rows);
            exit;
        }

        // All PIs
        $stmt = $db->query("SELECT {$piSelect} FROM pis ORDER BY created_at DESC");
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) decodePiRow($r, $hasMigrationCols);
        echo json_encode($rows);
        exit;
    }

    // ── POST — save / upsert PI ───────────────────────────────────────────────
    if ($method === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) { http_response_code(400); echo json_encode(['error' => 'Invalid JSON']); exit; }

        $piNumber    = trim($body['piNum']        ?? '');
        $customer    = trim($body['customer']     ?? '');
        $productLine = trim($body['productLine']  ?? '');
        $piDate      = ($body['piDate'] ?? '') !== '' ? $body['piDate'] : null;
        $grandQty    = (float)($body['grandQty']  ?? 0);
        $grandVal    = (float)($body['grandVal']  ?? 0);
        $pos         = json_encode($body['pos']   ?? []);
        $orderId     = trim($body['orderId']      ?? '') ?: null;
        $isMaster    = !empty($body['isMaster'])  ? 1 : 0;
        $includedPis = json_encode($body['includedPis'] ?? []);
        $piNumberManual = !empty($body['piNumberManual']) ? 1 : 0;

        if (!$piNumber) { http_response_code(400); echo json_encode(['error' => 'pi_number required']); exit; }

        $editNote = trim((string)($body['editNote'] ?? ''));
        $isEdit   = !empty($body['isEdit']);

        // On an edit, snapshot the CURRENT saved version into history_json before we
        // overwrite it. History is kept on the record but not surfaced to anyone yet.
        // The PI number never changes.
        $historyJson = null;
        if ($hasHistoryCol && $isEdit) {
            $prevStmt = $db->prepare('SELECT * FROM pis WHERE pi_number = ? LIMIT 1');
            $prevStmt->execute([$piNumber]);
            $prev = $prevStmt->fetch(PDO::FETCH_ASSOC);
            if ($prev) {
                $history = [];
                $existingHistory = json_decode((string)($prev['history_json'] ?? ''), true);
                if (is_array($existingHistory)) $history = $existingHistory;
                // Snapshot the prior version (without nesting old history inside it).
                unset($prev['history_json']);
                $history[] = [
                    'saved_at'   => date('c'),
                    'edited_by'  => (currentUser()['name'] ?? currentUser()['username'] ?? ''),
                    'edit_note'  => $editNote,
                    'snapshot'   => $prev,
                ];
                $historyJson = json_encode($history, JSON_UNESCAPED_UNICODE);
            }
        }

        if ($hasMigrationCols) {
            $stmt = $db->prepare('
                INSERT INTO pis (order_id, is_master, included_pis, pi_number, customer, product_line, pi_date, grand_qty, grand_val, pos)
                VALUES (:order_id, :is_master, :included_pis, :pi_number, :customer, :product_line, :pi_date, :grand_qty, :grand_val, :pos)
                ON DUPLICATE KEY UPDATE
                    order_id     = COALESCE(VALUES(order_id), order_id),
                    is_master    = VALUES(is_master),
                    included_pis = VALUES(included_pis),
                    customer     = VALUES(customer),
                    product_line = VALUES(product_line),
                    pi_date      = VALUES(pi_date),
                    grand_qty    = VALUES(grand_qty),
                    grand_val    = VALUES(grand_val),
                    pos          = VALUES(pos),
                    updated_at   = CURRENT_TIMESTAMP
            ');
            $stmt->execute([
                ':order_id'     => $orderId,
                ':is_master'    => $isMaster,
                ':included_pis' => $includedPis,
                ':pi_number'    => $piNumber,
                ':customer'     => $customer,
                ':product_line' => $productLine,
                ':pi_date'      => $piDate,
                ':grand_qty'    => $grandQty,
                ':grand_val'    => $grandVal,
                ':pos'          => $pos,
            ]);
            // Persist the history snapshot (separately, so it applies whether the
            // row was inserted or updated).
            if ($hasHistoryCol && $historyJson !== null) {
                $h = $db->prepare('UPDATE pis SET history_json = ? WHERE pi_number = ?');
                $h->execute([$historyJson, $piNumber]);
            }
            // Flag manual PI numbers (only set the flag when manual; never unset
            // an already-manual PI on a later auto-path save).
            if ($hasManualCol && $piNumberManual) {
                $m = $db->prepare('UPDATE pis SET pi_number_manual = 1 WHERE pi_number = ?');
                $m->execute([$piNumber]);
            }
        } else {
            $stmt = $db->prepare('
                INSERT INTO pis (pi_number, customer, product_line, pi_date, grand_qty, grand_val, pos)
                VALUES (:pi_number, :customer, :product_line, :pi_date, :grand_qty, :grand_val, :pos)
                ON DUPLICATE KEY UPDATE
                    customer     = VALUES(customer),
                    product_line = VALUES(product_line),
                    pi_date      = VALUES(pi_date),
                    grand_qty    = VALUES(grand_qty),
                    grand_val    = VALUES(grand_val),
                    pos          = VALUES(pos),
                    updated_at   = CURRENT_TIMESTAMP
            ');
            $stmt->execute([
                ':pi_number'    => $piNumber,
                ':customer'     => $customer,
                ':product_line' => $productLine,
                ':pi_date'      => $piDate,
                ':grand_qty'    => $grandQty,
                ':grand_val'    => $grandVal,
                ':pos'          => $pos,
            ]);
        }

        $id = $db->lastInsertId() ?: null;
        if (!$id) {
            $s = $db->prepare('SELECT id FROM pis WHERE pi_number = ?');
            $s->execute([$piNumber]);
            $id = $s->fetchColumn();
        }

        // The number is now saved → consume any reservation held for it.
        try { ensurePiReservationsTable($db); $db->prepare('DELETE FROM pi_number_reservations WHERE pi_number = ?')->execute([$piNumber]); } catch (Throwable $e) {}

        echo json_encode(['ok' => true, 'id' => $id, 'piNum' => $piNumber]);
        exit;
    }

    // ── DELETE ────────────────────────────────────────────────────────────────
    if ($method === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'id required']); exit; }
        $db->prepare('DELETE FROM pis WHERE id = ?')->execute([$id]);
        echo json_encode(['ok' => true]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
