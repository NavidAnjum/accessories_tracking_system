<?php
// App root path segment — e.g. '/ed_module' locally, '/accessories_tracking_system' on live
define('BASE_PATH', '/' . explode('/', ltrim($_SERVER['SCRIPT_NAME'], '/'))[0]);

// Fixed start date (Asia/Dhaka) for ALL notifications — both the workflow worklist
// and the ERP "new order" alerts. Anything created before this date is hidden, so
// the bell starts fresh from here. This floor never rolls forward with "today".
define('NOTIFY_FLOOR_DATE', '2026-09-01');
// ERP unconverted-order alerts use the same floor (kept as a separate name for
// existing references). Orders on/after this date stay until a work order exists.
define('ERP_ORDER_NOTIFY_FLOOR_DATE', NOTIFY_FLOOR_DATE);
