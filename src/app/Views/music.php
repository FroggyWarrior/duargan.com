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
                data-date="<?php $ts = strtotime($track['release_date']); echo $ts ? date('Y-m-d', $ts) : ''; ?>">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterChips = document.querySelectorAll('.filter-chip');
        const musicGrid   = document.getElementById('musicGrid');
        const filterCount = document.getElementById('filterCount');
        const noResults   = document.getElementById('noResults');
        const sortBtn     = document.getElementById('sortBtn');
        const sortLabel   = document.getElementById('sortLabel');
        const sortIcon    = sortBtn.querySelector('.material-icons');
        
        /** @type {{genre: string, type: string}} Current active filter state */
        let activeFilters = { genre: 'all', type: 'all' };
        
        /** @type {string} Sort direction: 'desc' (Newest) or 'asc' (Oldest) */
        let sortOrder     = 'desc';

        /**
         * Reorders cards in the DOM based on the data-date attribute.
         */
        function sortCards() {
            const cards = Array.from(musicGrid.querySelectorAll('.music-card'));
            cards.sort(function(a, b) {
                const dateA = new Date(a.getAttribute('data-date'));
                const dateB = new Date(b.getAttribute('data-date'));
                return sortOrder === 'desc' ? dateB - dateA : dateA - dateB;
            });
            cards.forEach(function(card) { musicGrid.appendChild(card); });
        }

        /**
         * Handles the sort button click event.
         */
        sortBtn.addEventListener('click', function() {
            sortOrder = sortOrder === 'desc' ? 'asc' : 'desc';

            if (sortOrder === 'asc') {
                sortLabel.textContent = 'Oldest first';
                sortIcon.textContent  = 'arrow_upward';
                sortBtn.classList.add('active');
            } else {
                sortLabel.textContent = 'Newest first';
                sortIcon.textContent  = 'arrow_downward';
                sortBtn.classList.remove('active');
            }

            sortCards();
            applyFilters();
        });

        /**
         * Initializes filter chip click events.
         */
        filterChips.forEach(function(chip) {
            chip.addEventListener('click', function() {
                const filterType  = this.getAttribute('data-filter');
                const filterValue = this.getAttribute('data-value');

                document.querySelectorAll(`.filter-chip[data-filter="${filterType}"]`).forEach(function(c) {
                    c.classList.remove('active');
                });
                this.classList.add('active');

                activeFilters[filterType] = filterValue;
                applyFilters();
            });
        });

        /**
         * Applies visibility logic to cards based on the selected filters.
         * Updates result count and visibility of the "no results" message.
         */
        function applyFilters() {
            const cards = musicGrid.querySelectorAll('.music-card');
            let visibleCount = 0;

            cards.forEach(function(card) {
                const cardGenres = card.getAttribute('data-genres');
                const cardType   = card.getAttribute('data-type');

                let genreMatch = activeFilters.genre === 'all';
                if (!genreMatch && cardGenres) {
                    genreMatch = cardGenres.split(' ').includes(activeFilters.genre);
                }
                const typeMatch = activeFilters.type === 'all' || cardType === activeFilters.type;

                if (genreMatch && typeMatch) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const totalCount = musicGrid.querySelectorAll('.music-card').length;
            if (activeFilters.genre === 'all' && activeFilters.type === 'all') {
                filterCount.textContent = `Showing all ${visibleCount} tracks`;
            } else {
                filterCount.textContent = `Showing ${visibleCount} of ${totalCount} tracks`;
            }

            if (visibleCount === 0) {
                noResults.style.display  = 'block';
                musicGrid.style.display  = 'none';
            } else {
                noResults.style.display  = 'none';
                musicGrid.style.display  = 'grid';
            }
        }
    });

    /**
     * Handles the filter section accordion animation.
     */
    const filterToggle = document.getElementById('filterToggle');
    const musicFilters = document.querySelector('.music-filters');

    if (filterToggle && musicFilters) {
        filterToggle.addEventListener('click', function() {
            const isActive   = musicFilters.classList.contains('active');
            const buttonText = filterToggle.querySelector('span:first-child');

            if (isActive) {
                musicFilters.style.maxHeight = musicFilters.scrollHeight + 'px';
                musicFilters.offsetHeight; // force reflow
                musicFilters.style.maxHeight = '0';
                musicFilters.classList.remove('active');
                filterToggle.setAttribute('aria-expanded', 'false');
                setTimeout(function() { buttonText.textContent = 'Show Filters'; }, 200);
                setTimeout(function() { musicFilters.style.maxHeight = ''; }, 500);
            } else {
                musicFilters.classList.add('active');
                musicFilters.style.maxHeight = musicFilters.scrollHeight + 'px';
                filterToggle.setAttribute('aria-expanded', 'true');
                buttonText.textContent = 'Hide Filters';
                setTimeout(function() { musicFilters.style.maxHeight = 'none'; }, 500);
            }
        });

        window.addEventListener('load', function() {
            musicFilters.style.maxHeight = '';
        });
    }
</script>