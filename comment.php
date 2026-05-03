<?php
// ============================================================
// EduLib — Ajout / suppression d'un commentaire (POST)
// ============================================================
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
csrf_verify();

$action      = $_POST['action'] ?? 'add';
$resource_id = isset($_POST['resource_id']) && ctype_digit($_POST['resource_id'])
    ? (int)$_POST['resource_id'] : 0;

if (!$resource_id) { http_response_code(400); exit; }

if ($action === 'delete') {
    $comment_id = isset($_POST['comment_id']) && ctype_digit($_POST['comment_id'])
        ? (int)$_POST['comment_id'] : 0;
    if (!$comment_id) { http_response_code(400); exit; }

    $stmt = db()->prepare('SELECT user_id FROM comments WHERE id = ?');
    $stmt->execute([$comment_id]);
    $c = $stmt->fetch();

    if ($c && ($c['user_id'] === current_user()['id'] || is_admin())) {
        db()->prepare('DELETE FROM comments WHERE id = ?')->execute([$comment_id]);
        flash_set('success', 'Commentaire supprimé.');
    }
    redirect('/resource-view.php?id=' . $resource_id);
}

// action = add
$contenu   = trim($_POST['contenu'] ?? '');
$parent_id = isset($_POST['parent_id']) && ctype_digit($_POST['parent_id'])
    ? (int)$_POST['parent_id'] : null;

if (!$contenu) {
    flash_set('error', 'Le commentaire ne peut pas être vide.');
    redirect('/resource-view.php?id=' . $resource_id);
}
if (mb_strlen($contenu) > 2000) {
    flash_set('error', 'Commentaire trop long (2000 caractères max).');
    redirect('/resource-view.php?id=' . $resource_id);
}

// Vérifie que le parent appartient bien à la même ressource
if ($parent_id) {
    $stmt = db()->prepare('SELECT id FROM comments WHERE id = ? AND resource_id = ? AND parent_id IS NULL');
    $stmt->execute([$parent_id, $resource_id]);
    if (!$stmt->fetch()) $parent_id = null;
}

db()->prepare('
    INSERT INTO comments (resource_id, user_id, parent_id, contenu)
    VALUES (?, ?, ?, ?)
')->execute([$resource_id, current_user()['id'], $parent_id, $contenu]);

// Notifie l'auteur de la ressource si ce n'est pas lui qui commente
$res = db()->prepare('SELECT auteur_id FROM resources WHERE id = ?');
$res->execute([$resource_id]);
$resource = $res->fetch();
if ($resource && $resource['auteur_id'] !== current_user()['id']) {
    $author = db()->prepare('SELECT email, prenom FROM users WHERE id = ?');
    $author->execute([$resource['auteur_id']]);
    $author = $author->fetch();
    if ($author) {
        send_notification(
            $author['email'],
            'Nouveau commentaire sur votre ressource — EduLib',
            "Bonjour {$author['prenom']},\n\n"
            . current_user()['prenom'] . ' ' . current_user()['nom']
            . " a commenté votre ressource.\n\n"
            . "Voir : http://localhost:8000/resource-view.php?id={$resource_id}\n"
        );
    }
}

flash_set('success', 'Commentaire ajouté.');
redirect('/resource-view.php?id=' . $resource_id . '#comments');
