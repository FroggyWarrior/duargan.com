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
        // Detect if post_max_size was exceeded (POST request with empty arrays but content sent)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
            $_SESSION['form_errors'][] = 'The total upload size is too large for the server. Try a smaller image.';
            $this->redirect('/admin/songs/create');
            return;
        }

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

        // Validation
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
        // Detect if post_max_size was exceeded
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
            $_SESSION['form_errors'][] = 'The uploaded file is too large for the server.';
            $this->redirect("/admin/songs/edit/{$id}");
            return;
        }

        $id = (int)$id;
        $title = trim($_POST['title'] ?? '');
        $releaseDate = trim($_POST['release_date'] ?? '');
        $typeId = (int)($_POST['type_id'] ?? 0);
        $selectedGenres = $_POST['genres'] ?? [];
        $selectedPlatforms = $_POST['platforms'] ?? [];
        $platformUrls = $_POST['platform_urls'] ?? [];
        
        // Fetch old data to check for existing files
        $oldSong = $this->songModel->getSongById($id);

        // Handle new image upload (if provided)
        $newCover = $this->handleCoverUpload();
        $coverImageUrl = $newCover; // If null, existing cover will be kept

        // If no file was uploaded but a URL was provided, use that URL
        if (!$newCover && !empty($_POST['cover_image_url'])) {
            $coverImageUrl = trim($_POST['cover_image_url']);
        }

        // Validation
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
            // If the cover was changed (via upload or URL), delete the old local file if it exists
            $coverChanged = ($coverImageUrl !== null && $oldSong && $oldSong['cover_image_url'] !== $coverImageUrl);
            
            if ($coverChanged && !empty($oldSong['cover_image_url'])) {
                $this->deleteLocalFile($oldSong['cover_image_url']);
            }

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
        $song = $this->songModel->getSongById($id);
        
        $deleted = $this->songModel->delete($id);
        if ($deleted) {
            // Delete the image file from server if it exists locally
            if ($song && !empty($song['cover_image_url'])) {
                $this->deleteLocalFile($song['cover_image_url']);
            }
            $_SESSION['success'] = 'Song deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete song.';
        }
        $this->redirect('/admin/panel');
    }

    /**
     * Deletes a local file from the server.
     * 
     * @param string $path The relative path stored in the database.
     * @return void
     */
    private function deleteLocalFile($path) {
        // Don't try to delete if it's an external URL
        if (empty($path) || preg_match('/^https?:\/\//', $path)) return;
        
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($path, '/');
        if (file_exists($fullPath) && is_file($fullPath)) {
            unlink($fullPath);
        }
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
     * Handles the upload and processing of a cover image file.
     * Compresses and resizes the image for optimal performance while maintaining quality.
     * Creates the upload directory if it doesn't exist.
     * @return string|null The relative path to the uploaded image (e.g., 'img/covers/abc.jpg')
     *                     or null if no file was uploaded or an error occurred.
     */
    private function handleCoverUpload()
    {
        if (!isset($_FILES['cover_image'])) {
            return null;
        }

        $file = $_FILES['cover_image'];

        // If no file was uploaded and no error occurred (standard empty input)
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        // Handle specific PHP upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
                $_SESSION['form_errors'][] = 'The image file is too large. Max allowed by server: ' . ini_get('upload_max_filesize') . '.';
            } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                $_SESSION['form_errors'][] = 'An error occurred during image upload (Error code: ' . $file['error'] . ').';
            }
            return null;
        }

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/img/covers/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed)) {
            $_SESSION['form_errors'][] = 'Invalid image format. Allowed: jpg, jpeg, png, gif, webp.';
            return null;
        }

        // Determine target extension and format based on system capabilities
        $useWebp = function_exists('imagewebp');
        $targetExt = $useWebp ? 'webp' : 'jpg';
        
        $filename = uniqid() . '_' . time() . '.' . $targetExt;
        $target = $uploadDir . $filename;

        if ($this->processImage($file['tmp_name'], $target, $targetExt)) {
            return 'img/covers/' . $filename;
        } else {
            $_SESSION['form_errors'][] = 'Failed to process image. Please try a different file.';
            return null;
        }
    }

    /**
     * Resizes and compresses an image using the PHP GD library.
     * Standardizes high-resolution uploads to a manageable size suitable for high-res displays.
     * 
     * @param string $sourcePath Path to the temporary uploaded file.
     * @param string $destinationPath Path where the processed image will be saved.
     * @param string $format The output format ('webp' or 'jpg').
     * @param int $maxDim Maximum width or height.
     * @param int $quality Compression quality (0-100).
     * @return bool True on success, false on failure.
     */
    private function processImage($sourcePath, $destinationPath, $format = 'webp', $maxDim = 900, $quality = 85)
    {
        if (!extension_loaded('gd')) {
            return move_uploaded_file($sourcePath, $destinationPath);
        }

        $info = getimagesize($sourcePath);
        if (!$info) return false;

        list($width, $height, $type) = $info;

        switch ($type) {
            case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($sourcePath); break;
            case IMAGETYPE_PNG:  $src = imagecreatefrompng($sourcePath); break;
            case IMAGETYPE_WEBP: $src = function_exists('imagecreatefromwebp') ? imagecreatefromwebp($sourcePath) : false; break;
            case IMAGETYPE_GIF:  $src = imagecreatefromgif($sourcePath); break;
            default: return false;
        }

        if (!$src) return false;

        // Calculate aspect-ratio-friendly dimensions
        $newWidth = $width;
        $newHeight = $height;

        if ($width > $maxDim || $height > $maxDim) {
            $ratio = $width / $height;
            if ($ratio > 1) {
                $newWidth = $maxDim;
                $newHeight = (int)($maxDim / $ratio);
            } else {
                $newHeight = $maxDim;
                $newWidth = (int)($maxDim * $ratio);
            }
        }

        $dst = imagecreatetruecolor($newWidth, $newHeight);
        
        // Handle transparency for PNG and WebP
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Output to standardized high-compression format
        if ($format === 'webp' && function_exists('imagewebp')) {
            $success = imagewebp($dst, $destinationPath, $quality);
        } else {
            $success = imagejpeg($dst, $destinationPath, $quality);
        }

        imagedestroy($src);
        imagedestroy($dst);

        return $success;
    }
}