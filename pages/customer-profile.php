<?php
$pageTitle  = 'Customer Profile';
$activePage = 'customer-profile';
$navSection = 'master';
include __DIR__ . '/../includes/db.php';

$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;
$db      = null;

try {
    $db = getDB();
    $where = $search ? 'WHERE company_name LIKE :s OR chairman_name LIKE :s OR chairman_mobile LIKE :s OR customer_type LIKE :s' : '';
    $params = $search ? [':s' => "%$search%"] : [];
    $cntStmt = $db->prepare("SELECT COUNT(*) FROM customers $where");
    $cntStmt->execute($params);
    $total = (int)$cntStmt->fetchColumn();
    $listStmt = $db->prepare("SELECT id, company_name, customer_type, chairman_name, chairman_mobile, address_head_office, stage, created_at FROM customers $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
    $listStmt->execute($params);
    $customers = $listStmt->fetchAll();
    $totalPages = max(1, (int)ceil($total / $perPage));
    $dbOk = true;
} catch (Exception $e) {
    $customers = [];
    $total = 0;
    $totalPages = 1;
    $dbOk = false;
    $dbErr = $e->getMessage();
}

include __DIR__ . '/../includes/header.php';

$userRole  = $__user['role'] ?? '';
$canCreate = in_array($userRole, ['sales_person', 'team_leader', 'admin']);

// Fetch pending approvals for the current non-admin user
$pendingApprovals = [];
if (!in_array($userRole, ['sales_person', 'admin', 'completed'])) {
    try {
        $pStmt = $db->prepare("SELECT id, company_name, customer_type, chairman_name, chairman_mobile, created_at FROM customers WHERE stage = ? ORDER BY created_at ASC");
        $pStmt->execute([$userRole]);
        $pendingApprovals = $pStmt->fetchAll();
    } catch (Exception $e) { /* ignore */ }
}

$stageMeta = [
    'sales_person' => ['label' => 'Awaiting Sales',      'color' => '#f59e0b', 'bg' => '#fef3c7'],
    'team_leader'  => ['label' => 'Awaiting Team Lead',  'color' => '#6366f1', 'bg' => '#ede9fe'],
    'finance'      => ['label' => 'Awaiting Finance',    'color' => '#0ea5e9', 'bg' => '#e0f2fe'],
    'commercial'   => ['label' => 'Awaiting Commercial', 'color' => '#8b5cf6', 'bg' => '#f3e8ff'],
    'completed'    => ['label' => 'Completed',           'color' => '#166534', 'bg' => '#dcfce7'],
];
?>
<?php if (!empty($pendingApprovals)): ?>
<!-- Pending Approvals -->
<section class="form-card" style="margin-bottom:16px;border:2px solid #fbbf24;background:#fffbeb;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
        <div style="width:40px;height:40px;background:#fef3c7;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;">&#9203;</div>
        <div>
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#92400e;">Pending Your Approval</div>
            <div style="font-size:16px;font-weight:800;color:#1e1e2e;"><?= count($pendingApprovals) ?> Customer Profile<?= count($pendingApprovals) > 1 ? 's' : '' ?> waiting for you</div>
        </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($pendingApprovals as $p): ?>
        <div style="background:#fff;border:1.5px solid #fcd34d;border-radius:12px;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div>
                <div style="font-weight:700;font-size:14px;color:#1e1e2e;"><?= htmlspecialchars($p['company_name']) ?></div>
                <div style="font-size:12px;color:#64748b;margin-top:2px;">
                    <?= htmlspecialchars($p['customer_type']) ?>
                    <?php if ($p['chairman_name']): ?> &middot; <?= htmlspecialchars($p['chairman_name']) ?><?php endif; ?>
                    &middot; Submitted <?= date('d M Y', strtotime($p['created_at'])) ?>
                </div>
            </div>
            <button class="primary-btn" style="background:linear-gradient(135deg,#f59e0b,#d97706);border-color:#d97706;white-space:nowrap;"
                    onclick="openReview(<?= (int)$p['id'] ?>)">
                Review &amp; Sign &rarr;
            </button>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ══ Customer List ══════════════════════════════════════════════════════ -->
<section class="form-card" style="margin-bottom:0;">
    <div class="section-head" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <div>
            <div class="eyebrow">Master Data</div>
            <h2 style="margin:0;">Customer Profiles</h2>
        </div>
        <form method="get" class="cp-search-form" style="margin-left:auto;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search company, chairman, mobile&hellip;" class="cp-search-input">
            <button type="submit" class="primary-btn" style="padding:7px 16px;">Search</button>
            <?php if ($search): ?>
                <a href="?" class="ghost-btn" style="padding:7px 14px;text-decoration:none;">Clear</a>
            <?php endif; ?>
        </form>
        <?php if ($canCreate): ?>
        <a class="primary-btn" href="<?= BASE_PATH ?>/pages/create-customer.php"
           style="white-space:nowrap;text-decoration:none;">+ New Profile</a>
        <?php endif; ?>
    </div>

    <?php if (!$dbOk): ?>
        <p style="color:red;padding:12px;">DB error: <?= htmlspecialchars($dbErr ?? '') ?></p>
    <?php elseif (empty($customers)): ?>
        <p style="color:#888;padding:16px;"><?= $search ? 'No results for "'.htmlspecialchars($search).'".' : 'No customer profiles yet. Fill the form below and submit.' ?></p>
    <?php else: ?>
    <div style="overflow-x:auto;margin-top:12px;">
        <table class="cp-list-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Company Name</th>
                    <th>Type</th>
                    <th>Chairman / MD</th>
                    <th>Mobile</th>
                    <th>Approval Stage</th>
                    <th>Submitted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($customers as $i => $c):
                $stage   = $c['stage'] ?? 'completed';
                $sm      = $stageMeta[$stage] ?? $stageMeta['completed'];
                $isMyTurn = ($stage === $userRole);
            ?>
                <tr style="<?= $isMyTurn ? 'background:#fffbeb;' : '' ?>">
                    <td><?= $offset + $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($c['company_name']) ?></strong></td>
                    <td><span class="cp-type-badge"><?= htmlspecialchars($c['customer_type']) ?></span></td>
                    <td><?= htmlspecialchars($c['chairman_name'] ?? '&mdash;') ?></td>
                    <td><?= htmlspecialchars($c['chairman_mobile'] ?? '&mdash;') ?></td>
                    <td>
                        <span style="display:inline-block;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;
                                     background:<?= $sm['bg'] ?>;color:<?= $sm['color'] ?>;">
                            <?= $sm['label'] ?>
                        </span>
                    </td>
                    <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                    <td style="white-space:nowrap;display:flex;gap:6px;flex-wrap:wrap;">
                        <?php if ($isMyTurn): ?>
                        <button class="primary-btn" style="padding:4px 12px;font-size:12px;background:linear-gradient(135deg,#f59e0b,#d97706);border-color:#d97706;"
                                onclick="openReview(<?= (int)$c['id'] ?>)">Review &amp; Sign</button>
                        <?php endif; ?>
                        <?php if (!$isMyTurn): ?>
                        <button class="ghost-btn" style="padding:4px 12px;font-size:12px;"
                                onclick="openCpDetail(<?= (int)$c['id'] ?>)">View</button>
                        <?php endif; ?>
                        <a class="ghost-btn" style="padding:4px 12px;font-size:12px;text-decoration:none;color:#4f46e5;border-color:#c7d2fe;"
                           href="<?= BASE_PATH ?>/pages/customer-pdf.php?id=<?= (int)$c['id'] ?>" target="_blank"
                           title="Download PDF">&#128424; PDF</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="cp-pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>" class="cp-pg-btn">&laquo; Prev</a>
        <?php endif; ?>
        <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
            <a href="?page=<?= $p ?>&search=<?= urlencode($search) ?>"
               class="cp-pg-btn <?= $p===$page ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>" class="cp-pg-btn">Next &raquo;</a>
        <?php endif; ?>
        <span style="margin-left:8px;color:#888;font-size:13px;"><?= $total ?> total</span>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</section>

<!-- ══ Detail Modal ═══════════════════════════════════════════════════════ -->
<div id="cpDetailModal" class="cp-modal-shell hidden" onclick="if(event.target===this)closeCpDetail()">
    <div class="cp-modal-card" style="max-width:780px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <h3 id="cpModalTitle" style="margin:0;">Customer Details</h3>
            <button onclick="closeCpDetail()" style="background:none;border:none;font-size:20px;cursor:pointer;">&times;</button>
        </div>

        <!-- Stage progress bar -->
        <div id="cpModalStageBar" style="margin-bottom:16px;"></div>

        <!-- Record details -->
        <div id="cpModalBody" style="overflow-y:auto;max-height:52vh;"></div>

        <!-- Approval section — shown only when this user's turn -->
        <div id="cpModalApprove" style="display:none;margin-top:16px;border-top:1.5px solid #e0e3ff;padding-top:16px;">
            <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6366f1;margin-bottom:10px;">
                &#9998; Your Approval Required
            </div>
            <div style="display:flex;align-items:flex-end;gap:20px;flex-wrap:wrap;">
                <div style="text-align:center;min-width:160px;">
                    <div id="approveImgWrap" style="min-height:60px;display:flex;align-items:center;justify-content:center;">
                        <img id="approveSigImg" src="" alt="" style="max-height:60px;max-width:160px;display:none;">
                        <label for="approveSigFile" id="approveSigLabel"
                               style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border:1.5px dashed #6366f1;border-radius:8px;cursor:pointer;font-size:12px;color:#6366f1;font-weight:600;">
                            &#128393; Upload Signature
                        </label>
                        <input type="file" id="approveSigFile" accept="image/*" style="display:none;"
                               onchange="loadApproveSig(this)">
                    </div>
                    <div style="border-top:1.5px solid #374151;margin-top:8px;padding-top:4px;">
                        <div id="approveSigRoleLabel" style="font-size:12px;color:#374151;font-weight:600;"></div>
                    </div>
                </div>
                <div style="flex:1;">
                    <p style="font-size:12px;color:#64748b;margin:0 0 10px;">
                        Upload your signature and click <strong>Sign &amp; Approve</strong> to forward this profile to the next stage.
                    </p>
                    <button class="primary-btn" onclick="doApprove()" id="approveBtn">
                        &#10003; Sign &amp; Approve
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══ Review & Sign Modal (for approvers) ═══════════════════════════════ -->
<div id="reviewModal" style="display:none;position:fixed;inset:0;background:rgba(10,10,30,.55);backdrop-filter:blur(4px);z-index:9998;overflow-y:auto;">
    <div style="min-height:100vh;display:flex;align-items:flex-start;justify-content:center;padding:24px 16px;">
        <div style="background:#fff;border-radius:18px;width:100%;max-width:860px;box-shadow:0 24px 80px rgba(0,0,0,.28);overflow:hidden;">

            <!-- Header bar -->
            <div style="background:linear-gradient(135deg,#1e1e2e,#2d2d44);padding:20px 28px;display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;">Pending Approval</div>
                    <div id="reviewModalTitle" style="font-size:18px;font-weight:800;color:#fff;margin-top:2px;">Customer Profile</div>
                </div>
                <button onclick="closeReview()" style="background:rgba(255,255,255,.1);border:none;color:#fff;border-radius:10px;width:36px;height:36px;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;">&#x2715;</button>
            </div>

            <!-- Stage progress -->
            <div id="reviewStageBar" style="padding:16px 28px;background:#f8f9ff;border-bottom:1.5px solid #e8eaff;"></div>

            <!-- Customer data -->
            <div id="reviewBody" style="padding:24px 28px;">
                <p style="color:#888;">Loading&hellip;</p>
            </div>

            <!-- Document Checklist (team_leader stage only) -->
            <div id="reviewDocChecklist" style="display:none;padding:0 28px 20px;">
                <div style="border-top:2px dashed #bae6fd;padding-top:18px;">
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#0284c7;margin-bottom:12px;">
                        9. Document Checklist
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px;">
                        <?php foreach(['Trade License','BIN','TIN','Bond License','Bank Solvency','Company Profile','Compliance Certificates','Factory Photos','Sample Approval'] as $doc): ?>
                        <?php $dcId = 'rdc_' . strtolower(preg_replace('/[^a-z0-9]/i','_',$doc)); ?>
                        <label style="display:flex;align-items:center;gap:7px;font-size:13px;cursor:pointer;padding:7px 10px;border:1.5px solid #e0e3ff;border-radius:8px;" id="rdclbl_<?= $dcId ?>">
                            <input type="checkbox" id="<?= $dcId ?>" value="<?= htmlspecialchars($doc) ?>"
                                   onchange="rdcHighlight('rdclbl_<?= $dcId ?>',this)">
                            <?= htmlspecialchars($doc) ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Signature & approve section -->
            <div id="reviewApproveSection" style="display:none;padding:0 28px 28px;">
                <div style="border-top:2px dashed #c7d2fe;padding-top:20px;">
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6366f1;margin-bottom:16px;">
                        &#9998; Your Signature &amp; Approval
                    </div>
                    <div style="display:flex;align-items:flex-end;gap:32px;flex-wrap:wrap;">
                        <div style="text-align:center;min-width:200px;">
                            <div style="min-height:70px;display:flex;align-items:center;justify-content:center;margin-bottom:8px;">
                                <img id="reviewSigImg" src="" style="max-height:70px;max-width:200px;display:none;">
                                <label for="reviewSigFile" id="reviewSigLabel"
                                       style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border:2px dashed #6366f1;border-radius:10px;cursor:pointer;font-size:13px;color:#6366f1;font-weight:700;">
                                    &#9998; Upload Signature
                                </label>
                                <input type="file" id="reviewSigFile" accept="image/*" style="display:none;" onchange="loadReviewSig(this)">
                            </div>
                            <div style="border-top:1.5px solid #374151;padding-top:6px;">
                                <div id="reviewSigRoleLabel" style="font-size:13px;color:#374151;font-weight:700;"></div>
                            </div>
                        </div>
                        <div style="flex:1;min-width:200px;">
                            <p style="font-size:13px;color:#64748b;margin:0 0 14px;line-height:1.6;">
                                Upload your signature above. By clicking <strong>Sign &amp; Approve</strong> you confirm you have reviewed this customer profile and approve it to proceed to the next stage.
                            </p>
                            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                <button class="primary-btn" style="font-size:14px;padding:10px 24px;" onclick="doReviewApprove()">
                                    &#10003; Sign &amp; Approve
                                </button>
                                <button class="ghost-btn" style="font-size:14px;padding:10px 20px;" onclick="closeReview()">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ══ New Profile Form moved to create-customer.php ══════════════════════ -->
<?php if (false): ?>
<div id="cpFormSection" style="display:none;margin-top:16px;">
<section class="form-card page-screen active">
    <div class="section-head">
        <div class="section-title">
            <span class="section-tag">New Customer</span>
            <h2>Customer Profile &amp; Commercial Assessment</h2>
        </div>
        <div class="page-actions-right" style="display:flex;gap:8px;">
            <button class="ghost-btn" onclick="toggleCpForm()">Cancel</button>
            <button class="ghost-btn" style="color:#6366f1;border-color:#6366f1;" onclick="cpSaveDraft()" id="cpDraftBtn">&#10003; Save Draft</button>
            <button class="primary-btn" onclick="cpSubmit()">Submit Profile</button>
        </div>
    </div>
    <div id="cpFeedback" style="margin-bottom:8px;"></div>

    <div id="customerProfileForm">

        <!-- ── Section 1: Customer Information ── -->
        <div class="cp-section-head">1. Customer Information</div>
        <div class="form-grid">
            <div class="span-3">
                <label class="field-label">Customer Code</label>
                <input type="text" id="cp_customerCode" class="field-input" placeholder="e.g. CUST-001">
            </div>
            <div class="span-6">
                <label class="field-label">Company Name <span style="color:#dc2626;">*</span></label>
                <input type="text" id="cp_companyName" class="field-input" placeholder="Full legal company name">
            </div>
            <div class="span-3">
                <label class="field-label">Customer Type</label>
                <select id="cp_customerType" class="field-input">
                    <option value="Regular">Regular</option>
                    <option value="Premium">Premium</option>
                    <option value="New">New</option>
                    <option value="Strategic">Strategic</option>
                </select>
            </div>
            <div class="span-4">
                <label class="field-label">Industry</label>
                <input type="text" id="cp_industry" class="field-input" placeholder="e.g. Garments, Textile">
            </div>
            <div class="span-4">
                <label class="field-label">Website</label>
                <input type="text" id="cp_website" class="field-input" placeholder="https://...">
            </div>
            <div class="span-4">
                <label class="field-label">Form Date</label>
                <input type="date" id="cp_dateForm" class="field-input">
            </div>
            <div class="span-6">
                <label class="field-label">Head Office / Registered Address</label>
                <input type="text" id="cp_addressHeadOffice" class="field-input" placeholder="Full head office address">
            </div>
            <div class="span-6">
                <label class="field-label">Factory Address</label>
                <input type="text" id="cp_factoryAddress" class="field-input" placeholder="Full factory address">
            </div>
            <div class="span-12" style="background:#f8f9ff;border:1.5px solid #e0e3ff;border-radius:10px;padding:14px 16px;">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6366f1;margin-bottom:12px;">Chairman / MD / Director</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="field-label">Name</label>
                        <input type="text" id="cp_chairmanName" class="field-input" placeholder="Full name">
                    </div>
                    <div>
                        <label class="field-label">Phone</label>
                        <input type="text" id="cp_chairmanMobile" class="field-input" placeholder="+880...">
                    </div>
                </div>
            </div>
            <div class="span-12" style="background:#f8f9ff;border:1.5px solid #e0e3ff;border-radius:10px;padding:14px 16px;">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6366f1;margin-bottom:12px;">Commercial Contact</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="field-label">Name</label>
                        <input type="text" id="cp_commercialName" class="field-input" placeholder="Contact name">
                    </div>
                    <div>
                        <label class="field-label">Number</label>
                        <input type="text" id="cp_commercialNumber" class="field-input" placeholder="+880...">
                    </div>
                </div>
            </div>
            <div class="span-12" style="background:#f8f9ff;border:1.5px solid #e0e3ff;border-radius:10px;padding:14px 16px;">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6366f1;margin-bottom:12px;">Merchandiser Contact</div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                    <div>
                        <label class="field-label">Name</label>
                        <input type="text" id="cp_merchandiserContact" class="field-input" placeholder="Full name">
                    </div>
                    <div>
                        <label class="field-label">Mobile</label>
                        <input type="text" id="cp_merchandiserMobile" class="field-input" placeholder="+880...">
                    </div>
                    <div>
                        <label class="field-label">Email</label>
                        <input type="email" id="cp_email" class="field-input" placeholder="contact@company.com">
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Section 2: Business & Compliance ── -->
        <div class="cp-section-head">2. Business &amp; Compliance</div>
        <div class="form-grid">
            <div class="span-4">
                <label class="field-label">Trade License No.</label>
                <input type="text" id="cp_tradeLicense" class="field-input" placeholder="License number">
            </div>
            <div class="span-4">
                <label class="field-label">BIN</label>
                <input type="text" id="cp_bin" class="field-input" placeholder="BIN number">
            </div>
            <div class="span-4">
                <label class="field-label">TIN</label>
                <input type="text" id="cp_tin" class="field-input" placeholder="TIN number">
            </div>
            <div class="span-4">
                <label class="field-label">Bond License No.</label>
                <input type="text" id="cp_bondLicense" class="field-input" placeholder="Bond license number">
            </div>
            <div class="span-4">
                <label class="field-label">Bond License Expiry</label>
                <input type="date" id="cp_bondLicenseExpiry" class="field-input">
            </div>
            <div class="span-4">
                <label class="field-label">Compliance Status</label>
                <input type="text" id="cp_complianceStatus" class="field-input" placeholder="e.g. Compliant, Non-compliant">
            </div>
            <div class="span-4">
                <label class="field-label">Factory Building</label>
                <div style="display:flex;gap:16px;align-items:center;margin-top:8px;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                        <input type="radio" name="cp_factoryBuilding" id="cp_factoryOwn" value="Own"> Own
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                        <input type="radio" name="cp_factoryBuilding" id="cp_factoryRent" value="Rent"> Rent
                    </label>
                </div>
            </div>
            <div class="span-8">
                <label class="field-label">Factory Certifications</label>
                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
                    <label style="display:flex;align-items:center;gap:5px;font-size:13px;cursor:pointer;"><input type="checkbox" id="cp_cert_bsci" value="BSCI"> BSCI</label>
                    <label style="display:flex;align-items:center;gap:5px;font-size:13px;cursor:pointer;"><input type="checkbox" id="cp_cert_wrap" value="WRAP"> WRAP</label>
                    <label style="display:flex;align-items:center;gap:5px;font-size:13px;cursor:pointer;"><input type="checkbox" id="cp_cert_sedex" value="SEDEX"> SEDEX</label>
                    <label style="display:flex;align-items:center;gap:5px;font-size:13px;cursor:pointer;"><input type="checkbox" id="cp_cert_iso" value="ISO"> ISO</label>
                    <label style="display:flex;align-items:center;gap:5px;font-size:13px;cursor:pointer;"><input type="checkbox" id="cp_cert_others" value="Others"> Others</label>
                </div>
            </div>
            <div class="span-12">
                <label class="field-label">Bank Name &amp; Branch</label>
                <input type="text" id="cp_bankName" class="field-input" placeholder="Bank name and branch">
            </div>
            <div class="span-4">
                <label class="field-label">Political Exposure</label>
                <div style="display:flex;gap:16px;align-items:center;margin-top:8px;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                        <input type="radio" name="cp_politics" id="cp_politicsYes" value="yes"> Yes
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;">
                        <input type="radio" name="cp_politics" id="cp_politicsNo" value="no" checked> No
                    </label>
                </div>
            </div>
        </div>

        <!-- ── Section 3: Production Capability ── -->
        <div class="cp-section-head">3. Production Capability</div>
        <div class="form-grid">
            <div class="span-4">
                <label class="field-label">Factory Type</label>
                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
                    <label style="display:flex;align-items:center;gap:5px;font-size:13px;cursor:pointer;"><input type="radio" name="cp_factoryType" id="cp_ftWoven" value="Woven"> Woven</label>
                    <label style="display:flex;align-items:center;gap:5px;font-size:13px;cursor:pointer;"><input type="radio" name="cp_factoryType" id="cp_ftKnit" value="Knit"> Knit</label>
                    <label style="display:flex;align-items:center;gap:5px;font-size:13px;cursor:pointer;"><input type="radio" name="cp_factoryType" id="cp_ftBoth" value="Both"> Both</label>
                </div>
            </div>
            <div class="span-4">
                <label class="field-label">Monthly Capacity (pcs)</label>
                <input type="text" id="cp_monthlyCapacity" class="field-input" placeholder="e.g. 50000">
            </div>
            <div class="span-4">
                <label class="field-label">Daily Production (pcs)</label>
                <input type="text" id="cp_dailyProduction" class="field-input" placeholder="e.g. 2000">
            </div>
            <div class="span-3">
                <label class="field-label">No. of Machines</label>
                <input type="text" id="cp_noOfMachines" class="field-input" placeholder="Count">
            </div>
            <div class="span-3">
                <label class="field-label">No. of Lines</label>
                <input type="text" id="cp_noOfLines" class="field-input" placeholder="Count">
            </div>
            <div class="span-6">
                <label class="field-label">Peak Season Capacity (pcs)</label>
                <input type="text" id="cp_peakCapacity" class="field-input" placeholder="e.g. 70000">
            </div>
            <div class="span-6">
                <label class="field-label">Major Buyers</label>
                <input type="text" id="cp_majorBuyers" class="field-input" placeholder="e.g. H&amp;M, Zara, Walmart">
            </div>
            <div class="span-6">
                <label class="field-label">Major Products</label>
                <input type="text" id="cp_majorProducts" class="field-input" placeholder="e.g. T-shirt, Polo, Trouser">
            </div>
            <div class="span-4">
                <label class="field-label">Subcontract Factory</label>
                <div style="display:flex;gap:16px;align-items:center;margin-top:8px;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;"><input type="radio" name="cp_subcontract" id="cp_subYes" value="Yes"> Yes</label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;"><input type="radio" name="cp_subcontract" id="cp_subNo" value="No" checked> No</label>
                </div>
            </div>
        </div>

        <!-- ── Section 4: Commercial Assessment ── -->
        <div class="cp-section-head">4. Commercial Assessment</div>
        <div class="form-grid">
            <div class="span-4">
                <label class="field-label">Expected Monthly Business</label>
                <input type="text" id="cp_expectedMonthlyBiz" class="field-input" placeholder="e.g. USD 50,000">
            </div>
            <div class="span-4">
                <label class="field-label">Average Monthly Order</label>
                <input type="text" id="cp_avgMonthlyOrder" class="field-input" placeholder="e.g. 10 orders">
            </div>
            <div class="span-4">
                <label class="field-label">Required Lead Time (items wise)</label>
                <input type="text" id="cp_leadTime" class="field-input" placeholder="e.g. 7 days for carton">
            </div>
            <div class="span-4">
                <label class="field-label">Credit Facility</label>
                <input type="text" id="cp_creditFacility" class="field-input" placeholder="e.g. 30 days credit">
            </div>
            <div class="span-4">
                <label class="field-label">Payment Currency</label>
                <input type="text" id="cp_paymentCurrency" class="field-input" placeholder="e.g. USD, BDT">
            </div>
            <div class="span-4">
                <label class="field-label">LC Terms</label>
                <input type="text" id="cp_lcTerms" class="field-input" placeholder="e.g. At sight, 30 days">
            </div>
            <div class="span-4">
                <label class="field-label">BBLC Terms</label>
                <input type="text" id="cp_bblcTerms" class="field-input" placeholder="e.g. At sight, 60 days">
            </div>
            <div class="span-4">
                <label class="field-label">Delivery Terms</label>
                <input type="text" id="cp_deliveryTerms" class="field-input" placeholder="e.g. After LC receive">
            </div>
            <div class="span-2">
                <label class="field-label">UD Required</label>
                <div style="display:flex;gap:12px;align-items:center;margin-top:8px;">
                    <label style="display:flex;align-items:center;gap:5px;font-size:13px;cursor:pointer;"><input type="radio" name="cp_udRequired" id="cp_udYes" value="Yes"> Yes</label>
                    <label style="display:flex;align-items:center;gap:5px;font-size:13px;cursor:pointer;"><input type="radio" name="cp_udRequired" id="cp_udNo" value="No" checked> No</label>
                </div>
            </div>
            <div class="span-2">
                <label class="field-label">Zone</label>
                <input type="text" id="cp_zone" class="field-input" placeholder="e.g. Gazipur">
            </div>
        </div>

        <!-- ── Section 5: Product Interest ── -->
        <div class="cp-section-head">5. Product Interest</div>
        <div class="form-grid">
            <div class="span-12">
                <div style="display:flex;gap:14px;flex-wrap:wrap;padding:4px 0;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;transition:all .15s;" id="pilbl_carton">
                        <input type="checkbox" id="cp_pi_carton" value="Carton" onchange="pillHighlight('pilbl_carton',this)"> Carton
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;transition:all .15s;" id="pilbl_poly">
                        <input type="checkbox" id="cp_pi_poly" value="Poly" onchange="pillHighlight('pilbl_poly',this)"> Poly
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;transition:all .15s;" id="pilbl_hangtag">
                        <input type="checkbox" id="cp_pi_hangtag" value="Hang Tag" onchange="pillHighlight('pilbl_hangtag',this)"> Hang Tag
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;transition:all .15s;" id="pilbl_label">
                        <input type="checkbox" id="cp_pi_label" value="Label" onchange="pillHighlight('pilbl_label',this)"> Label
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;transition:all .15s;" id="pilbl_offset">
                        <input type="checkbox" id="cp_pi_offset" value="Offset" onchange="pillHighlight('pilbl_offset',this)"> Offset
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;transition:all .15s;" id="pilbl_thread">
                        <input type="checkbox" id="cp_pi_thread" value="Thread" onchange="pillHighlight('pilbl_thread',this)"> Thread
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;transition:all .15s;" id="pilbl_elastic">
                        <input type="checkbox" id="cp_pi_elastic" value="Elastic" onchange="pillHighlight('pilbl_elastic',this)"> Elastic
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;transition:all .15s;" id="pilbl_narrowfabric">
                        <input type="checkbox" id="cp_pi_narrowfabric" value="Narrow Fabric" onchange="pillHighlight('pilbl_narrowfabric',this)"> Narrow Fabric
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;transition:all .15s;" id="pilbl_others">
                        <input type="checkbox" id="cp_pi_others" value="Others" onchange="pillHighlight('pilbl_others',this)"> Others
                    </label>
                </div>
            </div>
        </div>

        <!-- ── Section 6: Competitor Analysis ── -->
        <div class="cp-section-head">6. Competitor Analysis</div>
        <div class="form-grid">
            <div class="span-6">
                <label class="field-label">Existing Supplier</label>
                <input type="text" id="cp_compSupplier" class="field-input" placeholder="Supplier name">
            </div>
            <div class="span-6">
                <label class="field-label">Current Price</label>
                <input type="text" id="cp_compCurrentPrice" class="field-input" placeholder="e.g. USD 0.45/pcs">
            </div>
            <div class="span-6">
                <label class="field-label">Strength</label>
                <input type="text" id="cp_compStrength" class="field-input" placeholder="Competitor strengths">
            </div>
            <div class="span-6">
                <label class="field-label">Weakness</label>
                <input type="text" id="cp_compWeakness" class="field-input" placeholder="Competitor weaknesses">
            </div>
            <div class="span-12">
                <label class="field-label">Reason for Change</label>
                <input type="text" id="cp_compReasonForChange" class="field-input" placeholder="Why customer wants to switch supplier">
            </div>
        </div>

        <!-- ── Section 7: Risk Assessment ── -->
        <div class="cp-section-head">7. Risk Assessment</div>
        <div class="form-grid">
            <div class="span-3">
                <label class="field-label">Financial Risk</label>
                <select id="cp_financialRisk" class="field-input">
                    <option value="">-- Select --</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>
            </div>
            <div class="span-3">
                <label class="field-label">Payment History</label>
                <input type="text" id="cp_paymentHistory" class="field-input" placeholder="e.g. Good, Irregular">
            </div>
            <div class="span-3">
                <label class="field-label">Credit Limit Recommended</label>
                <input type="text" id="cp_creditLimitRec" class="field-input" placeholder="e.g. USD 100,000">
            </div>
            <div class="span-3">
                <label class="field-label">Remarks</label>
                <input type="text" id="cp_riskRemarks" class="field-input" placeholder="Any remarks">
            </div>
        </div>

        <!-- ── Section 8: Price Approval Matrix ── -->
        <div class="cp-section-head">8. Price Approval Matrix</div>
        <div style="overflow-x:auto;margin-bottom:16px;">
            <table class="cp-matrix-table" id="priceMatrixTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Existing Price</th>
                        <th>Target Price</th>
                        <th>Approved Price</th>
                        <th>Commission</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody id="priceMatrixBody">
                    <tr>
                        <td><input type="text" class="cp-td-inp pm-product" placeholder="e.g. Carton"></td>
                        <td><input type="text" class="cp-td-inp pm-existingPrice" placeholder="0.00"></td>
                        <td><input type="text" class="cp-td-inp pm-targetPrice" placeholder="0.00"></td>
                        <td><input type="text" class="cp-td-inp pm-approvedPrice" placeholder="0.00"></td>
                        <td><input type="text" class="cp-td-inp pm-commission" placeholder="0.00"></td>
                        <td><button type="button" class="cp-rm-row-btn" onclick="removePriceRow(this)" title="Remove row">&times;</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button type="button" class="ghost-btn" style="font-size:12px;padding:5px 14px;" onclick="addPriceRow()">+ Add Row</button>

        <!-- ── Section 9: Document Checklist ── -->
        <div class="cp-section-head">9. Document Checklist</div>
        <div class="form-grid">
            <div class="span-12">
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;padding:8px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="dclbl_tradelic">
                        <input type="checkbox" id="cp_dc_tradelic" value="Trade License" onchange="pillHighlight('dclbl_tradelic',this)"> Trade License
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;padding:8px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="dclbl_bin">
                        <input type="checkbox" id="cp_dc_bin" value="BIN" onchange="pillHighlight('dclbl_bin',this)"> BIN
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;padding:8px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="dclbl_tin">
                        <input type="checkbox" id="cp_dc_tin" value="TIN" onchange="pillHighlight('dclbl_tin',this)"> TIN
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;padding:8px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="dclbl_bondlic">
                        <input type="checkbox" id="cp_dc_bondlic" value="Bond License" onchange="pillHighlight('dclbl_bondlic',this)"> Bond License
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;padding:8px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="dclbl_banksol">
                        <input type="checkbox" id="cp_dc_banksol" value="Bank Solvency" onchange="pillHighlight('dclbl_banksol',this)"> Bank Solvency
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;padding:8px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="dclbl_compprofile">
                        <input type="checkbox" id="cp_dc_compprofile" value="Company Profile" onchange="pillHighlight('dclbl_compprofile',this)"> Company Profile
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;padding:8px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="dclbl_compliancecert">
                        <input type="checkbox" id="cp_dc_compliancecert" value="Compliance Certificates" onchange="pillHighlight('dclbl_compliancecert',this)"> Compliance Certificates
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;padding:8px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="dclbl_facphotos">
                        <input type="checkbox" id="cp_dc_facphotos" value="Factory Photos" onchange="pillHighlight('dclbl_facphotos',this)"> Factory Photos
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;padding:8px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="dclbl_sampleapproval">
                        <input type="checkbox" id="cp_dc_sampleapproval" value="Sample Approval" onchange="pillHighlight('dclbl_sampleapproval',this)"> Sample Approval
                    </label>
                </div>
            </div>
        </div>

        <!-- ── Signature Block ── -->
        <div class="cp-section-head">Sales Person Signature</div>
        <div style="display:flex;justify-content:flex-start;padding:8px 0 16px;">
            <div style="text-align:center;min-width:220px;">
                <div style="min-height:70px;display:flex;align-items:center;justify-content:center;margin-bottom:8px;" id="cpSigWrap">
                    <img id="cp_sigImg" src="" alt="Signature" style="max-height:70px;max-width:220px;display:none;">
                    <label for="cp_sigFile" id="cp_sigLabel"
                           style="display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border:2px dashed #6366f1;border-radius:10px;cursor:pointer;font-size:13px;color:#6366f1;font-weight:700;">
                        &#9998; Upload Signature
                    </label>
                    <input type="file" id="cp_sigFile" accept="image/*" style="display:none;" onchange="loadCpSig(this)">
                </div>
                <div style="border-top:1.5px solid #374151;padding-top:6px;">
                    <div style="font-size:13px;color:#374151;font-weight:700;">Sales Person</div>
                    <div style="font-size:11px;color:#94a3b8;margin-top:2px;">Signs on creation</div>
                </div>
            </div>
        </div>

    </div><!-- #customerProfileForm -->
</section>
</div><!-- #cpFormSection -->
<?php endif; // (false) — form moved to create-customer.php ?>

<style>
/* ── Form field helpers ──────────────────────────────────── */
.field-label {
    display:block; font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:.05em; color:#64748b; margin-bottom:6px;
}
.field-input {
    width:100%; padding:9px 12px; border:1.5px solid #d1d5db; border-radius:8px;
    font-size:13px; color:#1e1e2e; outline:none; transition:border-color .15s;
    background:#fff; box-sizing:border-box;
}
.field-input:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.08); }
select.field-input { appearance:auto; }

/* ── Section heading ─────────────────────────────────────── */
.cp-section-head {
    font-size:12px; font-weight:700; text-transform:uppercase;
    letter-spacing:.07em; color:#6366f1;
    border-bottom:2px solid #e0e3ff; padding-bottom:6px;
    margin:24px 0 14px;
}

/* ── List / Search / Pagination ─────────────────────────── */
.cp-search-form { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.cp-search-input {
    padding:7px 12px; border:1.5px solid #d1d5db; border-radius:8px;
    font-size:14px; width:280px; outline:none; transition:border-color .15s;
}
.cp-search-input:focus { border-color:#6366f1; }

.cp-list-table { width:100%; border-collapse:collapse; font-size:13px; }
.cp-list-table th {
    background:#f5f7ff; color:#6366f1; font-weight:700; font-size:11px;
    text-transform:uppercase; letter-spacing:.05em;
    padding:10px 12px; text-align:left; border-bottom:2px solid #e0e3ff;
}
.cp-list-table td { padding:10px 12px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
.cp-list-table tbody tr:hover { background:#f8f9ff; }

.cp-type-badge {
    display:inline-block; padding:2px 10px; border-radius:999px; font-size:11px; font-weight:700;
    background:#e0e7ff; color:#4f46e5;
}

.cp-pagination { display:flex; gap:6px; align-items:center; margin-top:14px; flex-wrap:wrap; }
.cp-pg-btn {
    display:inline-block; padding:5px 12px; border:1.5px solid #d1d5db; border-radius:7px;
    font-size:13px; color:#374151; text-decoration:none; transition:all .15s;
}
.cp-pg-btn:hover { border-color:#6366f1; color:#6366f1; background:#f0f0ff; }
.cp-pg-btn.active { background:#6366f1; color:#fff; border-color:#6366f1; }

/* ── Detail Modal ────────────────────────────────────────── */
.cp-modal-shell {
    position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:9999;
    display:flex; align-items:center; justify-content:center; padding:16px;
}
.cp-modal-shell.hidden { display:none; }
.cp-modal-card {
    background:#fff; border-radius:14px; padding:28px; width:100%;
    max-width:680px; box-shadow:0 20px 60px rgba(0,0,0,.25);
}
.cp-detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px 24px; font-size:13px; }
.cp-detail-item label { display:block; color:#888; font-size:11px; text-transform:uppercase; letter-spacing:.05em; margin-bottom:2px; }
.cp-detail-item span { font-weight:600; color:#1e1e2e; }
.cp-modal-section-title { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#6366f1; border-bottom:1.5px solid #e0e3ff; padding-bottom:4px; margin-bottom:10px; }

/* ── Price Matrix Table ──────────────────────────────────── */
.cp-matrix-table { width:100%; border-collapse:collapse; font-size:13px; min-width:560px; }
.cp-matrix-table th {
    background:#f5f7ff; color:#6366f1; font-weight:700; font-size:11px;
    text-transform:uppercase; letter-spacing:.05em;
    padding:8px 10px; text-align:left; border-bottom:2px solid #e0e3ff; border:1px solid #e0e3ff;
}
.cp-matrix-table td { padding:6px 6px; border:1px solid #e8eaff; }
.cp-td-inp {
    width:100%; border:none; border-bottom:1.5px solid #d1d5db; background:transparent;
    font-size:13px; padding:4px 4px; outline:none; border-radius:0;
    transition:border-color .15s;
}
.cp-td-inp:focus { border-bottom-color:#6366f1; background:#f5f7ff; }
.cp-rm-row-btn {
    background:#fee2e2; border:none; border-radius:6px; color:#dc2626;
    width:28px; height:28px; font-size:16px; cursor:pointer; display:flex;
    align-items:center; justify-content:center; transition:background .15s;
}
.cp-rm-row-btn:hover { background:#fecaca; }
</style>

<script>
// Stage metadata
const STAGE_ORDER  = ['sales_person','team_leader','finance','commercial','completed'];
const STAGE_LABELS = {
    sales_person: 'Sales',
    team_leader:  'Team Lead',
    finance:      'Finance',
    commercial:   'Commercial',
    completed:    'Completed',
};
const STAGE_COLORS = {
    sales_person: '#f59e0b',
    team_leader:  '#6366f1',
    finance:      '#0ea5e9',
    commercial:   '#8b5cf6',
    completed:    '#22c55e',
};
let _currentApproveId = null;
let _reviewId = null;

// ── Helpers ──────────────────────────────────────────────────────────────

function prettyExtraLabel(key) {
    let m = key.match(/^field_(\d+)$/i);
    if (m) return 'Field ' + (+m[1] + 1);
    m = key.match(/^option_(\d+)$/i);
    if (m) return 'Option ' + (+m[1] + 1);
    if (!/_/.test(key)) return key;
    return key.replace(/_/g,' ').replace(/\b\w/g, c => c.toUpperCase());
}

function pillHighlight(lblId, cb) {
    const lbl = document.getElementById(lblId);
    if (!lbl) return;
    lbl.style.borderColor  = cb.checked ? '#6366f1' : '#d1d5db';
    lbl.style.background   = cb.checked ? '#ede9fe' : '';
    lbl.style.color        = cb.checked ? '#4f46e5' : '';
}

function buildStageBar(stage, signatures, compact) {
    const stageIdx = STAGE_ORDER.indexOf(stage);
    const dotSize  = compact ? 28 : 32;
    const minW     = compact ? 72 : 82;
    let html = `<div style="display:flex;align-items:center;gap:0;overflow-x:auto;">`;
    STAGE_ORDER.filter(s => s !== 'completed').forEach((s, i) => {
        const done    = STAGE_ORDER.indexOf(s) < stageIdx;
        const current = s === stage;
        const color   = STAGE_COLORS[s] || '#94a3b8';
        const hasSig  = signatures && signatures[s];
        const isLast  = i === STAGE_ORDER.filter(s2=>s2!=='completed').length - 1;
        html += `<div style="display:flex;align-items:center;">
            <div style="text-align:center;min-width:${minW}px;">
                <div style="width:${dotSize}px;height:${dotSize}px;border-radius:50%;margin:0 auto 4px;
                    background:${done||current?color:'#e2e8f0'};
                    color:${done||current?'#fff':'#94a3b8'};
                    display:flex;align-items:center;justify-content:center;
                    font-size:${compact?12:13}px;font-weight:800;
                    border:2px solid ${done||current?color:'#e2e8f0'};">
                    ${done ? '&#10003;' : (i+1)}
                </div>
                <div style="font-size:10px;font-weight:700;color:${done||current?color:'#94a3b8'};white-space:nowrap;">
                    ${STAGE_LABELS[s]||s}
                </div>
                ${hasSig ? `<img src="${hasSig}" style="max-width:${compact?44:54}px;max-height:${compact?22:28}px;margin-top:3px;border-bottom:1px solid #ccc;">` : ''}
            </div>
            ${!isLast ? `<div style="flex:1;min-width:14px;height:2px;background:${done?color:'#e2e8f0'};margin-bottom:${hasSig?28:18}px;"></div>` : ''}
        </div>`;
    });
    html += `</div>`;
    return html;
}

function renderSectionTitle(t) {
    return `<div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#6366f1;border-bottom:1.5px solid #e0e3ff;padding-bottom:4px;margin:18px 0 10px;">${t}</div>`;
}

function renderGrid(rows, cols) {
    cols = cols || 2;
    return `<div style="display:grid;grid-template-columns:repeat(${cols},1fr);gap:10px 24px;font-size:13px;">` +
        rows.map(([l,v]) => `<div><div style="font-size:10px;font-weight:700;text-transform:uppercase;color:#94a3b8;margin-bottom:2px;">${l}</div><div style="font-weight:600;color:#1e1e2e;">${v||'&mdash;'}</div></div>`).join('') +
        `</div>`;
}

function renderBadges(items) {
    if (!items || !items.length) return '<span style="color:#94a3b8;font-size:13px;">None</span>';
    return `<div style="display:flex;flex-wrap:wrap;gap:6px;">` +
        items.map(o => `<span style="background:#e0e7ff;color:#4f46e5;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;">${o}</span>`).join('') +
        `</div>`;
}

function renderMatrixTable(matrix) {
    if (!matrix || !matrix.length) return '<span style="color:#94a3b8;font-size:13px;">No data</span>';
    let t = `<table style="border-collapse:collapse;width:100%;font-size:12px;">
        <thead><tr>
            <th style="background:#f5f7ff;color:#6366f1;font-size:11px;padding:6px 8px;border:1px solid #e0e3ff;font-weight:700;text-transform:uppercase;">Product</th>
            <th style="background:#f5f7ff;color:#6366f1;font-size:11px;padding:6px 8px;border:1px solid #e0e3ff;font-weight:700;text-transform:uppercase;">Existing Price</th>
            <th style="background:#f5f7ff;color:#6366f1;font-size:11px;padding:6px 8px;border:1px solid #e0e3ff;font-weight:700;text-transform:uppercase;">Target Price</th>
            <th style="background:#f5f7ff;color:#6366f1;font-size:11px;padding:6px 8px;border:1px solid #e0e3ff;font-weight:700;text-transform:uppercase;">Approved Price</th>
            <th style="background:#f5f7ff;color:#6366f1;font-size:11px;padding:6px 8px;border:1px solid #e0e3ff;font-weight:700;text-transform:uppercase;">Commission</th>
        </tr></thead><tbody>`;
    matrix.forEach(row => {
        t += `<tr>
            <td style="padding:6px 8px;border:1px solid #e8eaff;">${row.product||'&mdash;'}</td>
            <td style="padding:6px 8px;border:1px solid #e8eaff;">${row.existingPrice||'&mdash;'}</td>
            <td style="padding:6px 8px;border:1px solid #e8eaff;">${row.targetPrice||'&mdash;'}</td>
            <td style="padding:6px 8px;border:1px solid #e8eaff;">${row.approvedPrice||'&mdash;'}</td>
            <td style="padding:6px 8px;border:1px solid #e8eaff;">${row.commission||'&mdash;'}</td>
        </tr>`;
    });
    t += `</tbody></table>`;
    return t;
}

function renderCustomerDetail(data) {
    const extra = data.extra_data
        ? (typeof data.extra_data === 'string' ? JSON.parse(data.extra_data) : data.extra_data)
        : {};

    let html = '';

    // 1. Customer Information
    html += renderSectionTitle('1. Customer Information');
    html += renderGrid([
        ['Customer Category',   extra.customerCategory || '&mdash;'],
        ['Customer Code',       extra.customerCode || '&mdash;'],
        ['Company Name',        data.company_name],
        ['Customer Type',       data.customer_type],
        ['Industry',            extra.industry || '&mdash;'],
        ['Head Office',         data.address_head_office],
        ['Factory Address',     data.factory_address],
        ['Website',             extra.website || '&mdash;'],
        ['Chairman / MD',       data.chairman_name],
        ['Chairman Phone',      data.chairman_mobile],
        ['Commercial Name',     extra.commercialName || '&mdash;'],
        ['Commercial Number',   extra.commercialNumber || '&mdash;'],
        ['Merchandiser Contact',extra.merchandiserContact || '&mdash;'],
        ['Merchandiser Mobile', extra.merchandiserMobile || '&mdash;'],
        ['Email',               extra.email || '&mdash;'],
        ['Political Exposure',  data.politics_yes ? 'Yes' : 'No'],
        ['Form Date',           data.date_form || '&mdash;'],
        ['Submitted',           data.created_at ? new Date(data.created_at).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}) : '&mdash;'],
    ]);

    // 2. Business & Compliance
    html += renderSectionTitle('2. Business &amp; Compliance');
    html += renderGrid([
        ['Trade License No.',   extra.tradelicense || '&mdash;'],
        ['BIN',                 extra.bin || '&mdash;'],
        ['TIN',                 extra.tin || '&mdash;'],
        ['Bond License Expiry', extra.bondLicenseExpiry || '&mdash;'],
        ['Compliance Status',   extra.complianceStatus || '&mdash;'],
        ['Factory Building',    extra.factoryBuilding || '&mdash;'],
        ['Bank Name & Branch',  extra.bankName || '&mdash;'],
    ]);
    if (extra.factoryCertifications) {
        html += `<div style="margin-top:8px;font-size:11px;font-weight:700;text-transform:uppercase;color:#94a3b8;margin-bottom:4px;">Factory Certifications</div>`;
        html += renderBadges(Array.isArray(extra.factoryCertifications) ? extra.factoryCertifications : [extra.factoryCertifications]);
    }

    // 3. Production Capability
    html += renderSectionTitle('3. Production Capability');
    html += renderGrid([
        ['Factory Type',        extra.factoryType || '&mdash;'],
        ['Monthly Capacity',    extra.monthlyCapacity || '&mdash;'],
        ['Daily Production',    extra.dailyProduction || '&mdash;'],
        ['No. of Machines',     extra.noOfMachines || '&mdash;'],
        ['No. of Lines',        extra.noOfLines || '&mdash;'],
        ['Peak Season Capacity',extra.peakCapacity || '&mdash;'],
        ['Major Buyers',        extra.majorBuyers || '&mdash;'],
        ['Major Products',      extra.majorProducts || '&mdash;'],
        ['Subcontract Factory', extra.subcontractFactory || '&mdash;'],
    ]);

    // 4. Commercial Assessment
    html += renderSectionTitle('4. Commercial Assessment');
    html += renderGrid([
        ['Expected Monthly Biz',extra.expectedMonthlyBiz || '&mdash;'],
        ['Avg Monthly Order',   extra.avgMonthlyOrder || '&mdash;'],
        ['Lead Time',           extra.leadTime || '&mdash;'],
        ['Credit Facility',     extra.creditFacility || '&mdash;'],
        ['Payment Currency',    extra.paymentCurrency || '&mdash;'],
        ['LC Terms',            extra.lcTerms || '&mdash;'],
        ['BBLC Terms',          extra.bbkTerms || '&mdash;'],
        ['Delivery Terms',      extra.deliveryTerms || '&mdash;'],
        ['UD Required',         extra.udRequired || '&mdash;'],
        ['Zone',                extra.zone || '&mdash;'],
    ]);

    // 5. Product Interest
    html += renderSectionTitle('5. Product Interest');
    html += renderBadges(Array.isArray(extra.productInterest) ? extra.productInterest : []);

    // 6. Competitor Analysis
    const comp = extra.competitorAnalysis || {};
    html += renderSectionTitle('6. Competitor Analysis');
    html += renderGrid([
        ['Existing Supplier',   comp.supplier || '&mdash;'],
        ['Current Price',       comp.currentPrice || '&mdash;'],
        ['Strength',            comp.strength || '&mdash;'],
        ['Weakness',            comp.weakness || '&mdash;'],
        ['Reason for Change',   comp.reasonForChange || '&mdash;'],
    ]);

    // 7. Risk Assessment
    const risk = extra.riskAssessment || {};
    html += renderSectionTitle('7. Risk Assessment');
    html += renderGrid([
        ['Financial Risk',      risk.financialRisk || '&mdash;'],
        ['Payment History',     risk.paymentHistory || '&mdash;'],
        ['Credit Limit Rec.',   risk.creditLimitRec || '&mdash;'],
        ['Remarks',             risk.remarks || '&mdash;'],
    ]);

    // 8. Price Approval Matrix
    html += renderSectionTitle('8. Price Approval Matrix');
    html += renderMatrixTable(extra.priceMatrix);

    // 9. Document Checklist
    html += renderSectionTitle('9. Document Checklist');
    html += renderBadges(Array.isArray(extra.docChecklist) ? extra.docChecklist : []);

    return html;
}

// ── Detail Modal ─────────────────────────────────────────────────────────

async function openCpDetail(id) {
    _currentApproveId = id;
    const modal    = document.getElementById('cpDetailModal');
    const body     = document.getElementById('cpModalBody');
    const title    = document.getElementById('cpModalTitle');
    const stageBar = document.getElementById('cpModalStageBar');
    const approveSection = document.getElementById('cpModalApprove');

    body.innerHTML = '<p style="padding:20px;color:#888;">Loading&hellip;</p>';
    stageBar.innerHTML = '';
    approveSection.style.display = 'none';
    modal.classList.remove('hidden');

    try {
        const res  = await fetch(APP_BASE + '/api/customers.php?id=' + id);
        const data = await res.json();
        if (!data || data.error) { body.innerHTML = '<p style="color:red;">Failed to load.</p>'; return; }

        title.textContent = data.company_name || 'Customer Details';
        const stage      = data.stage || 'completed';
        const signatures = data.signatures || {};

        stageBar.innerHTML = buildStageBar(stage, signatures, true);
        body.innerHTML = '<div style="font-size:13px;">' + renderCustomerDetail(data) + '</div>';

        if (stage !== 'completed' && CURRENT_ROLE === stage) {
            approveSection.style.display = 'block';
            document.getElementById('approveSigRoleLabel').textContent = STAGE_LABELS[stage] || stage;
            document.getElementById('approveSigImg').style.display = 'none';
            document.getElementById('approveSigImg').src = '';
            document.getElementById('approveSigLabel').style.display = 'inline-flex';
            document.getElementById('approveSigFile').value = '';
        }
    } catch(e) {
        body.innerHTML = '<p style="color:red;">Error: ' + e.message + '</p>';
    }
}

function loadApproveSig(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('approveSigImg');
        img.src = e.target.result;
        img.style.display = 'block';
        document.getElementById('approveSigLabel').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

async function doApprove() {
    const btn    = document.getElementById('approveBtn');
    const sigImg = document.getElementById('approveSigImg');
    const sig    = (sigImg && sigImg.src && sigImg.style.display !== 'none') ? sigImg.src : null;

    btn.disabled = true;
    btn.textContent = 'Saving…';

    try {
        const res  = await fetch(APP_BASE + '/api/customers.php', {
            method: 'PUT',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ id: _currentApproveId, sig }),
        });
        const json = await res.json();
        if (!res.ok || json.error) {
            alert('Error: ' + (json.error || res.statusText));
            btn.disabled = false;
            btn.textContent = '✓ Sign & Approve';
        } else {
            const nextLabel = STAGE_LABELS[json.stage] || json.stage;
            const msg = json.stage === 'completed'
                ? '✓ Approval complete! Profile is fully signed.'
                : `✓ Signed! Forwarded to ${nextLabel}.`;
            alert(msg);
            closeCpDetail();
            location.reload();
        }
    } catch(e) {
        alert('Network error: ' + e.message);
        btn.disabled = false;
        btn.textContent = '✓ Sign & Approve';
    }
}

function closeCpDetail() {
    document.getElementById('cpDetailModal').classList.add('hidden');
    _currentApproveId = null;
}

// ── Review & Sign Modal ──────────────────────────────────────────────────

async function openReview(id) {
    _reviewId = id;
    const modal          = document.getElementById('reviewModal');
    const body           = document.getElementById('reviewBody');
    const stageBar       = document.getElementById('reviewStageBar');
    const approveSection = document.getElementById('reviewApproveSection');

    modal.style.display = 'block';
    body.innerHTML = '<p style="padding:20px;color:#888;">Loading&hellip;</p>';
    stageBar.innerHTML = '';
    approveSection.style.display = 'none';

    try {
        const res  = await fetch(APP_BASE + '/api/customers.php?id=' + id);
        const data = await res.json();
        if (!data) { body.innerHTML = '<p style="color:red;">Failed to load.</p>'; return; }

        document.getElementById('reviewModalTitle').textContent = data.company_name || 'Customer Profile';
        const stage      = data.stage || 'completed';
        const signatures = data.signatures || {};

        stageBar.innerHTML = buildStageBar(stage, signatures, false);
        body.innerHTML = '<div style="font-size:13px;">' + renderCustomerDetail(data) + '</div>';

        // Show/hide document checklist section for team_leader
        const dcSection = document.getElementById('reviewDocChecklist');
        if (dcSection) {
            if (stage === 'team_leader' && CURRENT_ROLE === 'team_leader') {
                dcSection.style.display = 'block';
                // Reset all checkboxes
                dcSection.querySelectorAll('input[type=checkbox]').forEach(cb => {
                    cb.checked = false;
                    rdcHighlight(cb.closest('label')?.id, cb);
                });
                // Pre-tick any already saved
                const extra = (typeof data.extra_data === 'string') ? JSON.parse(data.extra_data || '{}') : (data.extra_data || {});
                (extra.docChecklist || []).forEach(val => {
                    const cb = dcSection.querySelector(`input[value="${val}"]`);
                    if (cb) { cb.checked = true; rdcHighlight(cb.closest('label')?.id, cb); }
                });
            } else {
                dcSection.style.display = 'none';
            }
        }

        if (stage !== 'completed' && CURRENT_ROLE === stage) {
            approveSection.style.display = 'block';
            document.getElementById('reviewSigRoleLabel').textContent = STAGE_LABELS[stage] || stage;
            document.getElementById('reviewSigImg').style.display = 'none';
            document.getElementById('reviewSigImg').src = '';
            document.getElementById('reviewSigLabel').style.display = 'inline-flex';
            document.getElementById('reviewSigFile').value = '';
        }
    } catch(e) {
        body.innerHTML = '<p style="color:red;">Error: ' + e.message + '</p>';
    }
}

function loadReviewSig(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('reviewSigImg');
        img.src = e.target.result;
        img.style.display = 'block';
        document.getElementById('reviewSigLabel').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

async function doReviewApprove() {
    const sigImg = document.getElementById('reviewSigImg');
    const sig    = (sigImg && sigImg.src && sigImg.style.display !== 'none') ? sigImg.src : null;

    const btn = document.querySelector('#reviewApproveSection .primary-btn');
    btn.disabled = true;
    btn.textContent = 'Saving…';

    try {
        const docChecklist = Array.from(
            document.querySelectorAll('#reviewDocChecklist input[type=checkbox]:checked')
        ).map(cb => cb.value);

        const body = { id: _reviewId, sig };
        if (docChecklist.length) body.extraData = { docChecklist };

        const res  = await fetch(APP_BASE + '/api/customers.php', {
            method: 'PUT',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(body),
        });
        const json = await res.json();
        if (!res.ok || json.error) {
            alert('Error: ' + (json.error || res.statusText));
            btn.disabled = false;
            btn.textContent = '✓ Sign & Approve';
        } else {
            const nextLabel = STAGE_LABELS[json.stage] || json.stage;
            const msg = json.stage === 'completed'
                ? '✓ All approvals complete! Profile is fully signed.'
                : `✓ Signed! Forwarded to ${nextLabel}.`;
            closeReview();
            alert(msg);
            location.reload();
        }
    } catch(e) {
        alert('Network error: ' + e.message);
        btn.disabled = false;
        btn.textContent = '✓ Sign & Approve';
    }
}

function rdcHighlight(lblId, cb) {
    const lbl = document.getElementById(lblId);
    if (!lbl) return;
    lbl.style.borderColor = cb.checked ? '#0284c7' : '#e0e3ff';
    lbl.style.background  = cb.checked ? '#e0f2fe' : '';
    lbl.style.color       = cb.checked ? '#0369a1' : '';
}

function closeReview() {
    document.getElementById('reviewModal').style.display = 'none';
    _reviewId = null;
}

document.getElementById('reviewModal').addEventListener('click', function(e) {
    if (e.target === this) closeReview();
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeCpDetail(); closeReview(); }
});

// ── New Profile Form ─────────────────────────────────────────────────────

function toggleCpForm() {
    const sec = document.getElementById('cpFormSection');
    const btn = document.getElementById('newProfileBtn');
    if (!sec) return;
    const visible = sec.style.display !== 'none';
    sec.style.display = visible ? 'none' : 'block';
    if (btn) btn.textContent = visible ? '+ New Profile' : '✕ Close Form';
    if (!visible) {
        sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
        setTimeout(cpLoadDraft, 80);
    }
}

function loadCpSig(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('cp_sigImg');
        img.src = e.target.result;
        img.style.display = 'block';
        document.getElementById('cp_sigLabel').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function addPriceRow() {
    const tbody = document.getElementById('priceMatrixBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" class="cp-td-inp pm-product" placeholder="e.g. Carton"></td>
        <td><input type="text" class="cp-td-inp pm-existingPrice" placeholder="0.00"></td>
        <td><input type="text" class="cp-td-inp pm-targetPrice" placeholder="0.00"></td>
        <td><input type="text" class="cp-td-inp pm-approvedPrice" placeholder="0.00"></td>
        <td><input type="text" class="cp-td-inp pm-commission" placeholder="0.00"></td>
        <td><button type="button" class="cp-rm-row-btn" onclick="removePriceRow(this)" title="Remove row">&times;</button></td>
    `;
    tbody.appendChild(tr);
}

function removePriceRow(btn) {
    const tbody = document.getElementById('priceMatrixBody');
    if (tbody.rows.length <= 1) return; // keep at least one row
    btn.closest('tr').remove();
}

function cpSetFeedback(msg, isError) {
    const fb = document.getElementById('cpFeedback');
    if (!fb) return;
    fb.innerHTML = `<div style="padding:10px 14px;border-radius:8px;font-size:13px;font-weight:600;
        background:${isError?'#fee2e2':'#dcfce7'};color:${isError?'#dc2626':'#16a34a'};
        border:1.5px solid ${isError?'#fca5a5':'#86efac'};">${msg}</div>`;
}

async function cpSubmit() {
    const companyName = (document.getElementById('cp_companyName')?.value || '').trim();
    if (!companyName) {
        cpSetFeedback('Company Name is required before submitting.', true);
        document.getElementById('cp_companyName')?.focus();
        return;
    }

    // Collect certifications
    const certIds = ['cp_cert_bsci','cp_cert_wrap','cp_cert_sedex','cp_cert_iso','cp_cert_others'];
    const certifications = certIds
        .map(id => document.getElementById(id))
        .filter(el => el && el.checked)
        .map(el => el.value);

    // Collect product interest
    const piIds = ['cp_pi_carton','cp_pi_poly','cp_pi_hangtag','cp_pi_label','cp_pi_offset','cp_pi_thread','cp_pi_elastic','cp_pi_narrowfabric','cp_pi_others'];
    const productInterest = piIds
        .map(id => document.getElementById(id))
        .filter(el => el && el.checked)
        .map(el => el.value);

    // Collect doc checklist
    const dcIds = ['cp_dc_tradelic','cp_dc_bin','cp_dc_tin','cp_dc_bondlic','cp_dc_banksol','cp_dc_compprofile','cp_dc_compliancecert','cp_dc_facphotos','cp_dc_sampleapproval'];
    const docChecklist = dcIds
        .map(id => document.getElementById(id))
        .filter(el => el && el.checked)
        .map(el => el.value);

    // Collect price matrix rows
    const priceMatrix = [];
    document.querySelectorAll('#priceMatrixBody tr').forEach(tr => {
        const product       = tr.querySelector('.pm-product')?.value?.trim() || '';
        const existingPrice = tr.querySelector('.pm-existingPrice')?.value?.trim() || '';
        const targetPrice   = tr.querySelector('.pm-targetPrice')?.value?.trim() || '';
        const approvedPrice = tr.querySelector('.pm-approvedPrice')?.value?.trim() || '';
        const commission    = tr.querySelector('.pm-commission')?.value?.trim() || '';
        if (product || existingPrice || targetPrice || approvedPrice || commission) {
            priceMatrix.push({ product, existingPrice, targetPrice, approvedPrice, commission });
        }
    });

    const politicsYesEl = document.getElementById('cp_politicsYes');
    const politicsYes   = politicsYesEl && politicsYesEl.checked ? 1 : 0;

    const factoryBuildingEl = document.querySelector('input[name="cp_factoryBuilding"]:checked');
    const factoryBuilding   = factoryBuildingEl ? factoryBuildingEl.value : '';

    const factoryTypeEl = document.querySelector('input[name="cp_factoryType"]:checked');
    const factoryType   = factoryTypeEl ? factoryTypeEl.value : '';

    const subcontractEl = document.querySelector('input[name="cp_subcontract"]:checked');
    const subcontract   = subcontractEl ? subcontractEl.value : 'No';

    const udRequiredEl  = document.querySelector('input[name="cp_udRequired"]:checked');
    const udRequired    = udRequiredEl ? udRequiredEl.value : 'No';

    const extraData = {
        customerCode:         (document.getElementById('cp_customerCode')?.value || '').trim(),
        industry:             (document.getElementById('cp_industry')?.value || '').trim(),
        website:              (document.getElementById('cp_website')?.value || '').trim(),
        tradelicense:         (document.getElementById('cp_tradeLicense')?.value || '').trim(),
        bin:                  (document.getElementById('cp_bin')?.value || '').trim(),
        tin:                  (document.getElementById('cp_tin')?.value || '').trim(),
        bondLicenseExpiry:    (document.getElementById('cp_bondLicenseExpiry')?.value || '').trim(),
        complianceStatus:     (document.getElementById('cp_complianceStatus')?.value || '').trim(),
        factoryBuilding:      factoryBuilding,
        factoryCertifications: certifications,
        bankName:             (document.getElementById('cp_bankName')?.value || '').trim(),
        factoryType:          factoryType,
        monthlyCapacity:      (document.getElementById('cp_monthlyCapacity')?.value || '').trim(),
        dailyProduction:      (document.getElementById('cp_dailyProduction')?.value || '').trim(),
        noOfMachines:         (document.getElementById('cp_noOfMachines')?.value || '').trim(),
        noOfLines:            (document.getElementById('cp_noOfLines')?.value || '').trim(),
        majorBuyers:          (document.getElementById('cp_majorBuyers')?.value || '').trim(),
        majorProducts:        (document.getElementById('cp_majorProducts')?.value || '').trim(),
        peakCapacity:         (document.getElementById('cp_peakCapacity')?.value || '').trim(),
        subcontractFactory:   subcontract,
        commercialName:       (document.getElementById('cp_commercialName')?.value || '').trim(),
        commercialNumber:     (document.getElementById('cp_commercialNumber')?.value || '').trim(),
        merchandiserContact:  (document.getElementById('cp_merchandiserContact')?.value || '').trim(),
        merchandiserMobile:   (document.getElementById('cp_merchandiserMobile')?.value || '').trim(),
        email:                (document.getElementById('cp_email')?.value || '').trim(),
        expectedMonthlyBiz:   (document.getElementById('cp_expectedMonthlyBiz')?.value || '').trim(),
        avgMonthlyOrder:      (document.getElementById('cp_avgMonthlyOrder')?.value || '').trim(),
        leadTime:             (document.getElementById('cp_leadTime')?.value || '').trim(),
        creditFacility:       (document.getElementById('cp_creditFacility')?.value || '').trim(),
        paymentCurrency:      (document.getElementById('cp_paymentCurrency')?.value || '').trim(),
        lcTerms:              (document.getElementById('cp_lcTerms')?.value || '').trim(),
        bbkTerms:             (document.getElementById('cp_bblcTerms')?.value || '').trim(),
        deliveryTerms:        (document.getElementById('cp_deliveryTerms')?.value || '').trim(),
        udRequired:           udRequired,
        zone:                 (document.getElementById('cp_zone')?.value || '').trim(),
        productInterest:      productInterest,
        competitorAnalysis: {
            supplier:        (document.getElementById('cp_compSupplier')?.value || '').trim(),
            currentPrice:    (document.getElementById('cp_compCurrentPrice')?.value || '').trim(),
            strength:        (document.getElementById('cp_compStrength')?.value || '').trim(),
            weakness:        (document.getElementById('cp_compWeakness')?.value || '').trim(),
            reasonForChange: (document.getElementById('cp_compReasonForChange')?.value || '').trim(),
        },
        riskAssessment: {
            financialRisk:   (document.getElementById('cp_financialRisk')?.value || '').trim(),
            paymentHistory:  (document.getElementById('cp_paymentHistory')?.value || '').trim(),
            creditLimitRec:  (document.getElementById('cp_creditLimitRec')?.value || '').trim(),
            remarks:         (document.getElementById('cp_riskRemarks')?.value || '').trim(),
        },
        priceMatrix:   priceMatrix,
        docChecklist:  docChecklist,
    };

    const sigImg        = document.getElementById('cp_sigImg');
    const salesPersonSig = (sigImg && sigImg.src && sigImg.style.display !== 'none') ? sigImg.src : null;

    const payload = {
        companyName,
        addressHeadOffice: (document.getElementById('cp_addressHeadOffice')?.value || '').trim(),
        factoryAddress:    (document.getElementById('cp_factoryAddress')?.value || '').trim(),
        chairmanName:      (document.getElementById('cp_chairmanName')?.value || '').trim(),
        chairmanMobile:    (document.getElementById('cp_chairmanMobile')?.value || '').trim(),
        customerType:      document.getElementById('cp_customerType')?.value || 'Regular',
        dateForm:          document.getElementById('cp_dateForm')?.value || '',
        politicsYes,
        politicsParty:     '',
        extraData,
        salesPersonSig,
    };

    localStorage.removeItem('cp_draft');

    const submitBtn = document.querySelector('#cpFormSection .primary-btn[onclick="cpSubmit()"]');
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Saving…'; }

    try {
        const res  = await fetch(APP_BASE + '/api/customers.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify(payload),
        });
        const json = await res.json();
        if (!res.ok || json.error) {
            cpSetFeedback('Error: ' + (json.error || res.statusText), true);
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Submit Profile'; }
        } else {
            cpSetFeedback('✓ Profile saved &amp; forwarded to Commercial for approval.', false);
            setTimeout(() => location.reload(), 1600);
        }
    } catch(e) {
        cpSetFeedback('Network error: ' + e.message, true);
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Submit Profile'; }
    }
}

const CP_DRAFT_KEY = 'cp_draft';

function cpCollectDraftData() {
    const textIds = [
        'cp_customerCode','cp_companyName','cp_industry','cp_website','cp_dateForm',
        'cp_addressHeadOffice','cp_factoryAddress','cp_chairmanName','cp_chairmanMobile',
        'cp_commercialName','cp_commercialNumber','cp_merchandiserContact','cp_merchandiserMobile','cp_email',
        'cp_tradeLicense','cp_bin','cp_tin','cp_bondLicense','cp_bondLicenseExpiry',
        'cp_complianceStatus','cp_bankName',
        'cp_monthlyCapacity','cp_dailyProduction','cp_noOfMachines','cp_noOfLines',
        'cp_peakCapacity','cp_majorBuyers','cp_majorProducts',
        'cp_expectedMonthlyBiz','cp_avgMonthlyOrder','cp_leadTime','cp_creditFacility',
        'cp_paymentCurrency','cp_lcTerms','cp_bblcTerms','cp_deliveryTerms','cp_zone',
        'cp_compSupplier','cp_compCurrentPrice','cp_compStrength','cp_compWeakness','cp_compReasonForChange',
        'cp_financialRisk','cp_paymentHistory','cp_creditLimitRec','cp_riskRemarks',
    ];
    const selectIds = ['cp_customerType','cp_financialRisk'];
    const radioNames = ['cp_politics','cp_factoryBuilding','cp_factoryType','cp_subcontract','cp_udRequired'];
    const checkIds = [
        'cp_cert_bsci','cp_cert_wrap','cp_cert_sedex','cp_cert_iso','cp_cert_others',
        'cp_pi_carton','cp_pi_poly','cp_pi_hangtag','cp_pi_label','cp_pi_offset',
        'cp_pi_thread','cp_pi_elastic','cp_pi_narrowfabric','cp_pi_others',
        'cp_dc_tradelic','cp_dc_bin','cp_dc_tin','cp_dc_bondlic','cp_dc_banksol',
        'cp_dc_compprofile','cp_dc_compliancecert','cp_dc_facphotos','cp_dc_sampleapproval',
    ];

    const d = { texts: {}, selects: {}, radios: {}, checks: {}, priceMatrix: [] };
    textIds.forEach(id => { const el = document.getElementById(id); if (el) d.texts[id] = el.value; });
    selectIds.forEach(id => { const el = document.getElementById(id); if (el) d.selects[id] = el.value; });
    radioNames.forEach(name => {
        const el = document.querySelector(`input[name="${name}"]:checked`);
        if (el) d.radios[name] = el.value;
    });
    checkIds.forEach(id => { const el = document.getElementById(id); if (el) d.checks[id] = el.checked; });
    document.querySelectorAll('#priceMatrixBody tr').forEach(tr => {
        d.priceMatrix.push({
            product:       tr.querySelector('.pm-product')?.value || '',
            existingPrice: tr.querySelector('.pm-existingPrice')?.value || '',
            targetPrice:   tr.querySelector('.pm-targetPrice')?.value || '',
            approvedPrice: tr.querySelector('.pm-approvedPrice')?.value || '',
            commission:    tr.querySelector('.pm-commission')?.value || '',
        });
    });
    return d;
}

function cpSaveDraft() {
    const d = cpCollectDraftData();
    localStorage.setItem(CP_DRAFT_KEY, JSON.stringify(d));
    const btn = document.getElementById('cpDraftBtn');
    if (btn) { btn.textContent = '✓ Draft Saved'; setTimeout(() => { btn.textContent = '✓ Save Draft'; }, 2000); }
}

function cpLoadDraft() {
    const raw = localStorage.getItem(CP_DRAFT_KEY);
    if (!raw) return;
    try {
        const d = JSON.parse(raw);
        Object.entries(d.texts || {}).forEach(([id, val]) => { const el = document.getElementById(id); if (el) el.value = val; });
        Object.entries(d.selects || {}).forEach(([id, val]) => { const el = document.getElementById(id); if (el) el.value = val; });
        Object.entries(d.radios || {}).forEach(([name, val]) => {
            const el = document.querySelector(`input[name="${name}"][value="${val}"]`);
            if (el) el.checked = true;
        });
        Object.entries(d.checks || {}).forEach(([id, val]) => { const el = document.getElementById(id); if (el) el.checked = val; });
        if (d.priceMatrix && d.priceMatrix.length > 0) {
            const tbody = document.getElementById('priceMatrixBody');
            if (tbody) {
                tbody.innerHTML = '';
                d.priceMatrix.forEach(row => {
                    addPriceRow();
                    const tr = tbody.lastElementChild;
                    if (tr) {
                        tr.querySelector('.pm-product').value       = row.product;
                        tr.querySelector('.pm-existingPrice').value = row.existingPrice;
                        tr.querySelector('.pm-targetPrice').value   = row.targetPrice;
                        tr.querySelector('.pm-approvedPrice').value = row.approvedPrice;
                        tr.querySelector('.pm-commission').value    = row.commission;
                    }
                });
            }
        }
        cpSetFeedback('Draft restored. Fill remaining fields and submit when ready.', false);
    } catch(e) {}
}

// Auto-save draft every 60 seconds while form is open
setInterval(() => {
    if (document.getElementById('cpFormSection')?.style.display !== 'none') cpSaveDraft();
}, 60000);

</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

