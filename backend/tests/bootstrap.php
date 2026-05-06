<?php
// Bootstrap PHPUnit — charge les helpers sans démarrer de serveur HTTP
define('DB_HOST',    'localhost');
define('DB_NAME',    'edulib_test');
define('DB_USER',    'root');
define('DB_PASS',    'root');
define('DB_CHARSET', 'utf8mb4');
define('SITE_NAME',  'EduLib');
define('MAIL_FROM',  '');
define('LOG_DIR',    sys_get_temp_dir() . '/edulib_tests');
define('RATE_LIMIT_MAX',    5);
define('RATE_LIMIT_WINDOW', 900);

require_once __DIR__ . '/../includes/functions.php';

// Stub minimal de db() pour les tests unitaires ne nécessitant pas de BDD
if (!function_exists('db')) {
    function db() { return null; }
}

// Fonctions auth non-BDD uniquement
if (!function_exists('get_client_ip')) {
    function get_client_ip() {
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';
    }
}

// session_start_safe stub
if (!function_exists('session_start_safe')) {
    function session_start_safe() {}
}
