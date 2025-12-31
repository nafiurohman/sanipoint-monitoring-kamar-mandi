<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'config/config.php';
    require_once 'core/Database.php';
    
    $db = Database::getInstance();
    
    // Test basic functionality
    echo "<!DOCTYPE html>";
    echo "<html><head><title>SANIPOINT Test</title></head><body>";
    echo "<h1>SANIPOINT System Test</h1>";
    echo "<p>✅ PHP Working</p>";
    echo "<p>✅ Config Loaded</p>";
    echo "<p>✅ Database Connected</p>";
    echo "<p><a href='/login'>Go to Login</a></p>";
    echo "</body></html>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>