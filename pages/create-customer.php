<?php
$pageTitle  = 'Create Customer Profile';
$activePage = 'create-customer';
$navSection = 'master';
include __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/header.php';

$userRole      = $__user['role'] ?? '';
$isSalesPerson = in_array($userRole, ['sales_person', 'admin']);
$isTeamLead    = in_array($userRole, ['team_leader',  'admin']);
$isMarketing   = in_array($userRole, ['marketing',    'admin']);
$canCreate     = $isSalesPerson || $isTeamLead || $isMarketing;
$isCreator     = $isSalesPerson || $isTeamLead || $isMarketing;


if (!$canCreate) {
    echo '<section class="form-card"><p style="color:#dc2626;padding:16px;">You do not have permission to access this page.</p></section>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}
?>

<section class="form-card page-screen active">
    <div class="section-head">
        <div class="section-title">
            <span class="section-tag">New Customer</span>
            <h2>Customer Profile &amp; Commercial Assessment</h2>
        </div>
        <div class="page-actions-right" style="display:flex;gap:8px;align-items:center;">
            <a href="<?= BASE_PATH ?>/pages/customer-profile.php" class="ghost-btn" style="text-decoration:none;">&#8592; Back to List</a>
            <?php if ($isCreator): ?>
            <button class="ghost-btn" style="color:#6366f1;border-color:#6366f1;" onclick="cpSaveDraft()" id="cpDraftBtn">&#10003; Save Draft</button>
            <button class="primary-btn" onclick="cpSubmit()">Submit Profile</button>
            <?php else: ?>
            <button class="primary-btn" id="tlApproveBtn" onclick="tlApprove()" disabled style="opacity:.5;">Approve &amp; Sign</button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isTeamLead && !$isCreator): ?>
    <!-- Team Lead: search bar to load a submitted profile -->
    <div style="background:#f0f9ff;border:1.5px solid #bae6fd;border-radius:10px;padding:14px 16px;margin-bottom:18px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <span style="font-size:12px;font-weight:700;color:#0284c7;text-transform:uppercase;letter-spacing:.05em;">Load Submitted Profile</span>
        <input type="text" id="tlSearchInput" placeholder="Type company name…" list="tlCustomerList"
               style="flex:1;min-width:200px;padding:7px 12px;border:1.5px solid #7dd3fc;border-radius:8px;font-size:13px;outline:none;"
               oninput="tlFilterList(this.value)">
        <datalist id="tlCustomerList"></datalist>
        <button class="primary-btn" style="padding:7px 18px;font-size:13px;" onclick="tlLoadSelected()">Load</button>
        <span id="tlLoadStatus" style="font-size:12px;color:#0284c7;"></span>
    </div>
    <?php endif; ?>

    <div id="cpFeedback" style="margin-bottom:8px;"></div>

    <div id="customerProfileForm">

        <!-- ── Section 1: Customer Information ── -->
        <div class="cp-section-head">1. Customer Information</div>
        <div class="form-grid">
            <div class="span-4">
                <label class="field-label">Customer Category <span style="color:#dc2626;">*</span></label>
                <select id="cp_customerCategory" class="field-input">
                    <option value="">-- Select Category --</option>
                    <option value="Sample Production">Sample Production</option>
                    <option value="Bulk Production">Bulk Production</option>
                    <option value="Negotiation Stage">Negotiation Stage</option>
                </select>
            </div>
            <div class="span-3">
                <label class="field-label">Customer Code</label>
                <input type="text" id="cp_customerCode" class="field-input" placeholder="Auto-generated" readonly
                       style="background:#f8f9ff;color:#374151;cursor:default;">
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
            <input type="hidden" id="cp_salesPersonId" value="<?= (int)($__user['id'] ?? 0) ?>">
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
                <input type="date" id="cp_dateForm" class="field-input" readonly
                       style="background:#f8f9ff;color:#374151;cursor:default;">
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
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6366f1;margin-bottom:10px;">Chairman / MD / Director</div>
                <div style="display:flex;gap:20px;margin-bottom:12px;flex-wrap:wrap;">
                    <?php foreach(['Chairman','MD','Director','Owner'] as $role): ?>
                    <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;font-weight:500;color:#374151;">
                        <input type="radio" name="cp_chairmanRole" value="<?= $role ?>" id="cp_role_<?= strtolower($role) ?>"
                               style="accent-color:#6366f1;width:15px;height:15px;"
                               <?= $role==='Chairman' ? 'checked' : '' ?>>
                        <?= $role ?>
                    </label>
                    <?php endforeach; ?>
                </div>
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
                <input type="text" id="cp_complianceStatus" class="field-input" placeholder="e.g. Compliant">
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
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="pilbl_carton">
                        <input type="checkbox" id="cp_pi_carton" value="Carton" onchange="pillHighlight('pilbl_carton',this);syncLeadTimeFields()"> Carton
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="pilbl_poly">
                        <input type="checkbox" id="cp_pi_poly" value="Poly" onchange="pillHighlight('pilbl_poly',this);syncLeadTimeFields()"> Poly
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="pilbl_hangtag">
                        <input type="checkbox" id="cp_pi_hangtag" value="Hang Tag" onchange="pillHighlight('pilbl_hangtag',this);syncLeadTimeFields()"> Hang Tag
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="pilbl_label">
                        <input type="checkbox" id="cp_pi_label" value="Label" onchange="pillHighlight('pilbl_label',this);syncLeadTimeFields()"> Label
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="pilbl_offset">
                        <input type="checkbox" id="cp_pi_offset" value="Offset" onchange="pillHighlight('pilbl_offset',this);syncLeadTimeFields()"> Offset
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="pilbl_thread">
                        <input type="checkbox" id="cp_pi_thread" value="Thread" onchange="pillHighlight('pilbl_thread',this);syncLeadTimeFields()"> Thread
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="pilbl_elastic">
                        <input type="checkbox" id="cp_pi_elastic" value="Elastic" onchange="pillHighlight('pilbl_elastic',this);syncLeadTimeFields()"> Elastic
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="pilbl_narrowfabric">
                        <input type="checkbox" id="cp_pi_narrowfabric" value="Narrow Fabric" onchange="pillHighlight('pilbl_narrowfabric',this);syncLeadTimeFields()"> Narrow Fabric
                    </label>
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:6px 12px;border:1.5px solid #d1d5db;border-radius:8px;" id="pilbl_others">
                        <input type="checkbox" id="cp_pi_others" value="Others" onchange="pillHighlight('pilbl_others',this);syncLeadTimeFields()"> Others
                    </label>
                </div>
            </div>
            <div class="span-12">
                <label class="field-label">Required Lead Time <span style="font-weight:400;color:#9ca3af;font-size:11px;">(per selected product)</span></label>
                <div id="leadTimeFields" style="display:flex;flex-wrap:wrap;gap:10px;margin-top:4px;min-height:36px;">
                    <span style="font-size:12px;color:#9ca3af;padding:8px 0;">Select a product above to enter its lead time.</span>
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

        <!-- ── Section 9: Document Checklist (Team Lead only) ── -->
        <?php if ($isTeamLead): ?>
        <div class="cp-section-head">9. Document Checklist <span style="font-size:10px;font-weight:500;color:#0284c7;background:#e0f2fe;padding:2px 8px;border-radius:12px;margin-left:6px;">Team Lead</span></div>
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
        <?php else: ?>
        <div style="background:#f8fafc;border:1.5px dashed #cbd5e1;border-radius:10px;padding:16px 20px;margin:20px 0;text-align:center;color:#94a3b8;font-size:13px;">
            🔒 Section 9 — Document Checklist is completed by the <strong>Team Lead</strong> after review.
        </div>
        <?php endif; ?>


        <!-- Bottom actions -->
        <div style="display:flex;justify-content:flex-end;gap:10px;padding:20px 0 8px;border-top:1.5px solid #e0e3ff;margin-top:8px;">
            <?php if ($isCreator): ?>
            <button class="ghost-btn" style="color:#6366f1;border-color:#6366f1;" onclick="cpSaveDraft()">&#10003; Save Draft</button>
            <button class="primary-btn" style="padding:10px 32px;font-size:14px;" onclick="cpSubmit()">Submit Profile</button>
            <?php else: ?>
            <button class="primary-btn" style="padding:10px 32px;font-size:14px;" id="tlApproveBtnBottom" onclick="tlApprove()" disabled style="opacity:.5;">Approve &amp; Sign</button>
            <?php endif; ?>
        </div>

    </div><!-- #customerProfileForm -->
</section>

<style>
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

.cp-section-head {
    font-size:12px; font-weight:700; text-transform:uppercase;
    letter-spacing:.07em; color:#6366f1;
    border-bottom:2px solid #e0e3ff; padding-bottom:6px;
    margin:24px 0 14px;
}

.cp-matrix-table { width:100%; border-collapse:collapse; font-size:13px; min-width:560px; }
.cp-matrix-table th {
    background:#f5f7ff; color:#6366f1; font-weight:700; font-size:11px;
    text-transform:uppercase; letter-spacing:.05em;
    padding:8px 10px; text-align:left; border:1px solid #e0e3ff;
}
.cp-matrix-table td { padding:6px 6px; border:1px solid #e8eaff; }
.cp-td-inp {
    width:100%; border:none; border-bottom:1.5px solid #d1d5db; background:transparent;
    font-size:13px; padding:4px 4px; outline:none;
    transition:border-color .15s;
}
.cp-td-inp:focus { border-bottom-color:#6366f1; background:#f5f7ff; }
.cp-rm-row-btn {
    background:#fee2e2; border:none; border-radius:6px; color:#dc2626;
    width:28px; height:28px; font-size:16px; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
}
.cp-rm-row-btn:hover { background:#fecaca; }
</style>

<script>
let _cpCodeLocked = false;

function cpExtractCodeNumber(code) {
    const m = String(code || '').match(/CUST-(\d+)/i);
    return m ? parseInt(m[1], 10) : 0;
}

async function cpGenerateCustomerCode() {
    const codeEl = document.getElementById('cp_customerCode');
    if (!codeEl || _cpCodeLocked || (codeEl.value || '').trim()) return;
    try {
        const res = await fetch(window.APP_BASE + '/api/customers.php');
        const list = await res.json();
        if (!Array.isArray(list)) return;
        let maxNo = 0;
        list.forEach(c => {
            let extra = {};
            try { extra = typeof c.extra_data === 'string' ? JSON.parse(c.extra_data || '{}') : (c.extra_data || {}); } catch (_) {}
            maxNo = Math.max(maxNo, cpExtractCodeNumber(extra.customerCode || ''));
        });
        codeEl.value = 'CUST-' + String(maxNo + 1).padStart(3, '0');
    } catch (_) {}
}

// Auto-set today's date on the form date field
document.addEventListener('DOMContentLoaded', function() {
    const d = document.getElementById('cp_dateForm');
    if (d && !d.value) d.value = new Date().toISOString().slice(0,10);
    cpGenerateCustomerCode();
});

function pillHighlight(lblId, cb) {
    const lbl = document.getElementById(lblId);
    if (!lbl) return;
    lbl.style.borderColor = cb.checked ? '#6366f1' : '#d1d5db';
    lbl.style.background  = cb.checked ? '#ede9fe' : '';
    lbl.style.color       = cb.checked ? '#4f46e5' : '';
}

const PI_CHECKBOXES = [
    {id:'cp_pi_carton',     label:'Carton'},
    {id:'cp_pi_poly',       label:'Poly'},
    {id:'cp_pi_hangtag',    label:'Hang Tag'},
    {id:'cp_pi_label',      label:'Label'},
    {id:'cp_pi_offset',     label:'Offset'},
    {id:'cp_pi_thread',     label:'Thread'},
    {id:'cp_pi_elastic',    label:'Elastic'},
    {id:'cp_pi_narrowfabric',label:'Narrow Fabric'},
    {id:'cp_pi_others',     label:'Others'},
];

function syncLeadTimeFields() {
    const container = document.getElementById('leadTimeFields');
    if (!container) return;
    // preserve existing values before rebuilding
    const existing = {};
    container.querySelectorAll('input[data-product]').forEach(el => {
        existing[el.dataset.product] = el.value;
    });
    const checked = PI_CHECKBOXES.filter(p => document.getElementById(p.id)?.checked);
    if (!checked.length) {
        container.innerHTML = '<span style="font-size:12px;color:#9ca3af;padding:8px 0;">Check a product in Section 5 to set its lead time.</span>';
        return;
    }
    container.innerHTML = checked.map(p => `
        <div style="display:flex;align-items:center;gap:6px;background:#f5f7ff;border:1.5px solid #e0e3ff;border-radius:8px;padding:6px 10px;">
            <span style="font-size:12px;font-weight:600;color:#4f46e5;white-space:nowrap;">${p.label}</span>
            <input type="number" min="1" data-product="${p.label}"
                   style="width:60px;padding:4px 6px;border:1px solid #c7d2fe;border-radius:6px;font-size:13px;outline:none;text-align:center;"
                   placeholder="0" value="${existing[p.label] || ''}">
            <span style="font-size:12px;color:#6b7280;">days</span>
        </div>`).join('');
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

function loadTlSig(input) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const img = document.getElementById('tl_sigImg');
        if (!img) return;
        img.src = e.target.result;
        img.style.display = 'block';
        document.getElementById('tl_sigLabel').style.display = 'none';
    };
    reader.readAsDataURL(file);
}

<?php if ($isTeamLead): ?>
// ── Team Lead: load customer list for search ──────────────────────────────
let _tlCustomers = [];
let _tlLoadedId  = null;

(async function fetchTlCustomerList() {
    try {
        const res  = await fetch(window.APP_BASE + '/api/customers.php');
        const all  = await res.json();
        _tlCustomers = all.filter(c => ['commercial','team_leader'].includes(c.stage));
        const dl = document.getElementById('tlCustomerList');
        if (dl) {
            dl.innerHTML = _tlCustomers.map(c => `<option value="${c.company_name}" data-id="${c.id}">`).join('');
        }
        const status = document.getElementById('tlLoadStatus');
        if (status && _tlCustomers.length === 0) {
            status.textContent = 'No profiles pending review.';
        }
        // Auto-load if ?load=ID in URL
        const urlId = new URLSearchParams(window.location.search).get('load');
        if (urlId) {
            document.getElementById('tlLoadStatus').textContent = 'Loading…';
            const r2   = await fetch(window.APP_BASE + '/api/customers.php?id=' + urlId);
            const data = await r2.json();
            if (data) {
                _tlLoadedId = data.id;
                if (document.getElementById('tlSearchInput'))
                    document.getElementById('tlSearchInput').value = data.company_name;
                tlPopulateForm(data);
                document.getElementById('tlLoadStatus').textContent = '✓ Loaded — ' + (data.company_name || '');
                const approveBtn = document.getElementById('tlApproveBtn');
                if (approveBtn) { approveBtn.disabled = false; approveBtn.style.opacity = '1'; }
                const approveBtnB = document.getElementById('tlApproveBtnBottom');
                if (approveBtnB) { approveBtnB.disabled = false; approveBtnB.style.opacity = '1'; }
                const spSig = data.signatures?.sales_person;
                if (spSig) {
                    const img = document.getElementById('cp_sigImg');
                    if (img) { img.src = spSig; img.style.display = 'block'; }
                    const ph = document.getElementById('cpSigPlaceholder');
                    if (ph) ph.style.display = 'none';
                }
            }
        }
    } catch(e) {}
})();

function tlFilterList(val) {}  // datalist handles filtering natively

async function tlLoadSelected() {
    const input = document.getElementById('tlSearchInput');
    const name  = input?.value?.trim();
    if (!name) return;
    const match = _tlCustomers.find(c => c.company_name.toLowerCase() === name.toLowerCase());
    if (!match) { document.getElementById('tlLoadStatus').textContent = '✗ Not found'; return; }
    document.getElementById('tlLoadStatus').textContent = 'Loading…';
    try {
        const res  = await fetch(window.APP_BASE + '/api/customers.php?id=' + match.id);
        const data = await res.json();
        if (!data) { document.getElementById('tlLoadStatus').textContent = '✗ Error loading'; return; }
        _tlLoadedId = data.id;
        tlPopulateForm(data);
        document.getElementById('tlLoadStatus').textContent = '✓ Loaded — Stage: ' + (data.stage || '');
        const approveBtn = document.getElementById('tlApproveBtn');
        if (approveBtn) { approveBtn.disabled = false; approveBtn.style.opacity = '1'; }
        // Show sales person sig if present
        const spSig = data.signatures?.sales_person;
        if (spSig) {
            const img = document.getElementById('cp_sigImg');
            if (img) { img.src = spSig; img.style.display = 'block'; }
            const lbl = document.getElementById('cp_sigLabel');
            if (lbl) lbl.style.display = 'none';
        }
    } catch(e) { document.getElementById('tlLoadStatus').textContent = '✗ ' + e.message; }
}

function tlPopulateForm(d) {
    const extra = (typeof d.extra_data === 'string') ? JSON.parse(d.extra_data || '{}') : (d.extra_data || {});
    const set = (id, val) => { const el = document.getElementById(id); if (el) { el.value = val || ''; el.disabled = true; } };
    _cpCodeLocked = true;
    set('cp_customerCategory',  extra.customerCategory  || '');
    set('cp_customerCode',      extra.customerCode      || '');
    set('cp_companyName',       d.company_name          || '');
    set('cp_industry',          extra.industry          || '');
    set('cp_website',           extra.website           || '');
    set('cp_dateForm',          d.date_form             || '');
    set('cp_addressHeadOffice', d.address_head_office   || '');
    set('cp_factoryAddress',    d.factory_address       || '');
    set('cp_chairmanName',      d.chairman_name         || '');
    set('cp_chairmanMobile',    d.chairman_mobile       || '');
    set('cp_commercialName',    extra.commercialName    || '');
    set('cp_commercialNumber',  extra.commercialNumber  || '');
    set('cp_merchandiserContact', extra.merchandiserContact || '');
    set('cp_merchandiserMobile',  extra.merchandiserMobile  || '');
    set('cp_email',             extra.email             || '');
    set('cp_expectedMonthlyBiz',extra.expectedMonthlyBiz|| '');
    set('cp_avgMonthlyOrder',   extra.avgMonthlyOrder   || '');
    set('cp_creditFacility',    extra.creditFacility    || '');
    set('cp_paymentCurrency',   extra.paymentCurrency   || '');
    set('cp_lcTerms',           extra.lcTerms           || '');
    set('cp_bblcTerms',         extra.bblcTerms         || '');
    set('cp_deliveryTerms',     extra.deliveryTerms     || '');
    set('cp_zone',              extra.zone              || '');
    // product interest (read-only checkboxes)
    const pi = extra.productInterest || [];
    PI_CHECKBOXES.forEach(p => {
        const el = document.getElementById(p.id);
        if (el) { el.checked = pi.includes(p.label); el.disabled = true; pillHighlight('pilbl_' + p.id.replace('cp_pi_',''), el); }
    });
    syncLeadTimeFields();
    // lead times (read-only)
    const lt = extra.leadTimes || {};
    document.querySelectorAll('#leadTimeFields input[data-product]').forEach(el => {
        el.value = lt[el.dataset.product] || ''; el.disabled = true;
    });
    // customer type
    const ctEl = document.getElementById('cp_customerType');
    if (ctEl) { ctEl.value = d.customer_type || 'Regular'; ctEl.disabled = true; }
    // chairman role
    if (extra.chairmanRole) {
        const roleEl = document.querySelector(`input[name="cp_chairmanRole"][value="${extra.chairmanRole}"]`);
        if (roleEl) { roleEl.checked = true; }
    }
    document.querySelectorAll('input[name="cp_chairmanRole"]').forEach(el => el.disabled = true);
    // politics
    if (extra.politicsYes !== undefined) {
        const polEl = document.querySelector(`input[name="cp_politics"][value="${extra.politicsYes ? 'yes' : 'no'}"]`);
        if (polEl) polEl.checked = true;
    }
    document.querySelectorAll('input[name="cp_politics"]').forEach(el => el.disabled = true);
}

async function tlApprove() {
    const id = _tlLoadedId;
    if (!id) { alert('Please load a customer profile first.'); return; }
    const dcIds = ['cp_dc_tradelic','cp_dc_bin','cp_dc_tin','cp_dc_bondlic','cp_dc_banksol',
                   'cp_dc_compprofile','cp_dc_compliancecert','cp_dc_facphotos','cp_dc_sampleapproval'];
    const docChecklist = dcIds.map(i => document.getElementById(i)).filter(el => el?.checked).map(el => el.value);
    const tlSigImg = document.getElementById('tl_sigImg');
    const tlSig = (tlSigImg?.src && tlSigImg.style.display !== 'none') ? tlSigImg.src : null;
    const btn = document.getElementById('tlApproveBtn');
    if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }
    try {
        const res  = await fetch(window.APP_BASE + '/api/customers.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, sig: tlSig, extraData: { docChecklist } }),
        });
        const data = await res.json();
        if (data.ok) {
            alert('✓ Profile approved! Moved to stage: ' + data.stage);
            window.location.href = window.APP_BASE + '/pages/customer-profile.php';
        } else {
            alert('Error: ' + (data.error || 'Unknown'));
            if (btn) { btn.disabled = false; btn.textContent = 'Approve & Sign'; }
        }
    } catch(e) {
        alert('Network error: ' + e.message);
        if (btn) { btn.disabled = false; btn.textContent = 'Approve & Sign'; }
    }
}
<?php endif; ?>

function addPriceRow() {
    const tbody = document.getElementById('priceMatrixBody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" class="cp-td-inp pm-product" placeholder="e.g. Carton"></td>
        <td><input type="text" class="cp-td-inp pm-existingPrice" placeholder="0.00"></td>
        <td><input type="text" class="cp-td-inp pm-targetPrice" placeholder="0.00"></td>
        <td><input type="text" class="cp-td-inp pm-approvedPrice" placeholder="0.00"></td>
        <td><input type="text" class="cp-td-inp pm-commission" placeholder="0.00"></td>
        <td><button type="button" class="cp-rm-row-btn" onclick="removePriceRow(this)">&times;</button></td>
    `;
    tbody.appendChild(tr);
}

function removePriceRow(btn) {
    const tbody = document.getElementById('priceMatrixBody');
    if (tbody.rows.length <= 1) return;
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
    const customerCategory = (document.getElementById('cp_customerCategory')?.value || '').trim();
    if (!customerCategory) {
        cpSetFeedback('Customer Category is required before submitting.', true);
        document.getElementById('cp_customerCategory')?.focus();
        return;
    }
    if (!companyName) {
        cpSetFeedback('Company Name is required before submitting.', true);
        document.getElementById('cp_companyName')?.focus();
        return;
    }

    const certIds = ['cp_cert_bsci','cp_cert_wrap','cp_cert_sedex','cp_cert_iso','cp_cert_others'];
    const certifications = certIds.map(id => document.getElementById(id)).filter(el => el?.checked).map(el => el.value);

    const piIds = ['cp_pi_carton','cp_pi_poly','cp_pi_hangtag','cp_pi_label','cp_pi_offset','cp_pi_thread','cp_pi_elastic','cp_pi_narrowfabric','cp_pi_others'];
    const productInterest = piIds.map(id => document.getElementById(id)).filter(el => el?.checked).map(el => el.value);

    const dcIds = ['cp_dc_tradelic','cp_dc_bin','cp_dc_tin','cp_dc_bondlic','cp_dc_banksol','cp_dc_compprofile','cp_dc_compliancecert','cp_dc_facphotos','cp_dc_sampleapproval'];
    const docChecklist = dcIds.map(id => document.getElementById(id)).filter(el => el?.checked).map(el => el.value);

    const priceMatrix = [];
    document.querySelectorAll('#priceMatrixBody tr').forEach(tr => {
        const product       = tr.querySelector('.pm-product')?.value?.trim() || '';
        const existingPrice = tr.querySelector('.pm-existingPrice')?.value?.trim() || '';
        const targetPrice   = tr.querySelector('.pm-targetPrice')?.value?.trim() || '';
        const approvedPrice = tr.querySelector('.pm-approvedPrice')?.value?.trim() || '';
        const commission    = tr.querySelector('.pm-commission')?.value?.trim() || '';
        if (product || existingPrice || targetPrice || approvedPrice || commission)
            priceMatrix.push({ product, existingPrice, targetPrice, approvedPrice, commission });
    });

    const politicsYes      = document.getElementById('cp_politicsYes')?.checked ? 1 : 0;
    const factoryBuilding  = document.querySelector('input[name="cp_factoryBuilding"]:checked')?.value || '';
    const factoryType      = document.querySelector('input[name="cp_factoryType"]:checked')?.value || '';
    const subcontract      = document.querySelector('input[name="cp_subcontract"]:checked')?.value || 'No';
    const udRequired       = document.querySelector('input[name="cp_udRequired"]:checked')?.value || 'No';

    const extraData = {
        customerCategory,
        customerCode: document.getElementById('cp_customerCode')?.value?.trim() || '',
        industry: document.getElementById('cp_industry')?.value?.trim() || '',
        website: document.getElementById('cp_website')?.value?.trim() || '',
        tradelicense: document.getElementById('cp_tradeLicense')?.value?.trim() || '',
        bin: document.getElementById('cp_bin')?.value?.trim() || '',
        tin: document.getElementById('cp_tin')?.value?.trim() || '',
        bondLicenseExpiry: document.getElementById('cp_bondLicenseExpiry')?.value?.trim() || '',
        complianceStatus: document.getElementById('cp_complianceStatus')?.value?.trim() || '',
        factoryBuilding, factoryCertifications: certifications,
        bankName: document.getElementById('cp_bankName')?.value?.trim() || '',
        factoryType,
        monthlyCapacity: document.getElementById('cp_monthlyCapacity')?.value?.trim() || '',
        dailyProduction: document.getElementById('cp_dailyProduction')?.value?.trim() || '',
        noOfMachines: document.getElementById('cp_noOfMachines')?.value?.trim() || '',
        noOfLines: document.getElementById('cp_noOfLines')?.value?.trim() || '',
        majorBuyers: document.getElementById('cp_majorBuyers')?.value?.trim() || '',
        majorProducts: document.getElementById('cp_majorProducts')?.value?.trim() || '',
        peakCapacity: document.getElementById('cp_peakCapacity')?.value?.trim() || '',
        subcontractFactory: subcontract,
        commercialName: document.getElementById('cp_commercialName')?.value?.trim() || '',
        commercialNumber: document.getElementById('cp_commercialNumber')?.value?.trim() || '',
        merchandiserContact: document.getElementById('cp_merchandiserContact')?.value?.trim() || '',
        merchandiserMobile: document.getElementById('cp_merchandiserMobile')?.value?.trim() || '',
        email: document.getElementById('cp_email')?.value?.trim() || '',
        expectedMonthlyBiz: document.getElementById('cp_expectedMonthlyBiz')?.value?.trim() || '',
        avgMonthlyOrder: document.getElementById('cp_avgMonthlyOrder')?.value?.trim() || '',
        leadTimes: (function(){ const o={}; document.querySelectorAll('#leadTimeFields input[data-product]').forEach(el=>{ if(el.value.trim()) o[el.dataset.product]=el.value.trim(); }); return o; })(),
        creditFacility: document.getElementById('cp_creditFacility')?.value?.trim() || '',
        paymentCurrency: document.getElementById('cp_paymentCurrency')?.value?.trim() || '',
        lcTerms: document.getElementById('cp_lcTerms')?.value?.trim() || '',
        bbkTerms: document.getElementById('cp_bblcTerms')?.value?.trim() || '',
        deliveryTerms: document.getElementById('cp_deliveryTerms')?.value?.trim() || '',
        udRequired, zone: document.getElementById('cp_zone')?.value?.trim() || '',
        productInterest,
        competitorAnalysis: {
            supplier: document.getElementById('cp_compSupplier')?.value?.trim() || '',
            currentPrice: document.getElementById('cp_compCurrentPrice')?.value?.trim() || '',
            strength: document.getElementById('cp_compStrength')?.value?.trim() || '',
            weakness: document.getElementById('cp_compWeakness')?.value?.trim() || '',
            reasonForChange: document.getElementById('cp_compReasonForChange')?.value?.trim() || '',
        },
        riskAssessment: {
            financialRisk: document.getElementById('cp_financialRisk')?.value?.trim() || '',
            paymentHistory: document.getElementById('cp_paymentHistory')?.value?.trim() || '',
            creditLimitRec: document.getElementById('cp_creditLimitRec')?.value?.trim() || '',
            remarks: document.getElementById('cp_riskRemarks')?.value?.trim() || '',
        },
        priceMatrix, docChecklist,
    };

    const sigImg = document.getElementById('cp_sigImg');
    const creatorSig = (sigImg?.src && sigImg.style.display !== 'none') ? sigImg.src : null;

    const payload = {
        companyName,
        addressHeadOffice: document.getElementById('cp_addressHeadOffice')?.value?.trim() || '',
        factoryAddress:    document.getElementById('cp_factoryAddress')?.value?.trim() || '',
        chairmanRole:      document.querySelector('input[name="cp_chairmanRole"]:checked')?.value || 'Chairman',
        chairmanName:      document.getElementById('cp_chairmanName')?.value?.trim() || '',
        chairmanMobile:    document.getElementById('cp_chairmanMobile')?.value?.trim() || '',
        customerType:      document.getElementById('cp_customerType')?.value || 'Regular',
        dateForm:          document.getElementById('cp_dateForm')?.value || '',
        politicsYes, politicsParty: '', extraData, creatorSig,
        creatorRole: <?= json_encode($userRole) ?>,
        salesPersonId: parseInt(document.getElementById('cp_salesPersonId')?.value || '0'),
    };

    localStorage.removeItem('cp_draft');

    const submitBtn = document.querySelector('.primary-btn[onclick="cpSubmit()"]');
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
            document.getElementById('cpFeedback')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Submit Profile'; }
        } else {
            cpSetFeedback('✓ Customer profile saved successfully!', false);
            document.getElementById('cpFeedback')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            alert('✓ Customer profile saved successfully!\n\nRedirecting to customer list…');
            window.location.href = APP_BASE + '/pages/customer-profile.php';
        }
    } catch(e) {
        cpSetFeedback('Network error: ' + e.message, true);
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Submit Profile'; }
    }
}

const CP_DRAFT_KEY = 'cp_draft';

function cpSaveDraft() {
    const textIds = [
        'cp_customerCode','cp_companyName','cp_industry','cp_website',
        'cp_addressHeadOffice','cp_factoryAddress','cp_chairmanName','cp_chairmanMobile',
        'cp_commercialName','cp_commercialNumber','cp_merchandiserContact','cp_merchandiserMobile','cp_email',
        'cp_tradeLicense','cp_bin','cp_tin','cp_bondLicense','cp_bondLicenseExpiry',
        'cp_complianceStatus','cp_bankName',
        'cp_monthlyCapacity','cp_dailyProduction','cp_noOfMachines','cp_noOfLines',
        'cp_peakCapacity','cp_majorBuyers','cp_majorProducts',
        'cp_expectedMonthlyBiz','cp_avgMonthlyOrder','cp_creditFacility',
        'cp_paymentCurrency','cp_lcTerms','cp_bblcTerms','cp_deliveryTerms','cp_zone',
        'cp_compSupplier','cp_compCurrentPrice','cp_compStrength','cp_compWeakness','cp_compReasonForChange',
        'cp_financialRisk','cp_paymentHistory','cp_creditLimitRec','cp_riskRemarks',
    ];
    const d = { texts: {}, selects: {}, radios: {}, checks: {}, priceMatrix: [] };
    textIds.forEach(id => { const el = document.getElementById(id); if (el) d.texts[id] = el.value; });
    ['cp_customerCategory','cp_customerType','cp_financialRisk'].forEach(id => { const el = document.getElementById(id); if (el) d.selects[id] = el.value; });
    ['cp_chairmanRole','cp_politics','cp_factoryBuilding','cp_factoryType','cp_subcontract','cp_udRequired'].forEach(name => {
        const el = document.querySelector(`input[name="${name}"]:checked`);
        if (el) d.radios[name] = el.value;
    });
    [
        'cp_cert_bsci','cp_cert_wrap','cp_cert_sedex','cp_cert_iso','cp_cert_others',
        'cp_pi_carton','cp_pi_poly','cp_pi_hangtag','cp_pi_label','cp_pi_offset',
        'cp_pi_thread','cp_pi_elastic','cp_pi_narrowfabric','cp_pi_others',
        'cp_dc_tradelic','cp_dc_bin','cp_dc_tin','cp_dc_bondlic','cp_dc_banksol',
        'cp_dc_compprofile','cp_dc_compliancecert','cp_dc_facphotos','cp_dc_sampleapproval',
    ].forEach(id => { const el = document.getElementById(id); if (el) d.checks[id] = el.checked; });
    document.querySelectorAll('#priceMatrixBody tr').forEach(tr => {
        d.priceMatrix.push({
            product:       tr.querySelector('.pm-product')?.value || '',
            existingPrice: tr.querySelector('.pm-existingPrice')?.value || '',
            targetPrice:   tr.querySelector('.pm-targetPrice')?.value || '',
            approvedPrice: tr.querySelector('.pm-approvedPrice')?.value || '',
            commission:    tr.querySelector('.pm-commission')?.value || '',
        });
    });
    // save lead times
    d.leadTimes = {};
    document.querySelectorAll('#leadTimeFields input[data-product]').forEach(el => {
        d.leadTimes[el.dataset.product] = el.value.trim();
    });
    localStorage.setItem(CP_DRAFT_KEY, JSON.stringify(d));
    const btn = document.getElementById('cpDraftBtn');
    if (btn) { btn.textContent = '✓ Draft Saved'; setTimeout(() => { btn.textContent = '✓ Save Draft'; }, 2000); }
}

// Restore draft on load
(function cpLoadDraft() {
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
        // restore lead times: tick checkboxes first (already done above), then syncLeadTimeFields, then fill values
        if (d.leadTimes && Object.keys(d.leadTimes).length) {
            syncLeadTimeFields();
            Object.entries(d.leadTimes).forEach(([product, val]) => {
                const el = document.querySelector(`#leadTimeFields input[data-product="${product}"]`);
                if (el) el.value = val;
            });
        }
    } catch(e) {}
})();

// Auto-save draft every 60 seconds
setInterval(cpSaveDraft, 60000);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
