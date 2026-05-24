<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Song Types | Duargan Admin</title>
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
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="admin-header-bar">
            <h1 class="admin-title">Manage Song Types</h1>
        </div>

        <div class="form-card">
            <div class="genres-grid">
                <?php foreach ($types as $type): ?>
                <div class="genre-item <?= $type['is_active'] ? 'active' : 'inactive' ?>">
                    <div class="genre-info">
                        <h3 class="genre-name"><?= htmlspecialchars($type['name']) ?></h3>
                        <div class="genre-details">
                            <span class="genre-slug">Slug: <?= htmlspecialchars($type['slug']) ?></span>
                            <span class="genre-usage">Used in <?= $type['usage_count'] ?> song(s)</span>
                            <span class="genre-status">Status: <?= $type['is_active'] ? 'Active' : 'Inactive' ?></span>
                        </div>
                    </div>
                    <div class="genre-actions">
                        <a href="/admin/song-types/edit/<?= $type['id'] ?>" class="btn btn-edit"><span class="material-icons">edit</span></a>
                        <form action="/admin/song-types/toggle/<?= $type['id'] ?>" method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="btn btn-<?= $type['is_active'] ? 'warning' : 'success' ?>">
                                <span class="material-icons"><?= $type['is_active'] ? 'visibility_off' : 'visibility' ?></span>
                            </button>
                        </form>
                        <form action="/admin/song-types/delete/<?= $type['id'] ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="btn btn-delete"><span class="material-icons">delete</span></button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($types)): ?>
                <div class="no-items">
                    <span class="material-icons">label</span>
                    <h3>No song types found</h3>
                    <a href="/admin/song-types/create" class="btn btn-submit">Add First Song Type</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <a href="/admin/song-types/create" class="fab"><span class="material-icons">add</span></a>
    </main>
</div>
</body>
</html>