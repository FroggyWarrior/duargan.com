<?php
namespace App\Controllers\Admin;

use App\Models\SongTypeModel;

class SongTypesController extends BaseAdminController {
    private $typeModel;

    public function __construct() {
        parent::__construct();
        $this->typeModel = new SongTypeModel(true);
    }

    public function index() {
        $types = $this->typeModel->getAllWithUsage();
        $this->render('admin/song_types/index', ['types' => $types]);
    }

    public function create() {
        $this->render('admin/song_types/form', ['isEdit' => false, 'type' => null]);
    }

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

    public function edit($id) {
        $type = $this->typeModel->getById($id);
        if (!$type) {
            $_SESSION['error'] = 'Song type not found.';
            $this->redirect('/admin/song-types');
        }
        $this->render('admin/song_types/form', ['isEdit' => true, 'type' => $type]);
    }

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

    public function toggle($id) {
        $this->typeModel->toggleStatus($id);
        $_SESSION['success'] = 'Song type status toggled.';
        $this->redirect('/admin/song-types');
    }
}