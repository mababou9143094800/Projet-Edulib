<?php
// ============================================================
// EduLib — Configuration
// ============================================================

// Base de données — adaptez selon votre environnement
define('DB_HOST',    'localhost');
define('DB_NAME',    'edulib');
define('DB_USER',    'root');
define('DB_PASS',    'root');
define('DB_CHARSET', 'utf8mb4');

// Site
define('SITE_NAME', 'EduLib');

// Sécurité session
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode',  '1');
ini_set('session.cookie_samesite',  'Strict');

// Fuseau horaire
date_default_timezone_set('Europe/Paris');
