<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcement | Duargan Admin</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
<?php
$currentAdminPage = 'announcement';
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
            <h1 class="admin-title">Site Announcement</h1>
        </div>

        <?php if ($isConfigured && $announcement): ?>
            <div class="announcement-management-card">
                <div class="announcement-preview-label">
                    <span class="material-icons">visibility</span>
                    Live preview
                </div>
                <div class="announcement-preview-banner" style="background-color: <?= htmlspecialchars($announcement['background_color']) ?>;">
                    <p class="announcement-preview-title"><?= htmlspecialchars($announcement['title']) ?></p>
                    <p class="announcement-preview-text"><?= nl2br(htmlspecialchars($announcement['text'])) ?></p>
                </div>

                <div class="announcement-meta-row">
                    <div class="announcement-meta-item">
                        <span class="material-icons">palette</span>
                        <span>Background</span>
                        <span class="announcement-color-dot" style="background-color: <?= htmlspecialchars($announcement['background_color']) ?>;"></span>
                        <code><?= htmlspecialchars($announcement['background_color']) ?></code>
                    </div>
                    <div class="announcement-meta-item">
                        <span class="material-icons">schedule</span>
                        <span>Last updated: <?= date('M j, Y — H:i', strtotime($announcement['updated_at'] ?? 'now')) ?></span>
                    </div>
                    <div class="announcement-meta-item">
                        <span class="material-icons"><?= $announcement['is_active'] ? 'toggle_on' : 'toggle_off' ?></span>
                        <span class="<?= $announcement['is_active'] ? 'status-active' : 'status-inactive' ?>">
                            <?= $announcement['is_active'] ? 'Active — visible on site' : 'Inactive — hidden from site' ?>
                        </span>
                    </div>
                </div>

                <div class="announcement-actions">
                    <a href="/admin/announcement/edit" class="btn btn-edit" style="padding: 0.6rem 1.25rem; gap: 0.4rem;">
                        <span class="material-icons">edit</span> Edit
                    </a>
                    <form action="/admin/announcement/toggle" method="POST" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <?php if ($announcement['is_active']): ?>
                            <button type="submit" class="btn btn-warning" style="padding: 0.6rem 1.25rem; gap: 0.4rem;">
                                <span class="material-icons">visibility_off</span> Deactivate
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-success" style="padding: 0.6rem 1.25rem; gap: 0.4rem;">
                                <span class="material-icons">visibility</span> Activate
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="form-card">
                <div class="no-items" style="grid-column: unset;">
                    <span class="material-icons">campaign</span>
                    <h3>No announcement configured</h3>
                    <p>Create a site-wide announcement banner to inform your visitors.</p>
                    <a href="/admin/announcement/edit" class="btn btn-submit" style="display: inline-flex; margin: 0 auto; padding: 0.5rem 1rem;">
                        <span class="material-icons">add</span> Create Announcement
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>