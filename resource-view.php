<?php
// ============================================================
// EduLib — Vue d'une ressource
// ============================================================
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = db()->prepare('
  SELECT r.*, c.nom AS cat_nom, c.slug AS cat_slug,
         u.prenom, u.nom AS auteur_nom, u.id AS auteur_id
  FROM resources r
  JOIN categories c ON r.categorie_id = c.id
  JOIN users u ON r.auteur_id = u.id
  WHERE r.id = ?
');
$stmt->execute([$id]);
$resource = $stmt->fetch();

if (!$resource) {
    http_response_code(404);
    $page_title = 'Ressource introuvable';
    include __DIR__ . '/includes/header.php';
    echo '<div class="container section"><div class="alert alert--error">Cette ressource n\'existe pas ou a été supprimée.</div><a href="/resources.php" class="btn btn--ghost mt-2">Retour aux ressources</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$user    = current_user();
$is_mine = $user && $user['id'] === $resource['auteur_id'];
$page_title = $resource['titre'];

include __DIR__ . '/includes/header.php';
?>

<div class="container section">

  <!-- Breadcrumb -->
  <nav class="breadcrumb" aria-label="Fil d'Ariane">
    <a href="/index.php">Accueil</a>
    <span class="breadcrumb__sep" aria-hidden="true">›</span>
    <a href="/resources.php">Ressources</a>
    <span class="breadcrumb__sep" aria-hidden="true">›</span>
    <a href="/resources.php?categorie=<?= $resource['categorie_id'] ?>"><?= e($resource['cat_nom']) ?></a>
    <span class="breadcrumb__sep" aria-hidden="true">›</span>
    <span><?= e($resource['titre']) ?></span>
  </nav>

  <?php render_flash(); ?>

  <!-- En-tête ressource -->
  <div class="resource-header">
    <div class="resource-header__meta">
      <span class="badge"><?= e($resource['cat_nom']) ?></span>
      <span class="text-muted text-sm">Publié le <?= format_date_full($resource['created_at']) ?></span>
      <?php if ($resource['updated_at'] !== $resource['created_at']): ?>
        <span class="text-muted text-sm">· Modifié le <?= format_date_full($resource['updated_at']) ?></span>
      <?php endif; ?>
    </div>

    <h1 style="margin-bottom:.75rem;"><?= e($resource['titre']) ?></h1>

    <p style="color:var(--c-text-2); font-size:1.05rem; max-width:72ch; line-height:1.7;">
      <?= e($resource['description']) ?>
    </p>

    <p style="margin-top:.75rem; font-size:.875rem; color:var(--c-text-3);">
      Par <strong><?= e($resource['prenom'] . ' ' . $resource['auteur_nom']) ?></strong>
    </p>

    <?php if ($is_mine || is_admin()): ?>
      <div class="flex gap-1 mt-2 flex-wrap">
        <a href="/edit-resource.php?id=<?= $resource['id'] ?>" class="btn btn--ghost btn--sm">Modifier</a>
        <form method="post" action="/delete-resource.php" style="display:inline;"
              onsubmit="return confirm('Supprimer cette ressource définitivement ?')">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="id" value="<?= $resource['id'] ?>">
          <button type="submit" class="btn btn--danger btn--sm">Supprimer</button>
        </form>
      </div>
    <?php endif; ?>
  </div>

  <!-- Contenu -->
  <div class="resource-body">
    <?= nl2p($resource['contenu']) ?>
  </div>

  <hr class="divider">

  <a href="/resources.php" class="btn btn--ghost btn--sm">&larr; Retour aux ressources</a>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
