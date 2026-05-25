<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Model for public song retrieval.
 */
class SongModel {
    /**
     * @var PDO Database connection instance.
     */
    protected $db;

    /**
     * Constructor for SongModel.
     * Initializes the standard database connection.
     */
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Fetches the latest songs categorized as 'official' releases.
     * Includes associated platform data for each song.
     * 
     * @return array List of official release songs with platform data.
     */
    public function getOfficialReleases() {
        $stmt = $this->db->query("
            SELECT s.*, st.name as type_name 
            FROM songs s 
            JOIN song_types st ON s.type_id = st.id 
            WHERE st.slug = 'official' 
            ORDER BY s.release_date DESC
        ");
        $releases = $stmt->fetchAll();

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
        unset($release);

        return $releases;
    }

    /**
     * Fetches all songs with their associated genres and types for the Music page.
     * 
     * @return array List of all songs with genres and platform data.
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
     * 
     * @param int $id The song ID.
     * @return array|null The song data with details or null if not found.
     */
    public function getSongById($id) {
        $stmt = $this->db->prepare("
            SELECT s.*, st.name as type_name, st.slug as type_slug
            FROM songs s 
            LEFT JOIN song_types st ON s.type_id = st.id 
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $song = $stmt->fetch();

        if (!$song) return null;

        $genre_stmt = $this->db->prepare("
            SELECT g.name, g.slug 
            FROM song_genres sg 
            JOIN genres g ON sg.genre_id = g.id 
            WHERE sg.song_id = ? AND g.is_active = TRUE
            ORDER BY g.name
        ");
        $genre_stmt->execute([$id]);
        $song['genres'] = $genre_stmt->fetchAll();

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
     * 
     * @param int $excludeId The ID of the current song to exclude.
     * @param int $limit Number of songs to return.
     * @return array List of basic song information.
     */
    public function getOtherSongs($excludeId, $limit = 4) {
        $stmt = $this->db->prepare("
            SELECT id, title, cover_image_url, release_date
            FROM songs 
            WHERE id != ? 
            ORDER BY release_date DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $excludeId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Fetches other songs excluding the current one, including detailed genre and platform data.
     * 
     * @param int $excludeId The ID of the current song to exclude.
     * @param int $limit Number of songs to return.
     * @return array List of songs with full details.
     */
    public function getOtherSongsDetailed($excludeId, $limit = 4) {
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

        foreach ($songs as &$song) {
            $genreStmt = $this->db->prepare("
                SELECT g.name, g.slug
                FROM song_genres sg
                JOIN genres g ON sg.genre_id = g.id
                WHERE sg.song_id = ? AND g.is_active = TRUE
                ORDER BY g.name
            ");
            $genreStmt->execute([$song['id']]);
            $song['genres'] = $genreStmt->fetchAll();

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

    /**
     * Retrieves all active genres for navigation or filtering.
     * 
     * @return array List of active genres.
     */
    public function getAllGenres() {
        return $this->db->query("SELECT * FROM genres WHERE is_active = TRUE ORDER BY name")->fetchAll();
    }

    /**
     * Retrieves all active song types for navigation or filtering.
     * 
     * @return array List of active song types.
     */
    public function getAllTypes() {
        return $this->db->query("SELECT * FROM song_types WHERE is_active = TRUE ORDER BY name")->fetchAll();
    }
}