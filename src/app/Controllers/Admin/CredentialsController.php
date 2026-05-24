<?php
namespace App\Controllers\Admin;

use App\Models\AdminModel;
use App\Utils\TOTPAuthenticator;

class CredentialsController extends BaseAdminController
{
    private $adminModel;

    public function __construct()
    {
        parent::__construct();
        $this->adminModel = new AdminModel();
    }

    /**
     * Muestra la página principal de gestión de credenciales y 2FA
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
     * Procesa la actualización de username/password
     */
    public function updateCredentials()
    {
        $currentUsername = $_POST['current_username'] ?? '';
        $currentPassword = $_POST['current_password'] ?? '';
        $newUsername = trim($_POST['new_username'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Obtener credenciales actuales de la base de datos
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
            // Si el username cambió, la sesión sigue siendo válida (no es necesario reiniciar)
        } else {
            $_SESSION['error'] = "Failed to update credentials.";
        }
        $this->redirect('/admin/credentials');
    }

    /**
     * Activa 2FA (verifica el código antes de guardar)
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
            // Redirigir de nuevo a la página con el formulario de setup
            $this->redirect('/admin/credentials?setup_2fa=1');
            return;
        }
        $this->redirect('/admin/credentials');
    }

    /**
     * Desactiva 2FA
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