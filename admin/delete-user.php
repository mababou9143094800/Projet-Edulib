<?php
// ============================================================
// EduLib — Admin — Suppression d'un utilisateur (POST)
// ============================================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/users.php');
}

csrf_verify();

$id = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int)$_POST['id'] : 0;

// Empêcher l'auto-suppression
if ($id === current_user()['id']) {
    flash_set('error', 'Vous ne pouvez pas supprimer votre propre compte depuis le panneau admin.');
    redirect('/admin/users.php');
}

$stmt = db()->prepare('SELECT id FROM users WHERE id=? LIMIT 1');
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    flash_set('error', 'Utilisateur introuvable.');
    redirect('/admin/users.php');
}

db()->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
flash_set('success', 'Utilisateur supprimé.');
redirect('/admin/users.php');
