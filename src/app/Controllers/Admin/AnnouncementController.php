<?php
namespace App\Controllers\Admin;

use App\Models\AnnouncementModel;

class AnnouncementController extends BaseAdminController {
    private $announcementModel;

    public function __construct() {
        parent::__construct();
        $this->announcementModel = new AnnouncementModel(true);
    }

    /**
     * Muestra la página de gestión del anuncio
     */
    public function index() {
        $announcement = $this->announcementModel->get();
        $isConfigured = $announcement && trim($announcement['title'] ?? '') !== '';
        $this->render('admin/announcement/index', [
            'announcement' => $announcement,
            'isConfigured' => $isConfigured
        ]);
    }

    /**
     * Muestra el formulario de edición
     */
    public function edit() {
        $announcement = $this->announcementModel->get();
        $isEdit = $announcement && trim($announcement['title'] ?? '') !== '';
        $this->render('admin/announcement/form', [
            'announcement' => $announcement,
            'isEdit' => $isEdit
        ]);
    }

    /**
     * Procesa el formulario de guardado
     */
    public function update() {
        $title = trim($_POST['title'] ?? '');
        $backgroundColor = trim($_POST['background_color'] ?? '#6750a4');
        $text = trim($_POST['text'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if (empty($title)) $errors[] = 'Title is required.';
        if (empty($text)) $errors[] = 'Announcement text is required.';
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $backgroundColor)) {
            $errors[] = 'Background color must be a valid hex color (e.g., #6750a4).';
        }

        if (!empty($errors)) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/admin/announcement/edit');
            return;
        }

        if ($this->announcementModel->save($title, $backgroundColor, $text, $isActive)) {
            $_SESSION['success'] = 'Announcement saved successfully.';
        } else {
            $_SESSION['error'] = 'Failed to save announcement.';
        }
        $this->redirect('/admin/announcement');
    }

    /**
     * Alterna el estado activo/inactivo
     */
    public function toggle() {
        $this->announcementModel->toggle();
        $_SESSION['success'] = 'Announcement status toggled.';
        $this->redirect('/admin/announcement');
    }
}