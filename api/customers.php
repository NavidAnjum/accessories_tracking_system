<?php
/**
 * api/customers.php
 *
 * GET         -> list / single record
 * POST (JSON) -> create and start approval flow
 * PUT  (JSON) -> approve current stage, store signature, advance workflow
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Approval chain: Team Lead -> Commercial -> Completed (Finance removed)
const STAGE_ORDER = ['team_leader', 'commercial', 'completed'];

function nextStage(string $current): string {
    $idx = array_search($current, STAGE_ORDER, true);
    return ($idx !== false && isset(STAGE_ORDER[$idx + 1])) ? STAGE_ORDER[$idx + 1] : 'completed';
}

function normalizeCreatorRole(string $role): string {
    $role = trim($role);
    return in_array($role, ['sales_person', 'team_leader'], true) ? $role : 'sales_person';
}

// Marketing/sales creator -> needs Team Lead then Commercial.
// Team Lead creator -> skips team-lead step, only Commercial approval needed.
function firstApprovalStageForCreator(string $creatorRole): string {
    return $creatorRole === 'team_leader' ? 'commercial' : 'team_leader';
}

function scorecardFields(array $extraDataArray): array {
    $scorecard = isset($extraDataArray['scorecard']) && is_array($extraDataArray['scorecard'])
        ? $extraDataArray['scorecard']
        : [];

    return [
        'total'  => isset($scorecard['total']) ? (int) $scorecard['total'] : null,
        'grade'  => trim((string) ($scorecard['grade'] ?? '')),
        'action' => trim((string) ($scorecard['action'] ?? '')),
        'json'   => $scorecard ? json_encode($scorecard) : null,
    ];
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    $myTeam = currentUserTeam();

    if ($method === 'GET') {
        // Visibility is open to everyone — all customers are listed / viewable.
        // Team scoping applies only to WHO CAN APPROVE (handled in customer-profile.php + PUT below).
        if (!empty($_GET['id'])) {
            $stmt = $db->prepare('SELECT * FROM customers WHERE id = ?');
            $stmt->execute([(int) $_GET['id']]);
            $row = $stmt->fetch();
            if ($row && $row['signatures']) {
                $row['signatures'] = json_decode($row['signatures'], true) ?? [];
            } else if ($row) {
                $row['signatures'] = [];
            }
            echo json_encode($row ?: null);
        } else {
            $cols = "id, company_name, customer_type, chairman_name, chairman_mobile, address_head_office, factory_address, extra_data, scorecard_total, scorecard_grade, scorecard_action, team, COALESCE(stage, 'completed') as stage, created_at";
            $stmt = $db->query("SELECT $cols FROM customers ORDER BY company_name");
            echo json_encode($stmt->fetchAll());
        }
        exit;
    }

    if ($method === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }

        $companyName       = trim($body['companyName'] ?? '');
        $addressHeadOffice = trim($body['addressHeadOffice'] ?? '');
        $factoryAddress    = trim($body['factoryAddress'] ?? '');
        $chairmanName      = trim($body['chairmanName'] ?? '');
        $chairmanMobile    = trim($body['chairmanMobile'] ?? '');
        $customerType      = trim($body['customerType'] ?? 'Regular');
        $dateForm          = ($body['dateForm'] ?? '') !== '' ? $body['dateForm'] : null;
        $politicsYes       = !empty($body['politicsYes']) ? 1 : 0;
        $politicsParty     = trim($body['politicsParty'] ?? '');
        $extraDataArray    = isset($body['extraData']) && is_array($body['extraData']) ? $body['extraData'] : [];
        $extraData         = json_encode($extraDataArray);
        $scorecard         = scorecardFields($extraDataArray);
        $creatorRole       = normalizeCreatorRole((string) ($body['creatorRole'] ?? 'sales_person'));
        $creatorSig        = $body['creatorSig'] ?? ($body['salesPersonSig'] ?? null);

        if (!$companyName) {
            http_response_code(400);
            echo json_encode(['error' => 'companyName is required']);
            exit;
        }

        $signatures = [];
        if ($creatorSig) {
            $signatures[$creatorRole] = $creatorSig;
        }
        $sigJson = json_encode($signatures);
        $stage   = firstApprovalStageForCreator($creatorRole);

        // Stamp the creating user's team onto the customer
        $customerTeam = currentUserTeam();

        $sql = '
            INSERT INTO customers
                (company_name, address_head_office, factory_address, chairman_name,
                 chairman_mobile, customer_type, date_form, politics_yes, politics_party,
                 extra_data, scorecard_total, scorecard_grade, scorecard_action, scorecard_json,
                 stage, signatures, team)
            VALUES
                (:company_name, :address_head_office, :factory_address, :chairman_name,
                 :chairman_mobile, :customer_type, :date_form, :politics_yes, :politics_party,
                 :extra_data, :scorecard_total, :scorecard_grade, :scorecard_action, :scorecard_json,
                 :stage, :signatures, :team)
            ON DUPLICATE KEY UPDATE
                address_head_office = VALUES(address_head_office),
                factory_address     = VALUES(factory_address),
                chairman_name       = VALUES(chairman_name),
                chairman_mobile     = VALUES(chairman_mobile),
                customer_type       = VALUES(customer_type),
                date_form           = VALUES(date_form),
                politics_yes        = VALUES(politics_yes),
                politics_party      = VALUES(politics_party),
                extra_data          = VALUES(extra_data),
                scorecard_total     = VALUES(scorecard_total),
                scorecard_grade     = VALUES(scorecard_grade),
                scorecard_action    = VALUES(scorecard_action),
                scorecard_json      = VALUES(scorecard_json),
                updated_at          = CURRENT_TIMESTAMP
        ';

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':company_name'        => $companyName,
            ':address_head_office' => $addressHeadOffice,
            ':factory_address'     => $factoryAddress,
            ':chairman_name'       => $chairmanName,
            ':chairman_mobile'     => $chairmanMobile,
            ':customer_type'       => $customerType,
            ':date_form'           => $dateForm,
            ':politics_yes'        => $politicsYes,
            ':politics_party'      => $politicsParty,
            ':extra_data'          => $extraData,
            ':scorecard_total'     => $scorecard['total'],
            ':scorecard_grade'     => $scorecard['grade'] !== '' ? $scorecard['grade'] : null,
            ':scorecard_action'    => $scorecard['action'] !== '' ? $scorecard['action'] : null,
            ':scorecard_json'      => $scorecard['json'],
            ':stage'               => $stage,
            ':signatures'          => $sigJson,
            ':team'                => $customerTeam,
        ]);

        $id = (int) $db->lastInsertId();
        echo json_encode([
            'ok' => true,
            'id' => $id,
            'company_name' => $companyName,
            'creatorRole' => $creatorRole,
            'nextStage' => $stage,
        ]);
        exit;
    }

    if ($method === 'PUT') {
        $body = json_decode(file_get_contents('php://input'), true);
        if (!$body) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }

        $id  = (int) ($body['id'] ?? 0);
        $sig = $body['sig'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'id required']);
            exit;
        }

        $stmt = $db->prepare('SELECT stage, signatures, extra_data, team FROM customers WHERE id = ?');
        $stmt->execute([$id]);
        $rec = $stmt->fetch();
        if (!$rec) {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
            exit;
        }

        // A team leader may only approve customers belonging to their own team.
        if (currentUserRole() === 'team_leader' && ($rec['team'] ?? null) !== $myTeam) {
            http_response_code(403);
            echo json_encode(['error' => 'This customer belongs to another team.']);
            exit;
        }

        $currentStage = $rec['stage'] ?: 'completed';
        $signatures   = json_decode($rec['signatures'] ?? '{}', true) ?? [];
        $extraData    = json_decode($rec['extra_data'] ?? '{}', true) ?? [];

        if ($sig && $currentStage !== 'completed') {
            $signatures[$currentStage] = $sig;
        }

        if (!empty($body['extraData']) && is_array($body['extraData'])) {
            $extraData = array_merge($extraData, $body['extraData']);
        }

        $newStage = nextStage($currentStage);
        $scorecard = scorecardFields($extraData);

        $db->prepare('
            UPDATE customers
            SET stage = ?,
                signatures = ?,
                extra_data = ?,
                scorecard_total = ?,
                scorecard_grade = ?,
                scorecard_action = ?,
                scorecard_json = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ')->execute([
            $newStage,
            json_encode($signatures),
            json_encode($extraData),
            $scorecard['total'],
            $scorecard['grade'] !== '' ? $scorecard['grade'] : null,
            $scorecard['action'] !== '' ? $scorecard['action'] : null,
            $scorecard['json'],
            $id,
        ]);

        echo json_encode(['ok' => true, 'stage' => $newStage]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
