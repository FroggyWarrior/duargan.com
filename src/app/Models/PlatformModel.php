<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Model for managing streaming and purchase platforms (e.g., Spotify, Apple Music).
 */
class PlatformModel {
    /**
     * @var PDO Database connection instance.
     */
    protected $db;

    /**
     * Constructor for PlatformModel.
     * 
     * @param bool $useAdmin Whether to use the administrative database connection.
     */
    public function __construct($useAdmin = true) {
        $type = $useAdmin ? 'admin' : 'content';
        $this->db = Database::getInstance($type)->getConnection();
    }

    /**
     * Retrieves all platforms along with a count of songs associated with each.
     * 
     * @return array An array of platforms with usage counts.
     */
    public function getAllWithUsage() {
        $stmt = $this->db->query("
            SELECT p.*, COUNT(sp.song_id) as usage_count 
            FROM platforms p 
            LEFT JOIN song_platforms sp ON p.id = sp.platform_id 
            GROUP BY p.id 
            ORDER BY p.display_order, p.name
        ");
        return $stmt->fetchAll();
    }

    /**
     * Retrieves all active platforms, sorted by their display order.
     * 
     * @return array An array of active platforms.
     */
    public function getAllActive() {
        $stmt = $this->db->query("SELECT * FROM platforms WHERE is_active = TRUE ORDER BY display_order, name");
        return $stmt->fetchAll();
    }

    /**
     * Fetches a single platform by its ID.
     * 
     * @param int $id The platform ID.
     * @return array|false The platform data or false if not found.
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM platforms WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Creates a new platform entry.
     * 
     * @return bool True on success, false on failure.
     */
    public function create($name, $slug, $baseUrl, $iconSvg, $color, $displayOrder) {
        $stmt = $this->db->prepare("
            INSERT INTO platforms (name, slug, base_url, icon_svg, color, display_order) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$name, $slug, $baseUrl, $iconSvg, $color, $displayOrder]);
    }

    /**
     * Updates an existing platform.
     * 
     * @return bool True on success, false on failure.
     */
    public function update($id, $name, $slug, $baseUrl, $iconSvg, $color, $displayOrder) {
        $stmt = $this->db->prepare("
            UPDATE platforms 
            SET name = ?, slug = ?, base_url = ?, icon_svg = ?, color = ?, display_order = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$name, $slug, $baseUrl, $iconSvg, $color, $displayOrder, $id]);
    }

    /**
     * Deletes a platform record.
     * 
     * @param int $id The ID of the platform to delete.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM platforms WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Toggles the active status of a platform.
     * 
     * @param int $id The ID of the platform to toggle.
     * @return bool True on success, false on failure.
     */
    public function toggleStatus($id) {
        $stmt = $this->db->prepare("UPDATE platforms SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Checks if a platform is currently linked to any songs.
     * Useful for determining if a platform can be hard-deleted or just deactivated.
     * 
     * @param int $id The ID of the platform to check.
     * @return bool True if in use, false otherwise.
     */
    public function isUsed($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM song_platforms WHERE platform_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }
}