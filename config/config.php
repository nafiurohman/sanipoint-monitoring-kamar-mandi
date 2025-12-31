<?php
// Application configuration
define('APP_KEY', 'sanipoint_2024_secure_key_xyz789');

define('TIMEZONE', 'Asia/Jakarta');

// Database configuration production
// define('APP_URL', 'https://app-sanipoint.beznlabs.web.id/');
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'beznwebi_beznlabs_storage_241204_250305');
// define('DB_USER', 'beznwebi_beznlabs_storage_241204_250305_user');
// define('DB_PASS', 'beznlabs_storage_241204_250305_pw');

// Database configuration development
define('APP_URL', 'http://localhost/sanipoint/');
define('DB_HOST', 'localhost');
define('DB_NAME', 'sanipoint_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// UUID Generator function
if (!function_exists('generateUUID')) {
    function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
?>