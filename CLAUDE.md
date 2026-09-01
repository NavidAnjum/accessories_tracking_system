# CLAUDE.md — AI / Agent Working Notes for `ed_module` (ATS)

This file orients an AI assistant working on the **Accessories Tracking System
(ATS)**. Read it before making changes. For a fuller human description see
[SYSTEM_DOCUMENTATION.md](SYSTEM_DOCUMENTATION.md).

---

## What this project is
A PHP 8 + MySQL (PDO) web app, **no framework**, with vanilla-JS front ends.
It tracks an export accessories order through a 14-step workflow (Marketing
Intake → Costing → PI → Marketing → LC → … → Challan Sheet) and generates
export/bank documents. Company: Zaber & Zubair Accessories Ltd.

## Environment
- Runs under **XAMPP** at `E:\xampp`, served from `e:\xampp\htdocs\ed_module`.
- **Database is selected by host** in [includes/db.php](includes/db.php):
  - `localhost` → DB `ed_module`, user `root`, empty password.
  - live host → DB `talhagr1_atx`. **Never hard-code a DB name** — always use `getDB()`.
- Local MySQL CLI: `E:\xampp\mysql\bin\mysql.exe -u root ed_module`.
- App base URL locally: `http://localhost/ed_module`. `BASE_PATH` /
  `window.APP_BASE` are derived from the URL — use them, never hard-code paths.

## Layout
```
pages/      one PHP page per workflow step + master data + print pages
api/        JSON endpoints (orders, pis, save_page, customers, erp_proxy, notifications, …)
includes/   config.php, db.php, auth.php, header.php, footer.php, notifications.php,
            erp_sale_orders_cache.php
setup/      one-off migrations / seeds / ERP backfill + cron scripts
*.py        DOCX documentation generators (Playwright + python-docx)
```

## Core data model (4 tables to know)
- `orders` — one row per order (`order_id` = `ORD-YYYY-NNNN`), header + `current_step`.
- `pis` — Proforma Invoices; **`pos` is a JSON blob** of PO groups → `items[]`.
  Types: Single (multi-PO), Summary, Master (`is_master`, `included_pis`).
- `page_data` — generic `(order_id, page_name)` → JSON `data`. **Every step
  persists its form here** via [api/save_page.php](api/save_page.php).
- `notifications` — per-user worklist; written on step change.
- Master data: `customers`, `buyers`, `items`.
- ERP cache: `erp_sale_orders_cache` (+ `erp_backfill_state`).
- `erp_order_inbox` — one row per ERP **sales order** (`sale_order_no` unique).
  Tracks conversion (`work_order_id` is indexed and may repeat because one
  multi-PO work order can include multiple ERP sales orders),
  read state (`read_at` / `read_by_id`), and a `snapshot_json` of the ERP lines.
  Managed via [includes/erp_order_inbox.php](includes/erp_order_inbox.php); its
  tables/columns self-migrate through `ensureErpOrderInboxTable()`.

Important: **order detail for PI-created orders lives in `pis`, not `orders`.**
When surfacing order info (e.g. dashboards), enrich from `pis` — don't assume
the `orders` row is populated.

**Work-order id generation** (`ORD-YYYY-MM-NNNNN`): the next sequence number must
be the **max across BOTH `orders.order_id` and `erp_order_inbox.work_order_id`**.
Using only `orders` max reused an id already claimed in
the inbox → 1062 duplicate. See `nextErpWorkOrderId()`
([api/erp_create_work_order.php](api/erp_create_work_order.php)) and the same
logic in [api/erp_order_import.php](api/erp_order_import.php).

## Roles & the redirect guard
- Roles and their allowed pages are defined in `allowedTabs()`
  ([includes/auth.php](includes/auth.php)).
- [includes/header.php](includes/header.php) redirects any user who opens a page
  outside `allowedTabs()` to their **first allowed page**. Consequence:
  **post-submit navigation must stay within the current role's allowed tabs**,
  or the user gets bounced to their home page. (e.g. marketing cannot open
  `lc.php`, so a marketing submit must not `window.location` there.)

## Front-end conventions
- Talk to APIs with `fetch(APP_BASE + '/api/...')`.
- Current order id lives in `sessionStorage['ats_current_order_id']`.
- Header/footer call page hooks `window.onOrderLoad(res)` and
  `window.onNewOrder(orderId|null)`. A page that defines `onNewOrder` can start
  a fresh draft in place (no navigation).
- Prefer extending existing inline JS patterns on a page over introducing new
  libraries. There is **no jQuery / build step**; keep it dependency-free.

## ERP integration (see [api/erp_proxy.php](api/erp_proxy.php) + [includes/erp_sale_orders_cache.php](includes/erp_sale_orders_cache.php))
- Live ERP: `https://ebs.talhagroup.com:8080/ords/xxapi/ebs/sale-orders` (`?po=` or `?offset=&limit=`).
- Exact sales-order lookup from the PI page uses [api/erp_order_proxy.php](api/erp_order_proxy.php):
  check `erp_sale_orders_cache.sale_order_no` first and call the live ERP only
  when the order is absent locally; successful live results are cached.
- Proxy merges **live + local cache**, dedupes, groups by customer PO / sales
  order, prefers exact live PO, and returns `multiple:true` + `options[]` when
  ambiguous. ERP `customerName` is the real customer → map it to **Customer (TO)**,
  not to the Buyer field.
- Cache search is scored and deliberately does **not** split structured codes
  (letters+digits+separators like `601BLAUI27-210`) into loose tokens.

### ERP Live Orders report ([pages/erp-live-orders-report.php](pages/erp-live-orders-report.php) + [api/erp_live_orders_report.php](api/erp_live_orders_report.php))
- Full-width page, linked at the top nav beside Dashboard (gated by
  `canAccessTab('erp-orders-report')`). Commercial-facing.
- **Cache-first then reconcile**: the page first loads saved rows fast
  (`?cached=1`, read from `erp_sale_orders_cache` by `header_creation_date`),
  then fetches live and merges. **Every live load also upserts** what it fetched
  (`erpSaleOrdersUpsertItems`) and `syncErpOrderInbox()` so nothing is missed —
  the backfill cron is a backup, not the only writer.
- Auto-loads the last ~10 days on open and auto-reloads when the date range
  changes. Client-side filters: Work Order (All / Not Created / Already Created),
  Read (All / Unread / Read), and free-text **Customer / Buyer / Sales Person /
  PO** search boxes (all in `erpLiveApplyFilter()`).
- Rows carry `workOrderId` + `conversionStatus` (from `erpOrderInboxMappings`)
  and `readStatus` (from `erpOrderInboxReadMap`). "Already created" counts as read.
- Do **not** re-add a "saved N new rows to DB" indicator — it was removed, and
  the count computation (`erpSaleOrdersItemKeys`/`CountExistingKeys`) was dropped
  because it threw "undefined function" in some runtime paths.

### ERP → notifications
- `canManageErpOrderInbox()` roles see unconverted ERP orders as notifications.
- The list is floored at a **fixed start date** `ERP_ORDER_NOTIFY_FLOOR_DATE`
  ([includes/config.php](includes/config.php), currently `2026-08-30`): show every
  unconverted order with `header_creation_date >= floor`, staying until a work
  order is created. Do **not** pin the list to `MAX(header_creation_date)` or to
  "today" — that hid all but the newest day's orders.

## Print / PDF pages (export & bank documents)
- Print pages: [pages/pi-print.php](pages/pi-print.php),
  [pages/single-pi.php](pages/single-pi.php),
  [pages/commercial-print.php](pages/commercial-print.php),
  [pages/exchange-print.php](pages/exchange-print.php),
  [pages/document-print.php](pages/document-print.php).
- Each doc is a fixed A4 box: `.spi-doc`/equivalent at `width:210mm;height:297mm`,
  `@page{margin:0}`, `overflow:hidden`. The container is a **flex column** and the
  brand footer uses `margin-top:auto` so it pins to the page bottom; the content
  block uses `flex:1 1 auto` (not just `min-height:100%`, which is flaky) so it
  fills the page and the footer actually reaches the bottom.
- **Term/page-2 pagination** ([single-pi.php](pages/single-pi.php)): split to the
  continuation page only on **real overflow** (`docEl.scrollHeight >
  docEl.clientHeight`). An earlier "require N px of slack below the footer" check
  bumped a term to a near-empty page 2 even when everything fit.
- **Font sizing is per-document**: the **PI** (single-pi + pi-print) is scaled to
  75% of the original sizes; the other print docs are at their original sizes.
  Don't blanket-scale all print pages when a request says "PI only".
- **PLY** is removed from the printed PDFs only, **not** the data-entry UI.

## How to run / verify
- Just open pages in the browser under `http://localhost/ed_module` (XAMPP Apache
  must be running).
- Quick API check: `curl -s http://localhost/ed_module/api/orders.php`.
- DB inspection: `E:\xampp\mysql\bin\mysql.exe -u root ed_module -e "SELECT …"`.
- Regenerate a role guide: `python generate_doc.py commercial`.

## Conventions to follow when editing
- Match the surrounding style; PHP uses PDO prepared statements everywhere —
  never build SQL with string interpolation of user input.
- Endpoints return `{"error": "..."}` + an HTTP status on failure; keep that shape.
- Keep changes minimal and within the existing file's idiom; this is a
  live-ish app with hand-rolled JS per page.
- Windows host: shell is PowerShell/Git-Bash; paths use `e:\xampp\htdocs\ed_module`.

## Known gotchas (bugs previously fixed here — don't reintroduce)
1. Dashboard showed blank rows because it read only `orders`; enrich from `pis`.
2. ERP `customerName` was being dumped into the Buyer field, leaving Customer
   empty; it must populate Customer (TO).
3. Submitting from `marketing.php` navigated to `lc.php`, which bounced the
   marketing role back to intake; keep post-submit nav role-legal.
4. A PI could be submitted with no customer selected → blank order; validate/
   fill customer on submit and copy it onto the `orders` row.
5. Work-order id was generated from `orders` max only → collided with an id
   already in `erp_order_inbox.work_order_id` (1062 duplicate). Take the max
   across both tables. (See work-order id note above.)
6. PI print rendered a near-empty second page — the paginator split on
   insufficient slack instead of real overflow. Split on `scrollHeight`.
7. ERP notifications showed only the newest date because the query pinned to
   `MAX(header_creation_date)`; use the fixed `ERP_ORDER_NOTIFY_FLOOR_DATE`.
