<?php
require_once 'config/config.php';
require_once 'core/Database.php';

try {
    $db = Database::getInstance();
    
    echo "<h1>PIN Hash Verification & Fix</h1>";
    
    // Get karyawan1 user
    $user = $db->fetch("SELECT id, username, password, pin, pin_created_at FROM users WHERE username = 'karyawan1'");
    
    if (!$user) {
        echo "<p style='color: red;'>❌ User karyawan1 not found!</p>";
        exit;
    }
    
    echo "<h2>Current User Data:</h2>";
    echo "<p>Username: " . $user['username'] . "</p>";
    echo "<p>User ID: " . $user['id'] . "</p>";
    echo "<p>PIN: " . ($user['pin'] ? 'SET' : 'NOT SET') . "</p>";
    echo "<p>PIN Created: " . ($user['pin_created_at'] ?: 'Never') . "</p>";
    
    if ($user['pin']) {
        echo "<p>PIN Hash: " . substr($user['pin'], 0, 30) . "...</p>";
        
        // Check if PIN is properly hashed
        $pinInfo = password_get_info($user['pin']);
        echo "<p>PIN Hash Algorithm: " . ($pinInfo['algo'] ?: 'NONE/PLAIN TEXT') . "</p>";
        
        if ($pinInfo['algo'] === 0) {
            echo "<p style='color: red;'>❌ PIN is NOT hashed (plain text)</p>";
        } else {
            echo "<p style='color: green;'>✅ PIN is properly hashed</p>";
        }
    }
    
    echo "<h2>PIN Verification Tests:</h2>";
    
    $testPins = ['123456', 'password', '000000', '111111'];
    $correctPin = null;
    
    if ($user['pin']) {
        foreach ($testPins as $testPin) {
            $isValid = password_verify($testPin, $user['pin']);
            echo "<p>PIN '{$testPin}': " . ($isValid ? '✅ VALID' : '❌ INVALID') . "</p>";
            if ($isValid) {
                $correctPin = $testPin;
            }
        }
    }
    
    echo "<h2>Fix PIN Hash:</h2>";
    
    // Always create a fresh PIN hash
    $newPin = '123456';
    $newPinHash = password_hash($newPin, PASSWORD_DEFAULT);
    
    echo "<p>New PIN: {$newPin}</p>";
    echo "<p>New PIN Hash: " . substr($newPinHash, 0, 30) . "...</p>";
    
    // Verify new hash works
    $testNewHash = password_verify($newPin, $newPinHash);
    echo "<p>New Hash Test: " . ($testNewHash ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
    
    if ($testNewHash) {
        // Update database with new hash
        $sql = "UPDATE users SET pin = ?, pin_created_at = NOW() WHERE id = ?";
        $stmt = $db->getConnection()->prepare($sql);
        $result = $stmt->execute([$newPinHash, $user['id']]);
        
        if ($result) {
            echo "<p style='color: green;'>✅ PIN hash updated in database</p>";
            
            // Verify database update
            $updatedUser = $db->fetch("SELECT pin FROM users WHERE id = ?", [$user['id']]);
            $finalTest = password_verify($newPin, $updatedUser['pin']);
            echo "<p>Database Verification: " . ($finalTest ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
            
            if ($finalTest) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
                echo "<h3 style='color: #155724;'>✅ PIN Successfully Fixed!</h3>";
                echo "<p style='color: #155724;'>PIN: <strong>123456</strong></p>";
                echo "<p style='color: #155724;'>Hash Algorithm: bcrypt</p>";
                echo "</div>";
            }
        } else {
            echo "<p style='color: red;'>❌ Failed to update PIN in database</p>";
        }
    }
    
    echo "<h2>Transfer Controller Test:</h2>";
    
    // Simulate transfer controller PIN verification
    $testTransferPin = '123456';
    $updatedUser = $db->fetch("SELECT pin FROM users WHERE id = ?", [$user['id']]);
    
    if ($updatedUser['pin']) {
        $transferPinTest = password_verify($testTransferPin, $updatedUser['pin']);
        echo "<p>Transfer PIN Verification: " . ($transferPinTest ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
        
        if ($transferPinTest) {
            echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
            echo "<h3 style='color: #0c5460;'>🔄 Ready for Transfer!</h3>";
            echo "<p style='color: #0c5460;'>PIN verification will work in transfer form</p>";
            echo "<p><a href='/sanipoint/karyawan/transfer' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Transfer Now</a></p>";
            echo "</div>";
        }
    }
    
    echo "<h2>Debug Info for Transfer:</h2>";
    echo "<p><strong>Use these values in transfer form:</strong></p>";
    echo "<ul>";
    echo "<li>PIN: <strong>123456</strong></li>";
    echo "<li>Any amount within your balance</li>";
    echo "<li>Select any employee as recipient</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>