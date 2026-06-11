<?php 
/**
 * Public Site Header Partial
 * 
 * Renders the main navigation, logo, social links, and mobile menu.
 */
use App\Core\ViewHelper; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Duargan'); ?> | Duargan</title>
    
    <!-- Standard SEO -->
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription ?? $page_description); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle ?? 'Duargan'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageDescription ?? $page_description); ?>">
    <meta property="og:image" content="<?php echo isset($pageImage) ? (strpos($pageImage, 'http') === 0 ? $pageImage : (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/' . ltrim($pageImage, '/')) : ''; ?>">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <script src="/js/script.js?v=1.0.1"></script>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="burger-menu" id="burgerMenu">
                <span></span>
                <span></span>
                <span></span>
            </div>
            
            <a href="/" class="logo-link">
                <img src="/img/logo.svg" alt="Duargan Logo" class="logo">
            </a>
            <nav>
                <ul class="nav-menu">
                    <li>
                        <a href="/" class="nav-pill <?php echo ($currentPage == 'index') ? 'active' : ''; ?>">
                            <span class="material-icons">home</span>
                        </a>
                        <span class="nav-text">Home</span>
                    </li>
                    <li>
                        <a href="/music" class="nav-pill <?php echo ($currentPage == 'music') ? 'active' : ''; ?>">
                            <span class="material-icons">library_music</span>
                        </a>
                        <span class="nav-text">Music</span>
                    </li>
                    <li>
                        <a href="/about" class="nav-pill <?php echo ($currentPage == 'about') ? 'active' : ''; ?>">
                            <span class="material-icons">person</span>
                        </a>
                        <span class="nav-text">About Me</span>
                    </li>
                    <li>
                        <a href="/contact" class="nav-pill <?php echo ($currentPage == 'contact') ? 'active' : ''; ?>">
                            <span class="material-icons">mail</span>
                        </a>
                        <span class="nav-text">Contact</span>
                    </li>
                </ul>
            </nav>
            <div style="display: flex; align-items: center;">
                <?php echo ViewHelper::renderSocialMedia($social_media, 'social-links'); ?>
                <div class="theme-toggle" id="themeToggle">
                    <span class="material-icons">light_mode</span>
                </div>
            </div>
        </div>
    </header>

    <div class="mobile-menu" id="mobileMenu">
        <button class="mobile-menu-close" id="mobileMenuClose">
            <span class="material-icons">close</span>
        </button>
        <div class="mobile-menu-logo">
            <img src="/img/logo.svg" alt="Duargan Logo" class="logo">
        </div>
        <ul class="mobile-nav">
            <li><a href="/"><span class="material-icons">home</span><span>Home</span></a></li>
            <li><a href="/music"><span class="material-icons">library_music</span><span>Music</span></a></li>
            <li><a href="/about"><span class="material-icons">person</span><span>About</span></a></li>
            <li><a href="/contact"><span class="material-icons">mail</span><span>Contact</span></a></li>
        </ul>
        <div class="mobile-social">
            <?php echo ViewHelper::renderSocialMedia($social_media, 'mobile-social'); ?>
        </div>
        <div class="mobile-theme-toggle">
            <span class="material-icons">light_mode</span>
        </div>
    </div>