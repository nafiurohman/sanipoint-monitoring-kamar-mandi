<?php
require_once 'config/config.php';
require_once 'core/Database.php';

try {
    $db = Database::getInstance();
    
    echo "<h1>Database PIN Check</h1>";
    
    // Check table structure
    $result = $db->fetchAll("DESCRIBE users");
    
    echo "<h2>Users Table Structure:</h2>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $hasPinColumn = false;
    foreach ($result as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
        
        if ($column['Field'] === 'pin') {
            $hasPinColumn = true;
        }
    }
    echo "</table>";
    
    if (!$hasPinColumn) {
        echo "<h2 style='color: red;'>MASALAH: Kolom PIN tidak ada!</h2>";
        echo "<p>Jalankan query ini di phpMyAdmin:</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px;'>";
        echo "ALTER TABLE users ADD COLUMN pin VARCHAR(255) NULL;\n";
        echo "ALTER TABLE users ADD COLUMN pin_created_at TIMESTAMP NULL;\n";
        echo "ALTER TABLE users ADD COLUMN last_password_change TIMESTAMP NULL;";
        echo "</pre>";
    } else {
        echo "<h2 style='color: green;'>✓ Kolom PIN sudah ada!</h2>";
        
        // Check current user PIN
        $user = $db->fetch("SELECT id, username, pin FROM users WHERE username = 'karyawan1'");
        if ($user) {
            echo "<h3>Status PIN karyawan1:</h3>";
            echo "<p>PIN: " . ($user['pin'] ? 'Ada' : 'Belum dibuat') . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>