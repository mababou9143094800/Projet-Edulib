<?php
// ============================================================
// EduLib — Admin — Action groupée sur les utilisateurs
// ============================================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/admin/users.php'); }
csrf_verify();

$action = $_POST['bulk_action'] ?? '';
$ids    = array_filter(array_map('intval', (array)($_POST['ids'] ?? [])));
$me     = current_user()['id'];

// Retire l'admin courant de la sélection par sécurité
$ids = array_filter($ids, fn($id) => $id !== $me);

if (!$ids || !in_array($action, ['delete','promote','demote'])) {
    flash_set('error', 'Action invalide ou sélection vide.');
    redirect('/admin/users.php');
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));

switch ($action) {
    case 'delete':
        db()->prepare("DELETE FROM users WHERE id IN ($placeholders)")
            ->execute(array_values($ids));
        flash_set('success', count($ids) . ' utilisateur(s) supprimé(s).');
        log_info('Admin bulk delete users', ['ids' => $ids, 'by' => $me]);
        break;
    case 'promote':
        db()->prepare("UPDATE users SET role = 'admin' WHERE id IN ($placeholders)")
            ->execute(array_values($ids));
        flash_set('success', count($ids) . ' utilisateur(s) promu(s) admin.');
        break;
    case 'demote':
        db()->prepare("UPDATE users SET role = 'user' WHERE id IN ($placeholders)")
            ->execute(array_values($ids));
        flash_set('success', count($ids) . ' utilisateur(s) rétrogradé(s) en user.');
        break;
}

redirect('/admin/users.php');
