<?php
session_start();

// 1. Basic Autoloader: Automatically loads classes
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Never show errors to visitors
ini_set('display_errors', 0);
ini_set('log_errors', 1);

use App\Core\Router;

// 2. Initialize the Router
$router = new Router();

// 3. Define our Website's Routes
// When a user visits '/', go to PageController and run the 'index' function
$router->add('/', 'PageController', 'index', 'GET');
$router->add('/music', 'PageController', 'music', 'GET');
$router->add('/about', 'PageController', 'about', 'GET');
$router->add('/contact', 'PageController', 'contact', 'GET');
$router->add('/song', 'PageController', 'song', 'GET');

// Admin routes
$router->add('/admin', 'Admin\AuthController', 'login', 'GET');
$router->add('/admin/login', 'Admin\AuthController', 'login', 'GET');
$router->add('/admin/login', 'Admin\AuthController', 'doLogin', 'POST');
$router->add('/admin/2fa-verify', 'Admin\AuthController', 'verify2fa', 'GET');
$router->add('/admin/2fa-verify', 'Admin\AuthController', 'doVerify2fa', 'POST');
$router->add('/admin/logout', 'Admin\AuthController', 'logout', 'GET');

// Admin - Songs
$router->add('/admin/panel', 'Admin\DashboardController', 'index', 'GET');
$router->add('/admin/songs/create', 'Admin\DashboardController', 'create', 'GET');
$router->add('/admin/songs/store', 'Admin\DashboardController', 'store', 'POST');
$router->add('/admin/songs/edit/{id}', 'Admin\DashboardController', 'edit', 'GET');
$router->add('/admin/songs/update/{id}', 'Admin\DashboardController', 'update', 'POST');
$router->add('/admin/songs/delete/{id}', 'Admin\DashboardController', 'delete', 'POST');

// Admin - Genres
$router->add('/admin/genres', 'Admin\GenresController', 'index', 'GET');
$router->add('/admin/genres/create', 'Admin\GenresController', 'create', 'GET');
$router->add('/admin/genres/store', 'Admin\GenresController', 'store', 'POST');
$router->add('/admin/genres/edit/{id}', 'Admin\GenresController', 'edit', 'GET');
$router->add('/admin/genres/update/{id}', 'Admin\GenresController', 'update', 'POST');
$router->add('/admin/genres/delete/{id}', 'Admin\GenresController', 'delete', 'POST');
$router->add('/admin/genres/toggle/{id}', 'Admin\GenresController', 'toggle', 'POST');

// Admin - Song Types
$router->add('/admin/song-types', 'Admin\SongTypesController', 'index', 'GET');
$router->add('/admin/song-types/create', 'Admin\SongTypesController', 'create', 'GET');
$router->add('/admin/song-types/store', 'Admin\SongTypesController', 'store', 'POST');
$router->add('/admin/song-types/edit/{id}', 'Admin\SongTypesController', 'edit', 'GET');
$router->add('/admin/song-types/update/{id}', 'Admin\SongTypesController', 'update', 'POST');
$router->add('/admin/song-types/delete/{id}', 'Admin\SongTypesController', 'delete', 'POST');
$router->add('/admin/song-types/toggle/{id}', 'Admin\SongTypesController', 'toggle', 'POST');

// Admin - Platforms
$router->add('/admin/platforms', 'Admin\PlatformsController', 'index', 'GET');
$router->add('/admin/platforms/create', 'Admin\PlatformsController', 'create', 'GET');
$router->add('/admin/platforms/store', 'Admin\PlatformsController', 'store', 'POST');
$router->add('/admin/platforms/edit/{id}', 'Admin\PlatformsController', 'edit', 'GET');
$router->add('/admin/platforms/update/{id}', 'Admin\PlatformsController', 'update', 'POST');
$router->add('/admin/platforms/delete/{id}', 'Admin\PlatformsController', 'delete', 'POST');
$router->add('/admin/platforms/toggle/{id}', 'Admin\PlatformsController', 'toggle', 'POST');

// Admin - Social Media
$router->add('/admin/social-media', 'Admin\SocialMediaController', 'index', 'GET');
$router->add('/admin/social-media/create', 'Admin\SocialMediaController', 'create', 'GET');
$router->add('/admin/social-media/store', 'Admin\SocialMediaController', 'store', 'POST');
$router->add('/admin/social-media/edit/{id}', 'Admin\SocialMediaController', 'edit', 'GET');
$router->add('/admin/social-media/update/{id}', 'Admin\SocialMediaController', 'update', 'POST');
$router->add('/admin/social-media/delete/{id}', 'Admin\SocialMediaController', 'delete', 'POST');
$router->add('/admin/social-media/toggle/{id}', 'Admin\SocialMediaController', 'toggle', 'POST');

// Admin - Announcement
$router->add('/admin/announcement', 'Admin\AnnouncementController', 'index', 'GET');
$router->add('/admin/announcement/edit', 'Admin\AnnouncementController', 'edit', 'GET');
$router->add('/admin/announcement/update', 'Admin\AnnouncementController', 'update', 'POST');
$router->add('/admin/announcement/toggle', 'Admin\AnnouncementController', 'toggle', 'POST');

// Admin - Credentials
$router->add('/admin/credentials', 'Admin\CredentialsController', 'index', 'GET');
$router->add('/admin/credentials/update', 'Admin\CredentialsController', 'updateCredentials', 'POST');
$router->add('/admin/credentials/enable2fa', 'Admin\CredentialsController', 'enable2fa', 'POST');
$router->add('/admin/credentials/disable2fa', 'Admin\CredentialsController', 'disable2fa', 'POST');

// 4. Get the current URL and send it to the router
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($uri);