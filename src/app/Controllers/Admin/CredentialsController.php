<?php
namespace App\Controllers\Admin;

use App\Models\AdminModel;
use App\Utils\TOTPAuthenticator;

/**
 * Handles administrative operations for managing admin credentials and Two-Factor Authentication (2FA).
 */
class CredentialsController extends BaseAdminController
{
    /**
     * @var AdminModel The AdminModel instance for database operations.
     */
    private $adminModel;

    /**
     * Constructor for CredentialsController.
     * Initializes the AdminModel.
     */
    public function __construct()
    {
        parent::__construct();
        $this->adminModel = new AdminModel();
    }

    /**
     * Displays the main credentials management page, including options for 2FA setup.
     * @return void
     */
    public function index()
    {
        $admin = $this->adminModel->getAdmin();
        // Si se ha solicitado setup de 2FA y no está habilitado
        $show2faSetup = isset($_GET['setup_2fa']) && !$admin['2fa_enabled'];
        $new2faSecret = null;
        $otpauthUrl = null;
        if ($show2faSetup) {
            $ga = new TOTPAuthenticator();
            $new2faSecret = $ga->createSecret();
            $otpauthUrl = $ga->getOTPAuthUrl($admin['username'], $new2faSecret, 'Duargan Music');
        }
        $this->render('admin/credentials/index', [
            'admin' => $admin,
            'show2faSetup' => $show2faSetup,
            'new2faSecret' => $new2faSecret,
            'otpauthUrl' => $otpauthUrl
        ]);
    }

    /**
     * Processes the update of the administrator's username and/or password.
     * Requires current credentials for verification and validates new password confirmation.
     * @return void
     */
    public function updateCredentials()
    {
        $currentUsername = $_POST['current_username'] ?? '';
        $currentPassword = $_POST['current_password'] ?? '';
        $newUsername = trim($_POST['new_username'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Get current credentials from the database
        $admin = $this->adminModel->getAdminByUsername($currentUsername);
        if (!$admin || !password_verify($currentPassword, $admin['password'])) {
            $_SESSION['error'] = "Current username or password is incorrect.";
            $this->redirect('/admin/credentials');
            return;
        }

        if (empty($newUsername) && empty($newPassword)) {
            $_SESSION['error'] = "Please provide either a new username or new password.";
            $this->redirect('/admin/credentials');
            return;
        }

        if (!empty($newPassword) && $newPassword !== $confirmPassword) {
            $_SESSION['error'] = "New password and confirmation do not match.";
            $this->redirect('/admin/credentials');
            return;
        }

        $updateUsername = !empty($newUsername) ? $newUsername : null;
        $updatePasswordHash = !empty($newPassword) ? password_hash($newPassword, PASSWORD_DEFAULT) : null;

        if ($this->adminModel->updateCredentials($updateUsername, $updatePasswordHash)) {
            $_SESSION['success'] = "Credentials updated successfully!";
            // If the username changed, the session remains valid (no restart needed)
        } else {
            $_SESSION['error'] = "Failed to update credentials.";
        }
        $this->redirect('/admin/credentials');
    }

    /**
     * Activates Two-Factor Authentication (2FA) for the administrator.
     * Verifies the provided 2FA code before saving the secret to the database.
     * @return void
     */
    public function enable2fa()
    {
        $secret = $_POST['2fa_secret'] ?? '';
        $code = $_POST['2fa_code'] ?? '';

        $ga = new TOTPAuthenticator();
        if ($ga->verifyCode($secret, $code, 2)) {
            if ($this->adminModel->enable2fa($secret)) {
                $_SESSION['success'] = "2FA has been enabled successfully!";
            } else {
                $_SESSION['error'] = "Database error enabling 2FA.";
            }
        } else {
            $_SESSION['error'] = "Invalid 2FA code. Please try again.";
            $this->redirect('/admin/credentials?setup_2fa=1');
            return;
        }
        $this->redirect('/admin/credentials');
    }

    /**
     * Deactivates Two-Factor Authentication (2FA) for the administrator.
     * @return void
     */
    public function disable2fa()
    {
        if ($this->adminModel->disable2fa()) {
            $_SESSION['success'] = "2FA has been disabled.";
        } else {
            $_SESSION['error'] = "Database error disabling 2FA.";
        }
        $this->redirect('/admin/credentials');
    }
}