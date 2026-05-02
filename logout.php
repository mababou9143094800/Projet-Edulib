<?php
// ============================================================
// EduLib — Déconnexion
// ============================================================
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

logout_user();
session_start();
flash_set('success', 'Vous êtes déconnecté(e).');
redirect('/login.php');
