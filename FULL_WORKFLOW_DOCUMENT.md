# ED Module Full Workflow Document

## Overview

This document writes down the full process we understood from:

- the Excel workbook
- the sales-order HTML export from the other system
- your department flow explanation

The goal is to explain:

- who does what
- where PI fits
- where LC fits
- when approval happens
- when factory starts
- how invoice, packing, delivery, and export documents are created

## Main Business Flow

The simple business flow is:

`Buyer -> Marketing -> Commercial -> PI + LC Check -> Marketing Check -> Approval -> Factory -> Delivery / Invoice / Packing / Export Documents`

## Department Flow

### 1. Buyer
The buyer gives the order.

This starts the whole process.

Buyer may provide:

- order requirement
- item requirement
- quantity
- delivery requirement
- commercial requirement

### 2. Marketing
Marketing receives the buyer order first.

Marketing works as the first internal checkpoint.

Marketing responsibilities:

- receive order from buyer
- understand buyer requirement
- send order to Costing for price review
- receive price result back from Costing
- if price is revised, review and resubmit to Costing
- once price is approved, communicate order details to Commercial
- later check the commercial documents
- approve or return for correction

### 2b. Costing (Price Review — Back and Forth with Marketing)

Costing is involved before Commercial.

There is a loop between Marketing and Costing until the price is agreed.

Costing responsibilities:

- receive order details from Marketing
- review and calculate cost / price
- if price is acceptable: approve and send back to Marketing
- if price needs revision: revise and return to Marketing for review
- this loop continues until price is approved

Once price is approved, Marketing sends the order forward to Commercial.

### 3. Commercial
Commercial receives the order details from Marketing.

Commercial is responsible for:

- checking commercial terms
- checking PI
- checking LC
- preparing invoice-related documents
- preparing packing-related documents
- preparing delivery-related documents
- preparing export/bank documents

### 4. Factory / Production
Factory should start only after approval.

Factory responsibilities:

- receive approved order
- start production
- complete goods
- coordinate with dispatch/delivery side

## Correct Position of PI

PI belongs before production.

Best position in the flow:

1. Buyer gives order
2. Marketing receives order
3. Marketing sends details to Commercial
4. Commercial creates or confirms PI
5. Marketing checks PI
6. If PI is okay, it is approved
7. Then it goes to Factory / Production

So PI is an early commercial approval document.

Short version:

`Buyer -> Marketing -> Commercial -> PI -> Marketing Check -> Approval -> Factory`

## Correct Position of LC

LC should also be handled before production.

Commercial checks LC because many later documents depend on LC information.

Best position:

1. Buyer gives order
2. Marketing receives order
3. Marketing sends order to Commercial
4. Commercial creates/checks PI
5. Commercial checks LC
6. Marketing checks the commercial details
7. Approved order goes to Factory

Short version:

`Buyer -> Marketing -> Commercial -> PI + LC Check -> Marketing Check -> Approval -> Factory`

## Approval Flow

Approval should happen before Factory starts.

Approval logic:

- Commercial prepares PI and checks LC
- Marketing reviews the prepared documents
- if okay, approve
- if not okay, send back to Commercial for correction

So approval sits between Commercial and Factory.

## Factory Flow

After approval:

1. Approved order goes to Factory
2. Factory starts production
3. Goods are prepared
4. Delivery-related documents are used
5. Final invoice/packing/export documents are completed

## Document Flow

The workbook is not one single form.

It is a full document pack for one shipment/order/export case.

The likely document flow is:

`Sales Order -> PI / Commercial Base -> Packing / Delivery Documents -> Export / Bank Documents -> Forwarding Pack`

## External System Understanding

The file `TG_OM_Sales_Order_Status__In_H_170626.htm` shows that another system is already providing important sales-order data.

This means the system is a major source for the workflow.

The report already contains fields like:

- Sales Order Number
- Customer PO Number
- Buyer
- LC Number
- Payment Terms
- ItemCode
- Line Items
- Qty
- Price
- Amount
- Currency
- Requested Date
- Ex-Factory Date
- Scheduled Shipment Date
- Dispatch Date
- DC Number
- PI Number
- Receivables Invoice Number
- Receivables Invoice Date
- Receivables Invoice Status

So the external system is already a strong source for commercial line data.

## System Trigger

The real starting trigger inside a future system should be:

`Sales Order Number`

When user enters the sales order:

- buyer should come automatically
- item lines should come automatically
- qty should come automatically
- price should come automatically
- amount should come automatically
- PI/DC references should come automatically if available

## What the Workbook Shows

From the Excel workbook, these sheets exist:

- Bill of exchange
- Commercial Invoice
- Packing List
- Delivery Challan
- Truck Challan
- CERTIFICATE OF ORIGIN
- BENEFICIARY CERTIFICATE
- Forwarding
- Challan Sheet

These are not independent forms.
They are linked business documents for the same order/shipment.

## Sheet Understanding

### 1. Commercial Invoice
This looks like the central commercial sheet inside the workbook.

It contains:

- invoice / PI reference
- date
- L/C number
- proforma reference
- buyer/consignee information
- bank information
- item descriptions
- quantity
- price
- amount

This should be treated as the main commercial base sheet.

### 2. Challan Sheet
This looks like the operational item-level sheet.

It contains:

- PI NO
- Order Ref#
- item descriptions
- delivery dates
- quantity
- challan number
- inspection result

This is useful for production/delivery/QA history.

### 3. Packing List
This is the packing version of the same shipment.

It uses:

- item lines
- quantities
- buyer/consignee
- bank details
- LC details
- proforma reference
- sales contract reference

### 4. Delivery Challan
This is a dispatch or handover document.

It is downstream from the commercial data.

### 5. Truck Challan
This is another transport/dispatch document.

It is also downstream from the commercial and packing data.

### 6. Certificate of Origin
This is an export support document.

It depends on:

- LC details
- sales contract
- proforma invoice
- applicant information
- HS code

### 7. Beneficiary Certificate
This is also an export/bank support document.

It summarizes:

- quantity
- amount
- buyer
- bank
- LC details
- sales contract
- proforma reference

### 8. Bill of Exchange
This is not the first form.

This is one of the later bank/export documents.

It depends on:

- amount
- date
- LC details
- sales contract
- applicant information
- bank details
- HS code

So Bill of Exchange comes late in the process, not first.

### 9. Forwarding
This looks like the final cover letter / document pack listing.

It is near the end of the process.

## Best Business Sequence

The best sequence based on everything we saw is:

1. Buyer gives order
2. Marketing receives buyer order
3. Marketing sends order details to Commercial
4. Commercial enters or loads Sales Order Number
5. System fetches item and order data from the other system
6. Commercial creates or checks PI
7. Commercial checks LC
8. Commercial prepares commercial base documents
9. Marketing checks the prepared commercial details
10. If okay, order is approved
11. Approved order goes to Factory / Production
12. Factory produces goods
13. Commercial/dispatch prepares delivery and packing documents
14. Commercial prepares export and bank documents
15. Forwarding pack is prepared as final document set

## Auto-Fill Data

These should come automatically from the sales-order system where possible:

- Sales Order Number
- Customer PO Number
- Buyer
- item description
- item code
- UOM
- quantity
- price
- amount
- PI number
- DC number
- shipment-related dates
- receivables invoice details
- payment terms
- LC number if present in source system

## Manual or Master Data

These still look like they need manual entry or another master data source:

- consignee full address
- bank full address
- applicant IRC
- applicant TIN
- applicant VAT/BIN
- applicant bank BIN
- beneficiary VAT/BIN
- bond license number
- HS code
- sales contract number
- loading place
- delivery place
- certificate wording
- bill of exchange wording

## Short Final Flow

The simplest final version is:

1. Buyer gives order
2. Marketing receives order
3. Marketing sends it to Commercial
4. Commercial loads Sales Order data
5. Commercial prepares/checks PI
6. Commercial checks LC
7. Marketing checks
8. Approved
9. Factory starts
10. Delivery, invoice, packing, and export documents are completed

## One-Line Summary

Sales Order feeds Commercial. Commercial checks PI and LC. Marketing approves. Factory starts. Then delivery, invoice, packing, and export documents are finalized.

All users created. Credentials:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@ed.local | Admin@1234 |
| Sales Person | sales@ed.local | Pass@1234 |
| Commercial | commercial@ed.local | Pass@1234 |
| Team Leader (Operations) | teamleader@ed.local | Pass@1234 |
| Sales Coordinator (Finance) | coordinator@ed.local | Pass@1234 |
| Head of Business | head@ed.local | Pass@1234 |
| Managing Director | md@ed.local | Pass@1234 |

---

## Customer Profile & Commercial Assessment Form

### Purpose

To evaluate new customers for commercial approval, production feasibility, financial risk, and pricing before onboarding.

### Who Fills It

- **Sales Person** fills the form and signs on creation.
- The record then moves through 6 approval stages before completion.

---

### Approval Workflow

| Stage | Role | DB Slug |
|-------|------|---------|
| 1 | Sales | `sales_person` |
| 2 | Commercial | `commercial` |
| 3 | Operations | `team_leader` |
| 4 | Finance | `sales_coordinator` |
| 5 | Head of Business | `head_of_business` |
| 6 | Managing Director | `managing_director` |
| — | Completed | `completed` |

Each approver uploads their signature and clicks **Sign & Approve** to forward to the next stage.

---

### Section 1 — Customer Information

| Field | Type | Notes |
|-------|------|-------|
| Customer Code | Text | e.g. CUST-001 |
| Company Name | Text | Required |
| Customer Type | Select | Regular / Premium / New / Strategic |
| Industry | Text | e.g. Garments, Textile |
| Head Office / Registered Address | Text | Full address |
| Factory Address | Text | Full address |
| Website | Text | https://... |
| Form Date | Date | |
| Chairman / MD / Director Name | Text | |
| Chairman / MD Phone | Text | |
| Political Exposure | Radio | Yes / No |
| Commercial Name & Number | Text | Name – Phone |
| Merchandiser Contact Name | Text | |
| Merchandiser Mobile | Text | |
| Email Address | Email | |

---

### Section 2 — Business & Compliance

| Field | Type | Notes |
|-------|------|-------|
| Trade License No. | Text | |
| BIN | Text | Business Identification Number |
| TIN | Text | Tax Identification Number |
| Bond License No. | Text | |
| Bond License Expiry | Date | |
| Compliance Status | Text | e.g. Compliant, Non-compliant |
| Factory Building | Radio | Own / Rent |
| Factory Certifications | Checkboxes | BSCI / WRAP / SEDEX / ISO / Others |
| Bank Name & Branch | Text | |

---

### Section 3 — Production Capability

| Field | Type | Notes |
|-------|------|-------|
| Factory Type | Radio | Woven / Knit / Both |
| Monthly Capacity (pcs) | Text | |
| Daily Production (pcs) | Text | |
| No. of Machines | Text | |
| No. of Lines | Text | |
| Peak Season Capacity (pcs) | Text | |
| Major Buyers | Text | e.g. H&M, Zara, Walmart |
| Major Products | Text | e.g. T-shirt, Polo, Trouser |
| Subcontract Factory | Radio | Yes / No |

---

### Section 4 — Commercial Assessment

| Field | Type | Notes |
|-------|------|-------|
| Expected Monthly Business | Text | e.g. USD 50,000 |
| Average Monthly Order | Text | e.g. 10 orders |
| Required Lead Time (items wise) | Text | e.g. 7 days for carton |
| Credit Facility | Text | e.g. 30 days credit |
| Payment Currency | Text | e.g. USD, BDT |
| LC Terms | Text | e.g. At sight, 30 days |
| BBLC Terms | Text | e.g. At sight, 60 days |
| Delivery Terms | Text | e.g. After LC receive |
| UD Required | Radio | Yes / No |
| Zone | Text | e.g. Gazipur, Narayanganj |

---

### Section 5 — Product Interest

Checkboxes (multiple selection):

- Carton
- Poly
- Hang Tag
- Label
- Offset
- Thread
- Elastic
- Narrow Fabric
- Others

---

### Section 6 — Competitor Analysis

| Field | Type |
|-------|------|
| Existing Supplier | Text |
| Current Price | Text |
| Strength | Text |
| Weakness | Text |
| Reason for Change | Text |

---

### Section 7 — Risk Assessment

| Field | Type | Notes |
|-------|------|-------|
| Financial Risk | Select | Low / Medium / High |
| Payment History | Text | e.g. Good, Irregular |
| Credit Limit Recommended | Text | e.g. USD 100,000 |
| Remarks | Text | |

---

### Section 8 — Price Approval Matrix

Editable table (rows can be added/removed):

| Column | Description |
|--------|-------------|
| Product | Product name e.g. Carton |
| Existing Price | Competitor or previous price |
| Target Price | Customer's target price |
| Approved Price | Internally approved selling price |
| Commission | Commission percentage or value |

---

### Section 9 — Document Checklist

Checkboxes confirming documents received:

- Trade License
- BIN
- TIN
- Bond License
- Bank Solvency
- Company Profile
- Compliance Certificates
- Factory Photos
- Sample Approval

---

### Data Storage

| Location | Fields Stored |
|----------|---------------|
| `customers` table core columns | company_name, address_head_office, factory_address, chairman_name, chairman_mobile, customer_type, date_form, politics_yes, stage, signatures |
| `extra_data` JSON column | All other fields from Sections 1–9 |

The `signatures` column is a JSON object keyed by stage slug, storing the base64 signature image for each approver.

---

### Page Location

`/ed_module/pages/customer-profile.php`

API endpoint: `/ed_module/api/customers.php` (GET / POST / PUT)