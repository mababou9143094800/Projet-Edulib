<nav aria-label="Navigation admin">
  <p class="admin-nav__title">Administration</p>
  <ul class="admin-nav__list">
    <li>
      <a href="/backend/admin/index.php"
         class="admin-nav__link<?= basename($_SERVER['PHP_SELF']) === 'index.php' && strpos($_SERVER['PHP_SELF'], '/backend/admin/') !== false ? ' admin-nav__link--active' : '' ?>">
        Tableau de bord
      </a>
    </li>
    <li>
      <a href="/backend/admin/users.php"
         class="admin-nav__link<?= in_array(basename($_SERVER['PHP_SELF']), ['users.php','edit-user.php']) ? ' admin-nav__link--active' : '' ?>">
        Utilisateurs
      </a>
    </li>
    <li>
      <a href="/backend/admin/resources.php"
         class="admin-nav__link<?= basename($_SERVER['PHP_SELF']) === 'resources.php' && strpos($_SERVER['PHP_SELF'], '/backend/admin/') !== false ? ' admin-nav__link--active' : '' ?>">
        Ressources
      </a>
    </li>
  </ul>

  <hr class="divider" style="margin-block:.75rem;">

  <ul class="admin-nav__list">
    <li><a href="/dashboard.php" class="admin-nav__link">Mon espace</a></li>
    <li><a href="/logout.php" class="admin-nav__link" style="color:var(--c-text-3);">Déconnexion</a></li>
  </ul>
</nav>
