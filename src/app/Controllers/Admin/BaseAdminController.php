<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

class BaseAdminController extends Controller
{
    public function __construct()
    {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $allowedPaths = ['/admin/login', '/admin/2fa-verify'];
            if (!in_array($currentPath, $allowedPaths)) {
                $this->redirect('/admin/login');
            }
        }

        // Ensure CSRF token is available for admin views
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Security: Validate CSRF token on every POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'], $token)) {
                die('CSRF token validation failed. Unauthorized request.');
            }
        }
    }
}