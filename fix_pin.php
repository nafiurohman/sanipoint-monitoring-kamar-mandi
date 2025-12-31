<?php
require_once 'config/config.php';
require_once 'core/Database.php';

try {
    $db = Database::getInstance();
    
    echo "<h1>Menambah Kolom PIN</h1>";
    
    // Add PIN columns
    $queries = [
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS pin VARCHAR(255) NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS pin_created_at TIMESTAMP NULL", 
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_password_change TIMESTAMP NULL"
    ];
    
    foreach ($queries as $query) {
        try {
            $db->execute($query);
            echo "<p>✓ " . $query . "</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ " . $query . " - Error: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>Selesai!</h2>";
    echo "<p><a href='/sanipoint/karyawan/pengaturan'>Coba buat PIN sekarang</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
}
?>