<?php
namespace App\Controllers\Admin;

use App\Models\GenreModel;

/**
 * Handles administrative operations for managing music genres.
 */
class GenresController extends BaseAdminController {
    /**
     * @var GenreModel The GenreModel instance for database operations.
     */
    private $genreModel;

    /**
     * Constructor for GenresController.
     * Initializes the GenreModel with admin privileges.
     */
    public function __construct() {
        parent::__construct();
        $this->genreModel = new GenreModel(true);
    }

    /**
     * Displays a list of all genres, including their usage count.
     * @return void
     */
    public function index() {
        $genres = $this->genreModel->getAllWithUsage();
        $this->render('admin/genres/index', ['genres' => $genres]);
    }

    /**
     * Displays the form for creating a new genre.
     * @return void
     */
    public function create() {
        $this->render('admin/genres/form', ['isEdit' => false, 'genre' => null]);
    }

    /**
     * Processes the submission of the new genre creation form.
     * Validates input, generates a slug if not provided, and attempts to save the genre.
     * Handles duplicate entry errors.
     * @return void
     */
    public function store() {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        if (empty($name)) {
            $_SESSION['error'] = 'Genre name is required.';
            $this->redirect('/admin/genres/create');
            return;
        }
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        }
        
        if (empty($slug)) {
            $_SESSION['error'] = 'Could not generate a valid slug. Please use alphanumeric characters.';
            $this->redirect('/admin/genres/create');
            return;
        }

        try {
            if ($this->genreModel->create($name, $slug)) {
                $_SESSION['success'] = 'Genre added successfully.';
            } else {
                $_SESSION['error'] = 'Failed to add genre.';
            }
        } catch (\PDOException $e) {
            $_SESSION['error'] = ($e->getCode() == 23000) ? 'Failed to add genre: This name or slug already exists.' : 'Database error.';
        }
        $this->redirect('/admin/genres');
    }

    /**
     * Displays the form for editing an existing genre.
     * Redirects to the genre list if the genre is not found.
     * @param int $id The ID of the genre to edit.
     * @return void
     */
    public function edit($id) {
        $genre = $this->genreModel->getById($id);
        if (!$genre) {
            $_SESSION['error'] = 'Genre not found.';
            $this->redirect('/admin/genres');
        }
        $this->render('admin/genres/form', ['isEdit' => true, 'genre' => $genre]);
    }

    /**
     * Processes the submission of the genre update form.
     * Validates input and attempts to update the genre's name and slug.
     * Handles duplicate entry errors.
     * @param int $id The ID of the genre to update.
     * @return void
     */
    public function update($id) {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        if (empty($name)) {
            $_SESSION['error'] = 'Genre name is required.';
            $this->redirect("/admin/genres/edit/$id");
            return;
        }
        try {
            if ($this->genreModel->update($id, $name, $slug)) {
                $_SESSION['success'] = 'Genre updated.';
            } else {
                $_SESSION['error'] = 'Update failed.';
            }
        } catch (\PDOException $e) {
            $_SESSION['error'] = ($e->getCode() == 23000) ? 'Update failed: This name or slug is already taken.' : 'Database error.';
        }
        $this->redirect('/admin/genres');
    }

    /**
     * Deletes a genre or deactivates it if it's currently in use by songs.
     *
     * @param int $id The ID of the genre to delete/deactivate.
     * @return void
     */
    public function delete($id) {
        $used = $this->genreModel->isUsed($id);
        if ($used) {
            $this->genreModel->toggleStatus($id);
            $_SESSION['success'] = 'Genre deactivated (used in songs).';
        } else {
            $this->genreModel->delete($id);
            $_SESSION['success'] = 'Genre deleted.';
        }
        $this->redirect('/admin/genres');
    }

    /**
     * Toggles the active status of a genre.
     * @param int $id The ID of the genre to toggle.
     * @return void
     */
    public function toggle($id) {
        $this->genreModel->toggleStatus($id);
        $_SESSION['success'] = 'Genre status toggled.';
        $this->redirect('/admin/genres');
    }
}