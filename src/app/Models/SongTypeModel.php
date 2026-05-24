<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class SongTypeModel {
    protected $db;

    public function __construct($useAdmin = true) {
        $type = $useAdmin ? 'admin' : 'content';
        $this->db = Database::getInstance($type)->getConnection();
    }

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

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM song_types WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($name, $slug) {
        $stmt = $this->db->prepare("INSERT INTO song_types (name, slug) VALUES (?, ?)");
        return $stmt->execute([$name, $slug]);
    }

    public function update($id, $name, $slug) {
        $stmt = $this->db->prepare("UPDATE song_types SET name = ?, slug = ? WHERE id = ?");
        return $stmt->execute([$name, $slug, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM song_types WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function toggleStatus($id) {
        $stmt = $this->db->prepare("UPDATE song_types SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function isUsed($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM songs WHERE type_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    public function getAllActive() {
        $stmt = $this->db->query("SELECT * FROM song_types WHERE is_active = TRUE ORDER BY name");
        return $stmt->fetchAll();
    }
}