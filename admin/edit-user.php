<?php
// Placeholder - Édition d'utilisateur
require_once __DIR__ . '/../backend/includes/db.php';
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/functions.php';

require_admin();

$page_title = 'Modifier un utilisateur';
include __DIR__ . '/../backend/includes/header.php';
?>

<div class="container">
  <div class="admin-layout">
    <?php include __DIR__ . '/includes/admin-nav.php'; ?>
    <div>
      <div class="page-header mt-0" style="padding-top:0;">
        <p class="page-header__eyebrow">Administration</p>
        <h1>Modifier un utilisateur</h1>
      </div>
      <p class="text-muted">Module d'édition d'utilisateur - À compléter</p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../backend/includes/footer.php'; ?>
