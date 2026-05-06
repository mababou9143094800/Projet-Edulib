<<<<<<< HEAD
<?php
require_once __DIR__ . '/../backend/includes/db.php';
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/functions.php';

if (is_logged_in()) redirect('/frontend/dashboard.php');

$error   = '';
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';
    $old_email = $email;

    if (!$email || !$password) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $result = login_user($email, $password);
        if ($result === 'locked') {
            $secs  = rate_limit_remaining_seconds(get_client_ip());
            $mins  = ceil($secs / 60);
            $error = "Trop de tentatives échouées. Réessayez dans $mins minute(s).";
        } elseif ($result === false) {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
    if (!$error) {
        $redirect = $_POST['redirect'] ?? '/frontend/dashboard.php';
        // Sécurité : n'autoriser que les redirections internes
        if (!str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = '/frontend/dashboard.php';
        }
        flash_set('success', 'Bienvenue, ' . current_user()['prenom'] . ' !');
        redirect($redirect);
    }
}

$page_title = 'Connexion';
include __DIR__ . '/../backend/includes/header.php';
?>

<div class="auth-page">
  <div class="container">
    <div class="auth-card">
      <h1 class="auth-card__title">Connexion</h1>
      <p class="auth-card__subtitle">Accédez à vos ressources et à votre espace personnel.</p>

      <?php if ($error): ?>
        <div class="alert alert--error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" action="" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="redirect" value="<?= e($_GET['redirect'] ?? '/frontend/dashboard.php') ?>">

        <div class="form-stack">
          <div class="form-group">
            <label class="form-label" for="email">Adresse email</label>
            <input class="form-control" type="email" id="email" name="email"
                   value="<?= e($old_email) ?>"
                   autocomplete="email" required autofocus>
          </div>

          <div class="form-group">
            <label class="form-label" for="password">Mot de passe</label>
            <input class="form-control" type="password" id="password" name="password"
                   autocomplete="current-password" required>
          </div>

          <button type="submit" class="btn btn--primary btn--full">Se connecter</button>
        </div>
      </form>

      <div class="auth-card__footer">
        Pas encore de compte ? <a href="/frontend/register.php">S'inscrire</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../backend/includes/footer.php'; ?>
=======
﻿<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) redirect('/dashboard.php');

$error   = '';
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';
    $old_email = $email;

    if (!$email || !$password) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $result = login_user($email, $password);
        if ($result === 'locked') {
            $secs  = rate_limit_remaining_seconds(get_client_ip());
            $mins  = ceil($secs / 60);
            $error = "Trop de tentatives échouées. Réessayez dans $mins minute(s).";
        } elseif ($result === false) {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
    if (!$error) {
        $redirect = $_POST['redirect'] ?? '/dashboard.php';
        // Sécurité : n'autoriser que les redirections internes
        if (!str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = '/dashboard.php';
        }
        flash_set('success', 'Bienvenue, ' . current_user()['prenom'] . ' !');
        redirect($redirect);
    }
}

$page_title = 'Connexion';
include __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
  <div class="container">
    <div class="auth-card">
      <h1 class="auth-card__title">Connexion</h1>
      <p class="auth-card__subtitle">Accédez à vos ressources et à votre espace personnel.</p>

      <?php if ($error): ?>
        <div class="alert alert--error"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" action="" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="redirect" value="<?= e($_GET['redirect'] ?? '/dashboard.php') ?>">

        <div class="form-stack">
          <div class="form-group">
            <label class="form-label" for="email">Adresse email</label>
            <input class="form-control" type="email" id="email" name="email"
                   value="<?= e($old_email) ?>"
                   autocomplete="email" required autofocus>
          </div>

          <div class="form-group">
            <label class="form-label" for="password">Mot de passe</label>
            <input class="form-control" type="password" id="password" name="password"
                   autocomplete="current-password" required>
          </div>

          <button type="submit" class="btn btn--primary btn--full">Se connecter</button>
        </div>
      </form>

      <div class="auth-card__footer">
        Pas encore de compte ? <a href="/register.php">S'inscrire</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
>>>>>>> 53077f91002ccb0064ec702acb707b56956fdb1e
