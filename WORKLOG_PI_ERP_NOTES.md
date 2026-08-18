# PI / ERP Worklog Notes

Date: August 18, 2026
Project: `ed_module`
Live upload path: `accessories_tracking_system`

## Main Idea

We updated the PI / ERP flow so Commercial can:

- create orders from PI stage
- search ERP by PO / style / remarks more flexibly
- handle multiple PO matches with a choose-one UI
- keep multiple POs inside one Single PI
- use cached ERP data plus live ERP data together

## Files Changed So Far

- `pages/sales.php`
- `api/erp_proxy.php`
- `includes/erp_sale_orders_cache.php`
- `setup/cron_erp_sale_orders_backfill.php`
- `setup/backfill_erp_sale_orders_local.php`

## Sales Page Changes

File: `pages/sales.php`

### PI behavior

- Order number format updated to monthly style:
  - `ORD-2026-08-00001`
- Customer dropdown / ERP loading / saved PI loading were adjusted during the PI work.
- Single PI can now work with multiple POs inside the same PI block.

### ERP search behavior

- ERP search now supports:
  - exact PO
  - saved PI lookup
  - saved PO lookup
  - live ERP fallback
  - cached ERP fallback
- If multiple ERP POs match, user gets a selection list instead of auto-loading the wrong one.
- Match details now show:
  - matched field source
  - sales order numbers
  - preview item details

### Add PO behavior inside Single PI

- Added `+ Add PO` button inside a PO block.
- This now works as:
  1. click `+ Add PO`
  2. same PI block enters append mode
  3. search box clears
  4. type another PO
  5. click `Search ERP`
  6. second PO is appended into the same PI block

- It no longer opens a new PI block.

### Multi-PO append fixes

- When appending another PO, existing items are now preserved.
- Existing rows are collected from actual row ids before rebuilding the block.
- Combined PO values are shown in the same block separated with ` / `.
- Combined sales order values are stored together.

### ERP item display fixes

- Cancelled rows are filtered out.
- Zero-quantity rows are filtered out.
- Duplicate ERP item lines are merged by:
  - description
  - type / uom
  - price
- Requested date display was simplified and calendar input was removed from that ERP-loaded field.

### Single / Summary / Master UI changes

- `+ Add Another PI` is hidden for Single PI.
- `+ Add Another PI` remains only for Summary PI.
- Master PI keeps its own separate flow.

### Print / Excel unlock fixes

- Print button was getting re-disabled by old lock logic.
- Fixed unlock behavior so print becomes available again when:
  - PI is submitted
  - saved PI data is loaded
  - saved PI overview is present

## ERP Proxy Changes

File: `api/erp_proxy.php`

### What it does now

- Searches live ERP:
  - `sale-orders?po=...`
- Also searches local ERP cache
- Merges both result sets
- Deduplicates rows
- Groups results by:
  - customer PO
  - sales order

### Matching behavior

- If live ERP returns an exact PO match, that exact live result is preferred first.
- Cached broad matches are only used when live ERP does not already provide the clean answer.
- When multiple valid matches exist, the API returns:
  - `multiple: true`
  - `options: [...]`

### Better UI payload

- Each option now includes:
  - PO label
  - customer name
  - sales orders
  - line count
  - grouped ERP data
  - match summary by field

## ERP Cache Search Changes

File: `includes/erp_sale_orders_cache.php`

### Cache table purpose

Stores ERP sale order rows locally for faster and broader searching.

### Search improvements

- Broad matching includes:
  - `customer_po_no`
  - `remarks`
  - `item_description`
  - `item_code`
  - `ordered_item`
  - `raw_json`
  - normalized combined search blob

- Added support for searching by style / remarks / mixed ERP text.

### Important fix

Mixed structured codes like:

- `601BLAUI27-210`
- `601JEAUI27-211`
- similar PO/style codes

should not be broken into loose tokens anymore.

Reason:

- loose token matching was causing unrelated rows to appear
- now mixed letter+number+separator codes are searched as one structured key

## ERP Cache / Backfill Work

Files:

- `setup/cron_erp_sale_orders_backfill.php`
- `setup/backfill_erp_sale_orders_local.php`

### Goal

Build and refresh local ERP sale order cache from paginated ERP API:

- `sale-orders`
- `sale-orders?offset=1000`
- `sale-orders?offset=2000`
- etc.

### Behavior

- backfill saves ERP rows into local DB
- keeps track of offset / pages / row counts
- designed for cron use
- user requested repeated page processing per run

### Table involved

- `erp_sale_orders_cache`
- state table also used:
  - `erp_backfill_state`

## Database / Table Notes

Main ERP cache table:

- `erp_sale_orders_cache`

Key useful columns:

- `customer_po_no`
- `sale_order_no`
- `remarks`
- `item_description`
- `item_code`
- `ordered_item`
- `search_blob`
- `search_blob_normalized`
- `raw_json`

State / tracking table:

- `erp_backfill_state`

Other existing tables used by the sales / PI flow:

- `orders`
- `pis`
- `page_data`

## Important Upload Notes

Depending on which fix is needed, upload these:

### For Single PI / Add PO / Print issues

- `pages/sales.php`

### For ERP exact-vs-broad result issues

- `api/erp_proxy.php`

### For wrong loose cache matches

- `includes/erp_sale_orders_cache.php`

### For cron / cache population

- `setup/cron_erp_sale_orders_backfill.php`
- `setup/backfill_erp_sale_orders_local.php`

## Known Intent / Final Behavior Wanted

### Single PI

- one PI
- can contain multiple POs
- all selected PO items appear one after another in same PI block
- PO names combine in same block

### Summary PI

- can still use multi-PI style behavior

### ERP search

- exact ERP PO should win first
- broad cached matches should still help when exact ERP is not enough
- user should see why something matched

## Raw SQL Examples Used For Checking

Example style / code search:

```sql
SELECT customer_po_no, sale_order_no, remarks, item_description
FROM erp_sale_orders_cache
WHERE customer_po_no LIKE '%FPS27GPLOVE%'
   OR remarks LIKE '%FPS27GPLOVE%'
   OR item_description LIKE '%FPS27GPLOVE%'
   OR item_code LIKE '%FPS27GPLOVE%'
   OR ordered_item LIKE '%FPS27GPLOVE%'
   OR raw_json LIKE '%FPS27GPLOVE%'
LIMIT 50;
```

Example structured PO / style search:

```sql
SELECT id, customer_po_no, sale_order_no, remarks, item_description, item_code, ordered_item
FROM erp_sale_orders_cache
WHERE customer_po_no LIKE '%C26C-Z04-QLT%'
   OR remarks LIKE '%C26C-Z04-QLT%'
   OR item_description LIKE '%C26C-Z04-QLT%'
   OR item_code LIKE '%C26C-Z04-QLT%'
   OR ordered_item LIKE '%C26C-Z04-QLT%'
   OR raw_json LIKE '%C26C-Z04-QLT%'
ORDER BY sale_order_no ASC
LIMIT 100;
```

## Current Status

As of this note:

- Single PI append flow has been implemented.
- ERP exact-vs-broad search logic has been tightened.
- Loose token cache matching for structured style/PO codes has been reduced.
- Print unlock logic on `sales.php` was patched multiple times and should be tested again after uploading latest `sales.php`.

## Best Next Check

If anything still behaves differently on live vs local, compare these first:

- latest `pages/sales.php`
- latest `api/erp_proxy.php`
- latest `includes/erp_sale_orders_cache.php`
- whether browser cache is serving old JS
- whether live DB has the same cached ERP rows as local

