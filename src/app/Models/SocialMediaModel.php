<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class SocialMediaModel {
    protected $db;

    public function __construct($useAdmin = true) {
        $type = $useAdmin ? 'admin' : 'content';
        $this->db = Database::getInstance($type)->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM social_media ORDER BY display_order, name");
        return $stmt->fetchAll();
    }

    public function getActive() {
        $stmt = $this->db->query("SELECT * FROM social_media WHERE is_active = TRUE ORDER BY display_order, name");
        return $stmt->fetchAll();
    }

    // Alias method
    public function getActivePlatforms() {
        return $this->getActive();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM social_media WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($name, $slug, $baseUrl, $iconSvg, $displayOrder) {
        $stmt = $this->db->prepare("
            INSERT INTO social_media (name, slug, base_url, icon_svg, display_order) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$name, $slug, $baseUrl, $iconSvg, $displayOrder]);
    }

    public function update($id, $name, $slug, $baseUrl, $iconSvg, $displayOrder) {
        $stmt = $this->db->prepare("
            UPDATE social_media 
            SET name = ?, slug = ?, base_url = ?, icon_svg = ?, display_order = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$name, $slug, $baseUrl, $iconSvg, $displayOrder, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM social_media WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function toggleStatus($id) {
        $stmt = $this->db->prepare("UPDATE social_media SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }
}