<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\SocialMediaModel;

/**
 * BaseController provides common functionality and data (like headers/footers) 
 * for all public-facing controllers.
 */
class BaseController extends Controller {
    /**
     * @var array Common data to be passed to all views.
     */
    protected $commonData = [];

    /**
     * Constructor for BaseController.
     * Initializes common data and CSRF token.
     */
    public function __construct() {
        // Fetch data that most public page needs (like social media links for the footer)
        $socialModel = new SocialMediaModel();
        $this->commonData['social_media'] = $socialModel->getActivePlatforms();

        // Default SEO metadata
        $this->commonData['page_title'] = 'Duargan | Electronic Music Artist';
        $this->commonData['page_description'] = 'Listen to the latest music and official releases from Duargan.';
        $this->commonData['meta_keywords'] = 'Duargan, Hardstyle, Electronic Music, Producer, Official Releases';

        // Initialize CSRF token if it doesn't exist
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $this->commonData['csrf_token'] = $_SESSION['csrf_token'];
    }
    
    /**
     * Renders a view wrapped in the standard header and footer.
     * @param string $view Name of the view file (without .php extension).
     * @param array $data Associative array of variables to pass to the view.
     * @param bool $showFooter Whether to include the footer partial. Defaults to true.
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