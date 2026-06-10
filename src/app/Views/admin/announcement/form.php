<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $isEdit ? 'Edit Announcement' : 'Create Announcement' ?> | Duargan Admin</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/admin.css">
    <script src="/js/admin.js" defer></script>
</head>
<body>
<?php
$currentAdminPage = 'announcement';
include __DIR__ . '/../../partials/admin_sidebar.php';
renderAdminSidebar($currentAdminPage);
?>
<div class="admin-page-wrapper">
    <main class="admin-container">
        <div class="form-header">
            <a href="/admin/announcement" class="back-btn"><span class="material-icons">arrow_back</span></a>
            <h1 class="admin-title"><?= $isEdit ? 'Edit Announcement' : 'Create Announcement' ?></h1>
        </div>
        <div class="form-card">
            <?php if (isset($_SESSION['form_errors'])): ?>
                <div class="error-message" style="margin-bottom: 1.5rem;">
                    <?php foreach ($_SESSION['form_errors'] as $err): ?>
                        <p><?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION['form_errors']); ?>
            <?php endif; ?>

            <?php
            $old = $_SESSION['old_input'] ?? [];
            $title = $old['title'] ?? ($announcement['title'] ?? '');
            $bgColor = $old['background_color'] ?? ($announcement['background_color'] ?? '#6750a4');
            $text = $old['text'] ?? ($announcement['text'] ?? '');
            $isActive = isset($old['is_active']) ? (bool)$old['is_active'] : (isset($announcement['is_active']) ? (bool)$announcement['is_active'] : false);
            unset($_SESSION['old_input']);
            ?>

            <form method="POST" action="/admin/announcement/update">
                <!-- CSRF Protection -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="material-form-group with-help">
                    <input type="text" name="title" id="title" class="material-form-input" value="<?= htmlspecialchars($title) ?>" placeholder=" " required>
                    <label class="material-form-label" for="title">Title *</label>
                </div>
                <p class="form-help">Short headline (e.g., "New track out now!")</p>

                <div class="form-section">
                    <h3>Announcement Text *</h3>
                    <textarea name="text" id="text" class="form-textarea" rows="4" placeholder="Write the announcement body here…" required><?= htmlspecialchars($text) ?></textarea>
                    <p class="form-help">Supports line breaks. Keep it concise.</p>
                </div>

                <div class="form-section">
                    <h3>Background Color *</h3>
                    <div class="md3-color-picker">
                        <div class="color-picker-header">
                            <span class="color-picker-label">Selected color</span>
                            <span class="color-picker-value" id="colorHexDisplay"><?= htmlspecialchars($bgColor) ?></span>
                        </div>
                        <div class="color-picker-controls">
                            <div class="color-picker-preview">
                                <div class="color-preview-circle" id="colorPreviewCircle" style="background-color: <?= htmlspecialchars($bgColor) ?>;"></div>
                            </div>
                            <div class="color-picker-input-wrapper">
                                <input type="color" id="colorPicker" class="md3-color-input" value="<?= htmlspecialchars($bgColor) ?>">
                                <label class="md3-color-button" for="colorPicker"><span class="material-icons">colorize</span> Choose Color</label>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="background_color" name="background_color" value="<?= htmlspecialchars($bgColor) ?>">
                    <p class="form-help" style="margin-top: 0.75rem;">Choose a color that ensures text readability.</p>
                </div>

                <div class="form-section">
                    <h3>Preview</h3>
                    <div id="announcementPreview" class="announcement-preview-banner" style="background-color: <?= htmlspecialchars($bgColor) ?>;">
                        <p class="announcement-preview-title" id="previewTitle"><?= $title ? htmlspecialchars($title) : 'Your title here' ?></p>
                        <p class="announcement-preview-text" id="previewText"><?= $text ? nl2br(htmlspecialchars($text)) : 'Your announcement text will appear here.' ?></p>
                    </div>
                </div>

                <div class="checkbox-item" style="margin-bottom: 2rem; max-width: 320px;">
                    <input type="checkbox" id="is_active" name="is_active" <?= $isActive ? 'checked' : '' ?>>
                    <label for="is_active">Active — show announcement on site</label>
                </div>

                <div class="form-actions">
                    <a href="/admin/announcement" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-submit">
                        <span class="material-icons"><?= $isEdit ? 'save' : 'add' ?></span>
                        <?= $isEdit ? 'Save Changes' : 'Create Announcement' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>