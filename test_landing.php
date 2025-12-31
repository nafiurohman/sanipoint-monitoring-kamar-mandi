<?php
// Simple test to verify landing page functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 SANIPOINT Landing Test</h1>";

// Test 1: Check if files exist
echo "<h2>📁 File Checks:</h2>";
$files = [
    'index.php' => file_exists('index.php'),
    '.htaccess' => file_exists('.htaccess'),
    'config/config.php' => file_exists('config/config.php'),
    'core/Router.php' => file_exists('core/Router.php'),
    'controllers/LandingController.php' => file_exists('controllers/LandingController.php'),
    'landing/index.html' => file_exists('landing/index.html'),
];

foreach ($files as $file => $exists) {
    $status = $exists ? '✅' : '❌';
    echo "<p>$status $file</p>";
}

// Test 2: Check landing assets
echo "<h2>🎨 Landing Assets:</h2>";
$assets = [
    'landing/assets/index-BKOe9sSv.js' => file_exists('landing/assets/index-BKOe9sSv.js'),
    'landing/assets/index-C_7sqhOD.css' => file_exists('landing/assets/index-C_7sqhOD.css'),
];

foreach ($assets as $asset => $exists) {
    $status = $exists ? '✅' : '❌';
    echo "<p>$status $asset</p>";
}

// Test 3: Check config
echo "<h2>⚙️ Configuration:</h2>";
if (file_exists('config/config.php')) {
    require_once 'config/config.php';
    echo "<p>✅ Config loaded</p>";
    echo "<p>📊 DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "</p>";
    echo "<p>📊 DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "</p>";
} else {
    echo "<p>❌ Config not found</p>";
}

// Test 4: Test router
echo "<h2>🛣️ Router Test:</h2>";
if (file_exists('core/Router.php')) {
    require_once 'core/Router.php';
    $router = new Router();
    $router->add('/', 'LandingController@index');
    echo "<p>✅ Router initialized</p>";
    echo "<p>🎯 Root route added</p>";
} else {
    echo "<p>❌ Router not found</p>";
}

// Test 5: Check server info
echo "<h2>🖥️ Server Info:</h2>";
echo "<p>📍 Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>📍 Script Name: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>📍 Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>📍 Base Path: " . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . "</p>";

echo "<hr>";
echo "<p><a href='/' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Test Landing Page</a></p>";
echo "<p><a href='/auth/login' style='background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔐 Test Login</a></p>";
?>