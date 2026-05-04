<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

// Statistiques globales
$stats = db()->query('
  SELECT
    (SELECT COUNT(*) FROM users)        AS nb_users,
    (SELECT COUNT(*) FROM resources)    AS nb_resources,
    (SELECT COUNT(*) FROM categories)   AS nb_categories,
    (SELECT COUNT(*) FROM users WHERE role="admin") AS nb_admins
')->fetch();

// Derniers inscrits
$last_users = db()->query('
  SELECT id, nom, prenom, email, role, created_at
  FROM users ORDER BY created_at DESC LIMIT 5
')->fetchAll();

// Dernières ressources
$last_resources = db()->query('
  SELECT r.id, r.titre, r.created_at, c.nom AS cat_nom, u.prenom, u.nom AS auteur_nom
  FROM resources r
  JOIN categories c ON r.categorie_id = c.id
  JOIN users u ON r.auteur_id = u.id
  ORDER BY r.created_at DESC LIMIT 5
')->fetchAll();

$page_title = 'Administration';
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="admin-layout">

    <!-- Sidebar -->
    <?php include __DIR__ . '/includes/admin-nav.php'; ?>

    <!-- Contenu -->
    <div>
      <?php render_flash(); ?>

      <div class="page-header mt-0" style="padding-top:0;">
        <p class="page-header__eyebrow">Panneau d'administration</p>
        <h1>Tableau de bord</h1>
      </div>

      <!-- Stats -->
      <div class="stats-row mb-4">
        <div class="stat-card">
          <div class="stat-card__number"><?= (int)$stats['nb_users'] ?></div>
          <div class="stat-card__label">Utilisateurs</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__number"><?= (int)$stats['nb_admins'] ?></div>
          <div class="stat-card__label">Admins</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__number"><?= (int)$stats['nb_resources'] ?></div>
          <div class="stat-card__label">Ressources</div>
        </div>
        <div class="stat-card">
          <div class="stat-card__number"><?= (int)$stats['nb_categories'] ?></div>
          <div class="stat-card__label">Catégories</div>
        </div>
      </div>

      <!-- Derniers inscrits -->
      <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
        <h2 style="font-size:1.05rem;">Derniers inscrits</h2>
        <a href="/admin/users.php" class="btn btn--ghost btn--sm">Tous les utilisateurs &rarr;</a>
      </div>
      <div class="table-wrap mb-4">
        <table>
          <thead><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Inscrit le</th></tr></thead>
          <tbody>
            <?php foreach ($last_users as $u): ?>
              <tr>
                <td><?= e($u['prenom'] . ' ' . $u['nom']) ?></td>
                <td class="text-muted"><?= e($u['email']) ?></td>
                <td><span class="role-badge role-badge--<?= $u['role'] ?>"><?= $u['role'] ?></span></td>
                <td class="text-muted"><?= format_date($u['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Dernières ressources -->
      <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
        <h2 style="font-size:1.05rem;">Dernières ressources</h2>
        <a href="/resources.php" class="btn btn--ghost btn--sm">Voir les ressources &rarr;</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Titre</th><th>Matière</th><th>Auteur</th><th>Date</th></tr></thead>
          <tbody>
            <?php foreach ($last_resources as $r): ?>
              <tr>
                <td><a href="/resource-view.php?id=<?= $r['id'] ?>"><?= e($r['titre']) ?></a></td>
                <td><span class="badge"><?= e($r['cat_nom']) ?></span></td>
                <td class="text-muted"><?= e($r['prenom'] . ' ' . $r['auteur_nom']) ?></td>
                <td class="text-muted"><?= format_date($r['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
