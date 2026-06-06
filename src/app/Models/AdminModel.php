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
     * @var string Encryption key for 2FA secrets.
     */
    private $encryptionKey;

    /**
     * Constructor for AdminModel.
     * Uses the administrative database connection.
     */
    public function __construct()
    {
        $this->db = Database::getInstance('admin')->getConnection();
        $this->encryptionKey = $_ENV['APP_2FA_KEY'];
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
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && !empty($admin['2fa_secret'])) {
            $admin['2fa_secret'] = $this->decryptSecret($admin['2fa_secret']);
        }
        return $admin;
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
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && !empty($admin['2fa_secret'])) {
            $admin['2fa_secret'] = $this->decryptSecret($admin['2fa_secret']);
        }
        return $admin;
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
        $encryptedSecret = $this->encryptSecret($secret);
        $stmt = $this->db->prepare("UPDATE admin_credentials SET 2fa_enabled = 1, 2fa_secret = ? WHERE id = 1");
        return $stmt->execute([$encryptedSecret]);
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

    /**
     * Retrieves the encryption key, decoding it if it has a base64 prefix.
     * 
     * @return string The raw binary encryption key.
     */
    private function getKey() {
        $key = $this->encryptionKey;
        if (strpos($key, 'base64:') === 0) {
            $key = base64_decode(substr($key, 7));
        }
        return $key;
    }

    /**
     * Encrypts the 2FA secret for secure storage.
     * 
     * @param string $secret The plain text Base32 secret.
     * @return string The encrypted secret, encoded in base64 with its IV.
     */
    private function encryptSecret($secret) {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($secret, 'AES-256-CBC', $this->getKey(), 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypts the 2FA secret for verification.
     * 
     * @param string $encryptedSecret The encrypted secret string from the database.
     * @return string|false The plain text secret or false on failure.
     */
    private function decryptSecret($encryptedSecret) {
        $decoded = base64_decode($encryptedSecret);
        if (strlen($decoded) < 16) return false;

        $iv = substr($decoded, 0, 16);
        $encrypted = substr($decoded, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $this->getKey(), 0, $iv);
    }
}