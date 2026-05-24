<?php
namespace App\Models;

use App\Core\Database;

class AdminSongModel extends SongModel {
    public function __construct() {
        $this->db = Database::getInstance('admin')->getConnection();
    }

    public function create($title, $releaseDate, $coverImageUrl, $typeId) {
        $stmt = $this->db->prepare("INSERT INTO songs (title, release_date, cover_image_url, type_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $releaseDate, $coverImageUrl, $typeId]);
        return $this->db->lastInsertId();
    }

    public function update($id, $title, $releaseDate, $coverImageUrl, $typeId) {
        if ($coverImageUrl !== null) {
            $stmt = $this->db->prepare("UPDATE songs SET title = ?, release_date = ?, cover_image_url = ?, type_id = ? WHERE id = ?");
            return $stmt->execute([$title, $releaseDate, $coverImageUrl, $typeId, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE songs SET title = ?, release_date = ?, type_id = ? WHERE id = ?");
            return $stmt->execute([$title, $releaseDate, $typeId, $id]);
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM songs WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function syncGenres($songId, array $genreIds) {
        $this->db->prepare("DELETE FROM song_genres WHERE song_id = ?")->execute([$songId]);
        $stmt = $this->db->prepare("INSERT INTO song_genres (song_id, genre_id) VALUES (?, ?)");
        foreach ($genreIds as $gid) {
            $stmt->execute([$songId, $gid]);
        }
    }

    public function syncPlatforms($songId, array $platformUrls) {
        $this->db->prepare("DELETE FROM song_platforms WHERE song_id = ?")->execute([$songId]);
        $stmt = $this->db->prepare("INSERT INTO song_platforms (song_id, platform_id, track_url) VALUES (?, ?, ?)");
        foreach ($platformUrls as $platformId => $url) {
            if (!empty($url)) {
                $stmt->execute([$songId, $platformId, $url]);
            }
        }
    }

    public function getPlatformsForSong($songId) {
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, sp.track_url
            FROM song_platforms sp
            JOIN platforms p ON sp.platform_id = p.id
            WHERE sp.song_id = ?
        ");
        $stmt->execute([$songId]);
        return $stmt->fetchAll();
    }

    public function getGenresForSong($songId) {
        $stmt = $this->db->prepare("SELECT genre_id FROM song_genres WHERE song_id = ?");
        $stmt->execute([$songId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}