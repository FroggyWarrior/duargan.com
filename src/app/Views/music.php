<?php
/**
 * Music Library View
 * 
 * Displays the full discography with dynamic client-side filtering and sorting.
 */
?>
<main class="main-container">
    <section class="music-library">
        <h1 style="text-align: center;">All My Music</h1>
        <p class="music-subtitle">Explore all my tracks, including free and official releases, remixes, mixes and mash-ups</p>
        
        <!-- Filter Section -->
        <button id="filterToggle" class="filter-toggle-btn" aria-expanded="false" aria-controls="musicFilters">
            <span>Show Filters</span>
            <span class="material-icons" aria-hidden="true">arrow_drop_down</span>
        </button>

        <div class="music-filters" id="musicFilters">
            <div class="filter-group">
                <h3>Filter by Genre</h3>
                <div class="filter-chips">
                    <button class="filter-chip active" data-filter="genre" data-value="all">All Genres</button>
                    <?php foreach ($all_genres as $genre): ?>
                        <button class="filter-chip" data-filter="genre" data-value="<?php echo $genre['slug']; ?>">
                            <?php echo htmlspecialchars($genre['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="filter-group">
                <h3>Filter by Type</h3>
                <div class="filter-chips">
                    <button class="filter-chip active" data-filter="type" data-value="all">All Types</button>
                    <?php foreach ($all_types as $type): ?>
                        <button class="filter-chip" data-filter="type" data-value="<?php echo $type['slug']; ?>">
                            <?php echo htmlspecialchars($type['name']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="filter-actions">
                <span class="filter-count" id="filterCount" aria-live="polite">Showing all <?php echo count($musicTracks); ?> tracks</span>
                <button class="sort-btn" id="sortBtn" aria-label="Sort by date">
                    <span class="material-icons" aria-hidden="true">arrow_downward</span>
                    <span id="sortLabel">Newest first</span>
                </button>
            </div>
        </div>
        
        <!-- Music Grid -->
        <div class="music-grid" id="musicGrid">
            <?php foreach ($musicTracks as $track):
                $genre_ids = [];
                $genre_names = [];
                $genre_slugs = [];
                // Prepare arrays from GROUP_CONCAT database results
                if (!empty($track['genre_ids'])) {
                    $genre_ids = explode(',', $track['genre_ids']);
                }
                if (!empty($track['genre_names'])) {
                    $genre_names = array_map('trim', explode(',', $track['genre_names']));
                }
                if (!empty($track['genre_slugs'])) {
                    $genre_slugs = array_map('trim', explode(',', $track['genre_slugs']));
                }
            ?>
            <div class="music-card fade-in-on-scroll" 
                data-genres="<?php echo !empty($genre_slugs) ? htmlspecialchars(implode(' ', $genre_slugs)) : ''; ?>" 
                data-type="<?php echo htmlspecialchars($track['type_slug']); ?>"
                data-date="<?php echo htmlspecialchars($track['release_date']); ?>">
                <div class="music-card-bg" style="background-image: url('<?php echo $track['cover_image_url']; ?>')"></div>
                <img src="<?php echo $track['cover_image_url']; ?>" alt="Cover art for <?php echo htmlspecialchars($track['title']); ?>" class="music-cover" loading="lazy">
                <div class="music-card-content">
                    <div class="music-info">
                        <h3>
                            <a href="/song?id=<?php echo $track['id']; ?>" class="stretched-link"><?php echo htmlspecialchars($track['title']); ?></a>
                        </h3>
                        <div class="music-tags">
                            <?php // Display genre tags 
                            foreach ($genre_names as $index => $genre_name): 
                                if (!empty(trim($genre_name)) && isset($genre_slugs[$index])): ?>
                                    <span class="music-tag genre-tag" data-genre="<?php echo htmlspecialchars($genre_slugs[$index]); ?>">
                                        <?php echo htmlspecialchars(ucfirst(trim($genre_name))); ?>
                                    </span>
                                <?php endif;
                            endforeach; 
                            // Display type tag 
                            if (!empty(trim($track['type_name']))): ?>
                                <span class="music-tag type-tag" data-type="<?php echo htmlspecialchars(trim($track['type_slug'])); ?>">
                                    <?php echo htmlspecialchars(ucfirst(trim($track['type_name']))); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="music-platforms">
                        <?php foreach ($track['platforms_data'] as $platform): 
                            $platform_url = !empty($platform['track_url']) ? $platform['track_url'] : $platform['base_url'];
                            if (empty($platform_url)) continue;
                        ?>
                        <a href="<?php echo htmlspecialchars($platform_url); ?>" 
                            class="platform-btn icon-only" 
                            target="_blank" 
                            rel="noopener" 
                            aria-label="Listen on <?php echo htmlspecialchars($platform['name']); ?>"
                            style="color: <?php echo $platform['color']; ?>; background-color: color-mix(in srgb, <?php echo $platform['color']; ?> 10%, white);">
                            <?php echo html_entity_decode(stripslashes($platform['icon_svg'])); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="no-results" id="noResults" style="display: none;">
            <h3>No tracks found</h3>
            <p>Try adjusting your filters to see more results</p>
        </div>
    </section>
</main>