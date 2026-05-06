<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $old = [
        'titre'       => trim($_POST['titre']       ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'contenu'     => trim($_POST['contenu']      ?? ''),
        'categorie_id'=> (int)($_POST['categorie_id'] ?? 0),
        'status'      => in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'published',
    ];

    if (!$old['titre'])        $errors['titre']        = 'Le titre est requis.';
    if (mb_strlen($old['titre']) > 255) $errors['titre'] = 'Titre trop long (255 caractères max).';
    if (!$old['description'])  $errors['description']  = 'La description est requise.';
    if (!$old['contenu'])      $errors['contenu']       = 'Le contenu est requis.';
    if (!$old['categorie_id']) $errors['categorie_id'] = 'Veuillez choisir une matière.';

    if (!$errors) {
        $stmt = db()->prepare(
            'INSERT INTO resources (titre, description, contenu, status, categorie_id, auteur_id)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $old['titre'], $old['description'], $old['contenu'],
            $old['status'], $old['categorie_id'], current_user()['id'],
        ]);
        $new_id = db()->lastInsertId();

        if (!empty($_FILES['images']['name'][0])) {
            $order = 0;
            foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $file = [
                    'tmp_name' => $tmp,
                    'size'     => $_FILES['images']['size'][$i],
                    'name'     => $_FILES['images']['name'][$i],
                ];
                $filename = save_image_as_webp($file);
                if ($filename) {
                    db()->prepare('INSERT INTO resource_images (resource_id, filename, sort_order) VALUES (?, ?, ?)')
                        ->execute([$new_id, $filename, $order++]);
                }
            }
        }

        $msg = $old['status'] === 'draft' ? 'Brouillon enregistré.' : 'Votre ressource a été publiée avec succès.';
        flash_set('success', $msg);
        redirect('/frontend/resource-view.php?id=' . $new_id);
    }
}

$categories = get_categories();
$page_title  = 'Déposer une ressource';
include __DIR__ . '/includes/header.php';
?>

<div class="container section">

  <div class="breadcrumb">
    <a href="/frontend/dashboard.php">Mon espace</a>
    <span class="breadcrumb__sep">›</span>
    <span>Déposer une ressource</span>
  </div>

  <div style="max-width:700px;">
    <h1 class="mb-2">Déposer une ressource</h1>

    <?php if ($errors): ?>
      <div class="alert alert--error">Veuillez corriger les erreurs ci-dessous.</div>
    <?php endif; ?>

    <form method="post" action="" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

      <div class="form-stack">

        <div class="form-group">
          <label class="form-label" for="titre">Titre <abbr title="requis">*</abbr></label>
          <input class="form-control" type="text" id="titre" name="titre"
                 value="<?= e($old['titre'] ?? '') ?>"
                 maxlength="255" required autofocus>
          <?php if (isset($errors['titre'])): ?><p class="form-error"><?= e($errors['titre']) ?></p><?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="categorie_id">Matière <abbr title="requis">*</abbr></label>
          <select class="form-control" id="categorie_id" name="categorie_id" required>
            <option value="0">— Choisir une matière —</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>"<?= ($old['categorie_id'] ?? 0) == $c['id'] ? ' selected' : '' ?>>
                <?= e($c['nom']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (isset($errors['categorie_id'])): ?><p class="form-error"><?= e($errors['categorie_id']) ?></p><?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="description">Description courte <abbr title="requis">*</abbr></label>
          <textarea class="form-control" id="description" name="description"
                    rows="3" required><?= e($old['description'] ?? '') ?></textarea>
          <p class="form-hint">Un résumé en 1–3 phrases visible dans la liste des ressources.</p>
          <?php if (isset($errors['description'])): ?><p class="form-error"><?= e($errors['description']) ?></p><?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="contenu">Contenu <abbr title="requis">*</abbr></label>
          <textarea class="form-control" id="contenu" name="contenu"
                    rows="14" style="min-height:240px;" required><?= e($old['contenu'] ?? '') ?></textarea>
          <p class="form-hint">Le contenu complet de votre fiche. Sautez une ligne entre les paragraphes.</p>
          <?php if (isset($errors['contenu'])): ?><p class="form-error"><?= e($errors['contenu']) ?></p><?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="images">Images <span class="text-muted text-sm">(facultatif)</span></label>
          <input class="form-control" type="file" id="images" name="images[]"
                 accept="image/*" multiple>
          <p class="form-hint">Formats acceptés : JPEG, PNG, GIF, WebP — 8 Mo max par image. Les images seront converties en WebP.</p>
        </div>

        <div class="form-group">
          <label class="form-label">Visibilité</label>
          <div class="radio-group">
            <label class="radio-label">
              <input type="radio" name="status" value="published"<?= ($old['status'] ?? 'published') === 'published' ? ' checked' : '' ?>>
              Publier immédiatement
            </label>
            <label class="radio-label">
              <input type="radio" name="status" value="draft"<?= ($old['status'] ?? '') === 'draft' ? ' checked' : '' ?>>
              Enregistrer comme brouillon
            </label>
          </div>
        </div>

        <div class="flex gap-1 flex-wrap">
          <button type="submit" class="btn btn--primary">Enregistrer</button>
          <a href="/frontend/dashboard.php" class="btn btn--ghost">Annuler</a>
        </div>

      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
