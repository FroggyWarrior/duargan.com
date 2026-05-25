<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Model for managing music genres.
 */
class GenreModel {
    /**
     * @var PDO Database connection instance.
     */
    protected $db;

    /**
     * Constructor for GenreModel.
     * 
     * @param bool $useAdmin Whether to use the administrative database connection.
     */
    public function __construct($useAdmin = true) {
        $type = $useAdmin ? 'admin' : 'content';
        $this->db = Database::getInstance($type)->getConnection();
    }

    /**
     * Retrieves all genres along with a count of songs associated with each.
     * 
     * @return array An array of genres with usage counts.
     */
    public function getAllWithUsage() {
        $stmt = $this->db->query("
            SELECT g.*, COUNT(sg.song_id) as usage_count 
            FROM genres g 
            LEFT JOIN song_genres sg ON g.id = sg.genre_id 
            GROUP BY g.id 
            ORDER BY g.name
        ");
        return $stmt->fetchAll();
    }

    /**
     * Fetches a single genre by its ID.
     * 
     * @param int $id The genre ID.
     * @return array|false The genre data or false if not found.
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM genres WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Creates a new genre entry.
     * 
     * @param string $name The display name of the genre.
     * @param string $slug The URL-friendly slug.
     * @return bool True on success, false on failure.
     */
    public function create($name, $slug) {
        $stmt = $this->db->prepare("INSERT INTO genres (name, slug) VALUES (?, ?)");
        return $stmt->execute([$name, $slug]);
    }

    /**
     * Updates an existing genre.
     * 
     * @param int $id The ID of the genre to update.
     * @param string $name The new display name.
     * @param string $slug The new URL-friendly slug.
     * @return bool True on success, false on failure.
     */
    public function update($id, $name, $slug) {
        $stmt = $this->db->prepare("UPDATE genres SET name = ?, slug = ? WHERE id = ?");
        return $stmt->execute([$name, $slug, $id]);
    }

    /**
     * Deletes a genre from the database.
     * 
     * @param int $id The ID of the genre to delete.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM genres WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Toggles the active status of a genre.
     * 
     * @param int $id The ID of the genre to toggle.
     * @return bool True on success, false on failure.
     */
    public function toggleStatus($id) {
        $stmt = $this->db->prepare("UPDATE genres SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Checks if a genre is currently assigned to any songs.
     * Useful for determining if a genre can be hard-deleted or just deactivated.
     * 
     * @param int $id The ID of the genre to check.
     * @return bool True if in use, false otherwise.
     */
    public function isUsed($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM song_genres WHERE genre_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Retrieves all active genres for display in public filters or song forms.
     * 
     * @return array An array of active genres.
     */
    public function getAllActive() {
        $stmt = $this->db->query("SELECT * FROM genres WHERE is_active = TRUE ORDER BY name");
        return $stmt->fetchAll();
    }
}