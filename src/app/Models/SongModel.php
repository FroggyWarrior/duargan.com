<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class SongModel {
    protected $db;

    public function __construct() {
        // Get our single database connection
        $this->db = Database::getInstance()->getConnection();
    }

    public function getOfficialReleases() {
        // 1. Fetch the songs
        $stmt = $this->db->query("
            SELECT s.*, st.name as type_name 
            FROM songs s 
            JOIN song_types st ON s.type_id = st.id 
            WHERE st.slug = 'official' 
            ORDER BY s.release_date DESC
        ");
        $releases = $stmt->fetchAll();

        // 2. Fetch platforms for each song (directly from your original code!)
        foreach ($releases as &$release) {
            $platform_stmt = $this->db->prepare("
                SELECT p.*, sp.track_url 
                FROM song_platforms sp 
                JOIN platforms p ON sp.platform_id = p.id 
                WHERE sp.song_id = ? AND p.is_active = TRUE
                ORDER BY p.display_order
            ");
            $platform_stmt->execute([$release['id']]);
            $release['platforms_data'] = $platform_stmt->fetchAll();
        }
        unset($release); // Break the reference

        return $releases;
    }

    /**
     * Fetches all songs with their associated genres and types for the Music page.
     */
    public function getAllMusic() {
        $stmt = $this->db->query("
            SELECT s.*, 
                st.name as type_name,
                st.slug as type_slug,
                GROUP_CONCAT(DISTINCT g.id) as genre_ids,
                GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') as genre_names,
                GROUP_CONCAT(DISTINCT g.slug ORDER BY g.name) as genre_slugs
            FROM songs s
            LEFT JOIN song_genres sg ON s.id = sg.song_id
            LEFT JOIN genres g ON sg.genre_id = g.id AND g.is_active = TRUE
            LEFT JOIN song_types st ON s.type_id = st.id
            GROUP BY s.id
            ORDER BY s.release_date DESC
        ");
        $tracks = $stmt->fetchAll();

        // Fetch platforms for each track
        foreach ($tracks as &$track) {
            $platform_stmt = $this->db->prepare("
                SELECT p.*, sp.track_url 
                FROM song_platforms sp 
                JOIN platforms p ON sp.platform_id = p.id 
                WHERE sp.song_id = ? AND p.is_active = TRUE
                ORDER BY p.display_order
            ");
            $platform_stmt->execute([$track['id']]);
            $track['platforms_data'] = $platform_stmt->fetchAll();
        }
        return $tracks;
    }

    /**
     * Fetches a single song by ID with its type, genres, and platforms.
     */
    public function getSongById($id) {
        // 1. Fetch main song info
        $stmt = $this->db->prepare("
            SELECT s.*, st.name as type_name, st.slug as type_slug
            FROM songs s 
            LEFT JOIN song_types st ON s.type_id = st.id 
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $song = $stmt->fetch();

        if (!$song) return null;

        // 2. Fetch genres for this song
        $genre_stmt = $this->db->prepare("
            SELECT g.name, g.slug 
            FROM song_genres sg 
            JOIN genres g ON sg.genre_id = g.id 
            WHERE sg.song_id = ? AND g.is_active = TRUE
            ORDER BY g.name
        ");
        $genre_stmt->execute([$id]);
        $song['genres'] = $genre_stmt->fetchAll();

        // 3. Fetch platforms for this song
        $platform_stmt = $this->db->prepare("
            SELECT p.*, sp.track_url 
            FROM song_platforms sp 
            JOIN platforms p ON sp.platform_id = p.id 
            WHERE sp.song_id = ? AND p.is_active = TRUE
            ORDER BY p.display_order
        ");
        $platform_stmt->execute([$id]);
        $song['platforms'] = $platform_stmt->fetchAll();

        return $song;
    }

    /**
     * Fetches other songs (excluding the current one) for the "More Music" section.
     */
    public function getOtherSongs($excludeId, $limit = 4) {
        $stmt = $this->db->prepare("
            SELECT id, title, cover_image_url, release_date
            FROM songs 
            WHERE id != ? 
            ORDER BY release_date DESC 
            LIMIT ?
        ");
        // We use bindValue for the limit because it must be an integer
        $stmt->bindValue(1, $excludeId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function getOtherSongsDetailed($excludeId, $limit = 4) {
        // 1. Get songs by its type
        $stmt = $this->db->prepare("
            SELECT s.*, st.name as type_name, st.slug as type_slug
            FROM songs s
            LEFT JOIN song_types st ON s.type_id = st.id
            WHERE s.id != ?
            ORDER BY s.release_date DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $excludeId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $songs = $stmt->fetchAll();

        // 2. For each song, get genres and platforms
        foreach ($songs as &$song) {
            // Genres
            $genreStmt = $this->db->prepare("
                SELECT g.name, g.slug
                FROM song_genres sg
                JOIN genres g ON sg.genre_id = g.id
                WHERE sg.song_id = ? AND g.is_active = TRUE
                ORDER BY g.name
            ");
            $genreStmt->execute([$song['id']]);
            $song['genres'] = $genreStmt->fetchAll();

            // Platforms
            $platformStmt = $this->db->prepare("
                SELECT p.*, sp.track_url
                FROM song_platforms sp
                JOIN platforms p ON sp.platform_id = p.id
                WHERE sp.song_id = ? AND p.is_active = TRUE
                ORDER BY p.display_order
            ");
            $platformStmt->execute([$song['id']]);
            $song['platforms'] = $platformStmt->fetchAll();
        }
        unset($song);

        return $songs;
    }

    public function getAllGenres() {
        return $this->db->query("SELECT * FROM genres WHERE is_active = TRUE ORDER BY name")->fetchAll();
    }

    public function getAllTypes() {
        return $this->db->query("SELECT * FROM song_types WHERE is_active = TRUE ORDER BY name")->fetchAll();
    }
}