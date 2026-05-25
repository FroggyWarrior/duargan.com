<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Manage Social Media | Duargan Admin</title>
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
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="admin-header-bar">
            <h1 class="admin-title">Social Media</h1>
        </div>

        <div class="form-card">
            <div class="genres-grid">
                <?php foreach ($platforms as $platform): ?>
                <div class="genre-item <?= $platform['is_active'] ? 'active' : 'inactive' ?>">
                    <div class="platform-info-wrapper">
                        <div class="platform-icon-preview">
                            <?= $platform['icon_svg'] ?>
                        </div>
                        <div class="genre-info">
                            <h3 class="genre-name"><?= htmlspecialchars($platform['name']) ?></h3>
                            <div class="genre-details">
                                <span class="genre-slug">Slug: <?= htmlspecialchars($platform['slug']) ?></span>
                                <span class="platform-url">URL: <?= $platform['base_url'] ? htmlspecialchars($platform['base_url']) : '—' ?></span>
                                <span class="platform-order">Order: <?= $platform['display_order'] ?></span>
                                <span class="genre-status">Status: <?= $platform['is_active'] ? 'Active' : 'Inactive' ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="genre-actions">
                        <a href="/admin/social-media/edit/<?= $platform['id'] ?>" class="btn btn-edit"><span class="material-icons">edit</span></a>
                        <form action="/admin/social-media/toggle/<?= $platform['id'] ?>" method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="btn btn-<?= $platform['is_active'] ? 'warning' : 'success' ?>">
                                <span class="material-icons"><?= $platform['is_active'] ? 'visibility_off' : 'visibility' ?></span>
                            </button>
                        </form>
                        <form action="/admin/social-media/delete/<?= $platform['id'] ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="btn btn-delete"><span class="material-icons">delete</span></button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($platforms)): ?>
                <div class="no-items">
                    <span class="material-icons">share</span>
                    <h3>No social media platforms found</h3>
                    <a href="/admin/social-media/create" class="btn btn-submit">Add First Platform</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <a href="/admin/social-media/create" class="fab"><span class="material-icons">add</span></a>
    </main>
</div>
</body>
</html>