<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Panel | Duargan</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
<?php
$currentAdminPage = 'songs';
include __DIR__ . '/../partials/admin_sidebar.php';
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
            <h1 class="admin-title">Music Management</h1>
        </div>

        <div class="songs-grid">
            <?php foreach ($songs as $song): 
                $cover = $song['cover_image_url'];
                if (!empty($cover) && !preg_match('/^https?:\/\//', $cover)) {
                    $cover = '/' . ltrim($cover, '/');
                }
            ?>
            <div class="song-box">
                <div class="song-box-header">
                    <img src="<?= htmlspecialchars($cover) ?>" alt="<?= htmlspecialchars($song['title']) ?>" class="song-cover" onerror="this.style.display='none'">
                    <div class="song-info">
                        <h3 class="song-title"><?= htmlspecialchars($song['title']) ?></h3>
                        <div class="song-type"><?= htmlspecialchars($song['type_name'] ?? '') ?></div>
                    </div>
                </div>
                <div class="song-details">
                    <div class="song-detail-item">
                        <span class="song-detail-label">Release Date:</span><br>
                        <?= htmlspecialchars($song['release_date']) ?>
                    </div>
                    <div class="song-detail-item">
                        <span class="song-detail-label">Genres:</span><br>
                        <?= htmlspecialchars($song['genre_names'] ?? 'No genres') ?>
                    </div>
                </div>
                <div class="song-actions">
                    <a href="/admin/songs/edit/<?= $song['id'] ?>" class="btn btn-edit"><span class="material-icons">edit</span></a>
                    <form action="/admin/songs/delete/<?= $song['id'] ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" class="btn btn-delete">
                            <span class="material-icons">delete</span>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($songs)): ?>
            <div class="no-items">
                <span class="material-icons">library_music</span>
                <h3>No songs found</h3>
                <p>Get started by adding your first song</p>
                <a href="/admin/songs/create" class="btn btn-submit">Add First Song</a>
            </div>
            <?php endif; ?>
        </div>
        <a href="/admin/songs/create" class="fab"><span class="material-icons">add</span></a>
    </main>
</div>
</body>
</html>