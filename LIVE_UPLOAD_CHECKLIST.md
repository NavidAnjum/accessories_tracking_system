# Live Upload Checklist

Upload these files from local `E:\xampp\htdocs\ed_module` to the same paths inside live `accessories_tracking_system`.

## Files To Upload

| Local file | Live folder |
| --- | --- |
| `E:\xampp\htdocs\ed_module\setup\cron_erp_sale_orders_backfill.php` | `accessories_tracking_system/setup/cron_erp_sale_orders_backfill.php` |
| `E:\xampp\htdocs\ed_module\pages\erp-live-orders-report.php` | `accessories_tracking_system/pages/erp-live-orders-report.php` |

## Live Database Cleanup

File upload will not delete old live data. Run this in live phpMyAdmin for database `talhagr1_atx`.

This keeps `users`, `customers`, and role/user data. It clears only order/runtime/ERP cache data.

```sql
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE notifications;
TRUNCATE TABLE page_data;
TRUNCATE TABLE order_items;
TRUNCATE TABLE pis;
TRUNCATE TABLE orders;
TRUNCATE TABLE erp_order_inbox;
TRUNCATE TABLE erp_sale_orders_cache;
TRUNCATE TABLE erp_backfill_state;

SET FOREIGN_KEY_CHECKS = 1;
```

After uploading `setup/cron_erp_sale_orders_backfill.php`, run:

```text
https://znzal.com/accessories_tracking_system/setup/cron_erp_sale_orders_backfill.php?key=123
```

The cron/backfill will start from `01/09/2026`.

## Confirm After Upload

- ERP live orders report should default to today only.
- Notification count should not show old ERP/order notifications.
- Customers should still remain in the system.
- New ERP orders should start appearing only from today's sync onward.
