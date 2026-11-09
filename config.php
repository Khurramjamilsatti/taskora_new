<?php
/**
 * Taskora — core configuration.
 * Edit the database credentials below to match your XAMPP / hosting setup.
 */

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'taskora');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);

// Leave empty to auto-detect (works for http://localhost/taskora/).
// On a live domain at the web root, set to '' or '/'.
define('BASE_URL_OVERRIDE', '');

define('APP_DEBUG', true);
define('UPLOAD_DIR', __DIR__ . '/uploads');

date_default_timezone_set('Asia/Karachi');
