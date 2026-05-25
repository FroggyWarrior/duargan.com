<?php
namespace App\Controllers\Admin;

use App\Models\PlatformModel;
use App\Utils\SvgSanitizer;

/**
 * Handles administrative operations for managing music platforms.
 */
class PlatformsController extends BaseAdminController {
    /**
     * @var PlatformModel The PlatformModel instance for database operations.
     */
    private $platformModel;

    /**
     * Constructor for PlatformsController.
     * Initializes the PlatformModel with admin privileges.
     */
    public function __construct() {
        parent::__construct();
        $this->platformModel = new PlatformModel(true);
    }

    /**
     * Displays a list of all music platforms, including their usage count.
     * @return void
     */
    public function index() {
        $platforms = $this->platformModel->getAllWithUsage();
        $this->render('admin/platforms/index', ['platforms' => $platforms]);
    }

    /**
     * Displays the form for creating a new music platform.
     * @return void
     */
    public function create() {
        $this->render('admin/platforms/form', ['isEdit' => false, 'platform' => null]);
    }

    /**
     * Processes the submission of the new platform creation form.
     * Validates input, generates a slug if not provided, sanitizes SVG, and attempts to save the platform.
     * Handles duplicate entry errors.
     * @return void
     */
    public function store() {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $baseUrl = trim($_POST['base_url'] ?? '');
        $iconSvg = trim($_POST['icon_svg'] ?? '');
        $color = trim($_POST['color'] ?? '#666666');
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        
        // Validations
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

    /**
     * Displays the form for editing an existing music platform.
     * Redirects to the platform list if the platform is not found.
     * @param int $id The ID of the platform to edit.
     * @return void
     */
    public function edit($id) {
        $platform = $this->platformModel->getById($id);
        if (!$platform) {
            $_SESSION['error'] = 'Platform not found.';
            $this->redirect('/admin/platforms');
        }
        $this->render('admin/platforms/form', ['isEdit' => true, 'platform' => $platform]);
    }

    /**
     * Processes the submission of the platform update form.
     * Validates input, sanitizes SVG, and attempts to update the platform's details.
     * Handles duplicate entry errors.
     * @param int $id The ID of the platform to update.
     * @return void
     */
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

    /**
     * Deletes a platform or deactivates it if it's currently in use by songs.
     *
     * @param int $id The ID of the platform to delete/deactivate.
     * @return void
     */
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

    /**
     * Toggles the active status of a platform.
     * @param int $id The ID of the platform to toggle.
     * @return void
     */
    public function toggle($id) {
        $this->platformModel->toggleStatus($id);
        $_SESSION['success'] = 'Platform status toggled.';
        $this->redirect('/admin/platforms');
    }
}