<?php
require_once 'config/config.php';
require_once 'core/Database.php';

try {
    $db = Database::getInstance();
    
    // Check if column exists
    $result = $db->getConnection()->query("SHOW COLUMNS FROM orders LIKE 'received_at'");
    
    if ($result->rowCount() == 0) {
        // Add the column
        $db->getConnection()->exec("ALTER TABLE orders ADD COLUMN received_at TIMESTAMP NULL AFTER status");
        echo "Column 'received_at' added successfully to orders table.\n";
    } else {
        echo "Column 'received_at' already exists in orders table.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>