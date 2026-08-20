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

Important: **order detail for PI-created orders lives in `pis`, not `orders`.**
When surfacing order info (e.g. dashboards), enrich from `pis` — don't assume
the `orders` row is populated.

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
- Proxy merges **live + local cache**, dedupes, groups by customer PO / sales
  order, prefers exact live PO, and returns `multiple:true` + `options[]` when
  ambiguous. ERP `customerName` is the real customer → map it to **Customer (TO)**,
  not to the Buyer field.
- Cache search is scored and deliberately does **not** split structured codes
  (letters+digits+separators like `601BLAUI27-210`) into loose tokens.

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
```
