<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $isEdit ? 'Edit Social Media' : 'Add Social Media' ?> | Duargan Admin</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
<?php
$currentAdminPage = 'social_media';
include __DIR__ . '/../../partials/admin_sidebar.php';
renderAdminSidebar($currentAdminPage);
?>
<div class="admin-page-wrapper">
    <main class="admin-container">
        <div class="form-header">
            <a href="/admin/social-media" class="back-btn"><span class="material-icons">arrow_back</span></a>
            <h1 class="admin-title"><?= $isEdit ? 'Edit Social Media' : 'New Social Media' ?></h1>
        </div>
        <div class="form-card">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="warning-box">
                <h4><span class="material-icons">warning</span> SVG Icon Tips</h4>
                <p>Paste the complete SVG code. Use <code>fill="currentColor"</code> for theme compatibility.</p>
            </div>

            <form method="POST" action="<?= $isEdit ? "/admin/social-media/update/{$platform['id']}" : '/admin/social-media/store' ?>">
                <!-- CSRF Protection -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="material-form-group with-help">
                    <input type="text" name="name" id="name" class="material-form-input" value="<?= $isEdit ? htmlspecialchars($platform['name']) : '' ?>" placeholder=" " required oninput="AdminUtils.generateSlug(this.value, 'slug')">
                    <label class="material-form-label" for="name">Platform Name *</label>
                </div>
                <p class="form-help">e.g., YouTube, Instagram, Discord</p>

                <div class="material-form-group with-help">
                    <input type="text" name="slug" id="slug" class="material-form-input" value="<?= $isEdit ? htmlspecialchars($platform['slug']) : '' ?>" placeholder=" " required>
                    <label class="material-form-label" for="slug">Slug *</label>
                </div>
                <p class="form-help">URL-friendly version (e.g., "youtube", "instagram")</p>

                <div class="material-form-group with-help">
                    <input type="url" name="base_url" id="base_url" class="material-form-input" value="<?= $isEdit ? htmlspecialchars($platform['base_url']) : '' ?>" placeholder=" ">
                    <label class="material-form-label" for="base_url">Profile URL *</label>
                </div>
                <p class="form-help">Full URL to your profile (e.g., https://youtube.com/duargan)</p>

                <div class="material-form-group with-help">
                    <input type="number" name="display_order" id="display_order" class="material-form-input" value="<?= $isEdit ? $platform['display_order'] : '0' ?>" placeholder=" " required>
                    <label class="material-form-label" for="display_order">Display Order</label>
                </div>
                <p class="form-help">Lower numbers appear first.</p>

                <div class="form-section">
                    <h3>SVG Icon *</h3>
                    <textarea name="icon_svg" id="icon_svg" class="form-textarea" rows="8" placeholder='<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">...' required oninput="AdminUtils.updateSvgPreview('icon_svg', 'svgPreview')"><?= $isEdit ? htmlspecialchars($platform['icon_svg']) : '' ?></textarea>
                    <div class="svg-preview" id="svgPreview">
                        <?= $isEdit ? $platform['icon_svg'] : '<span class="material-icons">image</span>' ?>
                    </div>
                    <p class="form-help">Preview of your icon. Make sure it uses <code>fill="currentColor"</code>.</p>
                </div>

                <div class="form-actions">
                    <a href="/admin/social-media" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-submit">
                        <span class="material-icons"><?= $isEdit ? 'save' : 'add' ?></span>
                        <?= $isEdit ? 'Update' : 'Create' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>