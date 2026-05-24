<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class AdminModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance('admin')->getConnection();
    }

    public function getAdminByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT id, username, password, 2fa_enabled, 2fa_secret FROM admin_credentials WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAdminById($id)
    {
        $stmt = $this->db->prepare("SELECT id, username, 2fa_enabled, 2fa_secret FROM admin_credentials WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el registro de admin (único, id=1) para la gestión de credenciales
     */
    public function getAdmin() {
        $stmt = $this->db->query("SELECT id, username, 2fa_enabled FROM admin_credentials WHERE id = 1");
        return $stmt->fetch();
    }

    /**
     * Actualiza username y/o password (con hash)
     * @param string|null $newUsername Si es null o vacío, no se actualiza
     * @param string|null $newPasswordHash Si es null, no se actualiza
     */
    public function updateCredentials($newUsername = null, $newPasswordHash = null) {
        $fields = [];
        $params = [];
        if (!empty($newUsername)) {
            $fields[] = "username = ?";
            $params[] = $newUsername;
        }
        if (!empty($newPasswordHash)) {
            $fields[] = "password = ?";
            $params[] = $newPasswordHash;
        }
        if (empty($fields)) {
            return false;
        }
        $params[] = 1; // id
        $sql = "UPDATE admin_credentials SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Activa 2FA guardando el secreto
     */
    public function enable2fa($secret) {
        $stmt = $this->db->prepare("UPDATE admin_credentials SET 2fa_enabled = 1, 2fa_secret = ? WHERE id = 1");
        return $stmt->execute([$secret]);
    }

    /**
     * Desactiva 2FA y elimina el secreto
     */
    public function disable2fa() {
        $stmt = $this->db->prepare("UPDATE admin_credentials SET 2fa_enabled = 0, 2fa_secret = NULL WHERE id = 1");
        return $stmt->execute();
    }
}