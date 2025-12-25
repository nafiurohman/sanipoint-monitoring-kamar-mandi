<?php
class Router {
    private $routes = [];
    
    public function add($route, $handler) {
        $this->routes[$route] = $handler;
    }
    
    public function dispatch($uri) {
        $uri = parse_url($uri, PHP_URL_PATH);
        // Remove base path if running in subdirectory
        $uri = str_replace('/sanipoint', '', $uri);
        $uri = rtrim($uri, '/');
        if ($uri === '') $uri = '/';
        
        if (isset($this->routes[$uri])) {
            $this->callHandler($this->routes[$uri]);
        } else {
            $this->notFound();
        }
    }
    
    private function callHandler($handler) {
        list($controller, $method) = explode('@', $handler);
        $controllerFile = "controllers/{$controller}.php";
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controllerInstance = new $controller();
            $controllerInstance->$method();
        } else {
            $this->notFound();
        }
    }
    
    private function notFound() {
        http_response_code(404);
        echo "404 - Page Not Found";
    }
}
?>