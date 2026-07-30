# Accessories Tracking System (ATS) - Technical Documentation

## Overview

ATS is a PHP/MySQL web application that manages the full lifecycle of a garment accessories export order — from Marketing Intake through Proforma Invoice, LC, Commercial Invoice, Packing List, Delivery, and beyond. Each order gets a unique `ORD-YYYY-NNNN` ID and moves through a fixed set of workflow steps tracked across PHP pages.

- **Local URL:** `http://localhost/ed_module/`
- **Live URL:** `https://znzal.com/accessories_tracking_system/`
- **Local DB:** `ed_module` (MySQL, XAMPP root / no password)
- **Live DB:** `talhagr1_atx` (user: `talhagr1_sim`, host: `localhost`)

---

## Directory Structure

```
ed_module/
├── index.php                   Entry point — redirects to dashboard
├── script.js                   Main client-side JS (SPA helpers, order bar)
├── styles.css                  Global stylesheet
├── item-data.js                Product line items + paper grade data
├── js/
│   └── common.js               Shared utilities
├── includes/
│   ├── config.php              Defines BASE_PATH constant
│   ├── db.php                  PDO singleton (auto-detects local vs live)
│   ├── auth.php                Session auth helpers + role checks
│   ├── header.php              HTML head, nav tabs, order ID bar
│   └── footer.php              JS scripts, modals, universal save/restore
├── pages/
│   ├── login.php               Login form
│   ├── logout.php              Session destroy + redirect
│   ├── dashboard.php           Order tracking table
│   ├── customer-profile.php    Customer/buyer list
│   ├── create-customer.php     Create/edit customer profile
│   ├── item-master.php         Item master table
│   ├── users.php               User management (admin only)
│   ├── marketing-intake.php    Step 1 — order header data
│   ├── costing-review.php      Step 2 — cost sheet
│   ├── sales.php               Step 3 — PI creation + Master PI
│   ├── marketing.php           Step 4 — marketing approval + order summary
│   ├── lc.php                  Step 5 — LC details
│   ├── po-overview.php         Step 6 — PO status overview
│   ├── exchange.php            Step 7 — Bill of Exchange
│   ├── commercial.php          Step 8 — Commercial Invoice
│   ├── packing.php             Step 9 — Packing List
│   ├── delivery.php            Step 10 — Delivery Challan
│   ├── truck.php               Step 11 — Truck Challan
│   ├── origin.php              Step 12 — Certificate of Origin
│   ├── beneficiary.php         Step 13 — Beneficiary Certificate
│   ├── forwarding.php          Step 14 — Forwarding
│   └── po-status.php           Step 15 — Challan Sheet
├── api/
│   ├── order_lookup.php        GET order + pages + PIs; POST create order
│   ├── save_page.php           POST — upsert page_data blob + sync orders row
│   ├── pis.php                 CRUD for Proforma Invoices
│   ├── orders.php              Orders list (dashboard)
│   ├── buyers.php              Buyer master CRUD
│   ├── customers.php           Customer CRUD
│   ├── page_data.php           Raw page_data fetch
│   └── erp_proxy.php           ERP item lookup proxy
└── setup/
    ├── schema.sql              Full DB schema
    ├── run_schema.php          Runs schema.sql (strips CREATE DB on live)
    ├── add_pi_order_columns.php Migration — adds order_id/is_master/included_pis to pis
    ├── add_pis_table.php       Creates pis table if missing
    ├── add_stage.php           Adds stage column to orders
    ├── install.php             One-shot full install
    ├── create_users.php        Seed admin user
    ├── seed_users.php          Seed sample users
    └── clear_test.php          Wipe test data
```

---

## Path Portability

The app runs under different URL segments locally vs. on the live server. All paths are derived dynamically — never hardcoded.

### PHP side — `BASE_PATH`

Defined in `includes/config.php`:

```php
define('BASE_PATH', '/' . explode('/', ltrim($_SERVER['SCRIPT_NAME'], '/'))[0]);
// Local:  /ed_module
// Live:   /accessories_tracking_system
```

Used everywhere for links and redirects:

```php
header('Location: ' . BASE_PATH . '/pages/login.php');
<a href="<?= BASE_PATH ?>/pages/marketing-intake.php">...</a>
<link rel="stylesheet" href="<?= BASE_PATH ?>/styles.css">
```

### JS side — `APP_BASE`

Injected in `includes/header.php`:

```html
<script>window.APP_BASE = '<?= BASE_PATH ?>';</script>
```

`script.js` also sets a local fallback:

```js
const APP_BASE = window.APP_BASE || ('/' + window.location.pathname.split('/')[1]);
```

All fetch calls and navigation use `APP_BASE`:

```js
fetch(APP_BASE + '/api/order_lookup.php?id=' + id)
window.location.href = APP_BASE + '/pages/sales.php';
```

---

## Database

### Auto-detect credentials — `includes/db.php`

```php
$isLive = ($_SERVER['HTTP_HOST'] ?? '') !== 'localhost';
$dbName = $isLive ? 'talhagr1_atx'  : 'ed_module';
$dbUser = $isLive ? 'talhagr1_sim'  : 'root';
$dbPass = $isLive ? '[REDACTED]'  : '';
```

Returns a PDO singleton via `getDB()`.

### Key tables

| Table | Purpose |
|-------|---------|
| `users` | Login accounts; roles: `admin`, `sales_person`, `team_leader`, `commercial`, `sales_coordinator`, `head_of_business` |
| `orders` | One row per work order (`order_id`, `customer_name`, `salesperson`, `current_step`, `po_number`, `to_buyer`, etc.) |
| `page_data` | Keyed `(order_id, page_name)` → JSON blob of form field values |
| `pis` | Proforma Invoices; `pos` column is a JSON array of PO objects |
| `buyers` | Buyer master (code, name, address, customer) |
| `customers` | Customer profiles |
| `items` | Item master (product line, name, grade, paper combination, base price) |

### `pis` table migration columns

The columns `order_id`, `is_master`, `included_pis` were added later. Run the migration once:

```
/setup/add_pi_order_columns.php
```

All API endpoints that touch `pis` check for these columns before using them:

```php
$hasMigrationCols = false;
try { $db->query('SELECT order_id FROM pis LIMIT 0'); $hasMigrationCols = true; }
catch (PDOException $_) {}
```

---

## Authentication

Session-based. `includes/auth.php` exposes:

| Function | Description |
|----------|-------------|
| `currentUser()` | Returns `$_SESSION['ed_user']` array or `null` |
| `requireLogin()` | Redirects to login if not authenticated |
| `requireAdmin()` | 403 if not admin |
| `isAdmin()` | Boolean check |
| `sigVisibility()` | Returns signature block IDs the current role may see |

Every page calls `requireLogin()` via `includes/header.php`.

---

## Order Workflow

### Order ID bar

Rendered by `header.php` on all `$navSection === 'order'` pages. Users can:
- Type an order ID or PI number → **Load Order** → fetches full order snapshot
- Click **+ New Order** → creates a new `ORD-YYYY-NNNN` and stores it in `sessionStorage`

The loaded order ID is kept in `sessionStorage` under key `ats_current_order_id` and survives page navigation within the same browser tab.

### Order lifecycle

```
Marketing Intake → Costing Review → PI (Sales) → Marketing → LC
→ PO Status → Bill of Exchange → Commercial Invoice → Packing List
→ Delivery Challan → Truck Challan → Certificate of Origin
→ Beneficiary Certificate → Forwarding → Challan Sheet
```

Page navigation uses PHP page links (not SPA), so clicking Next/Previous reloads the PHP page:

```js
// script.js
const STEP_PAGES = {
  'marketing-intake': 'marketing-intake.php',
  'costing-review':   'costing-review.php',
  'sales':            'sales.php',
  'marketing':        'marketing.php',
  // ...
};
document.querySelectorAll('.js-next-page').forEach(btn => {
    btn.addEventListener('click', () => {
        const phpPage = STEP_PAGES[btn.dataset.nextPage];
        if (phpPage) window.location.href = APP_BASE + '/pages/' + phpPage;
    });
});
```

---

## Page Data Persistence

### Saving — `api/save_page.php`

Every page has a **Submit** button auto-injected by `footer.php` into `.page-actions-left`. Clicking it calls `saveCurrentPage()`:

```js
async function saveCurrentPage() {
    const orderId  = window.getCurrentOrderId();
    const pageName = document.body.dataset.page;  // set via data-page on <body>
    // collects all [id] form fields, POSTs to save_page.php
}
```

The API upserts a row in `page_data(order_id, page_name, data)` as a JSON blob. For the `marketing-intake` page it also syncs `customer_name`, `salesperson`, `intake_date`, `sub_description` into the `orders` table.

Pages that manage their own save logic are excluded from the auto-inject:

```js
const skipPages = ['marketing-intake', 'sales', 'dashboard'];
```

### Restoring — `footer.php`

When `loadOrderById()` resolves, `onOrderLoad(res)` fires. The footer wrapper automatically restores page fields:

```js
window.onOrderLoad = function(res) {
    if (typeof _origOnOrderLoad === 'function') _origOnOrderLoad(res);
    const pageName = document.body.dataset.page;
    if (pageName && res.pages && res.pages[pageName]) {
        restorePageFields(res.pages[pageName]);
    }
};
```

`restorePageFields(data)` fills all `input`, `textarea`, and `select` elements by their `id`.

---

## Proforma Invoice (PI) API — `api/pis.php`

| Method | Params | Description |
|--------|--------|-------------|
| `GET` | `?id=N` | Single PI by row ID |
| `GET` | `?q=term` | Search by PI number or PO number |
| `GET` | `?order_id=ORD-...` | All PIs for an order |
| `GET` | `?all=1` | All PIs (for Master PI modal) |
| `POST` | JSON body | Create/upsert PI |
| `DELETE` | `?id=N` | Delete PI |

### PI JSON shape

```json
{
  "id": 12,
  "pi_number": "PI-2026-0042",
  "order_id": "ORD-2026-0015",
  "is_master": 0,
  "included_pis": [],
  "customer": "LIZ Fashion",
  "product_line": "Poly",
  "pi_date": "2026-07-01",
  "grand_qty": 50000,
  "grand_val": 3200.00,
  "pos": [
    {
      "poNum": "PO-LIZ-001",
      "buyer": "Buyer Name",
      "qty": 50000,
      "val": 3200.00,
      "items": [
        { "desc": "Poly Bag 12x15", "ply": "2Ply", "qty": 50000, "price": 0.064, "total": 3200.00 }
      ]
    }
  ]
}
```

### Master PI

A Master PI combines multiple individual PIs into one document. Fields:
- `is_master: 1`
- `included_pis: ["PI-2026-0042", "PI-2026-0043"]`
- Its `pos` array merges the POs from all included PIs (each PO carries `sourcePi` for traceability)

---

## Proforma Invoice (PI) — Full Workflow

### Overview

A Proforma Invoice is a formal price quotation issued to a buyer before the actual LC (Letter of Credit) is opened. In ATS, one order can have multiple PIs — each PI covers one or more PO blocks (purchase orders from the buyer).

### PI Number Format

```
ZZAL/PI/YY/N
```

- `ZZAL` — company prefix
- `PI` — document type
- `YY` — two-digit year (e.g. `26` for 2026)
- `N` — sequential number (1, 2, 3 …)

Example: `ZZAL/PI/26/4`

PI numbers are auto-generated by `api/pis.php?next_num=1`. When multiple PO blocks are open on the same page, each gets the next sequential number (the form increments past any already-open blocks).

---

### Creating a PI — `pages/sales.php`

The Sales page is where PIs are created and managed.

#### Shared Header Fields (top of page)

These apply to all PIs for the order:

| Field | Notes |
|-------|-------|
| Customer | Dropdown from customer master |
| Buyer | Free text — the end buyer (brand name) |
| Buyer Address | Multi-line — used in print |
| PI Date | Date of issuance |
| Consignee Bank / Advising Bank | Used in Terms & Conditions |

#### PO Blocks

Each PO block = one PI record. A block contains:

- **PI Number** — auto-filled, editable
- **Matched Sales Order No** — from ERP (read-only)
- **Customer PO Number** — from ERP (read-only)
- **Buyer Name** — from ERP (read-only)
- **ERP Item Rows** — Description of Goods, Ply/Type, Quantity, Price $, Amount $

Multiple PO blocks can be open at once; saving creates one PI record per block.

#### Saving a PI

The **"💾 Save PI"** button calls `savePi()`:

1. Reads all PO blocks on the page
2. For each block, POSTs to `api/pis.php` with the PI data
3. Embeds `sharedBuyer` and `sharedBuyerAddress` inside each PO's JSON so they are available on print pages without needing the sales page snapshot

On success, the PI badge count updates and the "PIs for this Order" overview refreshes.

#### `pos` JSON (inside each PI record)

Each PI's `pos` column is a JSON array. Each element (one per PO block) has:

```json
{
  "piNum": "ZZAL/PI/26/4",
  "salesOrder": "NTML/AMAN2025-01",
  "poNum": "PO # 1401215",
  "style": "BENHP0007",
  "orderRef": "EF/PO-26/0353",
  "sharedBuyer": "test",
  "sharedBuyerAddress": "Gazipur Factory, Block-A",
  "items": [
    { "desc": "A.M INTERNATIONAL Bath Towel…", "ply": "PCS", "qty": 850, "price": 290.6, "total": 247010 }
  ]
}
```

`sharedBuyer` / `sharedBuyerAddress` mirror the page-level Buyer fields so print pages work without the sales snapshot.

---

### PI Print Pages

#### Single PI — `pages/single-pi.php`

Prints one PI at a time. Loaded via the **"🖨 Print Single PI"** button on the sales page.

Format:
```
PROFOMA INVOICE NO : ZZAL/PI/26/4        Date : 22/07/2026
BUYER: [buyer name]
TO
[Customer Name]
[Customer Address]

WE CONFIRM HAVING SOLD TO YOU THE FOLLOWING MERCHANDISE AS PER TERMS AND CONDITION STATED BELOW.

| SL NO | Description of goods    | PLY | Quantity/Pcs/con | Unit Price | Total Amount (USD) |
|-------|-------------------------|-----|-----------------|------------|---------------------|
|       | PO # NTML/AMAN2025-01   |     |                 |            |                     |
|   1   | Bath Towel…             | PCS | 850             | $ 290.60   | $ 247,010.00        |
...

TOTAL AMOUNT : US DOLLAR: [AMOUNT IN WORDS]
Terms & Conditions: ...
Signatures: ...
```

Data source: reads from `salesPg` snapshot (sales page data) and the loaded PI's `pos[0]` JSON. Falls back to `po.sharedBuyer` / `po.sharedBuyerAddress` if snapshot is absent.

#### Summary PI — `pages/summary-pi.php`

Prints a combined summary of all PIs for an order on one document. Each PI group shows:

- One combined ORDER REF + PO # row (no border between them, same `<td>` with `<br>`)
- Item rows with normal borders

The ORDER REF / PO # row is only created when content exists (empty rows are suppressed).

Format within the items table:
```
ORDER REF: EF/PO-26/0353
PO # 1401215  Style# BENHP0007/
[item rows...]
```

#### Master PI — `pages/master-pi.php`

A single combined Proforma Invoice that merges **selected items from multiple individual PIs**.

**Master PI does not create a new PI record in the database.** It is purely a print document.

Format: same layout as single-pi.php — Logo, PROFORMA INVOICE title, SL NO column, full Terms & Conditions, Signatures, Footer.

---

### Master PI — Item Selection Flow

1. On the Sales page, load an order with saved PIs
2. The **"PIs for this Order"** section lists all individual PIs as clickable cards
3. Click a PI card → its item rows expand inline below the card:
   - PO reference line: `PO # ... · ORDER REF: ... · Style# ...`
   - Item rows with checkboxes: `[□] Description | Ply | Qty | Unit Price | Amount`
4. Check items from this PI, then click another PI to expand and add more items
5. A **basket bar** appears at the bottom of the overview section showing selected count / total qty / total value
6. Click **"⊕ Create Master PI"** in the basket bar

Behind the scenes, `generateMasterPi()` in `sales.php`:
- Collects all `.mpi-item-chk:checked` elements
- Reads item data from `_savedPisCache` (populated from `api/pis.php`)
- Groups items by PI/PO, carrying `orderRef`, `poNum`, `style`, `sharedBuyer`, `sharedBuyerAddress`
- Writes the selection to `sessionStorage.mpi_custom_items` (JSON)
- Navigates to `master-pi.php` with URL params: `?days=90&lctype=Sight&tol=5&bin=...&bank=ncc`

On `master-pi.php`, `window.onOrderLoad` checks for `sessionStorage.mpi_custom_items`:
- If found: calls `renderMasterPiFromCustom(groups, res)` — renders the document from the custom selection, then clears the sessionStorage key
- If not found: falls back to normal behavior (shows PI checklist, renders all)

---

### Terms & Conditions on PI

The Terms panel on the sales page (and echoed on all PI print pages) contains 16 standard clauses. Key dynamic fields:

| Field | Source |
|-------|--------|
| LC Days | `termLcDays` select (At Sight / 30 / 60 / 90 / 120) |
| Tolerance | `termTolerance` select (3% / 5% / 10%) |
| Beneficiary BIN | `termBinNo` text input (auto-filled from customer master) |
| Bank details | `termBank` select (`ncc` or `dbbl`) |

Bank details (Name, Address, Account, Swift, Routing) are looked up from a `BANKS` constant in the print pages.

---

## Order Lookup API — `api/order_lookup.php`

### `GET ?id=ORD-2026-0015`

Returns the full order snapshot used to populate any page on load:

```json
{
  "found": true,
  "order": { "order_id": "ORD-2026-0015", "customer_name": "...", "current_step": "marketing", ... },
  "pages": {
    "marketing-intake": { "customer": "LIZ Fashion", "salesPerson": "Navid", ... },
    "sales": { ... }
  },
  "pis": [ { ...pi object... } ]
}
```

### `POST` (no body)

Creates a new order, returns `{ "ok": true, "order_id": "ORD-2026-0016" }`.

---

## Marketing Page — Order Summary

`pages/marketing.php` shows a full read-only summary of everything done for the loaded order.

`loadMktSummary(orderId, order)` is called:
1. **Immediately** on load if `sessionStorage` has an order ID (with `null` order — shows ID only)
2. **Again** via `onOrderLoad` with the full order object (populates meta chips + source-glance fields)

The function:
1. Shows the panel immediately (`display:block`) — never waits for PI fetch
2. Renders meta chips: Customer, Buyer, PO Number, TRIMS/IPO, Step, Date
3. Populates source-glance `[data-bind]` elements from `order` fields
4. Fetches PIs and renders expandable PI cards with full PO + item-level tables
5. On PI fetch error: shows "Could not load PIs" message — panel stays visible

### PI card types

| Badge | Condition |
|-------|-----------|
| `MASTER` | `pi.is_master === 1` |
| `IN MASTER` | PI number appears in a master's `included_pis` |
| `STANDALONE` | Individual PI not referenced by any master |

---

## Content Security Policy

Bluehost/LiteSpeed inherits WordPress CSP from the parent domain. `includes/header.php` overrides it:

```php
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval'; img-src * data: blob:; font-src * data:; connect-src *;");
```

This allows inline `<script>` blocks and dynamic `eval` used by the app.

---

## Setup / Installation

### First-time setup (local or live)

1. Upload all files to the web root folder
2. Visit `/setup/run_schema.php` — creates all tables
3. Visit `/setup/create_users.php` — seeds the admin user
4. Visit `/setup/add_pi_order_columns.php` — runs the PI migration (adds `order_id`, `is_master`, `included_pis` to `pis` table)
5. Navigate to the app root — redirects to login

### Live server specifics

- DB already exists; `run_schema.php` auto-strips `CREATE DATABASE` and `USE` statements
- Folder: `/home2/talhagr1/public_html/znzal/accessories_tracking_system/`
- Delete any `index.html` in the folder — it shadows `index.php`

### Caching issues on live

Bluehost runs LiteSpeed with aggressive caching. After uploading new files:

1. Clear LiteSpeed cache from cPanel → LiteSpeed Web Cache → Purge All
2. Hard-refresh in browser: Ctrl+Shift+R

The `.htaccess` in the project folder disables browser caching for PHP responses.

---

## Roles

| Role | Access |
|------|--------|
| `admin` | Full access + user management + all signature blocks |
| `sales_person` | Own signature block |
| `team_leader` | Sales person + team leader signature blocks |
| `commercial` | Commercial signature block |
| `sales_coordinator` | Coordinator signature block |
| `head_of_business` | Head signature block |

---

## Local Development

Requirements: XAMPP (PHP 8+, MySQL 8+)

1. Clone/copy project to `E:\xampp\htdocs\ed_module\`
2. Start Apache + MySQL in XAMPP
3. Visit `http://localhost/ed_module/setup/run_schema.php`
4. Visit `http://localhost/ed_module/setup/create_users.php`
5. Visit `http://localhost/ed_module/` — login with seeded admin credentials

Default admin credentials are set in `setup/create_users.php`.
