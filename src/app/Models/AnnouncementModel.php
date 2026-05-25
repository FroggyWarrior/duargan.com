<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Model for managing site-wide announcements.
 */
class AnnouncementModel {
    /**
     * @var PDO Database connection instance.
     */
    protected $db;

    /**
     * Constructor for AnnouncementModel.
     * 
     * @param bool $useAdmin Whether to use the administrative database connection.
     */
    public function __construct($useAdmin = true) {
        $type = $useAdmin ? 'admin' : 'content';
        $this->db = Database::getInstance($type)->getConnection();
    }

    /**
     * Fetches the unique announcement record.
     * 
     * @return array|false The announcement data or false if not found.
     */
    public function get() {
        $stmt = $this->db->query("SELECT * FROM announcement WHERE id = 1 LIMIT 1");
        return $stmt->fetch();
    }

    /**
     * Saves or updates the unique announcement.
     * Creates a new record if it doesn't exist, otherwise updates the existing one.
     * 
     * @param string $title The announcement title.
     * @param string $backgroundColor The hex color code for the background.
     * @param string $text The announcement content.
     * @param int|bool $isActive Activation status.
     * @return bool True on success, false on failure.
     */
    public function save($title, $backgroundColor, $text, $isActive) {
        $existing = $this->get();
        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE announcement 
                SET title = ?, background_color = ?, text = ?, is_active = ?, updated_at = NOW() 
                WHERE id = 1
            ");
            return $stmt->execute([$title, $backgroundColor, $text, $isActive]);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO announcement (id, title, background_color, text, is_active, created_at, updated_at) 
                VALUES (1, ?, ?, ?, 1, NOW(), NOW())
            ");
            return $stmt->execute([$title, $backgroundColor, $text]);
        }
    }

    /**
     * Toggles the active status of the announcement.
     * 
     * @return bool True on success, false on failure.
     */
    public function toggle() {
        $current = $this->get();
        if (!$current) return false;
        $newStatus = $current['is_active'] ? 0 : 1;
        $stmt = $this->db->prepare("UPDATE announcement SET is_active = ? WHERE id = 1");
        return $stmt->execute([$newStatus]);
    }

    /**
     * Fetches the active announcement for the public frontend.
     * 
     * @return array|false The active announcement data or false if none.
     */
    public function getActive() {
        $stmt = $this->db->query("SELECT title, background_color, text FROM announcement WHERE id = 1 AND is_active = 1 LIMIT 1");
        return $stmt->fetch();
    }
}