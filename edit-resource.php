<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = db()->prepare('SELECT * FROM resources WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$resource = $stmt->fetch();

if (!$resource) {
    http_response_code(404);
    flash_set('error', 'Ressource introuvable.');
    redirect('/dashboard.php');
}

$user = current_user();
if ($resource['auteur_id'] !== $user['id'] && !is_admin()) {
    http_response_code(403);
    flash_set('error', 'Vous ne pouvez pas modifier cette ressource.');
    redirect('/dashboard.php');
}

$errors = [];
$old    = $resource;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $old = [
        'titre'        => trim($_POST['titre']        ?? ''),
        'description'  => trim($_POST['description']  ?? ''),
        'contenu'      => trim($_POST['contenu']       ?? ''),
        'categorie_id' => (int)($_POST['categorie_id'] ?? 0),
        'status'       => in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'published',
    ];

    if (!$old['titre'])        $errors['titre']        = 'Le titre est requis.';
    if (mb_strlen($old['titre']) > 255) $errors['titre'] = 'Titre trop long.';
    if (!$old['description'])  $errors['description']  = 'La description est requise.';
    if (!$old['contenu'])      $errors['contenu']       = 'Le contenu est requis.';
    if (!$old['categorie_id']) $errors['categorie_id'] = 'Veuillez choisir une matière.';

    if (!$errors) {
        $stmt = db()->prepare(
            'UPDATE resources SET titre=?, description=?, contenu=?, categorie_id=?, status=? WHERE id=?'
        );
        $stmt->execute([
            $old['titre'], $old['description'], $old['contenu'],
            $old['categorie_id'], $old['status'], $id,
        ]);
        flash_set('success', 'Ressource mise à jour avec succès.');
        redirect('/resource-view.php?id=' . $id);
    }
}

$categories = get_categories();
$page_title  = 'Modifier : ' . $resource['titre'];
include __DIR__ . '/includes/header.php';
?>

<div class="container section">

  <div class="breadcrumb">
    <a href="/dashboard.php">Mon espace</a>
    <span class="breadcrumb__sep">›</span>
    <a href="/resource-view.php?id=<?= $id ?>">Ressource</a>
    <span class="breadcrumb__sep">›</span>
    <span>Modifier</span>
  </div>

  <div style="max-width:700px;">
    <h1 class="mb-2">Modifier la ressource</h1>

    <?php if ($errors): ?>
      <div class="alert alert--error">Veuillez corriger les erreurs ci-dessous.</div>
    <?php endif; ?>

    <form method="post" action="" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

      <div class="form-stack">

        <div class="form-group">
          <label class="form-label" for="titre">Titre <abbr title="requis">*</abbr></label>
          <input class="form-control" type="text" id="titre" name="titre"
                 value="<?= e($old['titre']) ?>" maxlength="255" required>
          <?php if (isset($errors['titre'])): ?><p class="form-error"><?= e($errors['titre']) ?></p><?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="categorie_id">Matière <abbr title="requis">*</abbr></label>
          <select class="form-control" id="categorie_id" name="categorie_id" required>
            <option value="0">— Choisir une matière —</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>"<?= $old['categorie_id'] == $c['id'] ? ' selected' : '' ?>>
                <?= e($c['nom']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['categorie_id'])): ?><p class="form-error"><?= e($errors['categorie_id']) ?></p><?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="description">Description courte <abbr title="requis">*</abbr></label>
          <textarea class="form-control" id="description" name="description" rows="3" required><?= e($old['description']) ?></textarea>
          <?php if (isset($errors['description'])): ?><p class="form-error"><?= e($errors['description']) ?></p><?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="contenu">Contenu <abbr title="requis">*</abbr></label>
          <textarea class="form-control" id="contenu" name="contenu" rows="14" style="min-height:240px;" required><?= e($old['contenu']) ?></textarea>
          <?php if (isset($errors['contenu'])): ?><p class="form-error"><?= e($errors['contenu']) ?></p><?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label">Visibilité</label>
          <div class="radio-group">
            <label class="radio-label">
              <input type="radio" name="status" value="published"<?= $old['status'] === 'published' ? ' checked' : '' ?>>
              Publié
            </label>
            <label class="radio-label">
              <input type="radio" name="status" value="draft"<?= $old['status'] === 'draft' ? ' checked' : '' ?>>
              Brouillon
            </label>
          </div>
        </div>

        <div class="flex gap-1 flex-wrap">
          <button type="submit" class="btn btn--primary">Enregistrer les modifications</button>
          <a href="/resource-view.php?id=<?= $id ?>" class="btn btn--ghost">Annuler</a>
        </div>

      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
