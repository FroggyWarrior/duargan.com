<?php
namespace App\Core;

class Router {
    protected $routes = [];

    public function add($route, $controller, $action, $method = 'GET') {
        $this->routes[$method][] = [
            'route' => $route,
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function dispatch($uri) {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($uri, PHP_URL_PATH); // Limpiar query string

        foreach ($this->routes[$method] as $routeDef) {
            $route = $routeDef['route'];
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Quitar el match completo
                $controllerName = "App\\Controllers\\" . $routeDef['controller'];
                $action = $routeDef['action'];

                if (class_exists($controllerName)) {
                    $controller = new $controllerName();
                    if (method_exists($controller, $action)) {
                        // Llamar al método con los parámetros extraídos
                        call_user_func_array([$controller, $action], $matches);
                        return;
                    }
                }
            }
        }

        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
    }
}