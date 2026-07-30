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

try {
    $db = getDB();

    // Detect whether migration columns exist
    $hasMigrationCols = false;
    try {
        $db->query('SELECT order_id FROM pis LIMIT 0');
        $hasMigrationCols = true;
    } catch (PDOException $_) {}

    $piSelect = $hasMigrationCols
        ? 'id, order_id, is_master, included_pis, pi_number, customer, product_line, pi_date, status, grand_qty, grand_val, created_at, pos'
        : 'id, pi_number, customer, product_line, pi_date, status, grand_qty, grand_val, created_at, pos';

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

        // Next PI number: ZZAL/PI/YY/N (sequential within the year)
        if (isset($_GET['next_num'])) {
            $yy = date('y'); // 2-digit year, e.g. "26"
            $prefix = 'ZZAL/PI/' . $yy . '/';
            $stmt = $db->prepare(
                "SELECT MAX(CAST(SUBSTRING_INDEX(pi_number, '/', -1) AS UNSIGNED)) FROM pis WHERE pi_number LIKE ?"
            );
            $stmt->execute([$prefix . '%']);
            $max = $stmt->fetchColumn();
            $next = ($max === null || $max === false) ? 0 : (int)$max + 1;
            echo json_encode(['pi_number' => $prefix . $next]);
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
                    if (stripos($po['poNum'] ?? '', $q) !== false) {
                        $pi['pos'] = $pos;
                        echo json_encode(['match' => 'po', 'poNum' => $po['poNum'], 'po' => $po, 'pi' => $pi]);
                        exit;
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

        if (!$piNumber) { http_response_code(400); echo json_encode(['error' => 'pi_number required']); exit; }

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
