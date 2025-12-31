<?php
// SANIPOINT Diagnostic Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><title>🔧 SANIPOINT Diagnostic</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";
echo "</head><body>";

echo "<h1>🔧 SANIPOINT System Diagnostic</h1>";

// Test 1: Basic PHP Info
echo "<h2>📊 PHP Environment</h2>";
echo "<p class='ok'>✅ PHP Version: " . PHP_VERSION . "</p>";
echo "<p class='ok'>✅ Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Test 2: File Structure
echo "<h2>📁 File Structure Check</h2>";
$requiredFiles = [
    'index.php',
    '.htaccess', 
    'config/config.php',
    'core/Router.php',
    'core/Database.php',
    'controllers/LandingController.php',
    'controllers/AuthController.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "<p class='ok'>✅ $file</p>";
    } else {
        echo "<p class='error'>❌ $file - MISSING!</p>";
    }
}

// Test 3: Landing Page Assets
echo "<h2>🎨 Landing Page Assets</h2>";
if (file_exists('landing/index.html')) {
    echo "<p class='ok'>✅ landing/index.html exists</p>";
    
    $landingAssets = [
        'landing/assets/index-BKOe9sSv.js',
        'landing/assets/index-C_7sqhOD.css'
    ];
    
    foreach ($landingAssets as $asset) {
        if (file_exists($asset)) {
            echo "<p class='ok'>✅ $asset</p>";
        } else {
            echo "<p class='warning'>⚠️ $asset - Missing (React app may not work)</p>";
        }
    }
} else {
    echo "<p class='warning'>⚠️ landing/index.html not found - will show placeholder</p>";
}

// Test 4: Configuration
echo "<h2>⚙️ Configuration Test</h2>";
try {
    require_once 'config/config.php';
    echo "<p class='ok'>✅ Config loaded successfully</p>";
    echo "<p>📊 DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "</p>";
    echo "<p>📊 DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'Not defined') . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Config error: " . $e->getMessage() . "</p>";
}

// Test 5: Router Test
echo "<h2>🛣️ Router Test</h2>";
try {
    require_once 'core/Router.php';
    $router = new Router();
    $router->add('/', 'LandingController@index');
    $router->add('/test', function() { echo "Test route works!"; });
    echo "<p class='ok'>✅ Router initialized</p>";
    echo "<p class='ok'>✅ Routes added successfully</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Router error: " . $e->getMessage() . "</p>";
}

// Test 6: Server Variables
echo "<h2>🖥️ Server Environment</h2>";
echo "<pre>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";
echo "SERVER_NAME: " . $_SERVER['SERVER_NAME'] . "\n";
echo "Base Path: " . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . "\n";
echo "</pre>";

// Test 7: .htaccess Test
echo "<h2>🔧 .htaccess Test</h2>";
if (file_exists('.htaccess')) {
    echo "<p class='ok'>✅ .htaccess exists</p>";
    $htaccess = file_get_contents('.htaccess');
    if (strpos($htaccess, 'RewriteEngine On') !== false) {
        echo "<p class='ok'>✅ URL rewriting enabled</p>";
    } else {
        echo "<p class='error'>❌ URL rewriting not configured</p>";
    }
} else {
    echo "<p class='error'>❌ .htaccess missing</p>";
}

// Test 8: Permissions
echo "<h2>🔐 Permissions Check</h2>";
if (is_readable('.')) {
    echo "<p class='ok'>✅ Directory readable</p>";
} else {
    echo "<p class='error'>❌ Directory not readable</p>";
}

if (is_writable('logs')) {
    echo "<p class='ok'>✅ Logs directory writable</p>";
} else {
    echo "<p class='warning'>⚠️ Logs directory not writable</p>";
}

// Test 9: Quick Route Test
echo "<h2>🧪 Quick Route Test</h2>";
echo "<p><a href='/' style='background:#3b82f6;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🏠 Test Root Route (/)</a></p>";
echo "<p><a href='/landing' style='background:#10b981;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🎯 Test Landing Route (/landing)</a></p>";
echo "<p><a href='/auth/login' style='background:#f59e0b;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>🔐 Test Login Route (/auth/login)</a></p>";

echo "<hr>";
echo "<h2>💡 Recommendations</h2>";
echo "<ul>";
echo "<li>If you see 404 errors, check the .htaccess file and mod_rewrite</li>";
echo "<li>Make sure XAMPP is running and the project is in htdocs/sanipoint/</li>";
echo "<li>Access via: http://localhost/sanipoint/</li>";
echo "<li>Check PHP error logs in logs/php-errors.log</li>";
echo "</ul>";

echo "</body></html>";
?>