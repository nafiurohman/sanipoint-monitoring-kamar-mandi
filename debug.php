<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP is working<br>";
echo "Current directory: " . __DIR__ . "<br>";

try {
    require_once 'config/config.php';
    echo "Config loaded successfully<br>";
    echo "DB_HOST: " . DB_HOST . "<br>";
    echo "DB_NAME: " . DB_NAME . "<br>";
} catch (Exception $e) {
    echo "Config error: " . $e->getMessage() . "<br>";
}

try {
    require_once 'core/Database.php';
    echo "Database class loaded<br>";
    
    $db = Database::getInstance();
    echo "Database connected successfully<br>";
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}
?>