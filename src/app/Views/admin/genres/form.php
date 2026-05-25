<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $isEdit ? 'Edit Genre' : 'Add Genre' ?> | Duargan Admin</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
<?php
$currentAdminPage = 'genres';
include __DIR__ . '/../../partials/admin_sidebar.php';
renderAdminSidebar($currentAdminPage);
?>
<div class="admin-page-wrapper">
    <main class="admin-container">
        <div class="form-header">
            <a href="/admin/genres" class="back-btn"><span class="material-icons">arrow_back</span></a>
            <h1 class="admin-title"><?= $isEdit ? 'Edit Genre' : 'New Genre' ?></h1>
        </div>
        <div class="form-card">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="<?= $isEdit ? "/admin/genres/update/{$genre['id']}" : '/admin/genres/store' ?>">
                <!-- CSRF Protection -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="material-form-group with-help">
                    <input type="text" name="name" id="name" class="material-form-input" value="<?= $isEdit ? htmlspecialchars($genre['name']) : '' ?>" placeholder=" " required oninput="generateSlug(this.value)">
                    <label class="material-form-label" for="name">Genre Name *</label>
                </div>
                <p class="form-help">Enter the genre name (e.g., "Future Bass")</p>

                <div class="material-form-group with-help">
                    <input type="text" name="slug" id="slug" class="material-form-input" value="<?= $isEdit ? htmlspecialchars($genre['slug']) : '' ?>" placeholder=" " required>
                    <label class="material-form-label" for="slug">Slug *</label>
                </div>
                <p class="form-help">URL-friendly version (e.g., "future-bass")</p>

                <div class="form-actions">
                    <a href="/admin/genres" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-submit">
                        <span class="material-icons"><?= $isEdit ? 'save' : 'add' ?></span>
                        <?= $isEdit ? 'Update Genre' : 'Create Genre' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
<script>
function generateSlug(name) {
    let slugInput = document.getElementById('slug');
    if (slugInput.value === '' || slugInput.value === '<?= $isEdit ? $genre['slug'] : '' ?>') {
        let slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        slugInput.value = slug;
    }
}
</script>
</body>
</html>