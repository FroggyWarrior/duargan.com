<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class GenreModel {
    protected $db;

    public function __construct($useAdmin = true) {
        $type = $useAdmin ? 'admin' : 'content';
        $this->db = Database::getInstance($type)->getConnection();
    }

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

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM genres WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($name, $slug) {
        $stmt = $this->db->prepare("INSERT INTO genres (name, slug) VALUES (?, ?)");
        return $stmt->execute([$name, $slug]);
    }

    public function update($id, $name, $slug) {
        $stmt = $this->db->prepare("UPDATE genres SET name = ?, slug = ? WHERE id = ?");
        return $stmt->execute([$name, $slug, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM genres WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function toggleStatus($id) {
        $stmt = $this->db->prepare("UPDATE genres SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function isUsed($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM song_genres WHERE genre_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    public function getAllActive() {
        $stmt = $this->db->query("SELECT * FROM genres WHERE is_active = TRUE ORDER BY name");
        return $stmt->fetchAll();
    }
}