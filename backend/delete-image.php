<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

csrf_verify();

$image_id   = isset($_POST['image_id'])   && ctype_digit($_POST['image_id'])   ? (int)$_POST['image_id']   : 0;
$resource_id = isset($_POST['resource_id']) && ctype_digit($_POST['resource_id']) ? (int)$_POST['resource_id'] : 0;

if (!$image_id || !$resource_id) {
    flash_set('error', 'Requête invalide.');
    redirect('/frontend/dashboard.php');
}

$stmt = db()->prepare('
    SELECT ri.filename, r.auteur_id
    FROM resource_images ri
    JOIN resources r ON r.id = ri.resource_id
    WHERE ri.id = ? AND ri.resource_id = ?
');
$stmt->execute([$image_id, $resource_id]);
$row = $stmt->fetch();

if (!$row) {
    flash_set('error', 'Image introuvable.');
    redirect('/backend/edit-resource.php?id=' . $resource_id);
}

$user = current_user();
if ($row['auteur_id'] !== $user['id'] && !is_admin()) {
    flash_set('error', 'Action non autorisée.');
    redirect('/backend/edit-resource.php?id=' . $resource_id);
}

$path = __DIR__ . '/../uploads/resources/' . basename($row['filename']);
if (is_file($path)) {
    unlink($path);
}

db()->prepare('DELETE FROM resource_images WHERE id = ?')->execute([$image_id]);

flash_set('success', 'Image supprimée.');
redirect('/backend/edit-resource.php?id=' . $resource_id);
