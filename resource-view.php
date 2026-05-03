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

// Brouillon : visible uniquement par l'auteur ou un admin
if ($resource['status'] === 'draft' && !$is_mine && !is_admin()) {
    http_response_code(403);
    $page_title = 'Accès refusé';
    include __DIR__ . '/includes/header.php';
    echo '<div class="container section"><div class="alert alert--error">Cette ressource n\'est pas encore publiée.</div><a href="/resources.php" class="btn btn--ghost mt-2">Retour aux ressources</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// ── ETag / Cache-Control ─────────────────────────────────────
$etag = '"' . md5($resource['id'] . $resource['updated_at']) . '"';
header('Cache-Control: private, must-revalidate');
header('ETag: ' . $etag);
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag) {
    http_response_code(304);
    exit;
}

// ── Votes ────────────────────────────────────────────────────
$vote_count = (int)db()->prepare('SELECT COUNT(*) FROM votes WHERE resource_id = ?')
    ->execute([$id]) ? db()->prepare('SELECT COUNT(*) FROM votes WHERE resource_id = ?') : 0;
$vote_stmt = db()->prepare('SELECT COUNT(*) FROM votes WHERE resource_id = ?');
$vote_stmt->execute([$id]);
$vote_count = (int)$vote_stmt->fetchColumn();

$user_voted = false;
if ($user) {
    $v = db()->prepare('SELECT 1 FROM votes WHERE resource_id = ? AND user_id = ?');
    $v->execute([$id, $user['id']]);
    $user_voted = (bool)$v->fetchColumn();
}

// ── Favoris ──────────────────────────────────────────────────
$user_favorited = false;
if ($user) {
    $f = db()->prepare('SELECT 1 FROM favorites WHERE resource_id = ? AND user_id = ?');
    $f->execute([$id, $user['id']]);
    $user_favorited = (bool)$f->fetchColumn();
}

// ── Commentaires ─────────────────────────────────────────────
$com_stmt = db()->prepare('
  SELECT c.*, u.prenom, u.nom AS auteur_nom, u.role AS auteur_role
  FROM comments c
  JOIN users u ON c.user_id = u.id
  WHERE c.resource_id = ?
  ORDER BY c.created_at ASC
');
$com_stmt->execute([$id]);
$all_comments = $com_stmt->fetchAll();

// Organise les commentaires en arbre (parent → enfants)
$top_comments = [];
$replies      = [];
foreach ($all_comments as $c) {
    if ($c['parent_id'] === null) {
        $top_comments[] = $c;
    } else {
        $replies[$c['parent_id']][] = $c;
    }
}

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
      <?php if ($resource['status'] === 'draft'): ?>
        <span class="status-badge status-badge--draft">Brouillon</span>
      <?php endif; ?>
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

    <!-- Actions : votes, favoris, édition -->
    <div class="flex gap-1 mt-2 flex-wrap items-center">

      <?php if ($resource['status'] === 'published'): ?>
        <!-- Vote -->
        <form method="post" action="/vote.php">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="resource_id" value="<?= $resource['id'] ?>">
          <?php if ($user): ?>
            <button type="submit" class="btn btn--sm <?= $user_voted ? 'btn--primary' : 'btn--outline' ?>" title="Voter pour cette ressource">
              ▲ <?= $vote_count ?> vote<?= $vote_count !== 1 ? 's' : '' ?>
            </button>
          <?php else: ?>
            <a href="/login.php" class="btn btn--sm btn--outline">▲ <?= $vote_count ?> vote<?= $vote_count !== 1 ? 's' : '' ?></a>
          <?php endif; ?>
        </form>

        <!-- Favori -->
        <?php if ($user): ?>
          <form method="post" action="/favorite.php">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="resource_id" value="<?= $resource['id'] ?>">
            <button type="submit" class="btn btn--sm <?= $user_favorited ? 'btn--primary' : 'btn--ghost' ?>">
              <?= $user_favorited ? '★ Dans mes favoris' : '☆ Ajouter aux favoris' ?>
            </button>
          </form>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($is_mine || is_admin()): ?>
        <a href="/edit-resource.php?id=<?= $resource['id'] ?>" class="btn btn--ghost btn--sm">Modifier</a>
        <form method="post" action="/delete-resource.php" style="display:inline;"
              onsubmit="return confirm('Supprimer cette ressource définitivement ?')">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="id" value="<?= $resource['id'] ?>">
          <button type="submit" class="btn btn--danger btn--sm">Supprimer</button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <!-- Contenu -->
  <div class="resource-body">
    <?= nl2p($resource['contenu']) ?>
  </div>

  <hr class="divider">

  <!-- ── Section commentaires ── -->
  <section id="comments" class="comments-section">
    <h2 class="comments-section__title">
      Commentaires
      <span class="text-muted text-sm" style="font-weight:400;">(<?= count($all_comments) ?>)</span>
    </h2>

    <?php if ($user && $resource['status'] === 'published'): ?>
      <!-- Formulaire nouveau commentaire -->
      <form method="post" action="/comment.php" class="comment-form mb-3">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="resource_id" value="<?= $resource['id'] ?>">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
          <label class="form-label sr-only" for="new-comment">Votre commentaire</label>
          <textarea class="form-control" id="new-comment" name="contenu" rows="3"
                    placeholder="Ajouter un commentaire…" maxlength="2000" required></textarea>
        </div>
        <div style="margin-top:.5rem;">
          <button type="submit" class="btn btn--primary btn--sm">Publier</button>
        </div>
      </form>
    <?php elseif (!$user): ?>
      <p class="text-muted text-sm mb-3"><a href="/login.php">Connectez-vous</a> pour laisser un commentaire.</p>
    <?php endif; ?>

    <?php if ($top_comments): ?>
      <div class="comments-list">
        <?php foreach ($top_comments as $c): ?>
          <div class="comment" id="comment-<?= $c['id'] ?>">
            <div class="comment__header">
              <strong class="comment__author"><?= e($c['prenom'] . ' ' . $c['auteur_nom']) ?></strong>
              <?php if ($c['auteur_role'] === 'admin'): ?>
                <span class="role-badge role-badge--admin" style="font-size:.65rem;">Admin</span>
              <?php endif; ?>
              <span class="comment__date text-muted"><?= format_date_full($c['created_at']) ?></span>
              <?php if ($user && ($c['user_id'] === $user['id'] || is_admin())): ?>
                <form method="post" action="/comment.php" style="margin-left:auto;">
                  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="resource_id" value="<?= $resource['id'] ?>">
                  <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                  <button type="submit" class="btn-link text-muted"
                          onclick="return confirm('Supprimer ce commentaire ?')">Supprimer</button>
                </form>
              <?php endif; ?>
            </div>
            <div class="comment__body"><?= nl2br(e($c['contenu'])) ?></div>

            <!-- Réponses -->
            <?php if (!empty($replies[$c['id']])): ?>
              <div class="comment-replies">
                <?php foreach ($replies[$c['id']] as $r): ?>
                  <div class="comment comment--reply" id="comment-<?= $r['id'] ?>">
                    <div class="comment__header">
                      <strong class="comment__author"><?= e($r['prenom'] . ' ' . $r['auteur_nom']) ?></strong>
                      <?php if ($r['auteur_role'] === 'admin'): ?>
                        <span class="role-badge role-badge--admin" style="font-size:.65rem;">Admin</span>
                      <?php endif; ?>
                      <span class="comment__date text-muted"><?= format_date_full($r['created_at']) ?></span>
                      <?php if ($user && ($r['user_id'] === $user['id'] || is_admin())): ?>
                        <form method="post" action="/comment.php" style="margin-left:auto;">
                          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="resource_id" value="<?= $resource['id'] ?>">
                          <input type="hidden" name="comment_id" value="<?= $r['id'] ?>">
                          <button type="submit" class="btn-link text-muted"
                                  onclick="return confirm('Supprimer ce commentaire ?')">Supprimer</button>
                        </form>
                      <?php endif; ?>
                    </div>
                    <div class="comment__body"><?= nl2br(e($r['contenu'])) ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- Formulaire de réponse -->
            <?php if ($user && $resource['status'] === 'published'): ?>
              <details class="reply-toggle">
                <summary class="btn-link text-sm">Répondre</summary>
                <form method="post" action="/comment.php" class="comment-form mt-1">
                  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                  <input type="hidden" name="resource_id" value="<?= $resource['id'] ?>">
                  <input type="hidden" name="parent_id" value="<?= $c['id'] ?>">
                  <input type="hidden" name="action" value="add">
                  <textarea class="form-control" name="contenu" rows="2"
                            placeholder="Votre réponse…" maxlength="2000" required></textarea>
                  <button type="submit" class="btn btn--primary btn--sm mt-1">Répondre</button>
                </form>
              </details>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-muted text-sm">Aucun commentaire pour l'instant. Soyez le premier !</p>
    <?php endif; ?>
  </section>

  <hr class="divider">
  <a href="/resources.php" class="btn btn--ghost btn--sm">&larr; Retour aux ressources</a>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
