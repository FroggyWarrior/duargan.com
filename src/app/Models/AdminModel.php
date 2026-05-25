<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Model for handling administrator credentials and authentication settings.
 */
class AdminModel
{
    /**
     * @var PDO Database connection instance.
     */
    private $db;

    /**
     * Constructor for AdminModel.
     * Uses the administrative database connection.
     */
    public function __construct()
    {
        $this->db = Database::getInstance('admin')->getConnection();
    }

    /**
     * Fetches administrator data by their username.
     * Used for login verification.
     * 
     * @param string $username The username to search for.
     * @return array|false The administrator record or false if not found.
     */
    public function getAdminByUsername($username)
    {
        $stmt = $this->db->prepare("SELECT id, username, password, 2fa_enabled, 2fa_secret FROM admin_credentials WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches administrator data by their ID.
     * Used during 2FA verification.
     * 
     * @param int $id The administrator ID.
     * @return array|false The administrator record or false if not found.
     */
    public function getAdminById($id)
    {
        $stmt = $this->db->prepare("SELECT id, username, 2fa_enabled, 2fa_secret FROM admin_credentials WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches the primary administrator record (id=1) for credentials management.
     * 
     * @return array|false The administrator record.
     */
    public function getAdmin() {
        $stmt = $this->db->query("SELECT id, username, 2fa_enabled FROM admin_credentials WHERE id = 1");
        return $stmt->fetch();
    }

    /**
     * Updates the administrator's username and/or password.
     * 
     * @param string|null $newUsername The new username or null to keep existing.
     * @param string|null $newPasswordHash The hashed new password or null to keep existing.
     * @return bool True on success, false on failure or if no fields updated.
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
     * Enables Two-Factor Authentication for the administrator.
     * 
     * @param string $secret The generated TOTP secret key.
     * @return bool True on success, false on failure.
     */
    public function enable2fa($secret) {
        $stmt = $this->db->prepare("UPDATE admin_credentials SET 2fa_enabled = 1, 2fa_secret = ? WHERE id = 1");
        return $stmt->execute([$secret]);
    }

    /**
     * Disables Two-Factor Authentication and removes the stored secret.
     * 
     * @return bool True on success, false on failure.
     */
    public function disable2fa() {
        $stmt = $this->db->prepare("UPDATE admin_credentials SET 2fa_enabled = 0, 2fa_secret = NULL WHERE id = 1");
        return $stmt->execute();
    }
}