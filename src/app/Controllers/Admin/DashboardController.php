<?php
namespace App\Controllers\Admin;

use App\Models\AdminSongModel;
use App\Models\GenreModel;
use App\Models\PlatformModel;
use App\Models\SongTypeModel;

class DashboardController extends BaseAdminController
{
    private $songModel;

    public function __construct()
    {
        parent::__construct();
        $this->songModel = new AdminSongModel(); // usa conexión admin
    }

    /**
     * Listado de canciones
     */
    public function index()
    {
        // Reutilizamos el método getAllMusic() del SongModel público
        $songs = $this->songModel->getAllMusic();
        $this->render('admin/panel', ['songs' => $songs]);
    }

    /**
     * Muestra formulario para crear nueva canción
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
     * Procesa el formulario de creación
     */
    public function store()
    {
        $title = trim($_POST['title'] ?? '');
        $releaseDate = trim($_POST['release_date'] ?? '');
        $typeId = (int)($_POST['type_id'] ?? 0);
        $selectedGenres = $_POST['genres'] ?? [];
        $selectedPlatforms = $_POST['platforms'] ?? [];
        $platformUrls = $_POST['platform_urls'] ?? [];

        // Manejo de imagen de portada
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

        // Guardar canción
        $songId = $this->songModel->create($title, $releaseDate, $coverImageUrl, $typeId);
        if ($songId) {
            // Sincronizar géneros
            $this->songModel->syncGenres($songId, $selectedGenres);
            // Sincronizar plataformas (solo las seleccionadas con URL)
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
     * Muestra formulario de edición
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

        // Obtener géneros asignados
        $songGenres = $this->songModel->getGenresForSong($id);

        // Obtener plataformas con URLs
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
     * Procesa la actualización de una canción
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

        // Manejo de nueva imagen (si se subió)
        $newCover = $this->handleCoverUpload();
        $coverImageUrl = $newCover; // si es null, se mantendrá la existente

        // Si no se subió archivo pero se envió URL, usar esa URL
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

        // Actualizar canción
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
     * Elimina una canción
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

    // -------------------------------------------------------------------------
    // Métodos privados auxiliares
    // -------------------------------------------------------------------------

    /**
     * Valida los datos del formulario de canción
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
     * Maneja la subida de la imagen de portada.
     * Retorna la ruta relativa (ej: 'img/covers/abc.jpg') o null si no se subió archivo.
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