<?php
namespace App\Core;

class Controller {
    protected function render($view, $data = []) {
        extract($data);
        $viewPath = __DIR__ . "/../Views/{$view}.php";
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die("View {$view} not found");
        }
    }

    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
}