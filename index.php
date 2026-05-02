<?php
// ============================================================
// EduLib — Page d'accueil
// ============================================================
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Accueil';

// Statistiques
$stats = db()->query('
  SELECT
    (SELECT COUNT(*) FROM resources) AS nb_resources,
    (SELECT COUNT(*) FROM users)     AS nb_users,
    (SELECT COUNT(*) FROM categories) AS nb_categories
')->fetch();

// Dernières ressources
$last_resources = db()->query('
  SELECT r.*, c.nom AS cat_nom, u.prenom, u.nom AS auteur_nom
  FROM resources r
  JOIN categories c ON r.categorie_id = c.id
  JOIN users u ON r.auteur_id = u.id
  ORDER BY r.created_at DESC
  LIMIT 6
')->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- ── Hero ── -->
<section class="hero">
  <div class="container">
    <p class="hero__eyebrow">Bibliothèque étudiante EFREI</p>
    <h1 class="hero__title">Partagez et trouvez<br>des ressources pédagogiques</h1>
    <p class="hero__description">
      EduLib centralise vos fiches de cours, résumés et exercices par matière.
      Un espace sobre, rapide et sans superflu.
    </p>
    <div class="hero__actions">
      <a href="/resources.php" class="btn btn--primary btn--lg">Voir les ressources</a>
      <?php if (!is_logged_in()): ?>
        <a href="/register.php" class="btn btn--outline btn--lg">S'inscrire gratuitement</a>
      <?php else: ?>
        <a href="/add-resource.php" class="btn btn--outline btn--lg">Déposer une fiche</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ── Statistiques ── -->
<section class="section section--sm">
  <div class="container">
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-card__number"><?= (int)$stats['nb_resources'] ?></div>
        <div class="stat-card__label">Ressources</div>
      </div>
      <div class="stat-card">
        <div class="stat-card__number"><?= (int)$stats['nb_users'] ?></div>
        <div class="stat-card__label">Étudiants</div>
      </div>
      <div class="stat-card">
        <div class="stat-card__number"><?= (int)$stats['nb_categories'] ?></div>
        <div class="stat-card__label">Matières</div>
      </div>
      <div class="stat-card">
        <div class="stat-card__number">&lt;&nbsp;200</div>
        <div class="stat-card__label">Ko par page</div>
      </div>
    </div>
  </div>
</section>

<!-- ── Dernières ressources ── -->
<?php if ($last_resources): ?>
<section class="section section--sm">
  <div class="container">
    <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
      <h2>Dernières ressources ajoutées</h2>
      <a href="/resources.php" class="btn btn--ghost btn--sm">Toutes les ressources &rarr;</a>
    </div>

    <div class="resource-grid">
      <?php foreach ($last_resources as $r): ?>
        <a href="/resource-view.php?id=<?= $r['id'] ?>" class="card-link">
          <div class="card-link__body">
            <div class="resource-card__category">
              <span class="badge"><?= e($r['cat_nom']) ?></span>
            </div>
            <h3 class="resource-card__title"><?= e($r['titre']) ?></h3>
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
  </div>
</section>
<?php endif; ?>

<!-- ── Engagement écologique ── -->
<section class="section section--sm">
  <div class="container container--sm">
    <div class="eco-block">
      <p class="eco-block__title">Engagement écologique</p>
      <ul class="eco-block__list">
        <li>HTML&nbsp;/&nbsp;CSS pur — 0&nbsp;framework JS</li>
        <li>&lt;&nbsp;200&nbsp;Ko par page</li>
        <li>0 tracker — 0 cookie tiers</li>
        <li>Police système — 0 Google&nbsp;Fonts</li>
        <li>Données minimales en base</li>
        <li>Hébergement sobre</li>
      </ul>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
