<?php
namespace App\Controllers\Admin;

use App\Models\SongTypeModel;

/**
 * Handles administrative operations for managing song types.
 */
class SongTypesController extends BaseAdminController {
    /**
     * @var SongTypeModel The SongTypeModel instance for database operations.
     */
    private $typeModel;

    /**
     * Constructor for SongTypesController.
     * Initializes the SongTypeModel with admin privileges.
     */
    public function __construct() {
        parent::__construct();
        $this->typeModel = new SongTypeModel(true);
    }

    /**
     * Displays a list of all song types, including their usage count.
     * @return void
     */
    public function index() {
        $types = $this->typeModel->getAllWithUsage();
        $this->render('admin/song_types/index', ['types' => $types]);
    }

    /**
     * Displays the form for creating a new song type.
     * @return void
     */
    public function create() {
        $this->render('admin/song_types/form', ['isEdit' => false, 'type' => null]);
    }

    /**
     * Processes the submission of the new song type creation form.
     * Validates input, generates a slug if not provided, and attempts to save the song type.
     * Handles duplicate entry errors.
     * @return void
     */
    public function store() {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        if (empty($name)) {
            $_SESSION['error'] = 'Song type name is required.';
            $this->redirect('/admin/song-types/create');
            return;
        }
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        }
        try {
            if ($this->typeModel->create($name, $slug)) {
                $_SESSION['success'] = 'Song type added successfully.';
            } else {
                $_SESSION['error'] = 'Failed to add song type.';
            }
        } catch (\PDOException $e) {
            $_SESSION['error'] = ($e->getCode() == 23000) ? 'Failed to add type: This name or slug already exists.' : 'Database error.';
        }
        $this->redirect('/admin/song-types');
    }

    /**
     * Displays the form for editing an existing song type.
     * Redirects to the song type list if the type is not found.
     * @param int $id The ID of the song type to edit.
     * @return void
     */
    public function edit($id) {
        $type = $this->typeModel->getById($id);
        if (!$type) {
            $_SESSION['error'] = 'Song type not found.';
            $this->redirect('/admin/song-types');
        }
        $this->render('admin/song_types/form', ['isEdit' => true, 'type' => $type]);
    }

    /**
     * Processes the submission of the song type update form.
     * Validates input and attempts to update the song type's name and slug.
     * Handles duplicate entry errors.
     * @param int $id The ID of the song type to update.
     * @return void
     */
    public function update($id) {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        if (empty($name)) {
            $_SESSION['error'] = 'Song type name is required.';
            $this->redirect("/admin/song-types/edit/$id");
            return;
        }
        try {
            if ($this->typeModel->update($id, $name, $slug)) {
                $_SESSION['success'] = 'Song type updated.';
            } else {
                $_SESSION['error'] = 'Update failed.';
            }
        } catch (\PDOException $e) {
            $_SESSION['error'] = ($e->getCode() == 23000) ? 'Update failed: This name or slug is already taken.' : 'Database error.';
        }
        $this->redirect('/admin/song-types');
    }

    /**
     * Deletes a song type or deactivates it if it's currently in use by songs.
     *
     * @param int $id The ID of the song type to delete/deactivate.
     * @return void
     */
    public function delete($id) {
        $used = $this->typeModel->isUsed($id);
        if ($used) {
            $this->typeModel->toggleStatus($id);
            $_SESSION['success'] = 'Song type deactivated (used in songs).';
        } else {
            $this->typeModel->delete($id);
            $_SESSION['success'] = 'Song type deleted.';
        }
        $this->redirect('/admin/song-types');
    }

    /**
     * Toggles the active status of a song type.
     * @param int $id The ID of the song type to toggle.
     * @return void
     */
    public function toggle($id) {
        $this->typeModel->toggleStatus($id);
        $_SESSION['success'] = 'Song type status toggled.';
        $this->redirect('/admin/song-types');
    }
}