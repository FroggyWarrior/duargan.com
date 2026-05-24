<?php
namespace App\Controllers\Admin;

use App\Models\GenreModel;

class GenresController extends BaseAdminController {
    private $genreModel;

    public function __construct() {
        parent::__construct();
        $this->genreModel = new GenreModel(true);
    }

    public function index() {
        $genres = $this->genreModel->getAllWithUsage();
        $this->render('admin/genres/index', ['genres' => $genres]);
    }

    public function create() {
        $this->render('admin/genres/form', ['isEdit' => false, 'genre' => null]);
    }

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

    public function edit($id) {
        $genre = $this->genreModel->getById($id);
        if (!$genre) {
            $_SESSION['error'] = 'Genre not found.';
            $this->redirect('/admin/genres');
        }
        $this->render('admin/genres/form', ['isEdit' => true, 'genre' => $genre]);
    }

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

    public function delete($id) {
        $used = $this->genreModel->isUsed($id);
        if ($used) {
            // Soft delete: deactivate instead of hard delete
            $this->genreModel->toggleStatus($id);
            $_SESSION['success'] = 'Genre deactivated (used in songs).';
        } else {
            $this->genreModel->delete($id);
            $_SESSION['success'] = 'Genre deleted.';
        }
        $this->redirect('/admin/genres');
    }

    public function toggle($id) {
        $this->genreModel->toggleStatus($id);
        $_SESSION['success'] = 'Genre status toggled.';
        $this->redirect('/admin/genres');
    }
}