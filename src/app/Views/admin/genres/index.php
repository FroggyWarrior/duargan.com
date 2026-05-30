<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Manage Genres | Duargan Admin</title>
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
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="admin-header-bar">
            <h1 class="admin-title">Manage Genres</h1>
        </div>

        <div class="form-card">
            <div class="genres-grid">
                <?php foreach ($genres as $genre): ?>
                <div class="genre-item <?= $genre['is_active'] ? 'active' : 'inactive' ?>">
                    <div class="genre-info">
                        <h3 class="genre-name"><?= htmlspecialchars($genre['name']) ?></h3>
                        <div class="genre-details">
                            <span class="genre-slug">Slug: <?= htmlspecialchars($genre['slug']) ?></span>
                            <span class="genre-usage">Used in <?= $genre['usage_count'] ?> song(s)</span>
                            <span class="genre-status">Status: <?= $genre['is_active'] ? 'Active' : 'Inactive' ?></span>
                        </div>
                    </div>
                    <div class="genre-actions">
                        <a href="/admin/genres/edit/<?= $genre['id'] ?>" class="btn btn-edit"><span class="material-icons">edit</span></a>
                        <form action="/admin/genres/toggle/<?= $genre['id'] ?>" method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="btn btn-<?= $genre['is_active'] ? 'warning' : 'success' ?>">
                                <span class="material-icons"><?= $genre['is_active'] ? 'visibility_off' : 'visibility' ?></span>
                            </button>
                        </form>
                        <form action="/admin/genres/delete/<?= $genre['id'] ?>" method="POST" style="display:inline;" class="delete-form">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="btn btn-delete"><span class="material-icons">delete</span></button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($genres)): ?>
                <div class="no-items">
                    <span class="material-icons">category</span>
                    <h3>No genres found</h3>
                    <a href="/admin/genres/create" class="btn btn-submit">Add First Genre</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <a href="/admin/genres/create" class="fab"><span class="material-icons">add</span></a>
    </main>
</div>
</body>
</html>