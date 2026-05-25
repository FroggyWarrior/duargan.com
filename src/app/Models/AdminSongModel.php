<?php
namespace App\Models;

use App\Core\Database;

/**
 * Model for administrative song management.
 * Extends SongModel to provide create, update, and delete capabilities.
 */
class AdminSongModel extends SongModel {
    /**
     * Constructor for AdminSongModel.
     * Uses the administrative database connection.
     */
    public function __construct() {
        $this->db = Database::getInstance('admin')->getConnection();
    }

    /**
     * Creates a new song record.
     * 
     * @return string|false The ID of the newly created song or false on failure.
     */
    public function create($title, $releaseDate, $coverImageUrl, $typeId) {
        $stmt = $this->db->prepare("INSERT INTO songs (title, release_date, cover_image_url, type_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $releaseDate, $coverImageUrl, $typeId]);
        return $this->db->lastInsertId();
    }

    /**
     * Updates an existing song record.
     * If coverImageUrl is null, the existing cover image is preserved.
     * 
     * @return bool True on success, false on failure.
     */
    public function update($id, $title, $releaseDate, $coverImageUrl, $typeId) {
        if ($coverImageUrl !== null) {
            $stmt = $this->db->prepare("UPDATE songs SET title = ?, release_date = ?, cover_image_url = ?, type_id = ? WHERE id = ?");
            return $stmt->execute([$title, $releaseDate, $coverImageUrl, $typeId, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE songs SET title = ?, release_date = ?, type_id = ? WHERE id = ?");
            return $stmt->execute([$title, $releaseDate, $typeId, $id]);
        }
    }

    /**
     * Deletes a song record by its ID.
     * 
     * @param int $id The song ID.
     * @return bool True on success, false on failure.
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM songs WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Synchronizes genres for a specific song by deleting existing associations and adding new ones.
     * 
     * @param int $songId The ID of the song.
     * @param array $genreIds An array of genre IDs to associate.
     */
    public function syncGenres($songId, array $genreIds) {
        $this->db->prepare("DELETE FROM song_genres WHERE song_id = ?")->execute([$songId]);
        $stmt = $this->db->prepare("INSERT INTO song_genres (song_id, genre_id) VALUES (?, ?)");
        foreach ($genreIds as $gid) {
            $stmt->execute([$songId, $gid]);
        }
    }

    /**
     * Synchronizes platform links for a specific song.
     * 
     * @param int $songId The ID of the song.
     * @param array $platformUrls Associative array where keys are platform IDs and values are track URLs.
     */
    public function syncPlatforms($songId, array $platformUrls) {
        $this->db->prepare("DELETE FROM song_platforms WHERE song_id = ?")->execute([$songId]);
        $stmt = $this->db->prepare("INSERT INTO song_platforms (song_id, platform_id, track_url) VALUES (?, ?, ?)");
        foreach ($platformUrls as $platformId => $url) {
            if (!empty($url)) {
                $stmt->execute([$songId, $platformId, $url]);
            }
        }
    }

    /**
     * Fetches platforms and their specific URLs associated with a song for administrative use.
     * 
     * @param int $songId The ID of the song.
     * @return array List of platforms with track URLs.
     */
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

    /**
     * Fetches all genre IDs associated with a specific song.
     * 
     * @param int $songId The ID of the song.
     * @return array List of genre IDs.
     */
    public function getGenresForSong($songId) {
        $stmt = $this->db->prepare("SELECT genre_id FROM song_genres WHERE song_id = ?");
        $stmt->execute([$songId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}