<?php
function renderAdminSidebar($currentPage = null) {
    $pages = [
        'songs'        => ['url' => '/admin/panel',          'icon' => 'library_music',      'label' => 'Songs'],
        'platforms'    => ['url' => '/admin/platforms',      'icon' => 'public',             'label' => 'Music Platforms'],
        'social_media' => ['url' => '/admin/social-media',   'icon' => 'share',              'label' => 'Social Media'],
        'genres'       => ['url' => '/admin/genres',         'icon' => 'category',           'label' => 'Genres'],
        'types'        => ['url' => '/admin/song-types',     'icon' => 'label',              'label' => 'Song Types'],
        'announcement' => ['url' => '/admin/announcement',   'icon' => 'campaign',           'label' => 'Announcement'],
        'credentials'  => ['url' => '/admin/credentials',    'icon' => 'admin_panel_settings','label' => 'Credentials'],
    ];
    ?>
    <script>(function(){var t=localStorage.getItem('theme')||'dark';document.documentElement.setAttribute('data-theme',t);})()</script>

    <div class="nav-overlay" id="navOverlay"></div>

    <aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
        <div class="sidebar-header">
            <img src="/img/logo.svg" alt="Duargan" class="sidebar-logo">
            <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Close menu">
                <span class="material-icons">close</span>
            </button>
        </div>

        <nav class="sidebar-nav">
            <?php foreach ($pages as $key => $page): ?>
            <a href="<?php echo $page['url']; ?>"
               class="admin-nav-item<?php echo $currentPage === $key ? ' active' : ''; ?>"
               <?php echo $currentPage === $key ? 'aria-current="page"' : ''; ?>>
                <span class="material-icons"><?php echo $page['icon']; ?></span>
                <span><?php echo $page['label']; ?></span>
            </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-footer">
            <button class="sidebar-footer-item" id="themeToggle" aria-label="Toggle theme">
                <span class="material-icons" id="themeIcon">light_mode</span>
                <span id="themeLabel">Light Mode</span>
            </button>
            <a href="/admin/logout" class="sidebar-footer-item sidebar-logout">
                <span class="material-icons">logout</span>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <header class="admin-mobile-header">
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Open menu">
            <span class="material-icons">menu</span>
        </button>
        <img src="/img/logo.svg" alt="Duargan" class="mobile-header-logo">
    </header>

    <script>
    (function () {
        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            var icon  = document.getElementById('themeIcon');
            var label = document.getElementById('themeLabel');
            if (!icon) return;
            if (theme === 'dark') {
                icon.textContent  = 'light_mode';
                label.textContent = 'Light Mode';
            } else {
                icon.textContent  = 'dark_mode';
                label.textContent = 'Dark Mode';
            }
        }

        applyTheme(localStorage.getItem('theme') || 'dark');

        document.addEventListener('DOMContentLoaded', function () {
            var sidebar      = document.getElementById('adminSidebar');
            var overlay      = document.getElementById('navOverlay');
            var hamburgerBtn = document.getElementById('hamburgerBtn');
            var closeBtn     = document.getElementById('sidebarCloseBtn');
            var themeToggle  = document.getElementById('themeToggle');

            function openSidebar() {
                sidebar.classList.add('open');
                overlay.classList.add('visible');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.remove('open');
                overlay.classList.remove('visible');
                document.body.style.overflow = '';
            }

            hamburgerBtn.addEventListener('click', openSidebar);
            closeBtn.addEventListener('click', closeSidebar);
            overlay.addEventListener('click', closeSidebar);

            sidebar.querySelectorAll('.admin-nav-item').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth <= 768) closeSidebar();
                });
            });

            themeToggle.addEventListener('click', function () {
                var current = document.documentElement.getAttribute('data-theme');
                var next    = current === 'dark' ? 'light' : 'dark';
                localStorage.setItem('theme', next);
                applyTheme(next);
            });
        });
    })();
    </script>
    <?php
}
?>