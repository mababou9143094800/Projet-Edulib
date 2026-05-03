<?php
// ============================================================
// EduLib — Toggle vote sur une ressource (POST uniquement)
// ============================================================
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
csrf_verify();

$resource_id = isset($_POST['resource_id']) && ctype_digit($_POST['resource_id'])
    ? (int)$_POST['resource_id'] : 0;

if (!$resource_id) { http_response_code(400); exit; }

// Vérifie que la ressource publiée existe
$stmt = db()->prepare('SELECT id FROM resources WHERE id = ? AND status = "published"');
$stmt->execute([$resource_id]);
if (!$stmt->fetch()) { http_response_code(404); exit; }

$user_id = current_user()['id'];

// Toggle
$check = db()->prepare('SELECT 1 FROM votes WHERE resource_id = ? AND user_id = ?');
$check->execute([$resource_id, $user_id]);

if ($check->fetch()) {
    db()->prepare('DELETE FROM votes WHERE resource_id = ? AND user_id = ?')
        ->execute([$resource_id, $user_id]);
} else {
    db()->prepare('INSERT INTO votes (resource_id, user_id) VALUES (?, ?)')
        ->execute([$resource_id, $user_id]);
}

redirect('/resource-view.php?id=' . $resource_id);
