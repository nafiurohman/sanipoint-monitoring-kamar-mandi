<?php
// Error handling configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php-errors.log');

// Security settings (must be before session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

session_start();

// Load configuration and core files
require_once 'config/config.php';
require_once 'core/Router.php';
require_once 'core/Database.php';
require_once 'core/Auth.php';
require_once 'core/Security.php';

// Console logging function
function console_log($message) {
    error_log("SANIPOINT: $message");
}

// No cache headers
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

console_log('🚀 SANIPOINT System Starting - URI: ' . $_SERVER['REQUEST_URI']);
console_log('📍 Script: ' . $_SERVER['SCRIPT_NAME']);
console_log('📁 Document Root: ' . $_SERVER['DOCUMENT_ROOT']);

$router = new Router();

// Landing page routes (React app)
require_once 'controllers/LandingController.php';
$router->add('/', 'LandingController@index');
$router->add('/landing', 'LandingController@index');

// Auth routes
require_once 'controllers/AuthController.php';
$router->add('/auth/login', 'AuthController@loginForm');
$router->add('/auth/logout', 'AuthController@logout');
$router->add('/login', 'AuthController@loginForm'); // Backward compatibility
$router->add('/logout', 'AuthController@logout'); // Backward compatibility

// Admin routes
require_once 'controllers/AdminController.php';
$router->add('/admin/dashboard', 'AdminController@dashboard');
$router->add('/admin/karyawan', 'AdminController@karyawan');
$router->add('/admin/kamar-mandi', 'AdminController@kamarMandi');
$router->add('/admin/produk', 'AdminController@produk');
$router->add('/admin/sensor', 'AdminController@sensor');
$router->add('/admin/transaksi', 'AdminController@transaksi');
$router->add('/admin/laporan', 'AdminController@laporan');
$router->add('/admin/laporan/pdf', 'AdminController@laporanPdf');
$router->add('/admin/pengaturan', function() {
    console_log('📋 Admin Settings accessed');
    require_once 'controllers/SettingsController.php';
    $controller = new SettingsController();
    $controller->index();
});

// Employee routes
require_once 'controllers/KaryawanController.php';
$router->add('/karyawan/dashboard', 'KaryawanController@dashboard');
$router->add('/karyawan/poin', 'KaryawanController@poin');
$router->add('/karyawan/riwayat', 'KaryawanController@riwayat');
$router->add('/karyawan/marketplace', 'KaryawanController@marketplace');
$router->add('/karyawan/transfer', 'KaryawanController@transfer');
$router->add('/karyawan/monitoring', 'KaryawanController@monitoring');
$router->add('/karyawan/pengaturan', function() {
    console_log('👤 Employee Settings accessed');
    require_once 'controllers/SettingsController.php';
    $controller = new SettingsController();
    $controller->index();
});

// API routes
require_once 'controllers/ApiController.php';
$router->add('/api/sensor-data', 'ApiController@sensorData');
$router->add('/api/rfid-tap', 'ApiController@rfidTap');
$router->add('/api/realtime-status', 'ApiController@realtimeStatus');

console_log('🎯 Dispatching to: ' . $_SERVER['REQUEST_URI']);
console_log('🗺️ Available routes: ' . implode(', ', array_keys($router->getRoutes())));

$router->dispatch($_SERVER['REQUEST_URI']);
?>