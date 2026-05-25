<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $isEdit ? 'Edit Song' : 'Add Song' ?> | Duargan Admin</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/admin.css">
    <style>
        .platform-url-group { display: none; margin-top: 0.5rem; width: 100%; }
        .platform-url-input { width: 100%; padding: 0.75rem; border: 2px solid var(--outline); border-radius: 8px; background-color: var(--surface); color: var(--on-surface); font-size: 0.9rem; box-sizing: border-box; }
        .platform-form-item { margin-bottom: 0.75rem; padding: 1rem; border-radius: 8px; background-color: var(--surface); border: 1px solid var(--outline); }
        .platform-form-header { display: flex; align-items: center; gap: 0.75rem; cursor: pointer; }
        .platform-form-icon svg { width: 20px; height: 20px; }
        .platform-form-checkbox { margin-left: auto; }
    </style>
</head>
<body>
<?php
$currentAdminPage = 'songs';
include __DIR__ . '/../partials/admin_sidebar.php';
renderAdminSidebar($currentAdminPage);
?>
<div class="admin-page-wrapper">
    <main class="admin-container">
        <div class="form-header">
            <a href="/admin/panel" class="back-btn" aria-label="Go back to dashboard"><span class="material-icons" aria-hidden="true">arrow_back</span></a>
            <h1 class="admin-title"><?= $isEdit ? 'Edit Song' : 'Add New Song' ?></h1>
        </div>
        <div class="form-card">
            <?php if (isset($_SESSION['form_errors'])): ?>
                <div class="error-message">
                    <?php foreach ($_SESSION['form_errors'] as $err): ?>
                        <p><?= htmlspecialchars($err) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION['form_errors']); ?>
            <?php endif; ?>

            <form method="POST" action="<?= $isEdit ? "/admin/songs/update/{$song['id']}" : '/admin/songs/store' ?>" enctype="multipart/form-data">
                <!-- CSRF Protection -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <!-- Title -->
                <div class="material-form-group with-help">
                    <input type="text" id="song_title" name="title" class="material-form-input" value="<?= htmlspecialchars($isEdit ? $song['title'] : ($_SESSION['old_input']['title'] ?? '')) ?>" placeholder=" " required aria-required="true" aria-describedby="title_help">
                    <label class="material-form-label">Song Title *</label>
                </div>
                <p id="title_help" class="form-help">Enter the title of the song</p>

                <!-- Release Date -->
                <div class="material-form-group with-help">
                    <input type="text" id="release_date" name="release_date" class="material-form-input" value="<?= htmlspecialchars($isEdit ? $song['release_date'] : ($_SESSION['old_input']['release_date'] ?? '')) ?>" placeholder=" " required aria-required="true" aria-describedby="date_help">
                    <label class="material-form-label">Release Date *</label>
                </div>
                <p id="date_help" class="form-help">Format: e.g., June 15, 2023</p>

                <!-- Cover Image Upload -->
                <div class="file-upload-group">
                    <button type="button" class="file-upload-btn" onclick="document.getElementById('cover_image').click()" aria-label="Upload cover image"><span class="material-icons" aria-hidden="true">upload</span> Upload Cover Image</button>
                    <input type="file" id="cover_image" name="cover_image" class="file-upload-input" accept="image/*" onchange="updateFileName(this)">
                    <div id="file_name" class="file-name" aria-live="polite">No file selected</div>
                    <p class="form-help">Upload a cover image file (JPG, PNG, etc.)</p>
                </div>

                <!-- Cover Image URL -->
                <div class="material-form-group with-help">
                    <input type="text" id="cover_image_url" name="cover_image_url" class="material-form-input" value="<?= htmlspecialchars($isEdit ? $song['cover_image_url'] : ($_SESSION['old_input']['cover_image_url'] ?? '')) ?>" placeholder=" " onchange="updatePreviewFromUrl(this.value)" aria-describedby="url_help">
                    <label class="material-form-label">Or use Image URL</label>
                </div>
                <p id="url_help" class="form-help"><?= $isEdit ? 'Leave empty to keep existing cover, or enter a new URL.' : 'Enter a direct URL to the cover image (required if not uploading a file).' ?></p>

                <?php if ($isEdit && $song['cover_image_url']): ?>
                <div style="margin-top: 1rem;">
                    <p><strong>Current Cover Image:</strong></p>
                    <img src="/<?= ltrim($song['cover_image_url'], '/') ?>" alt="Current cover image for <?= htmlspecialchars($song['title']) ?>" class="cover-preview" style="max-width: 200px;">
                </div>
                <?php else: ?>
                <img id="cover_preview" class="cover-preview" src="" alt="New cover image preview" style="display: none; max-width: 200px;">
                <?php endif; ?>

                <!-- Genres -->
                <div class="form-section">
                    <h3>Genres *</h3>
                    <div class="checkbox-group">
                        <?php foreach ($allGenres as $genre): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" name="genres[]" value="<?= $genre['id'] ?>" id="genre_<?= $genre['id'] ?>"
                                    <?php if ($isEdit && in_array($genre['id'], $songGenres)) echo 'checked'; ?>
                                    <?php if (!$isEdit && isset($_SESSION['old_input']['genres']) && in_array($genre['id'], $_SESSION['old_input']['genres'])) echo 'checked'; ?>>
                                <label for="genre_<?= $genre['id'] ?>"><?= htmlspecialchars($genre['name']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="form-help">Select one or multiple genres</p>
                </div>

                <!-- Song Type -->
                <div class="material-form-group with-help">
                    <select name="type_id" class="material-form-select" required>
                        <option value="" disabled <?= !$isEdit && empty($_SESSION['old_input']['type_id']) ? 'selected' : '' ?>> </option>
                        <?php foreach ($allTypes as $type): ?>
                            <option value="<?= $type['id'] ?>" <?php if ($isEdit && $song['type_id'] == $type['id']) echo 'selected'; if (!$isEdit && ($_SESSION['old_input']['type_id'] ?? '') == $type['id']) echo 'selected'; ?>><?= htmlspecialchars($type['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="material-form-label">Type *</label>
                </div>
                <p class="form-help">Select the type of release</p>

                <!-- Platforms -->
                <div class="form-section">
                    <h3>Platforms</h3>
                    <p class="form-help">Select platforms where this song is available and provide the track URLs</p>
                    <div class="platforms-list">
                        <?php foreach ($allPlatforms as $platform): 
                            $checked = false;
                            $url = '';
                            if ($isEdit && isset($songPlatformsUrls[$platform['id']])) {
                                $checked = true;
                                $url = $songPlatformsUrls[$platform['id']];
                            } elseif (!$isEdit && isset($_SESSION['old_input']['platforms']) && in_array($platform['id'], $_SESSION['old_input']['platforms'])) {
                                $checked = true;
                                $url = $_SESSION['old_input']['platform_urls'][$platform['id']] ?? '';
                            }
                        ?>
                        <div class="platform-form-item">
                            <div class="platform-form-header" onclick="document.getElementById('platform_<?= $platform['id'] ?>').click()">
                                <div class="platform-form-icon" aria-hidden="true"><?= $platform['icon_svg'] ?></div>
                                <div style="flex:1; min-width:0;">
                                    <div class="platform-form-name"><?= htmlspecialchars($platform['name']) ?></div>
                                    <?php if ($platform['base_url']): ?>
                                    <div class="platform-form-base-url">Base URL: <?= htmlspecialchars($platform['base_url']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <input type="checkbox" id="platform_<?= $platform['id'] ?>" name="platforms[]" value="<?= $platform['id'] ?>" class="platform-form-checkbox" <?= $checked ? 'checked' : '' ?> onchange="togglePlatformUrl(<?= $platform['id'] ?>)">
                            </div>
                            <div class="platform-url-group" id="platform_url_<?= $platform['id'] ?>" style="display: <?= $checked ? 'block' : 'none' ?>">
                                <input type="url" name="platform_urls[<?= $platform['id'] ?>]" class="platform-url-input" placeholder="Enter track URL for <?= htmlspecialchars($platform['name']) ?>" value="<?= htmlspecialchars($url) ?>">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/admin/panel" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-submit">
                        <span class="material-icons" aria-hidden="true"><?= $isEdit ? 'save' : 'add' ?></span>
                        <?= $isEdit ? 'Update Song' : 'Add Song' ?>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
<script>
    function togglePlatformUrl(platformId) {
        const cb = document.getElementById('platform_' + platformId);
        const div = document.getElementById('platform_url_' + platformId);
        if (cb && div) div.style.display = cb.checked ? 'block' : 'none';
    }
    document.querySelectorAll('.platform-form-checkbox').forEach(cb => {
        const id = cb.value;
        const div = document.getElementById('platform_url_' + id);
        if (div && cb.checked) div.style.display = 'block';
    });

    function updateFileName(input) {
        const fileName = document.getElementById('file_name');
        if (input.files.length > 0) {
            fileName.textContent = 'Selected: ' + input.files[0].name;
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('cover_preview');
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            fileName.textContent = 'No file selected';
        }
    }

    function updatePreviewFromUrl(url) {
        const preview = document.getElementById('cover_preview');
        if (preview && url) {
            preview.src = url;
            preview.style.display = 'block';
        } else if (preview && !url) {
            preview.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlInput = document.getElementById('cover_image_url');
        if (urlInput && urlInput.value) updatePreviewFromUrl(urlInput.value);
    });
</script>
</body>
</html>