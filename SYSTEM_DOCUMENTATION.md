# Accessories Tracking System (ATS) — System Documentation

_Company: Zaber & Zubair Accessories Ltd._
_Codebase: `ed_module` (local) · deployed as `accessories_tracking_system` (live)_
_Stack: PHP 8 + MySQL (PDO), vanilla JavaScript front end, no framework._

---

## 1. Purpose

ATS tracks an export accessories order through its entire lifecycle — from
onboarding a customer, capturing their purchase order, producing a Proforma
Invoice, and on through every bank/export document required to ship and get
paid. Each workflow step is a web page; the data entered at each step flows
forward and auto-fills the later documents. The system also generates
printable documents (PI, Commercial Invoice, LC, Certificate of Origin, etc.).

---

## 2. High-Level Architecture

```
Browser (vanilla JS)  ──fetch()──►  api/*.php  ──PDO──►  MySQL
        ▲                                │
        │                                ├─ includes/db.php      (PDO singleton)
   pages/*.php  ◄── includes/header.php ─┤  includes/auth.php    (session + roles)
   (server-rendered shell +             ├─ includes/notifications.php
    inline JS per page)                 └─ includes/erp_sale_orders_cache.php
                                                 │
                                        Live ERP: ebs.talhagroup.com (sale-orders API)
```

- **Pages** (`pages/*.php`) render an HTML shell + page-specific inline JS.
  They read/write data through the JSON APIs; they do not talk to the DB
  directly for order data.
- **APIs** (`api/*.php`) are small REST-ish endpoints returning JSON.
- **Includes** (`includes/*.php`) hold shared bootstrap: DB, auth, the top
  navigation/header, footer, notifications, and the ERP cache layer.
- **Setup** (`setup/*.php`) holds one-off migration/seed/backfill scripts.

### Bootstrap
- [index.php](index.php) → redirects to `pages/dashboard.php`.
- [includes/config.php](includes/config.php) → defines `BASE_PATH` from the URL
  (first path segment), so the app is path-agnostic between local and live.
- [includes/db.php](includes/db.php) → `getDB()` PDO singleton. **The database
  is chosen by host**: `localhost` → `ed_module` (user `root`, no password);
  anything else → live `talhagr1_atx`. Errors return JSON `{"error": ...}` with
  HTTP 500.

---

## 3. Authentication & Roles

Session-based login (`$_SESSION['ed_user']`). See [includes/auth.php](includes/auth.php).

| Role              | Workflow tabs allowed                                                        |
|-------------------|------------------------------------------------------------------------------|
| `admin`           | Everything (no restriction)                                                  |
| `marketing`       | marketing-intake, marketing, customer-profile, create-customer               |
| `team_leader`     | same as marketing (plus approval)                                            |
| `costing`         | costing-review                                                               |
| `production`      | production                                                                   |
| `commercial` / `commercial_dept` | customer-profile, sales(PI), single/summary/master-pi, erp-orders-report, lc, exchange, commercial, packing, delivery, truck, origin, beneficiary, forwarding, bank-forwarding, po-status |

- `allowedTabs()` returns the whitelist; empty = all (admin/legacy).
- `canAccessTab($id)` is used per page.
- **Page guard:** [includes/header.php](includes/header.php) checks the current
  page against `allowedTabs()` and, if not allowed, **redirects to the role's
  first allowed page**. (This is why submitting from a page that navigates a
  role somewhere it can't access can "bounce" it back to its home page.)
- **Team scoping:** `isTeamScopedRole()` (marketing, team_leader) limits customer
  visibility to the user's own `team`.

---

## 4. The Order Workflow (14 steps)

An order's progress is stored in `orders.current_step`. Steps, in order:

| # | Step key           | Page                    | Purpose                                             |
|---|--------------------|-------------------------|-----------------------------------------------------|
| 1 | `marketing-intake` | marketing-intake.php    | Capture customer PO, items, quantities, prices      |
| 2 | `costing-review`   | costing-review.php      | Review/revise item prices                           |
| 3 | `sales`            | sales.php               | Proforma Invoice (PI) — the main commercial doc     |
| 4 | `marketing`        | marketing.php           | Marketing approval; hands off to LC                 |
| 5 | `lc`               | lc.php                  | Letter of Credit details                            |
| 6 | `exchange`         | exchange.php            | Bill of Exchange                                    |
| 7 | `commercial`       | commercial.php          | Commercial Invoice                                  |
| 8 | `packing`          | packing.php             | Packing List (auto-filled)                          |
| 9 | `delivery`         | delivery.php            | Delivery Challan                                    |
| 10| `truck`            | truck.php               | Truck Challan                                       |
| 11| `origin`           | origin.php              | Certificate of Origin (auto-generated)              |
| 12| `beneficiary`      | beneficiary.php         | Beneficiary's Certificate                           |
| 13| `forwarding`       | forwarding.php          | Forwarding cover letter to the bank                 |
| 14| `po-status`        | po-status.php           | Challan Sheet (QA / delivery inspection)            |

Entry points: an order can start at **Marketing Intake** (step 1) or be created
directly at the **PI/Sales** stage (Commercial). Advancing a step is done by
`PUT api/orders.php?id=<order>&step=<next>`, which also fires notifications.

---

## 5. Data Model (core tables)

### `orders`
One row per order. `order_id` format `ORD-YYYY-NNNN` (e.g. `ORD-2026-0011`).
Holds header fields (customer_name, salesperson, po_number, to_buyer,
delivery_date, …) and `current_step`. **Note:** for orders created at the PI
stage, many header columns can be empty because the detail lives in `pis`.

### `pis`
Proforma Invoices. Key column `pos` is a **JSON blob**: an array of PO groups,
each with `poNum`, `qty`, `val`, `buyer`, and an `items[]` array. Supports
three PI types:
- **Single PI** — one PI that may contain multiple POs (items listed together).
- **Summary PI** — multiple PIs summarised.
- **Master PI** — a master over selected included PIs (`is_master`, `included_pis`).

PI numbers auto-generate as `ZZAL/PI/<yy>/<n>` via `GET api/pis.php?next_num=1`.

### `page_data`
Generic per-order-per-page store: unique key `(order_id, page_name)`, a JSON
`data` blob. This is how **every workflow step persists its form**
([api/save_page.php](api/save_page.php)). Saving the `marketing-intake` page also
syncs customer/salesperson/date back into the `orders` row.

### `customers`, `buyers`, `items`
Master data. Customers carry an `extra_data` JSON (profiling), a `stage`
(approval), and a `team`.

### `notifications`
Worklist alerts. On each step change, [includes/notifications.php](includes/notifications.php)
maps the step → target roles (+admin) and inserts one notification per user
(de-duped by order+step).

---

## 6. Key APIs

| Endpoint | Methods | Notes |
|---|---|---|
| [api/orders.php](api/orders.php) | GET / POST / PUT | GET-all enriches each order with PI-derived customer/PO/buyer/qty/item-count. PUT updates `current_step` (+ optional customer/buyer/po). POST upserts order + items. |
| [api/pis.php](api/pis.php) | GET / POST / DELETE | Search by PI/PO, next number, upsert PI (JSON `pos`). |
| [api/save_page.php](api/save_page.php) | POST | Upsert `page_data`; syncs intake fields into `orders`. |
| [api/customers.php](api/customers.php) | GET … | Master customer list used by dropdowns. |
| [api/erp_proxy.php](api/erp_proxy.php) | GET `?po=` | Live ERP + local cache search; returns grouped data or `multiple:true` + `options[]`. |
| [api/notifications.php](api/notifications.php) | GET / … | Per-user worklist. |

---

## 7. ERP Integration

The Commercial/PI flow pulls real sales-order data from the Talha Group ERP.

- **Live source:** `https://ebs.talhagroup.com:8080/ords/xxapi/ebs/sale-orders`
  (query by `?po=` or paginate with `?offset=&limit=`).
- **Proxy:** [api/erp_proxy.php](api/erp_proxy.php) queries live **and** the local
  cache, merges + de-duplicates line rows (by sale-order + line id), groups by
  customer PO / sales order, prefers an exact live PO match, and — when a query
  matches several POs — returns `multiple:true` with an `options[]` list so the
  UI can present a choose-one selector. Each option carries a `matchSummary`
  showing which field matched.
- **Cache:** [includes/erp_sale_orders_cache.php](includes/erp_sale_orders_cache.php)
  defines the `erp_sale_orders_cache` table and a **scored** search across
  `customer_po_no`, `remarks`, `item_description`, `item_code`, `ordered_item`,
  `raw_json`, and a normalized search blob. Structured codes (letters+digits+
  separators, e.g. `601BLAUI27-210`) are searched as one key rather than being
  split into loose tokens (prevents unrelated matches).
- **Backfill:** [setup/backfill_erp_sale_orders_local.php](setup/backfill_erp_sale_orders_local.php)
  and [setup/cron_erp_sale_orders_backfill.php](setup/cron_erp_sale_orders_backfill.php)
  page through the ERP API to populate the cache; state tracked in
  `erp_backfill_state`.

---

## 8. Front-end Conventions

- Each page defines inline JS and talks to APIs via `fetch(APP_BASE + '/api/...')`,
  where `window.APP_BASE` is injected by the header.
- The **work-order bar** (header) shows the loaded order and offers Load / New
  Order. The current order id is kept in `sessionStorage['ats_current_order_id']`.
- Pages expose hooks the header/footer call: `window.onOrderLoad`,
  `window.onNewOrder`. A page that implements `onNewOrder` can start a blank
  draft in place instead of navigating away.
- Printable documents are separate pages (`pages/*-print.php`, `pi-print.php`,
  `document-print.php`) and Excel export via [api/export_excel.php](api/export_excel.php).

---

## 9. Documentation Generators (Python)

- [generate_doc.py](generate_doc.py) — per-role illustrated user guides
  (`ATS_Marketing.docx`, `ATS_Costing.docx`, `ATS_Production.docx`,
  `ATS_Commercial.docx`). Uses Playwright to log in as each role, screenshot
  every page it can access, and build the DOCX. Accepts a role argument:
  `python generate_doc.py commercial` (no arg = all). Optional per-role
  `highlights` list renders a "What's New" section.
- [generate_manual.py](generate_manual.py) — the full end-user manual
  (`ATS_User_Manual.docx`).
- [generate_docs.py](generate_docs.py) — technical documentation
  (`ED_Module_Documentation.docx`).

Note: the role guides embed the demo login **password in plaintext** — keep
them out of any public share.

---

## 10. Notable Behaviours & Gotchas

- **Dashboard blanks:** the Order Tracking Dashboard historically read only the
  `orders` row; PI-created orders keep their detail in `pis`, so rows showed
  dashes. `api/orders.php` GET-all now backfills customer/PO/buyer/qty/items
  from `pis` for display.
- **Customer vs. Buyer from ERP:** the ERP returns `customerName` (the actual
  customer) and often no `buyer`. The PI page maps `customerName` into the
  **Customer (TO)** field; the "Buyer (Brand/End Buyer)" field is only filled
  when the ERP provides a distinct buyer.
- **Role bounce:** submitting on a page that then navigates a role to a page it
  cannot access triggers the header guard and lands the user on their home page.
  Keep post-submit navigation within the role's allowed tabs.
- **DB by host:** never hard-code the DB name; `getDB()` picks it from the host.
```
