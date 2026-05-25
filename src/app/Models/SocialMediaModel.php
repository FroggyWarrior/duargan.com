<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Model for managing social media links displayed in the website footer.
 */
class SocialMediaModel {
    /**
     * @var PDO Database connection instance.
     */
    protected $db;

    /**
     * Constructor for SocialMediaModel.
     * 
     * @param bool $useAdmin Whether to use the administrative database connection.
     */
    public function __construct($useAdmin = true) {
        $type = $useAdmin ? 'admin' : 'content';
        $this->db = Database::getInstance($type)->getConnection();
    }

    /**
     * Retrieves all social media platforms, including inactive ones.
     * 
     * @return array List of all social media platforms.
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM social_media ORDER BY display_order, name");
        return $stmt->fetchAll();
    }

    /**
     * Retrieves only active social media platforms.
     * 
     * @return array List of active social media platforms.
     */
    public function getActive() {
        $stmt = $this->db->query("SELECT * FROM social_media WHERE is_active = TRUE ORDER BY display_order, name");
        return $stmt->fetchAll();
    }

    /**
     * Alias for getActive().
     * 
     * @return array List of active social media platforms.
     */
    public function getActivePlatforms() {
        return $this->getActive();
    }

    /**
     * Fetches a single social media platform by its ID.
     * 
     * @param int $id The platform ID.
     * @return array|false The platform data or false if not found.
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM social_media WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Creates a new social media platform record.
     * 
     * @param string $name The platform name.
     * @param string $slug The URL slug.
     * @param string $baseUrl The platform's profile or base URL.
     * @param string $iconSvg The sanitized SVG icon markup.
     * @param int $displayOrder Sorting order for the UI.
     * @return bool True on success, false on failure.
     */
    public function create($name, $slug, $baseUrl, $iconSvg, $displayOrder) {
        $stmt = $this->db->prepare("
            INSERT INTO social_media (name, slug, base_url, icon_svg, display_order) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$name, $slug, $baseUrl, $iconSvg, $displayOrder]);
    }

    /**
     * Updates an existing social media platform record.
     * 
     * @return bool True on success, false on failure.
     */
    public function update($id, $name, $slug, $baseUrl, $iconSvg, $displayOrder) {
        $stmt = $this->db->prepare("
            UPDATE social_media 
            SET name = ?, slug = ?, base_url = ?, icon_svg = ?, display_order = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$name, $slug, $baseUrl, $iconSvg, $displayOrder, $id]);
    }

    /**
     * Deletes a social media platform record.
     * 
     * @param int $id The platform ID.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM social_media WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Toggles the active status of a social media platform.
     * 
     * @param int $id The platform ID.
     * @return bool True on success, false on failure.
     */
    public function toggleStatus($id) {
        $stmt = $this->db->prepare("UPDATE social_media SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }
}