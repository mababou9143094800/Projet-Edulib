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
    redirect('/frontend/dashboard.php');
}

$user = current_user();
if ($resource['auteur_id'] !== $user['id'] && !is_admin()) {
    http_response_code(403);
    flash_set('error', 'Vous ne pouvez pas modifier cette ressource.');
    redirect('/frontend/dashboard.php');
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

        if (!empty($_FILES['images']['name'][0])) {
            $order_stmt = db()->prepare('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM resource_images WHERE resource_id = ?');
            $order_stmt->execute([$id]);
            $next_order = (int)$order_stmt->fetchColumn();

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
                        ->execute([$id, $filename, $next_order++]);
                }
            }
        }

        flash_set('success', 'Ressource mise à jour avec succès.');
        redirect('/frontend/resource-view.php?id=' . $id);
    }
}

$img_stmt = db()->prepare('SELECT * FROM resource_images WHERE resource_id = ? ORDER BY sort_order ASC');
$img_stmt->execute([$id]);
$images = $img_stmt->fetchAll();

$categories = get_categories();
$page_title  = 'Modifier : ' . $resource['titre'];
include __DIR__ . '/includes/header.php';
?>

<div class="container section">

  <div class="breadcrumb">
    <a href="/frontend/dashboard.php">Mon espace</a>
    <span class="breadcrumb__sep">›</span>
    <a href="/frontend/resource-view.php?id=<?= $id ?>">Ressource</a>
    <span class="breadcrumb__sep">›</span>
    <span>Modifier</span>
  </div>

  <div style="max-width:700px;">
    <h1 class="mb-2">Modifier la ressource</h1>

    <?php if ($errors): ?>
      <div class="alert alert--error">Veuillez corriger les erreurs ci-dessous.</div>
    <?php endif; ?>

    <form method="post" action="" enctype="multipart/form-data" novalidate>
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

        <?php if ($images): ?>
          <div class="form-group">
            <label class="form-label">Images actuelles</label>
            <div class="image-gallery image-gallery--edit">
              <?php foreach ($images as $img): ?>
                <div class="image-gallery__item">
                  <img src="/uploads/resources/<?= e($img['filename']) ?>"
                       alt="Image de la ressource" loading="lazy">
                  <form method="post" action="/backend/delete-image.php" class="image-gallery__delete">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
                    <input type="hidden" name="resource_id" value="<?= $id ?>">
                    <button type="submit" class="image-gallery__del-btn"
                            onclick="return confirm('Supprimer cette image ?')" title="Supprimer">×</button>
                  </form>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label" for="images">Ajouter des images <span class="text-muted text-sm">(facultatif)</span></label>
          <input class="form-control" type="file" id="images" name="images[]"
                 accept="image/*" multiple>
          <p class="form-hint">Formats acceptés : JPEG, PNG, GIF, WebP — 8 Mo max par image.</p>
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
          <a href="/frontend/resource-view.php?id=<?= $id ?>" class="btn btn--ghost">Annuler</a>
        </div>

      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
