<?php
require_once __DIR__ . '/../config/config.php';

class Auth {
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function login($username, $password) {
        self::init();
        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT * FROM users WHERE username = ? AND is_active = 1",
            [$username]
        );
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    }
    
    public static function logout() {
        self::init();
        session_destroy();
        header('Location: ../login.php');
        exit;
    }
    
    public static function isLoggedIn() {
        self::init();
        return isset($_SESSION['user_id']);
    }
    
    public static function getUser() {
        self::init();
        if (!self::isLoggedIn()) return null;
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }
    
    public static function hasRole($role) {
        self::init();
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    public static function requireAuth() {
        self::init();
        if (!self::isLoggedIn()) {
            header('Location: ../login.php');
            exit;
        }
    }
    
    public static function requireRole($role) {
        self::init();
        if (!self::isLoggedIn()) {
            header('Location: ../login.php');
            exit;
        }
        
        if (!self::hasRole($role)) {
            http_response_code(403);
            die('Access denied. Insufficient permissions.');
        }
    }
}
?>