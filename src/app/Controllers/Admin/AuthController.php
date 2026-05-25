<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminModel;
use App\Utils\TOTPAuthenticator;

/**
 * Handles administrator authentication, including login, 2FA verification, and logout.
 */
class AuthController extends Controller
{
    /**
     * @var AdminModel The AdminModel instance for database operations.
     */
    private $adminModel;

    /**
     * Constructor for AuthController.
     * Initializes the AdminModel and ensures a CSRF token is present in the session.
     */
    public function __construct()
    {
        $this->adminModel = new AdminModel();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    /**
     * Displays the administrator login form.
     * Redirects to the admin panel if already logged in.
     * @return void
     */
    public function login()
    {
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            $this->redirect('/admin/panel');
        }
        $this->render('admin/login');
    }

    /**
     * Processes the administrator login attempt.
     * Handles CSRF verification, credential checking, and initiates 2FA if enabled.
     * @return void
     */
    public function doLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/login');
            return;
        }

        // CSRF Verification
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $_SESSION['login_error'] = 'Security token invalid. Please try again.';
            $this->redirect('/admin/login');
            return;
        }

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $admin = $this->adminModel->getAdminByUsername($username);

        if ($admin && password_verify($password, $admin['password'])) {
            if ($admin['2fa_enabled']) {
                session_regenerate_id(true);
                $_SESSION['2fa_admin_id'] = $admin['id'];
                $this->redirect('/admin/2fa-verify');
            } else {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $this->redirect('/admin/panel');
            }
        } else {
            $_SESSION['login_error'] = 'Invalid credentials';
            $this->redirect('/admin/login');
        }
    }

    /**
     * Displays the 2FA verification form.
     * Redirects to login if no admin ID is stored for 2FA.
     * @return void
     */
    public function verify2fa()
    {
        if (!isset($_SESSION['2fa_admin_id'])) {
            $this->redirect('/admin/login');
        }
        $this->render('admin/2fa_verify');
    }

    /**
     * Processes the 2FA verification code.
     * Verifies the provided code against the stored secret and logs in the admin upon success.
     * @return void
     */
    public function doVerify2fa()
    {
        if (!isset($_SESSION['2fa_admin_id'])) {
            $this->redirect('/admin/login');
        }

        // CSRF Verification for 2FA
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $_SESSION['2fa_error'] = 'Security token invalid. Please try again.';
            $this->redirect('/admin/2fa-verify');
            return;
        }

        $code = $_POST['2fa_code'] ?? '';
        $adminId = $_SESSION['2fa_admin_id'];
        $admin = $this->adminModel->getAdminById($adminId);

        $ga = new TOTPAuthenticator();
        if ($ga->verifyCode($admin['2fa_secret'], $code, 2)) {
            unset($_SESSION['2fa_admin_id']);
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $this->redirect('/admin/panel');
        } else {
            $_SESSION['2fa_error'] = 'Invalid verification code.';
            $this->redirect('/admin/2fa-verify');
        }
    }

    /**
     * Logs out the administrator.
     * Clears all session data and redirects to the login page.
     * @return void
     */
    public function logout()
    {
        // Clear all session variables from memory
        $_SESSION = [];

        // Invalidate the session cookie in the browser
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        // Destroy the session data on the server
        session_destroy();

        $this->redirect('/admin/login');
    }
}