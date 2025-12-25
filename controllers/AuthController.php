<?php
require_once 'core/Auth.php';
require_once 'core/Security.php';

class AuthController {
    private $auth;
    private $db;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->db = Database::getInstance();
    }
    
    public function loginForm() {
        if ($this->auth->isLoggedIn()) {
            $this->redirectToDashboard();
        }
        $this->render('auth/login');
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /sanipoint/login');
            exit;
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
        $this->auth->logout();
    }
    
    private function redirectToDashboard() {
        if ($this->auth->hasRole('admin')) {
            header('Location: /sanipoint/admin/dashboard');
        } else {
            header('Location: /sanipoint/karyawan/dashboard');
        }
        exit;
    }
    
    private function render($view, $data = []) {
        extract($data);
        include "views/{$view}.php";
    }
}
?>