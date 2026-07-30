<?php
// App root path segment — e.g. '/ed_module' locally, '/accessories_tracking_system' on live
define('BASE_PATH', '/' . explode('/', ltrim($_SERVER['SCRIPT_NAME'], '/'))[0]);
