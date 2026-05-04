<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$search = trim($_GET['q'] ?? '');
$status = in_array($_GET['status'] ?? '', ['','draft','published']) ? ($_GET['status'] ?? '') : '';
$params = [];
$where  = '1=1';
$conditions = [];

if ($search) {
    $conditions[] = '(r.titre LIKE ? OR r.description LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
}
if ($status) {
    $conditions[] = 'r.status = ?';
    $params[] = $status;
}
$where = $conditions ? implode(' AND ', $conditions) : '1=1';

$stmt = db()->prepare("
  SELECT r.id, r.titre, r.status, r.created_at, c.nom AS cat_nom,
         u.prenom, u.nom AS auteur_nom,
         (SELECT COUNT(*) FROM votes v WHERE v.resource_id = r.id) AS nb_votes,
         (SELECT COUNT(*) FROM comments cm WHERE cm.resource_id = r.id) AS nb_comments
  FROM resources r
  JOIN categories c ON r.categorie_id = c.id
  JOIN users u ON r.auteur_id = u.id
  WHERE $where
  ORDER BY r.created_at DESC
");
$stmt->execute($params);
$resources = $stmt->fetchAll();

$page_title = 'Gestion des ressources';
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="admin-layout">

    <?php include __DIR__ . '/includes/admin-nav.php'; ?>

    <div>
      <?php render_flash(); ?>

      <div class="page-header mt-0" style="padding-top:0;">
        <p class="page-header__eyebrow">Administration</p>
        <h1>Ressources <span class="text-muted text-sm" style="font-weight:400;">(<?= count($resources) ?>)</span></h1>
      </div>

      <!-- Filtres -->
      <form method="get" action="" class="filters mb-3">
        <div class="filters__form">
          <div class="filters__group">
            <label class="filters__label" for="q">Rechercher</label>
            <input class="form-control" type="search" id="q" name="q"
                   placeholder="Titre ou description…" value="<?= e($search) ?>">
          </div>
          <div class="filters__group" style="max-width:180px;">
            <label class="filters__label" for="status">Statut</label>
            <select class="form-control" id="status" name="status">
              <option value="">Tous</option>
              <option value="published"<?= $status === 'published' ? ' selected' : '' ?>>Publiés</option>
              <option value="draft"<?= $status === 'draft' ? ' selected' : '' ?>>Brouillons</option>
            </select>
          </div>
          <div class="filters__actions">
            <button type="submit" class="btn btn--primary btn--sm">Filtrer</button>
            <?php if ($search || $status): ?>
              <a href="/admin/resources.php" class="btn btn--ghost btn--sm">Effacer</a>
            <?php endif; ?>
          </div>
        </div>
      </form>

      <?php if ($resources): ?>
        <form method="post" action="/admin/bulk-resources.php" id="bulk-form">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

          <div class="bulk-bar mb-2">
            <label class="bulk-bar__select">
              <input type="checkbox" id="select-all" title="Tout sélectionner">
              <span class="text-sm text-muted">Tout sélectionner</span>
            </label>
            <div class="flex gap-1">
              <select name="bulk_action" class="form-control" style="width:auto; padding:.35rem .75rem; font-size:.82rem;">
                <option value="">— Action groupée —</option>
                <option value="delete">Supprimer</option>
                <option value="publish">Publier</option>
                <option value="draft">Mettre en brouillon</option>
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
                  <th>Titre</th>
                  <th>Matière</th>
                  <th>Auteur</th>
                  <th>Statut</th>
                  <th>Votes</th>
                  <th>Comm.</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($resources as $r): ?>
                  <tr>
                    <td><input type="checkbox" name="ids[]" value="<?= $r['id'] ?>" class="row-check"></td>
                    <td>
                      <a href="/resource-view.php?id=<?= $r['id'] ?>" style="font-weight:600; color:var(--c-text); text-decoration:none;">
                        <?= e(excerpt($r['titre'], 60)) ?>
                      </a>
                    </td>
                    <td><span class="badge"><?= e($r['cat_nom']) ?></span></td>
                    <td class="text-muted text-sm"><?= e($r['prenom'] . ' ' . $r['auteur_nom']) ?></td>
                    <td>
                      <?php if ($r['status'] === 'draft'): ?>
                        <span class="status-badge status-badge--draft">Brouillon</span>
                      <?php else: ?>
                        <span class="status-badge status-badge--published">Publié</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-muted"><?= (int)$r['nb_votes'] ?></td>
                    <td class="text-muted"><?= (int)$r['nb_comments'] ?></td>
                    <td class="text-muted text-sm"><?= format_date($r['created_at']) ?></td>
                    <td>
                      <div class="td-actions">
                        <a href="/edit-resource.php?id=<?= $r['id'] ?>" class="btn btn--ghost btn--sm">Modifier</a>
                        <form method="post" action="/delete-resource.php"
                              onsubmit="return confirm('Supprimer cette ressource ?')">
                          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                          <input type="hidden" name="id" value="<?= $r['id'] ?>">
                          <input type="hidden" name="redirect" value="/admin/resources.php">
                          <button type="submit" class="btn btn--danger btn--sm">Supprimer</button>
                        </form>
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
          <p class="empty-state__title">Aucune ressource trouvée</p>
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
  if (!checked) { alert('Sélectionnez au moins une ressource.'); return false; }
  if (action === 'delete') return confirm('Supprimer ' + checked + ' ressource(s) ?');
  return true;
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
