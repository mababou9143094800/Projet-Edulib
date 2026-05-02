<?php
// ============================================================
// EduLib — Admin — Gestion des utilisateurs
// ============================================================
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$search = trim($_GET['q'] ?? '');
$params = [];
$where  = '1=1';
if ($search) {
    $where    = '(nom LIKE ? OR prenom LIKE ? OR email LIKE ?)';
    $like     = '%' . $search . '%';
    $params   = [$like, $like, $like];
}

$users = db()->prepare("
  SELECT u.*,
    (SELECT COUNT(*) FROM resources r WHERE r.auteur_id = u.id) AS nb_resources
  FROM users u
  WHERE $where
  ORDER BY u.created_at DESC
");
$users->execute($params);
$users = $users->fetchAll();

$page_title = 'Gestion des utilisateurs';
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="admin-layout">

    <?php include __DIR__ . '/includes/admin-nav.php'; ?>

    <div>
      <?php render_flash(); ?>

      <div class="page-header mt-0" style="padding-top:0;">
        <p class="page-header__eyebrow">Administration</p>
        <h1>Utilisateurs <span class="text-muted text-sm" style="font-weight:400;">(<?= count($users) ?>)</span></h1>
      </div>

      <!-- Recherche -->
      <form method="get" action="" class="filters mb-3">
        <div class="filters__form">
          <div class="filters__group">
            <label class="filters__label" for="q">Rechercher</label>
            <input class="form-control" type="search" id="q" name="q"
                   placeholder="Nom, prénom ou email…"
                   value="<?= e($search) ?>">
          </div>
          <div class="filters__actions">
            <button type="submit" class="btn btn--primary btn--sm">Chercher</button>
            <?php if ($search): ?>
              <a href="/admin/users.php" class="btn btn--ghost btn--sm">Effacer</a>
            <?php endif; ?>
          </div>
        </div>
      </form>

      <?php if ($users): ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Ressources</th>
                <th>Inscrit le</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
                <tr>
                  <td style="font-weight:600;"><?= e($u['prenom'] . ' ' . $u['nom']) ?></td>
                  <td class="text-muted text-sm"><?= e($u['email']) ?></td>
                  <td><span class="role-badge role-badge--<?= $u['role'] ?>"><?= $u['role'] ?></span></td>
                  <td class="text-muted"><?= (int)$u['nb_resources'] ?></td>
                  <td class="text-muted text-sm"><?= format_date($u['created_at']) ?></td>
                  <td>
                    <div class="td-actions">
                      <a href="/admin/edit-user.php?id=<?= $u['id'] ?>" class="btn btn--ghost btn--sm">Modifier</a>
                      <?php if ($u['id'] !== current_user()['id']): ?>
                        <form method="post" action="/admin/delete-user.php"
                              onsubmit="return confirm('Supprimer l\'utilisateur <?= e(addslashes($u['prenom'] . ' ' . $u['nom'])) ?> ?')">
                          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                          <input type="hidden" name="id" value="<?= $u['id'] ?>">
                          <button type="submit" class="btn btn--danger btn--sm">Supprimer</button>
                        </form>
                      <?php else: ?>
                        <span class="text-muted text-sm">(vous)</span>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <p class="empty-state__title">Aucun utilisateur trouvé</p>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
