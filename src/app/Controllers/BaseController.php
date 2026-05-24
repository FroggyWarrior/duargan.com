<?php
namespace App\Controllers;

use App\Models\SocialMediaModel;

/**
 * BaseController provides common functionality and data (like headers/footers) 
 * for all public-facing controllers.
 */
class BaseController {
    protected $commonData = [];

    public function __construct() {
        // Fetch data that most public page needs (like social media links for the footer)
        $socialModel = new SocialMediaModel();
        $this->commonData['social_media'] = $socialModel->getActivePlatforms();

        // Initialize CSRF token if it doesn't exist
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $this->commonData['csrf_token'] = $_SESSION['csrf_token'];
    }
    
    /**
     * Renders a view wrapped in the standard header and footer.
     * * @param string $view Name of the view file (without .php)
     * @param array $data Variables to pass to the view
     */
    protected function render($view, $data = [], $showFooter = true) {
        // Security check: Only allow alphanumeric, slashes, dashes and underscores.
        // This strictly prevents directory traversal (../) and null-byte injections.
        if (preg_match('/[^a-zA-Z0-9\/_-]/', $view)) {
            header("HTTP/1.1 400 Bad Request");
            exit('Invalid characters detected in view path.');
        }

        // Merge common data with specific page data and extract into variables
        extract(array_merge($this->commonData, $data));
        
        require_once __DIR__ . '/../Views/partials/header.php';
        require_once __DIR__ . '/../Views/' . $view . '.php';
        if ($showFooter) {
            require_once __DIR__ . '/../Views/partials/footer.php';
        }
    }
}