<?php
// Placeholder - Gestion des ressources admin
require_once __DIR__ . '/../backend/includes/db.php';
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/functions.php';

require_admin();

$page_title = 'Gestion des ressources';
include __DIR__ . '/../backend/includes/header.php';
?>

<div class="container">
  <div class="admin-layout">
    <?php include __DIR__ . '/includes/admin-nav.php'; ?>
    <div>
      <div class="page-header mt-0" style="padding-top:0;">
        <p class="page-header__eyebrow">Administration</p>
        <h1>Ressources</h1>
      </div>
      <p class="text-muted">Module de gestion des ressources - À compléter</p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../backend/includes/footer.php'; ?>
