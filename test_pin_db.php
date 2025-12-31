<?php
require_once 'config/config.php';
require_once 'core/Database.php';

try {
    $db = Database::getInstance();
    
    echo "<h1>Database PIN Test</h1>";
    
    // 1. Check if PIN column exists
    echo "<h2>1. Check PIN Column</h2>";
    $columns = $db->fetchAll("SHOW COLUMNS FROM users LIKE 'pin'");
    if (empty($columns)) {
        echo "<p style='color: red;'>❌ PIN column does not exist!</p>";
        
        // Add PIN column
        echo "<h3>Adding PIN column...</h3>";
        $db->execute("ALTER TABLE users ADD COLUMN pin VARCHAR(255) NULL");
        $db->execute("ALTER TABLE users ADD COLUMN pin_created_at TIMESTAMP NULL");
        echo "<p style='color: green;'>✅ PIN columns added!</p>";
    } else {
        echo "<p style='color: green;'>✅ PIN column exists</p>";
    }
    
    // 2. Test user exists
    echo "<h2>2. Check User</h2>";
    $user = $db->fetch("SELECT id, username, password FROM users WHERE username = 'karyawan1'");
    if ($user) {
        echo "<p>✅ User found: " . $user['username'] . "</p>";
        echo "<p>User ID: " . $user['id'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ User karyawan1 not found!</p>";
        exit;
    }
    
    // 3. Test PIN update directly
    echo "<h2>3. Test PIN Update</h2>";
    $testPin = '123456';
    $hashedPin = password_hash($testPin, PASSWORD_DEFAULT);
    
    echo "<p>Original PIN: " . $testPin . "</p>";
    echo "<p>Hashed PIN: " . substr($hashedPin, 0, 30) . "...</p>";
    
    try {
        // Use direct SQL instead of update method
        $sql = "UPDATE users SET pin = ?, pin_created_at = ? WHERE id = ?";
        $stmt = $db->getConnection()->prepare($sql);
        $result = $stmt->execute([$hashedPin, date('Y-m-d H:i:s'), $user['id']]);
        
        if ($result) {
            echo "<p style='color: green;'>✅ PIN update SUCCESS!</p>";
            
            // Verify the update
            $updatedUser = $db->fetch("SELECT pin, pin_created_at FROM users WHERE id = ?", [$user['id']]);
            if ($updatedUser && $updatedUser['pin']) {
                echo "<p style='color: green;'>✅ PIN verified in database!</p>";
                echo "<p>PIN created at: " . $updatedUser['pin_created_at'] . "</p>";
            } else {
                echo "<p style='color: red;'>❌ PIN not found in database after update!</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ PIN update FAILED!</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>4. Database Method Test</h2>";
    echo "<p>Testing Database->update() method...</p>";
    
    // Check Database class update method
    $reflection = new ReflectionClass($db);
    if ($reflection->hasMethod('update')) {
        echo "<p>✅ update() method exists</p>";
    } else {
        echo "<p style='color: red;'>❌ update() method missing!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p><pre>" . $e->getTraceAsString() . "</pre>";
}
?>