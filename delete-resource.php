<?php
// ============================================================
// EduLib — Suppression d'une ressource (POST seulement)
// ============================================================
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/resources.php');
}

csrf_verify();

$id = isset($_POST['id']) && ctype_digit($_POST['id']) ? (int)$_POST['id'] : 0;

$stmt = db()->prepare('SELECT auteur_id FROM resources WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$resource = $stmt->fetch();

if (!$resource) {
    flash_set('error', 'Ressource introuvable.');
    redirect('/dashboard.php');
}

$user = current_user();
if ($resource['auteur_id'] !== $user['id'] && !is_admin()) {
    flash_set('error', 'Action non autorisée.');
    redirect('/dashboard.php');
}

db()->prepare('DELETE FROM resources WHERE id = ?')->execute([$id]);
flash_set('success', 'Ressource supprimée.');
redirect('/dashboard.php');
