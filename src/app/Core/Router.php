<?php
namespace App\Core;

/**
 * Core Router class responsible for mapping URLs to controllers and actions.
 */
class Router {
    /**
     * @var array List of registered routes grouped by HTTP method.
     */
    protected $routes = [];

    /**
     * Registers a new route.
     * 
     * @param string $route The URI pattern (e.g., '/songs/{id}').
     * @param string $controller The controller class name.
     * @param string $action The method name within the controller.
     * @param string $method The HTTP method (GET, POST, etc.).
     */
    public function add($route, $controller, $action, $method = 'GET') {
        $this->routes[$method][] = [
            'route' => $route,
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Dispatches the request to the appropriate controller action based on the URI.
     * 
     * @param string $uri The requested URI.
     */
    public function dispatch($uri) {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($uri, PHP_URL_PATH);

        foreach ($this->routes[$method] as $routeDef) {
            $route = $routeDef['route'];
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $controllerName = "App\\Controllers\\" . $routeDef['controller'];
                $action = $routeDef['action'];

                if (class_exists($controllerName)) {
                    $controller = new $controllerName();
                    if (method_exists($controller, $action)) {
                        // Execute the action with extracted parameters
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