<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class PlatformModel {
    protected $db;

    public function __construct($useAdmin = true) {
        $type = $useAdmin ? 'admin' : 'content';
        $this->db = Database::getInstance($type)->getConnection();
    }

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

    public function getAllActive() {
        $stmt = $this->db->query("SELECT * FROM platforms WHERE is_active = TRUE ORDER BY display_order, name");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM platforms WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($name, $slug, $baseUrl, $iconSvg, $color, $displayOrder) {
        $stmt = $this->db->prepare("
            INSERT INTO platforms (name, slug, base_url, icon_svg, color, display_order) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$name, $slug, $baseUrl, $iconSvg, $color, $displayOrder]);
    }

    public function update($id, $name, $slug, $baseUrl, $iconSvg, $color, $displayOrder) {
        $stmt = $this->db->prepare("
            UPDATE platforms 
            SET name = ?, slug = ?, base_url = ?, icon_svg = ?, color = ?, display_order = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$name, $slug, $baseUrl, $iconSvg, $color, $displayOrder, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM platforms WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function toggleStatus($id) {
        $stmt = $this->db->prepare("UPDATE platforms SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function isUsed($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM song_platforms WHERE platform_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }
}