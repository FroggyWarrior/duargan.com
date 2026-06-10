/**
 * Global Site Script
 * 
 * Handles shared interface functionality including theme management,
 * responsive navigation, and global UI updates.
 */
document.addEventListener('DOMContentLoaded', function() {
    
    /** --- 1. Theme Management --- */
    const themeToggles = document.querySelectorAll('.theme-toggle, .mobile-theme-toggle');
    const currentTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', currentTheme);
    
    /**
     * Updates the theme toggle icons based on the current root data-theme attribute.
     */
    function updateThemeIcons() {
        const theme = document.documentElement.getAttribute('data-theme');
        themeToggles.forEach(toggle => {
            const icon = toggle.querySelector('.material-icons');
            if (icon) {
                if (theme === 'light') {
                    icon.textContent = 'dark_mode';
                } else {
                    icon.textContent = 'light_mode';
                }
            }
        });
    }
    updateThemeIcons();
    
    themeToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            let theme = document.documentElement.getAttribute('data-theme');
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            }
            updateThemeIcons();
        });
    });

    /** --- 2. Dynamic Content --- */
    const currentYear = new Date().getFullYear();
    const yearElement = document.getElementById('footerYear');
    if (yearElement) {
        yearElement.textContent = currentYear;
    }

    /** --- 3. Mobile Navigation --- */
    const burgerMenu = document.getElementById('burgerMenu');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuClose = document.getElementById('mobileMenuClose');
    
    if (burgerMenu && mobileMenu && mobileMenuClose) {
        burgerMenu.addEventListener('click', function() {
            mobileMenu.classList.add('active');
            burgerMenu.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        });
        
        mobileMenuClose.addEventListener('click', function() {
            mobileMenu.classList.remove('active');
            burgerMenu.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = 'auto';
        });
        
        // Auto-close menu when navigating
        const mobileLinks = mobileMenu.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenu.classList.remove('active');
                burgerMenu.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = 'auto';
            });
        });
        
        // Backdrop click to close
        mobileMenu.addEventListener('click', function(e) {
            if (e.target === mobileMenu) {
                mobileMenu.classList.remove('active');
                burgerMenu.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = 'auto';
            }
        });
    }

    /** --- 4. Scroll Reveal Animation --- */
    const scrollElements = document.querySelectorAll('.fade-in-on-scroll');
    
    const elementInView = (el, dividend = 1) => {
        const elementTop = el.getBoundingClientRect().top;
        return (elementTop <= (window.innerHeight || document.documentElement.clientHeight) / dividend);
    };

    const displayScrollElement = (element) => { el.classList.add('visible'); };

    // Using Intersection Observer for better performance than scroll listeners
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                // Once visible, we can stop observing this specific element
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    scrollElements.forEach(el => {
        observer.observe(el);
    });

    /** --- 5. Stop Propagation for Platform Buttons in Cards --- */
    // Prevent clicks on platform buttons from triggering the parent card's link
    document.addEventListener('click', function(e) {
        const platformBtn = e.target.closest('.platform-btn');
        if (platformBtn) {
            // Stop the click from bubbling up to the .music-card or .stretched-link
            e.stopPropagation();
        }
    });

    /** --- 6. Music Library Filters and Sorting --- */
    const musicGrid = document.getElementById('musicGrid');
    if (musicGrid) {
        const filterChips = document.querySelectorAll('.filter-chip');
        const filterCount = document.getElementById('filterCount');
        const noResults = document.getElementById('noResults');
        const sortBtn = document.getElementById('sortBtn');
        const sortLabel = document.getElementById('sortLabel');
        const sortIcon = sortBtn ? sortBtn.querySelector('.material-icons') : null;

        let activeFilters = { genre: 'all', type: 'all' };
        let sortOrder = 'desc';

        const sortCards = () => {
            const cards = Array.from(musicGrid.querySelectorAll('.music-card'));
            cards.sort((a, b) => {
                const dateA = new Date(a.getAttribute('data-date'));
                const dateB = new Date(b.getAttribute('data-date'));
                return sortOrder === 'desc' ? dateB - dateA : dateA - dateB;
            });
            cards.forEach(card => musicGrid.appendChild(card));
        };

        const applyFilters = () => {
            const cards = musicGrid.querySelectorAll('.music-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const cardGenres = card.getAttribute('data-genres');
                const cardType = card.getAttribute('data-type');
                let genreMatch = activeFilters.genre === 'all' || (cardGenres && cardGenres.split(' ').includes(activeFilters.genre));
                const typeMatch = activeFilters.type === 'all' || cardType === activeFilters.type;

                if (genreMatch && typeMatch) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (filterCount) {
                filterCount.textContent = (activeFilters.genre === 'all' && activeFilters.type === 'all') 
                    ? `Showing all ${visibleCount} tracks` 
                    : `Showing ${visibleCount} of ${cards.length} tracks`;
            }

            if (noResults) {
                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
                musicGrid.style.display = visibleCount === 0 ? 'none' : 'grid';
            }
        };

        if (sortBtn) {
            sortBtn.addEventListener('click', () => {
                sortOrder = sortOrder === 'desc' ? 'asc' : 'desc';
                sortLabel.textContent = sortOrder === 'asc' ? 'Oldest first' : 'Newest first';
                if (sortIcon) sortIcon.textContent = sortOrder === 'asc' ? 'arrow_upward' : 'arrow_downward';
                sortBtn.classList.toggle('active', sortOrder === 'asc');
                sortCards();
                applyFilters();
            });
        }

        filterChips.forEach(chip => {
            chip.addEventListener('click', function() {
                const type = this.getAttribute('data-filter');
                document.querySelectorAll(`.filter-chip[data-filter="${type}"]`).forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                activeFilters[type] = this.getAttribute('data-value');
                applyFilters();
            });
        });

        // Filter Accordion
        const filterToggle = document.getElementById('filterToggle');
        const musicFilters = document.querySelector('.music-filters');
        if (filterToggle && musicFilters) {
            filterToggle.addEventListener('click', () => {
                const isActive = musicFilters.classList.contains('active');
                const buttonText = filterToggle.querySelector('span:first-child');

                if (isActive) {
                    musicFilters.style.maxHeight = musicFilters.scrollHeight + 'px';
                    musicFilters.offsetHeight; 
                    musicFilters.style.maxHeight = '0';
                    musicFilters.classList.remove('active');
                    filterToggle.setAttribute('aria-expanded', 'false');
                    setTimeout(() => { if (buttonText) buttonText.textContent = 'Show Filters'; }, 200);
                } else {
                    musicFilters.classList.add('active');
                    musicFilters.style.maxHeight = musicFilters.scrollHeight + 'px';
                    filterToggle.setAttribute('aria-expanded', 'true');
                    if (buttonText) buttonText.textContent = 'Hide Filters';
                    setTimeout(() => { musicFilters.style.maxHeight = 'none'; }, 500);
                }
            });
        }
    }

    /** --- 7. Song Details Functionality --- */
    const copyLinkButtons = document.querySelectorAll('.share-icon.copy-link');
    copyLinkButtons.forEach(button => {
        button.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            const tooltip = this.querySelector('.copy-tooltip');
            
            const showSuccess = () => {
                if (tooltip) {
                    tooltip.textContent = 'Copied!';
                    tooltip.classList.add('show');
                    setTimeout(() => {
                        tooltip.classList.remove('show');
                        setTimeout(() => { tooltip.textContent = 'Copy link'; }, 300);
                    }, 2000);
                }
            };

            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(showSuccess).catch(err => {
                    console.error('Clipboard API failed, falling back', err);
                    fallbackCopy(url, showSuccess);
                });
            } else {
                fallbackCopy(url, showSuccess);
            }
        });
    });

    function fallbackCopy(text, callback) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            callback();
        } catch (err) {
            console.error('Fallback copy failed', err);
        }
        document.body.removeChild(textArea);
    }
});