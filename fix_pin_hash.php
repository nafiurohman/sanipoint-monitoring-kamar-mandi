<?php
require_once 'config/config.php';
require_once 'core/Database.php';

try {
    $db = Database::getInstance();
    
    echo "<h1>PIN Hash Debug & Fix</h1>";
    
    // Get user data
    $user = $db->fetch("SELECT id, username, password, pin, pin_created_at FROM users WHERE username = 'karyawan1'");
    
    if (!$user) {
        echo "<p style='color: red;'>❌ User karyawan1 not found!</p>";
        exit;
    }
    
    echo "<h2>Current User Data:</h2>";
    echo "<p>Username: " . $user['username'] . "</p>";
    echo "<p>User ID: " . $user['id'] . "</p>";
    echo "<p>Password hash: " . substr($user['password'], 0, 30) . "...</p>";
    echo "<p>PIN hash: " . ($user['pin'] ? substr($user['pin'], 0, 30) . "..." : 'NULL') . "</p>";
    echo "<p>PIN created: " . ($user['pin_created_at'] ?: 'Never') . "</p>";
    
    echo "<h2>Password Verification Test:</h2>";
    $testPassword = 'password';
    $passwordVerify = password_verify($testPassword, $user['password']);
    echo "<p>Password 'password' verify: " . ($passwordVerify ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
    
    echo "<h2>PIN Tests:</h2>";
    
    if ($user['pin']) {
        // Test current PIN
        $testPins = ['123456', 'password'];
        foreach ($testPins as $testPin) {
            $pinVerify = password_verify($testPin, $user['pin']);
            echo "<p>PIN '{$testPin}' verify: " . ($pinVerify ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
        }
    } else {
        echo "<p>❌ No PIN set for user</p>";
    }
    
    echo "<h2>Fix PIN:</h2>";
    
    // Create new PIN hash
    $newPin = '123456';
    $newPinHash = password_hash($newPin, PASSWORD_DEFAULT);
    
    echo "<p>New PIN: {$newPin}</p>";
    echo "<p>New PIN hash: " . substr($newPinHash, 0, 30) . "...</p>";
    
    // Test new hash immediately
    $testNewHash = password_verify($newPin, $newPinHash);
    echo "<p>New hash test: " . ($testNewHash ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
    
    if ($testNewHash) {
        // Update database
        $sql = "UPDATE users SET pin = ?, pin_created_at = NOW() WHERE id = ?";
        $stmt = $db->getConnection()->prepare($sql);
        $result = $stmt->execute([$newPinHash, $user['id']]);
        
        if ($result) {
            echo "<p style='color: green;'>✅ PIN updated successfully!</p>";
            
            // Verify update
            $updatedUser = $db->fetch("SELECT pin FROM users WHERE id = ?", [$user['id']]);
            $finalTest = password_verify($newPin, $updatedUser['pin']);
            echo "<p>Final verification: " . ($finalTest ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
            
            if ($finalTest) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
                echo "<h3 style='color: #155724; margin: 0 0 10px 0;'>✅ PIN Fixed Successfully!</h3>";
                echo "<p style='color: #155724; margin: 0;'>You can now use PIN: <strong>123456</strong> for transfers</p>";
                echo "</div>";
            }
        } else {
            echo "<p style='color: red;'>❌ Failed to update PIN in database</p>";
        }
    }
    
    echo "<h2>Test Transfer Now:</h2>";
    echo "<p><a href='/sanipoint/karyawan/transfer' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Transfer Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>