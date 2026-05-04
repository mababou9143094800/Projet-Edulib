<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Ressources';

// Filtres
$cat_id  = isset($_GET['categorie']) && ctype_digit($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$search  = trim($_GET['q'] ?? '');

// Pagination
$per_page   = 12;
$page       = max(1, (int)($_GET['page'] ?? 1));
$offset     = ($page - 1) * $per_page;

// Construction de la requête
$where  = ['(r.status = "published" OR r.auteur_id = ?)'];
$params = [current_user()['id'] ?? 0];
if ($cat_id) {
    $where[]  = 'r.categorie_id = ?';
    $params[] = $cat_id;
}
if ($search !== '') {
    $where[]  = '(r.titre LIKE ? OR r.description LIKE ?)';
    $like     = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
}
$where_sql = implode(' AND ', $where);

// Total
$count_stmt = db()->prepare("SELECT COUNT(*) FROM resources r WHERE $where_sql");
$count_stmt->execute($params);
$total       = (int)$count_stmt->fetchColumn();
$total_pages = max(1, (int)ceil($total / $per_page));
$page        = min($page, $total_pages);
$offset      = ($page - 1) * $per_page;

// Résultats
$stmt = db()->prepare("
  SELECT r.*, c.nom AS cat_nom, c.slug AS cat_slug,
         u.prenom, u.nom AS auteur_nom
  FROM resources r
  JOIN categories c ON r.categorie_id = c.id
  JOIN users u ON r.auteur_id = u.id
  WHERE $where_sql
  ORDER BY r.created_at DESC
  LIMIT $per_page OFFSET $offset
");
$stmt->execute($params);
$resources = $stmt->fetchAll();

$categories = get_categories();

include __DIR__ . '/includes/header.php';
?>

<div class="container section">

  <div class="page-header mt-0" style="padding-top:0; margin-top:0;">
    <h1>Ressources pédagogiques</h1>
    <p class="text-muted text-sm mt-1">
      <?= $total ?> ressource<?= $total > 1 ? 's' : '' ?> disponible<?= $total > 1 ? 's' : '' ?>
    </p>
    <?php if (is_logged_in()): ?>
      <div class="page-header__actions">
        <a href="/add-resource.php" class="btn btn--primary btn--sm">+ Déposer une ressource</a>
      </div>
    <?php endif; ?>
  </div>

  <!-- ── Filtres ── -->
  <div class="filters">
    <form method="get" action="" class="filters__form">
      <div class="filters__group">
        <label class="filters__label" for="q">Rechercher</label>
        <input class="form-control" type="search" id="q" name="q"
               placeholder="Titre ou description…"
               value="<?= e($search) ?>">
      </div>
      <div class="filters__group" style="max-width:220px;">
        <label class="filters__label" for="categorie">Matière</label>
        <select class="form-control" id="categorie" name="categorie">
          <option value="0">Toutes les matières</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>"<?= $cat_id === $c['id'] ? ' selected' : '' ?>>
              <?= e($c['nom']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="filters__actions">
        <button type="submit" class="btn btn--primary btn--sm">Filtrer</button>
        <?php if ($cat_id || $search): ?>
          <a href="/resources.php" class="btn btn--ghost btn--sm">Effacer</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- ── Résultats ── -->
  <?php if ($resources): ?>
    <div class="resource-grid">
      <?php foreach ($resources as $r): ?>
        <a href="/resource-view.php?id=<?= $r['id'] ?>" class="card-link">
          <div class="card-link__body">
            <div class="resource-card__category">
              <span class="badge"><?= e($r['cat_nom']) ?></span>
            </div>
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

    <!-- ── Pagination ── -->
    <?php if ($total_pages > 1): ?>
      <nav class="pagination" aria-label="Pagination">
        <?php
        $base = '/resources.php?' . http_build_query(array_filter(['q' => $search, 'categorie' => $cat_id ?: null]));
        ?>
        <a class="pagination__link<?= $page <= 1 ? ' pagination__link--disabled' : '' ?>"
           href="<?= $base ?>&page=<?= $page - 1 ?>" aria-label="Page précédente">&lsaquo;</a>

        <?php for ($p = max(1, $page - 2); $p <= min($total_pages, $page + 2); $p++): ?>
          <a class="pagination__link<?= $p === $page ? ' pagination__link--active' : '' ?>"
             href="<?= $base ?>&page=<?= $p ?>"
             <?= $p === $page ? 'aria-current="page"' : '' ?>><?= $p ?></a>
        <?php endfor; ?>

        <a class="pagination__link<?= $page >= $total_pages ? ' pagination__link--disabled' : '' ?>"
           href="<?= $base ?>&page=<?= $page + 1 ?>" aria-label="Page suivante">&rsaquo;</a>
      </nav>
    <?php endif; ?>

  <?php else: ?>
    <div class="empty-state">
      <div class="empty-state__icon">&#9634;</div>
      <p class="empty-state__title">Aucune ressource trouvée</p>
      <p class="empty-state__desc text-muted">
        <?= $search || $cat_id ? 'Modifiez vos filtres ou' : 'Soyez le premier à' ?>
        <a href="/add-resource.php">déposer une ressource</a>.
      </p>
    </div>
  <?php endif; ?>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
