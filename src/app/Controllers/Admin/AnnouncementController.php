<?php
namespace App\Controllers\Admin;

use App\Models\AnnouncementModel;

/**
 * Handles administrative operations for managing the site-wide announcement.
 */
class AnnouncementController extends BaseAdminController {
    /**
     * @var AnnouncementModel The AnnouncementModel instance for database operations.
     */
    private $announcementModel;
    /**
     * Constructor for AnnouncementController.
     * Initializes the AnnouncementModel with admin privileges.
     */
    public function __construct() {
        parent::__construct();
        $this->announcementModel = new AnnouncementModel(true);
    }

    /**
     * Displays the announcement management page, showing the current announcement status.
     * @return void
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
     * Displays the form for editing the site-wide announcement.
     * Populates the form with existing announcement data if available.
     * @return void
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
     * Processes the submission of the announcement update form.
     * Validates input data and attempts to save the announcement to the database.
     * @return void
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
     * Toggles the active status of the site-wide announcement.
     * If the announcement is active, it becomes inactive, and vice-versa.
     * @return void
     */
    public function toggle() {
        $this->announcementModel->toggle();
        $_SESSION['success'] = 'Announcement status toggled.';
        $this->redirect('/admin/announcement');
    }
}