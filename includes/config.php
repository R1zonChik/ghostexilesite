<?php
define('DB_HOST', '192.168.0.150');
define('DB_USER', 'RizonChik');
define('DB_PASS', 'QOR265YC');
define('DB_NAME', 'GhostExile');

define('SITE_NAME', 'Ghost Exile Guide');
define('SITE_DESCRIPTION', 'Полное руководство по игре Ghost Exile');
define('SITE_URL', 'https://geguide.ru');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOADS_DIR', $_SERVER['DOCUMENT_ROOT'] . '/assets/uploads/');
define('UPLOADS_URL', SITE_URL . '/assets/uploads/');

date_default_timezone_set('Europe/Moscow');
define('DEBUG_MODE', false);

function debug_mode() {
    if (DEBUG_MODE) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    } else {
        error_reporting(0);
        ini_set('display_errors', 0);
    }
}
debug_mode();

require_once __DIR__ . '/database.php';
$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>