<?php
/**
 * Home Page View
 * 
 * Displays the site-wide announcement, the latest featured release hero section,
 * and a grid of other official releases.
 */
?>
<main class="main-container">
    <?php if ($announcement): ?>
    <div class="site-announcement" style="background-color: <?php echo htmlspecialchars($announcement['background_color']); ?>;">
        <p class="site-announcement-title"><?php echo htmlspecialchars($announcement['title']); ?></p>
        <p class="site-announcement-text"><?php echo nl2br(htmlspecialchars($announcement['text'])); ?></p>
    </div>
    <?php endif; ?>

    <?php if ($latestRelease): ?>
    <section class="latest-release fade-in-on-scroll">
        <div class="latest-release-bg" style="background-image: url('<?php echo $latestRelease['cover_image_url']; ?>')"></div>
        <img src="<?php echo $latestRelease['cover_image_url']; ?>" alt="Latest Release: <?php echo htmlspecialchars($latestRelease['title']); ?>" class="latest-release-cover" loading="lazy">
        <div class="release-content" role="region" aria-label="Latest Release Details">
            <h2>
                <a href="/song?id=<?php echo $latestRelease['id']; ?>" class="stretched-link" style="color: white; text-decoration: none; display: block;"><?php echo htmlspecialchars($latestRelease['title']); ?></a>
            </h2>
            <p>Latest single released on <?php echo date("F j, Y", strtotime($latestRelease['release_date'])); ?></p>
            <div class="platform-buttons" aria-label="Streaming platforms" style="position: relative; z-index: 3;">
                <?php foreach ($latestRelease['platforms_data'] as $platform): 
                    $platform_url = !empty($platform['track_url']) ? $platform['track_url'] : $platform['base_url'];
                    if (empty($platform_url)) continue; // Skip if no URL is provided
                ?>
                <a href="<?php echo htmlspecialchars($platform_url); ?>" 
                    class="platform-btn with-text" 
                    target="_blank" 
                    rel="noopener" 
                    style="color: <?php echo $platform['color']; ?>; background-color: color-mix(in srgb, <?php echo $platform['color']; ?> 5%, white);">
                    <?php echo html_entity_decode(stripslashes($platform['icon_svg'])); ?>
                    <?php echo htmlspecialchars($platform['name']); ?>
                </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Mobile circular buttons -->
            <div class="platform-buttons mobile">
                <?php foreach ($latestRelease['platforms_data'] as $platform): 
                    $platform_url = !empty($platform['track_url']) ? $platform['track_url'] : $platform['base_url'];
                    if (empty($platform_url)) continue;
                ?>
                <a href="<?php echo htmlspecialchars($platform_url); ?>" 
                    class="platform-btn icon-only" 
                    target="_blank" 
                    rel="noopener" 
                    aria-label="Listen to <?php echo htmlspecialchars($latestRelease['title']); ?> on <?php echo htmlspecialchars($platform['name']); ?>"
                    style="color: <?php echo $platform['color']; ?>; background-color: color-mix(in srgb, <?php echo $platform['color']; ?> 5%, white);">
                    <?php echo html_entity_decode(stripslashes($platform['icon_svg'])); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <section class="singles-list">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Official Releases</h2>
            <a href="/music" class="view-all-link" style="color: var(--primary); text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                <span>View All Music</span>
                <span class="material-icons" style="font-size: 18px;" aria-hidden="true">arrow_forward</span>
            </a>
        </div>
        <div class="music-grid">
            <?php foreach ($officialReleases as $index => $single): 
                if ($index === 0) continue;
            ?>
            <div class="music-card">
                <div class="music-card-bg" style="background-image: url('<?php echo $single['cover_image_url']; ?>')"></div>
                <img src="<?php echo $single['cover_image_url']; ?>" alt="Cover art for <?php echo htmlspecialchars($single['title']); ?>" class="music-cover">
                <div class="music-card-content">
                    <div class="music-info">
                        <h3>
                            <a href="/song?id=<?php echo $single['id']; ?>" class="stretched-link"><?php echo htmlspecialchars($single['title']); ?></a>
                        </h3>
                        <p>Released: <?php echo date("M j, Y", strtotime($single['release_date'])); ?></p>
                    </div>
                    <div class="music-platforms">
                        <?php foreach ($single['platforms_data'] as $platform): 
                            $platform_url = !empty($platform['track_url']) ? $platform['track_url'] : $platform['base_url'];
                            if (empty($platform_url)) continue;
                        ?>
                        <a href="<?php echo htmlspecialchars($platform_url); ?>" 
                            class="platform-btn icon-only" 
                            target="_blank" 
                            rel="noopener" 
                            aria-label="Listen on <?php echo htmlspecialchars($platform['name']); ?>"
                            style="color: <?php echo $platform['color']; ?>; background-color: color-mix(in srgb, <?php echo $platform['color']; ?> 5%, white);">
                            <?php echo html_entity_decode(stripslashes($platform['icon_svg'])); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>