<?php
namespace App\Controllers\Admin;

use App\Models\SocialMediaModel;
use App\Utils\SvgSanitizer;

class SocialMediaController extends BaseAdminController {
    private $socialModel;

    public function __construct() {
        parent::__construct();
        $this->socialModel = new SocialMediaModel(true);
    }

    public function index() {
        $platforms = $this->socialModel->getAll();
        $this->render('admin/social_media/index', ['platforms' => $platforms]);
    }

    public function create() {
        $this->render('admin/social_media/form', ['isEdit' => false, 'platform' => null]);
    }

    public function store() {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $baseUrl = trim($_POST['base_url'] ?? '');
        $iconSvg = trim($_POST['icon_svg'] ?? '');
        $displayOrder = (int)($_POST['display_order'] ?? 0);

        if (empty($name)) {
            $_SESSION['error'] = 'Social media name is required.';
            $this->redirect('/admin/social-media/create');
            return;
        }
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        }
        if (empty($iconSvg)) {
            $_SESSION['error'] = 'SVG icon is required.';
            $this->redirect('/admin/social-media/create');
            return;
        }

        // Sanitize SVG to prevent XSS
        $iconSvg = SvgSanitizer::sanitize($iconSvg);

        try {
            if ($this->socialModel->create($name, $slug, $baseUrl, $iconSvg, $displayOrder)) {
                $_SESSION['success'] = 'Social media platform added successfully.';
            } else {
                $_SESSION['error'] = 'Failed to add platform.';
            }
        } catch (\PDOException $e) {
            // Check for duplicate entry SQL state (23000)
            $_SESSION['error'] = ($e->getCode() == 23000) 
                ? 'Failed to add platform: A platform with this name or slug already exists.' 
                : 'Database error: ' . $e->getMessage();
        }
        $this->redirect('/admin/social-media');
    }

    public function edit($id) {
        $platform = $this->socialModel->getById($id);
        if (!$platform) {
            $_SESSION['error'] = 'Platform not found.';
            $this->redirect('/admin/social-media');
        }
        $this->render('admin/social_media/form', ['isEdit' => true, 'platform' => $platform]);
    }

    public function update($id) {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $baseUrl = trim($_POST['base_url'] ?? '');
        $iconSvg = trim($_POST['icon_svg'] ?? '');
        $displayOrder = (int)($_POST['display_order'] ?? 0);

        if (empty($name)) {
            $_SESSION['error'] = 'Name is required.';
            $this->redirect("/admin/social-media/edit/$id");
            return;
        }
        if (empty($iconSvg)) {
            $_SESSION['error'] = 'SVG icon is required.';
            $this->redirect("/admin/social-media/edit/$id");
            return;
        }

        // Sanitize SVG to prevent XSS
        $iconSvg = SvgSanitizer::sanitize($iconSvg);

        try {
            if ($this->socialModel->update($id, $name, $slug, $baseUrl, $iconSvg, $displayOrder)) {
                $_SESSION['success'] = 'Platform updated.';
            } else {
                $_SESSION['error'] = 'Update failed.';
            }
        } catch (\PDOException $e) {
            $_SESSION['error'] = ($e->getCode() == 23000) ? 'Update failed: That name or slug is already taken.' : 'Database error.';
        }
        $this->redirect('/admin/social-media');
    }

    public function delete($id) {
        $this->socialModel->delete($id);
        $_SESSION['success'] = 'Platform deleted.';
        $this->redirect('/admin/social-media');
    }

    public function toggle($id) {
        $this->socialModel->toggleStatus($id);
        $_SESSION['success'] = 'Platform status toggled.';
        $this->redirect('/admin/social-media');
    }
}