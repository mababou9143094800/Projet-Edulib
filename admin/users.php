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
    $where  = '(nom LIKE ? OR prenom LIKE ? OR email LIKE ?)';
    $like   = '%' . $search . '%';
    $params = [$like, $like, $like];
}

$users_stmt = db()->prepare("
  SELECT u.*,
    (SELECT COUNT(*) FROM resources r WHERE r.auteur_id = u.id) AS nb_resources
  FROM users u
  WHERE $where
  ORDER BY u.created_at DESC
");
$users_stmt->execute($params);
$users = $users_stmt->fetchAll();

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
        <form method="post" action="/admin/bulk-users.php" id="bulk-form">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

          <!-- Barre d'actions en masse -->
          <div class="bulk-bar mb-2">
            <label class="bulk-bar__select">
              <input type="checkbox" id="select-all" title="Tout sélectionner">
              <span class="text-sm text-muted">Tout sélectionner</span>
            </label>
            <div class="flex gap-1">
              <select name="bulk_action" class="form-control" style="width:auto; padding:.35rem .75rem; font-size:.82rem;">
                <option value="">— Action groupée —</option>
                <option value="delete">Supprimer la sélection</option>
                <option value="promote">Passer en admin</option>
                <option value="demote">Passer en user</option>
              </select>
              <button type="submit" class="btn btn--sm btn--ghost"
                      onclick="return confirmBulk(this.form)">Appliquer</button>
            </div>
          </div>

          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th style="width:2rem;"></th>
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
                    <td>
                      <?php if ($u['id'] !== current_user()['id']): ?>
                        <input type="checkbox" name="ids[]" value="<?= $u['id'] ?>" class="row-check">
                      <?php endif; ?>
                    </td>
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
                                onsubmit="return confirm('Supprimer <?= e(addslashes($u['prenom'] . ' ' . $u['nom'])) ?> ?')">
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
        </form>
      <?php else: ?>
        <div class="empty-state">
          <p class="empty-state__title">Aucun utilisateur trouvé</p>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
document.getElementById('select-all')?.addEventListener('change', function() {
  document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
});
function confirmBulk(form) {
  const action = form.bulk_action.value;
  const checked = form.querySelectorAll('.row-check:checked').length;
  if (!action) { alert('Choisissez une action.'); return false; }
  if (!checked) { alert('Sélectionnez au moins un utilisateur.'); return false; }
  if (action === 'delete') return confirm('Supprimer ' + checked + ' utilisateur(s) ?');
  return true;
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
