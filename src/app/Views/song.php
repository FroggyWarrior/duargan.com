<?php
/**
 * Song Detail View
 * 
 * Displays full information for a specific song, including high-res cover, 
 * metadata, platform links, social sharing, and related tracks.
 */
?>
<main class="main-container">
    <section class="song-detail">
        <a href="/music" class="back-button">
            <span class="material-icons" style="font-size: 20px;">arrow_back</span>
            Back to All Music
        </a>

        <div class="song-detail-content">
            <div class="song-hero">
                <div class="song-cover-container">
                    <img src="<?= htmlspecialchars($song['cover_image_url']) ?>" alt="<?= htmlspecialchars($song['title']) ?>" class="song-cover-large">
                </div>
                
                <div class="song-info">
                    <h1><?= htmlspecialchars($song['title']) ?></h1>
                    
                    <div class="song-meta">
                        <div class="meta-item">
                            <span class="meta-label">Release Date:</span>
                            <span class="meta-value"><?= htmlspecialchars($song['release_date']) ?></span>
                        </div>
                        
                        <div class="meta-item">
                            <span class="meta-label">Type:</span>
                            <span class="song-type-tag"><?= htmlspecialchars($song['type_name']) ?></span>
                        </div>
                        
                        <?php if (!empty($song_genres)): ?>
                        <div class="meta-item">
                            <span class="meta-label">Genres:</span>
                            <div class="song-genre-tags">
                                <?php foreach ($song_genres as $genre): ?>
                                <span class="song-genre-tag"><?= htmlspecialchars($genre['name']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Platform buttons -->
                    <?php if (!empty($song_platforms)): ?>
                    <div class="song-platforms">
                        <h3>Listen on:</h3>
                        <div class="platform-buttons">
                            <?php foreach ($song_platforms as $platform): 
                                $platform_url = !empty($platform['track_url']) ? $platform['track_url'] : $platform['base_url'];
                                if (empty($platform_url)) continue;
                            ?>
                            <a href="<?= htmlspecialchars($platform_url) ?>" 
                                class="platform-btn with-text" 
                                target="_blank" 
                                rel="noopener" 
                                style="color: <?= $platform['color'] ?>; background-color: color-mix(in srgb, <?= $platform['color'] ?> 5%, white);">
                                <?= html_entity_decode(stripslashes($platform['icon_svg'])) ?>
                                <?= htmlspecialchars($platform['name']) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Social Sharing Buttons -->
                    <?= $shareButtons ?>
                </div>
            </div>
        </div>
    </section>

    <!-- More Music Section -->
    <?php if (!empty($otherSongs)): ?>
    <section class="more-music">
        <h2>More Music</h2>
        <div class="music-grid">
            <?php foreach ($otherSongs as $other_song): 
                // Prepare tags and slugs for the card metadata
                $other_genre_slugs = array_map(function($g) { return $g['slug']; }, $other_song['genres']);
                $other_genre_names = array_map(function($g) { return $g['name']; }, $other_song['genres']);
            ?>
            <div class="music-card" 
                onclick="window.location.href='/song?id=<?= $other_song['id'] ?>'" 
                data-genres="<?= !empty($other_genre_slugs) ? htmlspecialchars(implode(' ', $other_genre_slugs)) : '' ?>" 
                data-type="<?= htmlspecialchars($other_song['type_slug']) ?>">
                <div class="music-card-bg" style="background-image: url('<?= $other_song['cover_image_url'] ?>')"></div>
                <img src="<?= $other_song['cover_image_url'] ?>" alt="<?= htmlspecialchars($other_song['title']) ?>" class="music-cover">
                <div class="music-card-content">
                    <div class="music-info">
                        <h3><?= htmlspecialchars($other_song['title']) ?></h3>
                        <div class="music-tags">
                            <?php foreach ($other_song['genres'] as $genre): ?>
                                <span class="music-tag genre-tag" data-genre="<?= htmlspecialchars($genre['slug']) ?>">
                                    <?= htmlspecialchars(ucfirst(trim($genre['name']))) ?>
                                </span>
                            <?php endforeach; ?>
                            <span class="music-tag type-tag" data-type="<?= htmlspecialchars(trim($other_song['type_slug'])) ?>">
                                <?= htmlspecialchars(ucfirst(trim($other_song['type_name']))) ?>
                            </span>
                        </div>
                    </div>
                    <div class="music-platforms">
                        <?php foreach ($other_song['platforms'] as $platform): 
                            $platform_url = !empty($platform['track_url']) ? $platform['track_url'] : $platform['base_url'];
                            if (empty($platform_url)) continue;
                        ?>
                        <a href="<?= htmlspecialchars($platform_url) ?>" 
                            class="platform-btn icon-only" 
                            target="_blank" 
                            rel="noopener" 
                            onclick="event.stopPropagation();"
                            style="color: <?= $platform['color'] ?>; background-color: color-mix(in srgb, <?= $platform['color'] ?> 10%, white);">
                            <?= html_entity_decode(stripslashes($platform['icon_svg'])) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<script>
/**
 * Clipboard Copy functionality for the song page.
 * Handles copying the song URL to the clipboard and managing tooltip feedback.
 */
document.addEventListener('DOMContentLoaded', function() {
    const copyLinkButtons = document.querySelectorAll('.share-icon.copy-link');
    
    copyLinkButtons.forEach(button => {
        button.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            const tooltip = this.querySelector('.copy-tooltip');
            
            navigator.clipboard.writeText(url).then(function() {
                if (tooltip) {
                    tooltip.textContent = 'Copied!';
                    tooltip.classList.add('show');
                    setTimeout(() => {
                        tooltip.classList.remove('show');
                        setTimeout(() => {
                            tooltip.textContent = 'Copy link';
                        }, 300);
                    }, 2000);
                }
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
                const textArea = document.createElement('textarea');
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                if (tooltip) {
                    tooltip.textContent = 'Copied!';
                    tooltip.classList.add('show');
                    setTimeout(() => {
                        tooltip.classList.remove('show');
                        setTimeout(() => {
                            tooltip.textContent = 'Copy link';
                        }, 300);
                    }, 2000);
                }
            });
        });
    });
});
</script>