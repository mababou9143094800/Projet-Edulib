<?php
// ============================================================
// EduLib — Configuration
// Charge .env s'il existe, sinon utilise les valeurs par défaut
// ============================================================

$_env_file = __DIR__ . '/../.env';
if (is_file($_env_file)) {
    foreach (file($_env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
        if ($_line[0] === '#' || !str_contains($_line, '=')) continue;
        [$_k, $_v] = explode('=', $_line, 2);
        $_ENV[trim($_k)] = trim($_v);
    }
}
unset($_env_file, $_line, $_k, $_v);

define('DB_HOST',    $_ENV['DB_HOST']    ?? 'localhost');
define('DB_NAME',    $_ENV['DB_NAME']    ?? 'edulib');
define('DB_USER',    $_ENV['DB_USER']    ?? 'root');
define('DB_PASS',    $_ENV['DB_PASS']    ?? 'root');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');
define('SITE_NAME',  $_ENV['SITE_NAME']  ?? 'EduLib');
define('MAIL_FROM',  $_ENV['MAIL_FROM']  ?? '');

// Sécurité session
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode',  '1');
ini_set('session.cookie_samesite',  'Strict');

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

// Répertoire de logs
define('LOG_DIR', __DIR__ . '/../var/logs');
