<?php
require_once __DIR__ . '/../config/config.php';

class Router {
    private $routes = [];
    
    public function add($route, $handler) {
        $this->routes[$route] = $handler;
    }
    
    public function getRoutes() {
        return $this->routes;
    }
    
    public function dispatch($uri) {
        // Get the path from URI
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Remove trailing slash except for root
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        
        console_log("🎯 Original URI: {$_SERVER['REQUEST_URI']}");
        console_log("🎯 Parsed path: $path");
        console_log("🗺️ Available routes: " . implode(', ', array_keys($this->routes)));
        
        // Direct route match
        if (isset($this->routes[$path])) {
            console_log("✅ Direct route found: $path");
            $this->executeHandler($this->routes[$path]);
            return;
        }
        
        // Try with leading slash if not present
        if (strpos($path, '/') !== 0) {
            $pathWithSlash = '/' . $path;
            if (isset($this->routes[$pathWithSlash])) {
                console_log("✅ Route found with slash: $pathWithSlash");
                $this->executeHandler($this->routes[$pathWithSlash]);
                return;
            }
        }
        
        // Try without leading slash
        if (strpos($path, '/') === 0 && $path !== '/') {
            $pathWithoutSlash = substr($path, 1);
            if (isset($this->routes['/' . $pathWithoutSlash])) {
                console_log("✅ Route found without slash: /" . $pathWithoutSlash);
                $this->executeHandler($this->routes['/' . $pathWithoutSlash]);
                return;
            }
        }
        
        console_log("❌ No route found for: $path");
        $this->notFound();
    }
    
    private function executeHandler($handler) {
        if (is_callable($handler)) {
            $handler();
        } else {
            $this->callHandler($handler);
        }
    }
    
    private function callHandler($handler) {
        list($controller, $method) = explode('@', $handler);
        $controllerFile = "controllers/{$controller}.php";
        
        console_log("🎮 Calling: {$controller}@{$method}");
        
        if (!file_exists($controllerFile)) {
            console_log("⚠️ Controller file not found: {$controllerFile}");
            $this->notFound();
            return;
        }
        
        require_once $controllerFile;
        
        if (!class_exists($controller)) {
            console_log("⚠️ Class not found: {$controller}");
            $this->notFound();
            return;
        }
        
        $controllerInstance = new $controller();
        
        if (!method_exists($controllerInstance, $method)) {
            console_log("⚠️ Method not found: {$controller}::{$method}");
            $this->notFound();
            return;
        }
        
        $controllerInstance->$method();
    }
    
    private function notFound() {
        http_response_code(404);
        console_log("🚫 404 Error - Page not found");
        echo $this->getInline404();
    }
    
    private function getInline404() {
        return '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | SANIPOINT</title>
    <style>
        body { 
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0; padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        .error-code { font-size: 6em; color: #e74c3c; margin: 0; }
        .error-message { color: #666; margin: 20px 0; }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: transform 0.3s ease;
            margin: 5px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="error-code">404</h1>
        <h2>Halaman Tidak Ditemukan</h2>
        <p class="error-message">Maaf, halaman yang Anda cari tidak dapat ditemukan.</p>
        <a href="' . APP_URL . '" class="btn">🏠 Kembali ke Beranda</a>
        <a href="' . APP_URL . 'login" class="btn">🚀 Login</a>
    </div>
    <script>
        console.log("🚫 404 - Page not found");
        console.log("🔗 Available links: Home, Login");
    </script>
</body>
</html>';
    }
}
?>