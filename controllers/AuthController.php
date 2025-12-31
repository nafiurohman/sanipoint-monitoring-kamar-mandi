<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../config/config.php';

class AuthController {
    private $auth;
    private $db;
    
    public function __construct() {
        console_log('🔐 AuthController initialized');
        $this->auth = new Auth();
        $this->db = Database::getInstance();
    }
    
    public function loginForm() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->login();
        }
        
        if ($this->auth->isLoggedIn()) {
            $this->redirectToDashboard();
        }
        $this->render('auth/login');
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToLogin();
        }
        
        $username = Security::sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $csrf_token = $_POST['csrf_token'] ?? '';
        
        if (!Security::validateCSRFToken($csrf_token)) {
            $this->render('auth/login', ['error' => 'Invalid security token']);
            return;
        }
        
        if ($this->auth->login($username, $password)) {
            $this->redirectToDashboard();
        } else {
            $this->render('auth/login', ['error' => 'Invalid username or password']);
        }
    }
    
    public function logout() {
        console_log('🚪 User logging out');
        $this->auth->logout();
        header('Location: ' . APP_URL);
        exit;
    }
    
    private function redirectToDashboard() {
        if ($this->auth->hasRole('admin')) {
            header('Location: ' . APP_URL . 'admin/dashboard');
        } else {
            header('Location: ' . APP_URL . 'karyawan/dashboard');
        }
        exit;
    }
    
    private function redirectToLogin() {
        header('Location: ' . APP_URL . 'login');
        exit;
    }
    
    private function render($view, $data = []) {
        extract($data);
        include "views/{$view}.php";
    }
}
?>