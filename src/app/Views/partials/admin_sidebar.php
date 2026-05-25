<?php
/**
 * Admin Sidebar Partial
 * 
 * Renders the primary navigation drawer for the admin panel.
 * Handles theme switching and mobile drawer state.
 * @param string|null $currentPage Used to highlight the active menu item.
 */
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
    
    <!-- Ensure admin.js is loaded for sidebar functionality -->
    <script src="/js/admin.js"></script>

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

    <?php
}
?>