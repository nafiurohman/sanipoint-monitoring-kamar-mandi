<?php
require_once __DIR__ . '/../config/config.php';

class LandingController {
    
    public function index() {
        // Redirect to login using APP_URL
        header('Location: ' . APP_URL . 'login');
        exit();
    }
}
?>