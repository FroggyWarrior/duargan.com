<?php
namespace App\Controllers\Admin;

use App\Models\PlatformModel;
use App\Utils\SvgSanitizer;

class PlatformsController extends BaseAdminController {
    private $platformModel;

    public function __construct() {
        parent::__construct();
        $this->platformModel = new PlatformModel(true);
    }

    public function index() {
        $platforms = $this->platformModel->getAllWithUsage();
        $this->render('admin/platforms/index', ['platforms' => $platforms]);
    }

    public function create() {
        $this->render('admin/platforms/form', ['isEdit' => false, 'platform' => null]);
    }

    public function store() {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $baseUrl = trim($_POST['base_url'] ?? '');
        $iconSvg = trim($_POST['icon_svg'] ?? '');
        $color = trim($_POST['color'] ?? '#666666');
        $displayOrder = (int)($_POST['display_order'] ?? 0);

        // Validaciones
        if (empty($name)) {
            $_SESSION['error'] = 'Platform name is required.';
            $this->redirect('/admin/platforms/create');
            return;
        }
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        }
        if (empty($iconSvg)) {
            $_SESSION['error'] = 'SVG icon is required.';
            $this->redirect('/admin/platforms/create');
            return;
        }
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            $_SESSION['error'] = 'Color must be a valid hex color (e.g., #1DB954).';
            $this->redirect('/admin/platforms/create');
            return;
        }

        // Sanitize SVG to prevent XSS
        $iconSvg = SvgSanitizer::sanitize($iconSvg);

        try {
            if ($this->platformModel->create($name, $slug, $baseUrl, $iconSvg, $color, $displayOrder)) {
                $_SESSION['success'] = 'Platform added successfully.';
            } else {
                $_SESSION['error'] = 'Failed to add platform.';
            }
        } catch (\PDOException $e) {
            $_SESSION['error'] = ($e->getCode() == 23000) ? 'Failed to add platform: A platform with this name or slug already exists.' : 'Database error.';
        }
        $this->redirect('/admin/platforms');
    }

    public function edit($id) {
        $platform = $this->platformModel->getById($id);
        if (!$platform) {
            $_SESSION['error'] = 'Platform not found.';
            $this->redirect('/admin/platforms');
        }
        $this->render('admin/platforms/form', ['isEdit' => true, 'platform' => $platform]);
    }

    public function update($id) {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $baseUrl = trim($_POST['base_url'] ?? '');
        $iconSvg = trim($_POST['icon_svg'] ?? '');
        $color = trim($_POST['color'] ?? '#666666');
        $displayOrder = (int)($_POST['display_order'] ?? 0);

        if (empty($name)) {
            $_SESSION['error'] = 'Platform name is required.';
            $this->redirect("/admin/platforms/edit/$id");
            return;
        }
        if (empty($iconSvg)) {
            $_SESSION['error'] = 'SVG icon is required.';
            $this->redirect("/admin/platforms/edit/$id");
            return;
        }
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            $_SESSION['error'] = 'Color must be a valid hex color.';
            $this->redirect("/admin/platforms/edit/$id");
            return;
        }

        // Sanitize SVG to prevent XSS
        $iconSvg = SvgSanitizer::sanitize($iconSvg);

        try {
            if ($this->platformModel->update($id, $name, $slug, $baseUrl, $iconSvg, $color, $displayOrder)) {
                $_SESSION['success'] = 'Platform updated.';
            } else {
                $_SESSION['error'] = 'Update failed.';
            }
        } catch (\PDOException $e) {
            $_SESSION['error'] = ($e->getCode() == 23000) ? 'Update failed: That name or slug is already taken.' : 'Database error.';
        }
        $this->redirect('/admin/platforms');
    }

    public function delete($id) {
        $used = $this->platformModel->isUsed($id);
        if ($used) {
            $this->platformModel->toggleStatus($id);
            $_SESSION['success'] = 'Platform deactivated (used in songs).';
        } else {
            $this->platformModel->delete($id);
            $_SESSION['success'] = 'Platform deleted.';
        }
        $this->redirect('/admin/platforms');
    }

    public function toggle($id) {
        $this->platformModel->toggleStatus($id);
        $_SESSION['success'] = 'Platform status toggled.';
        $this->redirect('/admin/platforms');
    }
}