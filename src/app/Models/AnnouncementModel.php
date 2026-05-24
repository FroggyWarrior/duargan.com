<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class AnnouncementModel {
    protected $db;

    public function __construct($useAdmin = true) {
        $type = $useAdmin ? 'admin' : 'content';
        $this->db = Database::getInstance($type)->getConnection();
    }

    /**
     * Obtiene el anuncio único (id = 1)
     * @return array|false
     */
    public function get() {
        $stmt = $this->db->query("SELECT * FROM announcement WHERE id = 1 LIMIT 1");
        return $stmt->fetch();
    }

    /**
     * Guarda o actualiza el anuncio (si no existe, lo crea con id=1)
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
     * Alterna el estado activo/inactivo
     */
    public function toggle() {
        $current = $this->get();
        if (!$current) return false;
        $newStatus = $current['is_active'] ? 0 : 1;
        $stmt = $this->db->prepare("UPDATE announcement SET is_active = ? WHERE id = 1");
        return $stmt->execute([$newStatus]);
    }

    /**
     * Obtiene el anuncio activo para el frontend
     */
    public function getActive() {
        $stmt = $this->db->query("SELECT title, background_color, text FROM announcement WHERE id = 1 AND is_active = 1 LIMIT 1");
        return $stmt->fetch();
    }
}