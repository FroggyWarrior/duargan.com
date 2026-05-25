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
});