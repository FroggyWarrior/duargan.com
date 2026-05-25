<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Model for managing song types (e.g., Official Release, Remix, etc.).
 */
class SongTypeModel {
    /**
     * @var PDO Database connection instance.
     */
    protected $db;

    /**
     * Constructor for SongTypeModel.
     * 
     * @param bool $useAdmin Whether to use the administrative database connection.
     */
    public function __construct($useAdmin = true) {
        $type = $useAdmin ? 'admin' : 'content';
        $this->db = Database::getInstance($type)->getConnection();
    }

    /**
     * Retrieves all song types along with a count of songs associated with each.
     * 
     * @return array An array of song types with usage counts.
     */
    public function getAllWithUsage() {
        $stmt = $this->db->query("
            SELECT st.*, COUNT(s.id) as usage_count 
            FROM song_types st 
            LEFT JOIN songs s ON st.id = s.type_id 
            GROUP BY st.id 
            ORDER BY st.name
        ");
        return $stmt->fetchAll();
    }

    /**
     * Fetches a single song type by its ID.
     * 
     * @param int $id The song type ID.
     * @return array|false The song type data or false if not found.
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM song_types WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Creates a new song type.
     * 
     * @param string $name The display name of the type.
     * @param string $slug The URL-friendly slug.
     * @return bool True on success, false on failure.
     */
    public function create($name, $slug) {
        $stmt = $this->db->prepare("INSERT INTO song_types (name, slug) VALUES (?, ?)");
        return $stmt->execute([$name, $slug]);
    }

    /**
     * Updates an existing song type.
     * 
     * @param int $id The ID of the type to update.
     * @param string $name The new display name.
     * @param string $slug The new URL-friendly slug.
     * @return bool True on success, false on failure.
     */
    public function update($id, $name, $slug) {
        $stmt = $this->db->prepare("UPDATE song_types SET name = ?, slug = ? WHERE id = ?");
        return $stmt->execute([$name, $slug, $id]);
    }

    /**
     * Deletes a song type from the database.
     * 
     * @param int $id The ID of the type to delete.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM song_types WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Toggles the active status of a song type.
     * 
     * @param int $id The ID of the type to toggle.
     * @return bool True on success, false on failure.
     */
    public function toggleStatus($id) {
        $stmt = $this->db->prepare("UPDATE song_types SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Checks if a song type is currently assigned to any songs.
     * Useful for determining if a type can be hard-deleted or just deactivated.
     * 
     * @param int $id The ID of the type to check.
     * @return bool True if in use, false otherwise.
     */
    public function isUsed($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM songs WHERE type_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Retrieves all active song types for display in forms or filters.
     * 
     * @return array An array of active song types.
     */
    public function getAllActive() {
        $stmt = $this->db->query("SELECT * FROM song_types WHERE is_active = TRUE ORDER BY name");
        return $stmt->fetchAll();
    }
}