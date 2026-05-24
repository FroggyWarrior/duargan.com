// Theme toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const themeToggles = document.querySelectorAll('.theme-toggle, .mobile-theme-toggle');
    
    // Check for saved theme preference or default to dark
    const currentTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', currentTheme);
    
    // Update icon based on current theme
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
    
    // Add event listeners to all theme toggles
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

    // Automatically update copyright year
    const currentYear = new Date().getFullYear();
    const yearElement = document.getElementById('footerYear');
    if (yearElement) {
        yearElement.textContent = currentYear;
    }

    // Mobile menu functionality
    const burgerMenu = document.getElementById('burgerMenu');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuClose = document.getElementById('mobileMenuClose');
    
    if (burgerMenu && mobileMenu && mobileMenuClose) {
        burgerMenu.addEventListener('click', function() {
            mobileMenu.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent scrolling when menu is open
        });
        
        mobileMenuClose.addEventListener('click', function() {
            mobileMenu.classList.remove('active');
            document.body.style.overflow = 'auto'; // Restore scrolling
        });
        
        // Close menu when clicking on a link
        const mobileLinks = mobileMenu.querySelectorAll('a');
        mobileLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenu.classList.remove('active');
                document.body.style.overflow = 'auto';
            });
        });
        
        // Close menu when clicking outside
        mobileMenu.addEventListener('click', function(e) {
            if (e.target === mobileMenu) {
                mobileMenu.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
    }
});