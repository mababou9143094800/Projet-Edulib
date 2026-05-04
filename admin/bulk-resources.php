<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/admin/resources.php'); }
csrf_verify();

$action = $_POST['bulk_action'] ?? '';
$ids    = array_filter(array_map('intval', (array)($_POST['ids'] ?? [])));

if (!$ids || !in_array($action, ['delete','publish','draft'])) {
    flash_set('error', 'Action invalide ou sélection vide.');
    redirect('/admin/resources.php');
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));

switch ($action) {
    case 'delete':
        db()->prepare("DELETE FROM resources WHERE id IN ($placeholders)")
            ->execute(array_values($ids));
        flash_set('success', count($ids) . ' ressource(s) supprimée(s).');
        log_info('Suppression groupée ressources', ['ids' => $ids, 'par' => current_user()['id']]);
        break;
    case 'publish':
        db()->prepare("UPDATE resources SET status = 'published' WHERE id IN ($placeholders)")
            ->execute(array_values($ids));
        flash_set('success', count($ids) . ' ressource(s) publiée(s).');
        break;
    case 'draft':
        db()->prepare("UPDATE resources SET status = 'draft' WHERE id IN ($placeholders)")
            ->execute(array_values($ids));
        flash_set('success', count($ids) . ' ressource(s) passée(s) en brouillon.');
        break;
}

redirect('/admin/resources.php');
