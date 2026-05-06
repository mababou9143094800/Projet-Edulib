<<<<<<<< HEAD:backend/includes/header.php
<?php
ob_start();
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'");

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
session_start_safe();

$page_title = isset($page_title) ? e($page_title) . ' — ' . SITE_NAME : SITE_NAME;
$user = current_user();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="EduLib — Bibliothèque de ressources pédagogiques pour étudiants EFREI">
  <meta name="color-scheme" content="light dark">
  <title><?= $page_title ?></title>
  <link rel="stylesheet" href="/css/style.css">

  <script>
    const t = localStorage.getItem('theme');
    if (t === 'dark' || (!t && matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  </script>
</head>
<body>

<a href="#main" class="skip-link">Aller au contenu principal</a>

<header class="site-header">
  <div class="container site-header__inner">

    <a href="/index.php" class="site-logo" aria-label="EduLib — Accueil">
      <span class="site-logo__icon" aria-hidden="true">&#9634;</span>
      <span class="site-logo__name">Edu<strong>Lib</strong></span>
    </a>

    <nav class="site-nav" aria-label="Navigation principale">
      <ul class="site-nav__list">
        <li><a href="/index.php" class="site-nav__link<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? ' site-nav__link--active' : '' ?>">Accueil</a></li>
        <li><a href="/frontend/resources.php" class="site-nav__link<?= basename($_SERVER['PHP_SELF']) === 'resources.php' ? ' site-nav__link--active' : '' ?>">Ressources</a></li>
        <?php if ($user): ?>
          <?php if (is_admin()): ?>
            <li><a href="/admin/index.php" class="site-nav__link<?= strpos($_SERVER['PHP_SELF'], '/admin/') !== false && strpos($_SERVER['PHP_SELF'], '/backend/admin/') === false ? ' site-nav__link--active' : '' ?>">Admin</a></li>
          <?php endif; ?>
          <li class="site-nav__sep" aria-hidden="true"></li>
          <li><a href="/frontend/dashboard.php" class="site-nav__link<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? ' site-nav__link--active' : '' ?>"><?= e($user['prenom']) ?></a></li>
          <li><a href="/backend/logout.php" class="site-nav__link site-nav__link--muted">Déconnexion</a></li>
        <?php else: ?>
          <li class="site-nav__sep" aria-hidden="true"></li>
          <li><a href="/frontend/login.php" class="site-nav__link<?= basename($_SERVER['PHP_SELF']) === 'login.php' ? ' site-nav__link--active' : '' ?>">Connexion</a></li>
          <li><a href="/frontend/register.php" class="btn btn--sm btn--primary">S'inscrire</a></li>
        <?php endif; ?>

        <li>
          <button id="theme-toggle" class="btn btn--ghost btn--sm"
                  aria-label="Changer le thème" onclick="toggleTheme()">
            🌙
          </button>
        </li>
      </ul>
    </nav>

  </div>
</header>

<main id="main">
========
﻿<?php
ob_start();
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'");

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
session_start_safe();

$page_title = isset($page_title) ? e($page_title) . ' — ' . SITE_NAME : SITE_NAME;
$user = current_user();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="EduLib — Bibliothèque de ressources pédagogiques pour étudiants EFREI
  <meta name="color-scheme" content="light dark">
  <title><?= $page_title ?></title>
  <link rel="stylesheet" href="/css/style.css">

  <script>
    const t = localStorage.getItem('theme');
    if (t === 'dark' || (!t && matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  </script>
</head>
<body>

<a href="#main" class="skip-link">Aller au contenu principal</a>

<header class="site-header">
  <div class="container site-header__inner">

    <a href="/index.php" class="site-logo" aria-label="EduLib — Accueil">
      <span class="site-logo__icon" aria-hidden="true">&#9634;</span>
      <span class="site-logo__name">Edu<strong>Lib</strong></span>
    </a>

    <nav class="site-nav" aria-label="Navigation principale">
      <ul class="site-nav__list">
        <li><a href="/index.php" class="site-nav__link<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? ' site-nav__link--active' : '' ?>">Accueil</a></li>
        <li><a href="/resources.php" class="site-nav__link<?= basename($_SERVER['PHP_SELF']) === 'resources.php' ? ' site-nav__link--active' : '' ?>">Ressources</a></li>
        <?php if ($user): ?>
          <?php if (is_admin()): ?>
            <li><a href="/admin/index.php" class="site-nav__link<?= strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? ' site-nav__link--active' : '' ?>">Admin</a></li>
          <?php endif; ?>
          <li class="site-nav__sep" aria-hidden="true"></li>
          <li><a href="/dashboard.php" class="site-nav__link<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? ' site-nav__link--active' : '' ?>"><?= e($user['prenom']) ?></a></li>
          <li><a href="/logout.php" class="site-nav__link site-nav__link--muted">Déconnexion</a></li>
        <?php else: ?>
          <li class="site-nav__sep" aria-hidden="true"></li>
          <li><a href="/login.php" class="site-nav__link<?= basename($_SERVER['PHP_SELF']) === 'login.php' ? ' site-nav__link--active' : '' ?>">Connexion</a></li>
          <li><a href="/register.php" class="btn btn--sm btn--primary">S'inscrire</a></li>
        <?php endif; ?>

        <li>
      <button id="theme-toggle" class="btn btn--ghost btn--sm"
              aria-label="Changer le thème" onclick="toggleTheme()">
        🌙
      </button>
    </li>
      </ul>
    </nav>

  </div>
</header>

<main id="main">
>>>>>>>> 53077f91002ccb0064ec702acb707b56956fdb1e:frontend/includes/header.php
