<?php
// ============================================================
// EduLib — Tableau de bord utilisateur
// ============================================================
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$user = current_user();

// Ressources de l'utilisateur
$stmt = db()->prepare('
  SELECT r.*, c.nom AS cat_nom
  FROM resources r
  JOIN categories c ON r.categorie_id = c.id
  WHERE r.auteur_id = ?
  ORDER BY r.created_at DESC
');
$stmt->execute([$user['id']]);
$my_resources = $stmt->fetchAll();

$page_title = 'Mon espace';
include __DIR__ . '/includes/header.php';
?>

<div class="container section">

  <?php render_flash(); ?>

  <div class="page-header mt-0" style="padding-top:0;">
    <p class="page-header__eyebrow">Tableau de bord</p>
    <h1>Bonjour, <?= e($user['prenom']) ?> <?= e($user['nom']) ?>
      <?php if ($user['role'] === 'admin'): ?>
        <span class="role-badge role-badge--admin">Admin</span>
      <?php endif; ?>
    </h1>
    <p class="text-muted text-sm mt-1"><?= e($user['email']) ?></p>
    <div class="page-header__actions">
      <a href="/add-resource.php"  class="btn btn--primary btn--sm">+ Déposer une ressource</a>
      <a href="/profile.php"       class="btn btn--ghost btn--sm">Modifier mon profil</a>
      <?php if (is_admin()): ?>
        <a href="/admin/index.php" class="btn btn--ghost btn--sm">Panneau admin</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Mes ressources -->
  <h2 class="mb-2">Mes ressources <span class="text-muted text-sm" style="font-weight:400;">(<?= count($my_resources) ?>)</span></h2>

  <?php if ($my_resources): ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Titre</th>
            <th>Matière</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($my_resources as $r): ?>
            <tr>
              <td>
                <a href="/resource-view.php?id=<?= $r['id'] ?>" style="text-decoration:none; font-weight:600; color:var(--c-text);">
                  <?= e($r['titre']) ?>
                </a>
              </td>
              <td><span class="badge"><?= e($r['cat_nom']) ?></span></td>
              <td class="text-muted"><?= format_date($r['created_at']) ?></td>
              <td>
                <div class="td-actions">
                  <a href="/edit-resource.php?id=<?= $r['id'] ?>" class="btn btn--ghost btn--sm">Modifier</a>
                  <form method="post" action="/delete-resource.php"
                        onsubmit="return confirm('Supprimer cette ressource ?')">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <button type="submit" class="btn btn--danger btn--sm">Supprimer</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty-state" style="padding:2rem 0;">
      <div class="empty-state__icon">&#9634;</div>
      <p class="empty-state__title">Vous n'avez pas encore de ressources</p>
      <p class="empty-state__desc text-muted">
        <a href="/add-resource.php">Déposez votre première fiche</a> pour la partager avec vos camarades.
      </p>
    </div>
  <?php endif; ?>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
