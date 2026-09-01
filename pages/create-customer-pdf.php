<?php
$pageTitle = 'Printable Customer Form';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/print-brand.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ZNZAL Customer Profile Form</title>
<style>
    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: "Segoe UI", Arial, sans-serif;
        background: #e5e7eb;
        color: #1f2937;
    }
    .toolbar {
        max-width: 210mm;
        margin: 16px auto 0;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }
    .btn {
        display: inline-block;
        padding: 10px 18px;
        border-radius: 10px;
        border: 1.5px solid #4f46e5;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }
    .btn.primary {
        background: #4f46e5;
        color: #fff;
    }
    .btn.ghost {
        background: #fff;
        color: #4f46e5;
    }
    .sheet {
        width: 210mm;
        min-height: 297mm;
        margin: 14px auto 20px;
        background: #fff;
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.14);
        padding: 12mm 12mm 16mm;
    }
    .doc-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        border-bottom: 2px solid #4f46e5;
        padding-bottom: 10px;
        margin-bottom: 14px;
    }
    .doc-head h1 {
        margin: 0;
        font-size: 24px;
        line-height: 1.15;
        color: #111827;
    }
    .doc-sub {
        margin-top: 4px;
        font-size: 12px;
        color: #6b7280;
    }
    .doc-meta {
        text-align: right;
        font-size: 12px;
        color: #4f46e5;
        font-weight: 700;
    }
    .section {
        margin-top: 18px;
        page-break-inside: avoid;
    }
    .section-title {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #4f46e5;
        border-bottom: 1.5px solid #dbe4ff;
        padding-bottom: 5px;
        margin-bottom: 10px;
    }
    .grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 10px 12px;
    }
    .span-12 { grid-column: span 12; }
    .span-8 { grid-column: span 8; }
    .span-6 { grid-column: span 6; }
    .span-4 { grid-column: span 4; }
    .span-3 { grid-column: span 3; }
    .span-2 { grid-column: span 2; }
    .field label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #64748b;
        margin-bottom: 5px;
    }
    .field input,
    .field select,
    .field textarea {
        width: 100%;
        border: 1.25px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #111827;
        padding: 8px 10px;
        font-size: 12px;
        font-family: inherit;
    }
    .field textarea {
        min-height: 66px;
        resize: vertical;
    }
    .line-box {
        border: 1.25px solid #dbe4ff;
        border-radius: 10px;
        padding: 12px;
        background: #fafbff;
    }
    .choice-row {
        display: flex;
        flex-wrap: wrap;
        gap: 14px 18px;
        padding-top: 4px;
        min-height: 34px;
    }
    .choice-row label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #1f2937;
        text-transform: none;
        letter-spacing: 0;
        margin: 0;
    }
    .choice-row input[type="checkbox"],
    .choice-row input[type="radio"] {
        width: 14px;
        height: 14px;
        margin: 0;
    }
    table.matrix {
        width: 100%;
        border-collapse: collapse;
        margin-top: 6px;
        font-size: 12px;
    }
    table.matrix th,
    table.matrix td {
        border: 1px solid #dbe4ff;
        padding: 7px 8px;
        text-align: left;
        vertical-align: top;
    }
    table.matrix th {
        background: #f6f8ff;
        color: #4f46e5;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    table.matrix td input {
        width: 100%;
        border: none;
        outline: none;
        padding: 2px 0;
        background: transparent;
        font-size: 12px;
        font-family: inherit;
    }
    .signature-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
        margin-top: 10px;
    }
    .signature-box {
        min-height: 92px;
        border: 1.25px solid #dbe4ff;
        border-radius: 10px;
        padding: 10px;
        background: #fff;
    }
    .signature-line {
        margin-top: 48px;
        border-top: 1px solid #334155;
        padding-top: 6px;
        font-size: 11px;
        font-weight: 700;
        text-align: center;
    }
    .hint {
        margin-top: 8px;
        font-size: 11px;
        color: #6b7280;
    }
    @media print {
        @page { size: A4; margin: 10mm; }
        body { background: #fff; }
        .toolbar { display: none; }
        .sheet {
            width: 100%;
            min-height: 0;
            margin: 0;
            box-shadow: none;
            padding: 0;
        }
    }
</style>
</head>
<body>
<div class="toolbar">
    <a class="btn ghost" href="<?= BASE_PATH ?>/pages/create-customer.php">&#8592; Back to Customer Form</a>
    <button class="btn primary" type="button" onclick="window.print()">Download / Print PDF</button>
</div>

<div class="sheet">
    <?= zzal_print_brand_header() ?>

    <div class="doc-head">
        <div>
            <h1>Customer Profile &amp; Commercial Assessment</h1>
        </div>
        <div class="doc-meta">
            Date: ____________
        </div>
    </div>

    <div class="section">
        <div class="section-title">1. Customer Information</div>
        <div class="grid">
            <div class="field span-3">
                <label>Customer Code</label>
                <input type="text">
            </div>
            <div class="field span-6">
                <label>Group Name</label>
                <input type="text">
            </div>
            <div class="field span-3">
                <label>Customer Type</label>
                <select>
                    <option></option>
                    <option>New</option>
                    <option>Regular</option>
                </select>
            </div>
            <div class="field span-4">
                <label>Business Type</label>
                <input type="text">
            </div>
            <div class="field span-3">
                <label>Website</label>
                <input type="text">
            </div>
            <div class="field span-2">
                <label>Form Date</label>
                <input type="text">
            </div>
            <div class="field span-6">
                <label>Head Office / Registered Address</label>
                <textarea></textarea>
            </div>
            <div class="field span-6">
                <label>Factory Address</label>
                <textarea></textarea>
            </div>
        </div>

        <div class="grid" style="margin-top:12px;">
            <div class="span-12 line-box">
                <div class="field">
                    <label>Chairman / MD / Director / Owner</label>
                    <div class="choice-row">
                        <label><input type="radio" name="chair_role"> Chairman</label>
                        <label><input type="radio" name="chair_role"> MD</label>
                        <label><input type="radio" name="chair_role"> Director</label>
                        <label><input type="radio" name="chair_role"> Owner</label>
                    </div>
                </div>
                <div class="grid" style="margin-top:10px;">
                    <div class="field span-4">
                        <label>Name</label>
                        <input type="text">
                    </div>
                    <div class="field span-4">
                        <label>Phone</label>
                        <input type="text">
                    </div>
                    <div class="field span-4">
                        <label>Email</label>
                        <input type="text">
                    </div>
                </div>
            </div>

            <div class="span-12 line-box">
                <div class="grid">
                    <div class="field span-4">
                        <label>Commercial Contact Name</label>
                        <input type="text">
                    </div>
                    <div class="field span-4">
                        <label>Commercial Contact Number</label>
                        <input type="text">
                    </div>
                    <div class="field span-4">
                        <label>Commercial Contact Email</label>
                        <input type="text">
                    </div>
                </div>
            </div>

            <div class="span-12 line-box">
                <div class="grid">
                    <div class="field span-4">
                        <label>Merchandiser Name</label>
                        <input type="text">
                    </div>
                    <div class="field span-4">
                        <label>Merchandiser Mobile</label>
                        <input type="text">
                    </div>
                    <div class="field span-4">
                        <label>Email</label>
                        <input type="text">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">2. KYC</div>
        <div class="grid">
            <div class="field span-4"><label>Trade License No.</label><input type="text"></div>
            <div class="field span-4"><label>BIN</label><input type="text"></div>
            <div class="field span-4"><label>TIN</label><input type="text"></div>
            <div class="field span-4"><label>ERC</label><input type="text"></div>
            <div class="field span-4"><label>Bond License No.</label><input type="text"></div>
            <div class="field span-4"><label>Bond License Expiry Date</label><input type="text"></div>
            <div class="field span-4"><label>Compliance Status</label><input type="text"></div>
            <div class="field span-4">
                <label>Factory Building</label>
                <div class="choice-row">
                    <label><input type="radio" name="factory_building"> Own</label>
                    <label><input type="radio" name="factory_building"> Rent</label>
                </div>
            </div>
            <div class="field span-8">
                <label>Factory Certifications</label>
                <div class="choice-row">
                    <label><input type="checkbox"> BSCI</label>
                    <label><input type="checkbox"> WRAP</label>
                    <label><input type="checkbox"> SEDEX</label>
                    <label><input type="checkbox"> ISO</label>
                    <label><input type="checkbox"> Others</label>
                </div>
            </div>
            <div class="field span-8"><label>Bank Name &amp; Branch</label><input type="text"></div>
            <div class="field span-4">
                <label>Political Exposure</label>
                <div class="choice-row">
                    <label><input type="radio" name="politics"> Yes</label>
                    <label><input type="radio" name="politics"> No</label>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">3. Production Capability</div>
        <div class="grid">
            <div class="field span-4">
                <label>Factory Type</label>
                <div class="choice-row">
                    <label><input type="radio" name="factory_type"> Woven</label>
                    <label><input type="radio" name="factory_type"> Knit</label>
                    <label><input type="radio" name="factory_type"> Both</label>
                </div>
            </div>
            <div class="field span-4"><label>Monthly Capacity (pcs)</label><input type="text"></div>
            <div class="field span-4"><label>Daily Production (pcs)</label><input type="text"></div>
            <div class="field span-3"><label>No. of Machines</label><input type="text"></div>
            <div class="field span-3"><label>No. of Lines</label><input type="text"></div>
            <div class="field span-6"><label>Peak Season Capacity (pcs)</label><input type="text"></div>
            <div class="field span-6"><label>Major Buyers</label><textarea></textarea></div>
            <div class="field span-6"><label>Major Products</label><textarea></textarea></div>
            <div class="field span-4">
                <label>Subcontract Factory</label>
                <div class="choice-row">
                    <label><input type="radio" name="subcontract"> Yes</label>
                    <label><input type="radio" name="subcontract"> No</label>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">4. Commercial Assessment</div>
        <div class="grid">
            <div class="field span-4"><label>Expected Monthly Business</label><input type="text"></div>
            <div class="field span-4"><label>Average Monthly Order</label><input type="text"></div>
            <div class="field span-4"><label>Credit Facility</label><input type="text"></div>
            <div class="field span-4"><label>Payment Currency</label><input type="text"></div>
            <div class="field span-4"><label>LC Terms</label><input type="text"></div>
            <div class="field span-4"><label>BBLC Terms</label><input type="text"></div>
            <div class="field span-4"><label>Delivery Terms</label><input type="text"></div>
            <div class="field span-2">
                <label>UD Required</label>
                <div class="choice-row">
                    <label><input type="radio" name="ud_required"> Yes</label>
                    <label><input type="radio" name="ud_required"> No</label>
                </div>
            </div>
            <div class="field span-2"><label>Zone</label><input type="text"></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">5. Product Interest</div>
        <div class="field">
            <label>Product Interest</label>
            <div class="choice-row">
                <label><input type="checkbox"> Carton</label>
                <label><input type="checkbox"> Poly</label>
                <label><input type="checkbox"> Hang Tag</label>
                <label><input type="checkbox"> Label</label>
                <label><input type="checkbox"> Offset</label>
                <label><input type="checkbox"> Thread</label>
                <label><input type="checkbox"> Elastic</label>
                <label><input type="checkbox"> Narrow Fabric</label>
                <label><input type="checkbox"> Others</label>
            </div>
        </div>
        <div class="field" style="margin-top:10px;">
            <label>Required Lead Time (per selected product)</label>
            <textarea></textarea>
        </div>
    </div>

    <div class="section">
        <div class="section-title">6. Competitor Analysis</div>
        <div class="grid">
            <div class="field span-6"><label>Existing Supplier</label><input type="text"></div>
            <div class="field span-6"><label>Current Price</label><input type="text"></div>
            <div class="field span-6"><label>Strength</label><textarea></textarea></div>
            <div class="field span-6"><label>Weakness</label><textarea></textarea></div>
            <div class="field span-12"><label>Reason for Change</label><textarea></textarea></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">7. Risk Assessment</div>
        <div class="grid">
            <div class="field span-3">
                <label>Financial Risk</label>
                <select>
                    <option></option>
                    <option>Low</option>
                    <option>Medium</option>
                    <option>High</option>
                </select>
            </div>
            <div class="field span-3"><label>Payment History</label><input type="text"></div>
            <div class="field span-3"><label>Credit Limit Recommended</label><input type="text"></div>
            <div class="field span-3"><label>Remarks</label><input type="text"></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">8. Price Approval Matrix</div>
        <table class="matrix">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Existing Price</th>
                    <th>Target Price</th>
                    <th>Approved Price</th>
                    <th>Commission</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 0; $i < 6; $i++): ?>
                <tr>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                    <td><input type="text"></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">9. Document Checklist</div>
        <div class="field">
            <label>Documents Submitted</label>
            <div class="choice-row">
                <label><input type="checkbox"> Trade License</label>
                <label><input type="checkbox"> BIN</label>
                <label><input type="checkbox"> TIN</label>
                <label><input type="checkbox"> ERC</label>
                <label><input type="checkbox"> Bond License</label>
                <label><input type="checkbox"> Bank Solvency</label>
                <label><input type="checkbox"> Company Profile</label>
                <label><input type="checkbox"> Compliance Certificates</label>
                <label><input type="checkbox"> Factory Photos</label>
                <label><input type="checkbox"> Sample Approval</label>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">10. Signatures</div>
        <div class="signature-grid">
            <div class="signature-box"><div class="signature-line">Prepared By</div></div>
            <div class="signature-box"><div class="signature-line">Reviewed By</div></div>
            <div class="signature-box"><div class="signature-line">Approved By</div></div>
        </div>
    </div>

    <div style="margin-top:16px;">
        <?= zzal_print_brand_footer() ?>
    </div>
</div>
</body>
</html>
