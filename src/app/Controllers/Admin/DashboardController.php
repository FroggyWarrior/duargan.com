<?php
namespace App\Controllers\Admin;

use App\Models\AdminSongModel;
use App\Models\GenreModel;
use App\Models\PlatformModel;
use App\Models\SongTypeModel;

/**
 * Handles administrative operations related to songs, including listing, creating,
 * editing, and deleting songs in the admin panel.
 */
class DashboardController extends BaseAdminController
{
    /**
     * @var AdminSongModel The AdminSongModel instance for song-related database operations.
     */
    private $songModel;

    /**
     * Constructor for DashboardController.
     * Calls the parent constructor for authentication and initializes AdminSongModel.
     */
    public function __construct()
    {
        parent::__construct();
        $this->songModel = new AdminSongModel();
    }

    /**
     * Displays a list of all songs in the admin panel.
     * @return void
     */
    public function index()
    {
        $songs = $this->songModel->getAllMusic();
        $this->render('admin/panel', ['songs' => $songs]);
    }

    /**
     * Displays the form for creating a new song.
     * Populates the form with all active genres, platforms, and song types.
     * @return void
     */
    public function create()
    {
        $genreModel = new GenreModel(true);
        $platformModel = new PlatformModel(true);
        $typeModel = new SongTypeModel(true);

        $allGenres = $genreModel->getAllActive();
        $allPlatforms = $platformModel->getAllActive();
        $allTypes = $typeModel->getAllActive();

        $this->render('admin/song_form', [
            'isEdit' => false,
            'song' => null,
            'allGenres' => $allGenres,
            'allPlatforms' => $allPlatforms,
            'allTypes' => $allTypes,
            'songGenres' => [],
            'songPlatformsUrls' => []
        ]);
    }

    /**
     * Processes the submission of the new song creation form.
     * Handles data validation, cover image upload, and database insertion for the song,
     * its genres, and platforms.
     * @return void
     */
    public function store()
    {
        $title = trim($_POST['title'] ?? '');
        $releaseDate = trim($_POST['release_date'] ?? '');
        $typeId = (int)($_POST['type_id'] ?? 0);
        $selectedGenres = $_POST['genres'] ?? [];
        $selectedPlatforms = $_POST['platforms'] ?? [];
        $platformUrls = $_POST['platform_urls'] ?? [];

        // Handle cover image upload
        $coverImageUrl = $this->handleCoverUpload();
        if (!$coverImageUrl && !empty($_POST['cover_image_url'])) {
            $coverImageUrl = trim($_POST['cover_image_url']);
        }

        // Validaciones
        $errors = $this->validateSongData($title, $releaseDate, $typeId, $selectedGenres, $coverImageUrl, false);
        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/admin/songs/create');
            return;
        }

        // Save song
        $songId = $this->songModel->create($title, $releaseDate, $coverImageUrl, $typeId);
        if ($songId) {
            // Sync genres
            $this->songModel->syncGenres($songId, $selectedGenres);
            // Sync platforms (only selected ones with URL)
            $platformsToSync = [];
            foreach ($selectedPlatforms as $pId) {
                if (!empty($platformUrls[$pId])) {
                    $platformsToSync[$pId] = $platformUrls[$pId];
                }
            }
            $this->songModel->syncPlatforms($songId, $platformsToSync);
            $_SESSION['success'] = 'Song added successfully.';
        } else {
            $_SESSION['error'] = 'Failed to add song.';
        }
        $this->redirect('/admin/panel');
    }

    /**
     * Displays the form for editing an existing song.
     * Populates the form with the song's current data, including its genres and platforms.
     *
     * @param int $id The ID of the song to edit.
     * @return void
     */
    public function edit($id)
    {
        $id = (int)$id;
        $song = $this->songModel->getSongById($id);
        if (!$song) {
            $_SESSION['error'] = 'Song not found.';
            $this->redirect('/admin/panel');
        }

        $genreModel = new GenreModel(true);
        $platformModel = new PlatformModel(true);
        $typeModel = new SongTypeModel(true);

        $allGenres = $genreModel->getAllActive();
        $allPlatforms = $platformModel->getAllActive();
        $allTypes = $typeModel->getAllActive();

        // Get assigned genres
        $songGenres = $this->songModel->getGenresForSong($id);

        // Get platforms with URLs
        $songPlatforms = $this->songModel->getPlatformsForSong($id);
        $platformsWithUrls = [];
        foreach ($songPlatforms as $sp) {
            $platformsWithUrls[$sp['id']] = $sp['track_url'];
        }

        $this->render('admin/song_form', [
            'isEdit' => true,
            'song' => $song,
            'allGenres' => $allGenres,
            'allPlatforms' => $allPlatforms,
            'allTypes' => $allTypes,
            'songGenres' => $songGenres,
            'songPlatformsUrls' => $platformsWithUrls
        ]);
    }

    /**
     * Processes the submission of the song update form.
     * Handles data validation, cover image upload, and database update for the song,
     * its genres, and platforms.
     * @param int $id The ID of the song to update.
     * @return void
     */
    public function update($id)
    {
        $id = (int)$id;
        $title = trim($_POST['title'] ?? '');
        $releaseDate = trim($_POST['release_date'] ?? '');
        $typeId = (int)($_POST['type_id'] ?? 0);
        $selectedGenres = $_POST['genres'] ?? [];
        $selectedPlatforms = $_POST['platforms'] ?? [];
        $platformUrls = $_POST['platform_urls'] ?? [];
        
        // Handle new image upload (if provided)
        $newCover = $this->handleCoverUpload();
        $coverImageUrl = $newCover; // If null, existing cover will be kept

        // If no file was uploaded but a URL was provided, use that URL
        if (!$newCover && !empty($_POST['cover_image_url'])) {
            $coverImageUrl = trim($_POST['cover_image_url']);
        }

        // Validaciones (para edición, la imagen no es obligatoria si ya existe)
        $errors = $this->validateSongData($title, $releaseDate, $typeId, $selectedGenres, $coverImageUrl, true);
        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect("/admin/songs/edit/{$id}");
            return;
        }

        // Update song
        $updated = $this->songModel->update($id, $title, $releaseDate, $coverImageUrl, $typeId);
        if ($updated) {
            $this->songModel->syncGenres($id, $selectedGenres);
            $platformsToSync = [];
            foreach ($selectedPlatforms as $pId) {
                if (!empty($platformUrls[$pId])) {
                    $platformsToSync[$pId] = $platformUrls[$pId];
                }
            }
            $this->songModel->syncPlatforms($id, $platformsToSync);
            $_SESSION['success'] = 'Song updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update song.';
        }
        $this->redirect('/admin/panel');
    }

    /**
     * Deletes a song from the database.
     *
     * @param int $id The ID of the song to delete.
     * @return void
     */
    public function delete($id)
    {
        $id = (int)$id;
        $deleted = $this->songModel->delete($id);
        if ($deleted) {
            $_SESSION['success'] = 'Song deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete song.';
        }
        $this->redirect('/admin/panel');
    }

    /**
     * Validates the song data submitted via a form.
     *
     * @param string $title The song title.
     * @param string $releaseDate The release date of the song.
     * @param int $typeId The ID of the song type.
     * @param array $genres An array of selected genre IDs.
     * @param string|null $cover The URL or path to the cover image, or null if none.
     * @param bool $isEdit Flag indicating if the validation is for an edit operation (cover image not mandatory).
     * @return array An array of error messages, empty if validation passes.
     */
    private function validateSongData($title, $releaseDate, $typeId, $genres, $cover, $isEdit)
    {
        $errors = [];
        if (empty($title)) {
            $errors[] = 'Song title is required.';
        }
        if (empty($releaseDate)) {
            $errors[] = 'Release date is required.';
        }
        if (empty($typeId)) {
            $errors[] = 'Please select a song type.';
        }
        if (empty($genres)) {
            $errors[] = 'Please select at least one genre.';
        }
        if (!$isEdit && empty($cover)) {
            $errors[] = 'Cover image is required for new songs. Please upload a file or provide an image URL.';
        }
        return $errors;
    }

    /**
     * Handles the upload of a cover image file.
     * Creates the upload directory if it doesn't exist.
     * @return string|null The relative path to the uploaded image (e.g., 'img/covers/abc.jpg')
     *                     or null if no file was uploaded or an error occurred.
     */
    private function handleCoverUpload()
    {
        if (!isset($_FILES['cover_image']) || $_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/img/covers/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $file = $_FILES['cover_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) {
            $_SESSION['form_errors'][] = 'Invalid image format. Allowed: jpg, jpeg, png, gif, webp.';
            return null;
        }

        $filename = uniqid() . '_' . time() . '.' . $ext;
        $target = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            return 'img/covers/' . $filename;
        } else {
            $_SESSION['form_errors'][] = 'Failed to upload image.';
            return null;
        }
    }
}