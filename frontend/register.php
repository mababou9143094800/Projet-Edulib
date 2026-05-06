<<<<<<< HEAD
<?php
require_once __DIR__ . '/../backend/includes/db.php';
require_once __DIR__ . '/../backend/includes/auth.php';
require_once __DIR__ . '/../backend/includes/functions.php';

if (is_logged_in()) redirect('/frontend/dashboard.php');

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $old = [
        'nom'    => trim($_POST['nom']    ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'email'  => trim($_POST['email']  ?? ''),
    ];
    $password  = $_POST['password']  ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (!$old['nom'])    $errors['nom']    = 'Le nom est requis.';
    if (!$old['prenom']) $errors['prenom'] = 'Le prénom est requis.';
    if (!$old['email'] || !filter_var($old['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Adresse email invalide.';
    if (strlen($password) < 8)
        $errors['password'] = 'Le mot de passe doit faire au moins 8 caractères.';
    if ($password !== $password2)
        $errors['password2'] = 'Les mots de passe ne correspondent pas.';

    if (!$errors) {
        // Vérifier unicité email
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Cette adresse email est déjà utilisée.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = db()->prepare(
                'INSERT INTO users (nom, prenom, email, password) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$old['nom'], $old['prenom'], $old['email'], $hash]);

            // Connexion automatique
            login_user($old['email'], $password);
            flash_set('success', 'Bienvenue ' . $old['prenom'] . ' ! Votre compte est créé.');
            redirect('/frontend/dashboard.php');
        }
    }
}

$page_title = "S'inscrire";
include __DIR__ . '/../backend/includes/header.php';
?>

<div class="auth-page">
  <div class="container">
    <div class="auth-card">
      <h1 class="auth-card__title">Créer un compte</h1>
      <p class="auth-card__subtitle">Rejoignez la communauté EduLib pour partager et accéder aux ressources.</p>

      <?php if ($errors): ?>
        <div class="alert alert--error">Veuillez corriger les erreurs ci-dessous.</div>
      <?php endif; ?>

      <form method="post" action="" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-stack">
          <div class="form-row form-row--2">
            <div class="form-group">
              <label class="form-label" for="prenom">Prénom <abbr title="requis">*</abbr></label>
              <input class="form-control<?= isset($errors['prenom']) ? ' is-error' : '' ?>"
                     type="text" id="prenom" name="prenom"
                     value="<?= e($old['prenom'] ?? '') ?>"
                     autocomplete="given-name" required>
              <?php if (isset($errors['prenom'])): ?><p class="form-error"><?= e($errors['prenom']) ?></p><?php endif; ?>
            </div>
            <div class="form-group">
              <label class="form-label" for="nom">Nom <abbr title="requis">*</abbr></label>
              <input class="form-control<?= isset($errors['nom']) ? ' is-error' : '' ?>"
                     type="text" id="nom" name="nom"
                     value="<?= e($old['nom'] ?? '') ?>"
                     autocomplete="family-name" required>
              <?php if (isset($errors['nom'])): ?><p class="form-error"><?= e($errors['nom']) ?></p><?php endif; ?>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="email">Adresse email <abbr title="requis">*</abbr></label>
            <input class="form-control<?= isset($errors['email']) ? ' is-error' : '' ?>"
                   type="email" id="email" name="email"
                   value="<?= e($old['email'] ?? '') ?>"
                   autocomplete="email" required>
            <?php if (isset($errors['email'])): ?><p class="form-error"><?= e($errors['email']) ?></p><?php endif; ?>
          </div>

          <div class="form-group">
            <label class="form-label" for="password">Mot de passe <abbr title="requis">*</abbr></label>
            <input class="form-control<?= isset($errors['password']) ? ' is-error' : '' ?>"
                   type="password" id="password" name="password"
                   autocomplete="new-password" required minlength="8">
            <p class="form-hint">8 caractères minimum.</p>
            <?php if (isset($errors['password'])): ?><p class="form-error"><?= e($errors['password']) ?></p><?php endif; ?>
          </div>

          <div class="form-group">
            <label class="form-label" for="password2">Confirmer le mot de passe <abbr title="requis">*</abbr></label>
            <input class="form-control<?= isset($errors['password2']) ? ' is-error' : '' ?>"
                   type="password" id="password2" name="password2"
                   autocomplete="new-password" required>
            <?php if (isset($errors['password2'])): ?><p class="form-error"><?= e($errors['password2']) ?></p><?php endif; ?>
          </div>

          <button type="submit" class="btn btn--primary btn--full">Créer mon compte</button>
        </div>
      </form>

      <div class="auth-card__footer">
        Déjà un compte ? <a href="/frontend/login.php">Se connecter</a>
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

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $old = [
        'nom'    => trim($_POST['nom']    ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'email'  => trim($_POST['email']  ?? ''),
    ];
    $password  = $_POST['password']  ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (!$old['nom'])    $errors['nom']    = 'Le nom est requis.';
    if (!$old['prenom']) $errors['prenom'] = 'Le prénom est requis.';
    if (!$old['email'] || !filter_var($old['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Adresse email invalide.';
    if (strlen($password) < 8)
        $errors['password'] = 'Le mot de passe doit faire au moins 8 caractères.';
    if ($password !== $password2)
        $errors['password2'] = 'Les mots de passe ne correspondent pas.';

    if (!$errors) {
        // Vérifier unicité email
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Cette adresse email est déjà utilisée.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = db()->prepare(
                'INSERT INTO users (nom, prenom, email, password) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$old['nom'], $old['prenom'], $old['email'], $hash]);

            // Connexion automatique
            login_user($old['email'], $password);
            flash_set('success', 'Bienvenue ' . $old['prenom'] . ' ! Votre compte est créé.');
            redirect('/dashboard.php');
        }
    }
}

$page_title = "S'inscrire";
include __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
  <div class="container">
    <div class="auth-card">
      <h1 class="auth-card__title">Créer un compte</h1>
      <p class="auth-card__subtitle">Rejoignez la communauté EduLib pour partager et accéder aux ressources.</p>

      <?php if ($errors): ?>
        <div class="alert alert--error">Veuillez corriger les erreurs ci-dessous.</div>
      <?php endif; ?>

      <form method="post" action="" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-stack">
          <div class="form-row form-row--2">
            <div class="form-group">
              <label class="form-label" for="prenom">Prénom <abbr title="requis">*</abbr></label>
              <input class="form-control<?= isset($errors['prenom']) ? ' is-error' : '' ?>"
                     type="text" id="prenom" name="prenom"
                     value="<?= e($old['prenom'] ?? '') ?>"
                     autocomplete="given-name" required>
              <?php if (isset($errors['prenom'])): ?><p class="form-error"><?= e($errors['prenom']) ?></p><?php endif; ?>
            </div>
            <div class="form-group">
              <label class="form-label" for="nom">Nom <abbr title="requis">*</abbr></label>
              <input class="form-control<?= isset($errors['nom']) ? ' is-error' : '' ?>"
                     type="text" id="nom" name="nom"
                     value="<?= e($old['nom'] ?? '') ?>"
                     autocomplete="family-name" required>
              <?php if (isset($errors['nom'])): ?><p class="form-error"><?= e($errors['nom']) ?></p><?php endif; ?>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="email">Adresse email <abbr title="requis">*</abbr></label>
            <input class="form-control<?= isset($errors['email']) ? ' is-error' : '' ?>"
                   type="email" id="email" name="email"
                   value="<?= e($old['email'] ?? '') ?>"
                   autocomplete="email" required>
            <?php if (isset($errors['email'])): ?><p class="form-error"><?= e($errors['email']) ?></p><?php endif; ?>
          </div>

          <div class="form-group">
            <label class="form-label" for="password">Mot de passe <abbr title="requis">*</abbr></label>
            <input class="form-control<?= isset($errors['password']) ? ' is-error' : '' ?>"
                   type="password" id="password" name="password"
                   autocomplete="new-password" required minlength="8">
            <p class="form-hint">8 caractères minimum.</p>
            <?php if (isset($errors['password'])): ?><p class="form-error"><?= e($errors['password']) ?></p><?php endif; ?>
          </div>

          <div class="form-group">
            <label class="form-label" for="password2">Confirmer le mot de passe <abbr title="requis">*</abbr></label>
            <input class="form-control<?= isset($errors['password2']) ? ' is-error' : '' ?>"
                   type="password" id="password2" name="password2"
                   autocomplete="new-password" required>
            <?php if (isset($errors['password2'])): ?><p class="form-error"><?= e($errors['password2']) ?></p><?php endif; ?>
          </div>

          <button type="submit" class="btn btn--primary btn--full">Créer mon compte</button>
        </div>
      </form>

      <div class="auth-card__footer">
        Déjà un compte ? <a href="/login.php">Se connecter</a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
>>>>>>> 53077f91002ccb0064ec702acb707b56956fdb1e
