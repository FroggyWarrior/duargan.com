<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit Song Type' : 'Add Song Type' ?> | Duargan Admin</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
<?php
$currentAdminPage = 'types';
include __DIR__ . '/../../partials/admin_sidebar.php';
renderAdminSidebar($currentAdminPage);
?>
<div class="admin-page-wrapper">
    <main class="admin-container">
        <div class="form-header">
            <a href="/admin/song-types" class="back-btn"><span class="material-icons">arrow_back</span></a>
            <h1 class="admin-title"><?= $isEdit ? 'Edit Song Type' : 'New Song Type' ?></h1>
        </div>
        <div class="form-card">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="<?= $isEdit ? "/admin/song-types/update/{$type['id']}" : '/admin/song-types/store' ?>">
                <!-- CSRF Protection -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="material-form-group with-help">
                    <input type="text" name="name" id="name" class="material-form-input" value="<?= $isEdit ? htmlspecialchars($type['name']) : '' ?>" placeholder=" " required oninput="generateSlug(this.value)">
                    <label class="material-form-label" for="name">Type Name *</label>
                </div>
                <p class="form-help">Enter the song type (e.g., "Official Release", "Remix")</p>

                <div class="material-form-group with-help">
                    <input type="text" name="slug" id="slug" class="material-form-input" value="<?= $isEdit ? htmlspecialchars($type['slug']) : '' ?>" placeholder=" " required>
                    <label class="material-form-label" for="slug">Slug *</label>
                </div>
                <p class="form-help">URL-friendly version (e.g., "official", "remix")</p>

                <div class="form-actions">
                    <a href="/admin/song-types" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-submit">
                        <span class="material-icons"><?= $isEdit ? 'save' : 'add' ?></span>
                        <?= $isEdit ? 'Update' : 'Create' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
<script>
function generateSlug(name) {
    let slugInput = document.getElementById('slug');
    if (slugInput.value === '' || slugInput.value === '<?= $isEdit ? $type['slug'] : '' ?>') {
        let slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        slugInput.value = slug;
    }
}
</script>
</body>
</html>