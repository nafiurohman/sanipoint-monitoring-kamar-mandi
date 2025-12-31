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
require_once 'config/config.php';
require_once 'core/Router.php';
require_once 'core/Database.php';
require_once 'core/Auth.php';
require_once 'core/Security.php';

// No cache headers
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$router = new Router();

// Public routes
$router->add('/login', 'AuthController@loginForm');
$router->add('/logout', 'AuthController@logout');

// Admin routes
$router->add('/admin/dashboard', 'AdminController@dashboard');
$router->add('/admin/karyawan', 'AdminController@karyawan');
$router->add('/admin/kamar-mandi', 'AdminController@kamarMandi');
$router->add('/admin/produk', 'AdminController@produk');
$router->add('/admin/sensor', 'AdminController@sensor');
$router->add('/admin/transaksi', 'AdminController@transaksi');
$router->add('/admin/laporan', 'AdminController@laporan');
$router->add('/admin/laporan/pdf', 'AdminController@laporanPdf');

// Employee routes
$router->add('/karyawan/dashboard', 'KaryawanController@dashboard');
$router->add('/karyawan/poin', 'KaryawanController@poin');
$router->add('/karyawan/riwayat', 'KaryawanController@riwayat');
$router->add('/karyawan/marketplace', 'KaryawanController@marketplace');
$router->add('/karyawan/transfer', 'KaryawanController@transfer');
$router->add('/karyawan/monitoring', 'KaryawanController@monitoring');
$router->add('/karyawan/pengaturan', 'KaryawanController@pengaturan');

// API routes
$router->add('/api/sensor-data', 'ApiController@sensorData');
$router->add('/api/rfid-tap', 'ApiController@rfidTap');
$router->add('/api/realtime-status', 'ApiController@realtimeStatus');

$router->dispatch($_SERVER['REQUEST_URI']);
?>