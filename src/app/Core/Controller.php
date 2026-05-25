<?php
namespace App\Core;

/**
 * Base Controller class providing common functionalities for all controllers.
 */
class Controller {
    /**
     * Renders a view file.
     *
     * @param string $view The name of the view file (without .php extension).
     * @param array $data An associative array of data to pass to the view.
     * @return void
     */
    protected function render($view, $data = []) {
        extract($data);
        $viewPath = __DIR__ . "/../Views/{$view}.php";
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die("View '{$view}' not found at {$viewPath}");
        }
    }

    /**
     * Redirects the browser to a specified URL.
     * @param string $url The URL to redirect to.
     * @return void
     */
    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
}