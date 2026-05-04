<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$user   = current_user();
$errors = [];
$old    = $user;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $action = $_POST['action'] ?? 'profile';

    if ($action === 'profile') {
        $old = [
            'nom'    => trim($_POST['nom']    ?? ''),
            'prenom' => trim($_POST['prenom'] ?? ''),
            'email'  => trim($_POST['email']  ?? ''),
        ];
        if (!$old['nom'])    $errors['nom']    = 'Le nom est requis.';
        if (!$old['prenom']) $errors['prenom'] = 'Le prénom est requis.';
        if (!$old['email'] || !filter_var($old['email'], FILTER_VALIDATE_EMAIL))
            $errors['email'] = 'Email invalide.';

        if (!$errors) {
            // Unicité email (sauf pour l'utilisateur lui-même)
            $stmt = db()->prepare('SELECT id FROM users WHERE email=? AND id<>? LIMIT 1');
            $stmt->execute([$old['email'], $user['id']]);
            if ($stmt->fetch()) {
                $errors['email'] = 'Cette adresse est déjà utilisée.';
            } else {
                db()->prepare('UPDATE users SET nom=?, prenom=?, email=? WHERE id=?')
                    ->execute([$old['nom'], $old['prenom'], $old['email'], $user['id']]);
                // Rafraîchir la session
                $_SESSION['user'] = array_merge($_SESSION['user'], $old);
                flash_set('success', 'Profil mis à jour.');
                redirect('/profile.php');
            }
        }
    }

    if ($action === 'password') {
        $current  = $_POST['current_password']  ?? '';
        $new_pwd  = $_POST['new_password']       ?? '';
        $new_pwd2 = $_POST['new_password2']      ?? '';

        // Vérifier le mot de passe actuel
        $stmt = db()->prepare('SELECT password FROM users WHERE id=? LIMIT 1');
        $stmt->execute([$user['id']]);
        $row = $stmt->fetch();
        if (!password_verify($current, $row['password'])) {
            $errors['current_password'] = 'Mot de passe actuel incorrect.';
        }
        if (strlen($new_pwd) < 8) $errors['new_password'] = 'Au moins 8 caractères.';
        if ($new_pwd !== $new_pwd2) $errors['new_password2'] = 'Les mots de passe ne correspondent pas.';

        if (!$errors) {
            $hash = password_hash($new_pwd, PASSWORD_BCRYPT);
            db()->prepare('UPDATE users SET password=? WHERE id=?')->execute([$hash, $user['id']]);
            flash_set('success', 'Mot de passe mis à jour.');
            redirect('/profile.php');
        }
    }

    if ($action === 'delete') {
        csrf_verify();
        $pwd = $_POST['delete_password'] ?? '';
        $stmt = db()->prepare('SELECT password FROM users WHERE id=? LIMIT 1');
        $stmt->execute([$user['id']]);
        $row = $stmt->fetch();
        if (!password_verify($pwd, $row['password'])) {
            $errors['delete_password'] = 'Mot de passe incorrect.';
        } else {
            db()->prepare('DELETE FROM users WHERE id=?')->execute([$user['id']]);
            logout_user();
            session_start();
            flash_set('success', 'Votre compte a été supprimé.');
            redirect('/index.php');
        }
    }
}

$page_title = 'Mon profil';
include __DIR__ . '/includes/header.php';
?>

<div class="container section">

  <?php render_flash(); ?>

  <div class="breadcrumb">
    <a href="/dashboard.php">Mon espace</a>
    <span class="breadcrumb__sep">›</span>
    <span>Mon profil</span>
  </div>

  <div style="max-width:560px;">
    <h1 class="mb-4">Mon profil</h1>

    <!-- Modifier les informations -->
    <div class="card mb-4">
      <div class="card__header"><h2 style="font-size:1rem;">Informations personnelles</h2></div>
      <div class="card__body">
        <form method="post" action="" novalidate>
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="profile">
          <div class="form-stack">
            <div class="form-row form-row--2">
              <div class="form-group">
                <label class="form-label" for="prenom">Prénom</label>
                <input class="form-control" type="text" id="prenom" name="prenom"
                       value="<?= e($old['prenom'] ?? $user['prenom']) ?>" required>
                <?php if (isset($errors['prenom'])): ?><p class="form-error"><?= e($errors['prenom']) ?></p><?php endif; ?>
              </div>
              <div class="form-group">
                <label class="form-label" for="nom">Nom</label>
                <input class="form-control" type="text" id="nom" name="nom"
                       value="<?= e($old['nom'] ?? $user['nom']) ?>" required>
                <?php if (isset($errors['nom'])): ?><p class="form-error"><?= e($errors['nom']) ?></p><?php endif; ?>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label" for="email">Email</label>
              <input class="form-control" type="email" id="email" name="email"
                     value="<?= e($old['email'] ?? $user['email']) ?>" required>
              <?php if (isset($errors['email'])): ?><p class="form-error"><?= e($errors['email']) ?></p><?php endif; ?>
            </div>
            <button type="submit" class="btn btn--primary btn--sm">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Changer de mot de passe -->
    <div class="card mb-4">
      <div class="card__header"><h2 style="font-size:1rem;">Changer le mot de passe</h2></div>
      <div class="card__body">
        <form method="post" action="" novalidate>
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="password">
          <div class="form-stack">
            <div class="form-group">
              <label class="form-label" for="current_password">Mot de passe actuel</label>
              <input class="form-control" type="password" id="current_password" name="current_password" required>
              <?php if (isset($errors['current_password'])): ?><p class="form-error"><?= e($errors['current_password']) ?></p><?php endif; ?>
            </div>
            <div class="form-group">
              <label class="form-label" for="new_password">Nouveau mot de passe</label>
              <input class="form-control" type="password" id="new_password" name="new_password" required minlength="8">
              <?php if (isset($errors['new_password'])): ?><p class="form-error"><?= e($errors['new_password']) ?></p><?php endif; ?>
            </div>
            <div class="form-group">
              <label class="form-label" for="new_password2">Confirmer le nouveau mot de passe</label>
              <input class="form-control" type="password" id="new_password2" name="new_password2" required>
              <?php if (isset($errors['new_password2'])): ?><p class="form-error"><?= e($errors['new_password2']) ?></p><?php endif; ?>
            </div>
            <button type="submit" class="btn btn--outline btn--sm">Changer le mot de passe</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Supprimer le compte -->
    <div class="card" style="border-color:#f0b8b0;">
      <div class="card__header" style="background:var(--c-error-bg);"><h2 style="font-size:1rem; color:var(--c-error);">Zone dangereuse</h2></div>
      <div class="card__body">
        <p class="text-sm text-muted mb-2">La suppression de votre compte est définitive. Toutes vos ressources seront supprimées.</p>
        <form method="post" action="" novalidate
              onsubmit="return confirm('Êtes-vous sûr(e) de vouloir supprimer votre compte ? Cette action est irréversible.')">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <input type="hidden" name="action" value="delete">
          <div class="form-stack">
            <div class="form-group">
              <label class="form-label" for="delete_password">Confirmez avec votre mot de passe</label>
              <input class="form-control" type="password" id="delete_password" name="delete_password" required>
              <?php if (isset($errors['delete_password'])): ?><p class="form-error"><?= e($errors['delete_password']) ?></p><?php endif; ?>
            </div>
            <button type="submit" class="btn btn--danger btn--sm">Supprimer mon compte</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
