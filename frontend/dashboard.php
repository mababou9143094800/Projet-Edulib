<?php
require_once __DIR__ . '/../backend/includes/db.php';
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/functions.php';

require_login();

$user = current_user();
$tab  = in_array($_GET['tab'] ?? '', ['resources','favorites']) ? $_GET['tab'] : 'resources';

// Ressources de l'utilisateur
$my_resources = db()->prepare('
  SELECT r.*, c.nom AS cat_nom
  FROM resources r
  JOIN categories c ON r.categorie_id = c.id
  WHERE r.auteur_id = ?
  ORDER BY r.created_at DESC
');
$my_resources->execute([$user['id']]);
$my_resources = $my_resources->fetchAll();

// Favoris de l'utilisateur
$my_favorites = db()->prepare('
  SELECT r.*, c.nom AS cat_nom, u.prenom, u.nom AS auteur_nom
  FROM favorites f
  JOIN resources r ON f.resource_id = r.id
  JOIN categories c ON r.categorie_id = c.id
  JOIN users u ON r.auteur_id = u.id
  WHERE f.user_id = ? AND r.status = "published"
  ORDER BY f.created_at DESC
');
$my_favorites->execute([$user['id']]);
$my_favorites = $my_favorites->fetchAll();

$page_title = 'Mon espace';
include __DIR__ . '/../backend/includes/header.php';
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
      <a href="/backend/add-resource.php"  class="btn btn--primary btn--sm">+ Déposer une ressource</a>
      <a href="/frontend/profile.php"       class="btn btn--ghost btn--sm">Modifier mon profil</a>
      <?php if (is_admin()): ?>
        <a href="/admin/index.php" class="btn btn--ghost btn--sm">Panneau admin</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Onglets -->
  <div class="tabs mb-3">
    <a href="/frontend/dashboard.php?tab=resources"
       class="tab<?= $tab === 'resources' ? ' tab--active' : '' ?>">
      Mes ressources <span class="tab__count"><?= count($my_resources) ?></span>
    </a>
    <a href="/frontend/dashboard.php?tab=favorites"
       class="tab<?= $tab === 'favorites' ? ' tab--active' : '' ?>">
      Mes favoris <span class="tab__count"><?= count($my_favorites) ?></span>
    </a>
  </div>

  <?php if ($tab === 'resources'): ?>

    <?php if ($my_resources): ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Titre</th>
              <th>Matière</th>
              <th>Statut</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($my_resources as $r): ?>
              <tr>
                <td>
                  <a href="/frontend/resource-view.php?id=<?= $r['id'] ?>" style="text-decoration:none; font-weight:600; color:var(--c-text);">
                    <?= e($r['titre']) ?>
                  </a>
                </td>
                <td><span class="badge"><?= e($r['cat_nom']) ?></span></td>
                <td>
                  <?php if ($r['status'] === 'draft'): ?>
                    <span class="status-badge status-badge--draft">Brouillon</span>
                  <?php else: ?>
                    <span class="status-badge status-badge--published">Publié</span>
                  <?php endif; ?>
                </td>
                <td class="text-muted"><?= format_date($r['created_at']) ?></td>
                <td>
                  <div class="td-actions">
                    <a href="/backend/edit-resource.php?id=<?= $r['id'] ?>" class="btn btn--ghost btn--sm">Modifier</a>
                    <form method="post" action="/backend/delete-resource.php"
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
          <a href="/backend/add-resource.php">Déposez votre première fiche</a> pour la partager avec vos camarades.
        </p>
      </div>
    <?php endif; ?>

  <?php else: ?>

    <?php if ($my_favorites): ?>
      <div class="resource-grid">
        <?php foreach ($my_favorites as $r): ?>
          <a href="/frontend/resource-view.php?id=<?= $r['id'] ?>" class="card-link">
            <div class="card-link__body">
              <div class="resource-card__category"><span class="badge"><?= e($r['cat_nom']) ?></span></div>
              <h2 class="resource-card__title"><?= e($r['titre']) ?></h2>
              <p class="resource-card__excerpt"><?= e(excerpt($r['description'])) ?></p>
            </div>
            <div class="card-link__footer">
              <div class="resource-card__meta">
                <span><?= e($r['prenom'] . ' ' . $r['auteur_nom']) ?></span>
                <span><?= format_date_full($r['created_at']) ?></span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state" style="padding:2rem 0;">
        <div class="empty-state__icon">&#9734;</div>
        <p class="empty-state__title">Aucun favori pour l'instant</p>
        <p class="empty-state__desc text-muted">
          Cliquez sur <strong>★ Ajouter aux favoris</strong> sur une ressource pour la retrouver ici.
        </p>
      </div>
    <?php endif; ?>

  <?php endif; ?>

</div>

<?php include __DIR__ . '/../backend/includes/footer.php'; ?>
