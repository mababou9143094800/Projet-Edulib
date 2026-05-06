<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = db()->prepare('SELECT * FROM users WHERE id=? LIMIT 1');
$stmt->execute([$id]);
$target = $stmt->fetch();

if (!$target) {
    flash_set('error', 'Utilisateur introuvable.');
    redirect('/backend/admin/users.php');
}

$errors = [];
$old    = $target;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $old = [
        'nom'    => trim($_POST['nom']    ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'email'  => trim($_POST['email']  ?? ''),
        'role'   => $_POST['role'] === 'admin' ? 'admin' : 'user',
    ];
    $new_pwd = trim($_POST['new_password'] ?? '');

    if (!$old['nom'])    $errors['nom']    = 'Le nom est requis.';
    if (!$old['prenom']) $errors['prenom'] = 'Le prénom est requis.';
    if (!$old['email'] || !filter_var($old['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Email invalide.';
    if ($new_pwd && strlen($new_pwd) < 8)
        $errors['new_password'] = 'Au moins 8 caractères.';

    if (!$errors) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email=? AND id<>? LIMIT 1');
        $stmt->execute([$old['email'], $id]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Email déjà utilisé.';
        } else {
            if ($new_pwd) {
                $hash = password_hash($new_pwd, PASSWORD_BCRYPT);
                db()->prepare('UPDATE users SET nom=?, prenom=?, email=?, role=?, password=? WHERE id=?')
                    ->execute([$old['nom'], $old['prenom'], $old['email'], $old['role'], $hash, $id]);
            } else {
                db()->prepare('UPDATE users SET nom=?, prenom=?, email=?, role=? WHERE id=?')
                    ->execute([$old['nom'], $old['prenom'], $old['email'], $old['role'], $id]);
            }
            flash_set('success', 'Utilisateur mis à jour.');
            redirect('/backend/admin/users.php');
        }
    }
}

$page_title = 'Modifier ' . $target['prenom'] . ' ' . $target['nom'];
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="admin-layout">

    <?php include __DIR__ . '/includes/admin-nav.php'; ?>

    <div>
      <?php render_flash(); ?>

      <div class="breadcrumb mt-2">
        <a href="/backend/admin/users.php">Utilisateurs</a>
        <span class="breadcrumb__sep">›</span>
        <span>Modifier</span>
      </div>

      <h1 class="mb-3">Modifier l'utilisateur</h1>

      <div style="max-width:500px;">
        <?php if ($errors): ?>
          <div class="alert alert--error">Veuillez corriger les erreurs.</div>
        <?php endif; ?>

        <form method="post" action="" novalidate>
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
          <div class="form-stack">

            <div class="form-row form-row--2">
              <div class="form-group">
                <label class="form-label" for="prenom">Prénom</label>
                <input class="form-control" type="text" id="prenom" name="prenom"
                       value="<?= e($old['prenom']) ?>" required>
                <?php if (isset($errors['prenom'])): ?><p class="form-error"><?= e($errors['prenom']) ?></p><?php endif; ?>
              </div>
              <div class="form-group">
                <label class="form-label" for="nom">Nom</label>
                <input class="form-control" type="text" id="nom" name="nom"
                       value="<?= e($old['nom']) ?>" required>
                <?php if (isset($errors['nom'])): ?><p class="form-error"><?= e($errors['nom']) ?></p><?php endif; ?>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="email">Email</label>
              <input class="form-control" type="email" id="email" name="email"
                     value="<?= e($old['email']) ?>" required>
              <?php if (isset($errors['email'])): ?><p class="form-error"><?= e($errors['email']) ?></p><?php endif; ?>
            </div>

            <div class="form-group">
              <label class="form-label" for="role">Rôle</label>
              <select class="form-control" id="role" name="role">
                <option value="user"<?= $old['role'] === 'user' ? ' selected' : '' ?>>Utilisateur</option>
                <option value="admin"<?= $old['role'] === 'admin' ? ' selected' : '' ?>>Administrateur</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label" for="new_password">Nouveau mot de passe <span class="form-hint">(laisser vide = inchangé)</span></label>
              <input class="form-control" type="password" id="new_password" name="new_password"
                     autocomplete="new-password" minlength="8">
              <?php if (isset($errors['new_password'])): ?><p class="form-error"><?= e($errors['new_password']) ?></p><?php endif; ?>
            </div>

            <div class="flex gap-1 flex-wrap">
              <button type="submit" class="btn btn--primary">Enregistrer</button>
              <a href="/backend/admin/users.php" class="btn btn--ghost">Annuler</a>
            </div>

          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
