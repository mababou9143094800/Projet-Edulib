<?php
// ============================================================
// EduLib — Sitemap XML dynamique
// ============================================================
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';

$base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
      . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000');

header('Content-Type: application/xml; charset=UTF-8');
header('Cache-Control: public, max-age=3600');

$resources = db()->query("
  SELECT id, updated_at FROM resources WHERE status = 'published' ORDER BY updated_at DESC
")->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

  <url>
    <loc><?= $base ?>/index.php</loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>

  <url>
    <loc><?= $base ?>/resources.php</loc>
    <changefreq>hourly</changefreq>
    <priority>0.9</priority>
  </url>

  <url>
    <loc><?= $base ?>/register.php</loc>
    <changefreq>monthly</changefreq>
    <priority>0.5</priority>
  </url>

  <?php foreach ($resources as $r): ?>
  <url>
    <loc><?= $base ?>/resource-view.php?id=<?= (int)$r['id'] ?></loc>
    <lastmod><?= date('Y-m-d', strtotime($r['updated_at'])) ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
  </url>
  <?php endforeach; ?>

</urlset>
