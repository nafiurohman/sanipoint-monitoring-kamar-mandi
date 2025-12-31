<?php
require_once 'config/config.php';
require_once 'core/Database.php';

try {
    $db = Database::getInstance();
    
    echo "<h1>Hash All Passwords & PINs with Bcrypt</h1>";
    
    // Get all users
    $users = $db->fetchAll("SELECT id, username, password, pin FROM users");
    
    echo "<h2>Found " . count($users) . " users</h2>";
    
    foreach ($users as $user) {
        echo "<h3>Processing: " . htmlspecialchars($user['username']) . "</h3>";
        
        $updates = [];
        $params = [];
        
        // Check password hash
        if ($user['password']) {
            $passwordInfo = password_get_info($user['password']);
            echo "<p>Password algo: " . ($passwordInfo['algo'] ?: 'UNKNOWN') . "</p>";
            
            if ($passwordInfo['algo'] === 0) {
                // Not hashed, hash it
                $newPasswordHash = password_hash('password', PASSWORD_DEFAULT);
                $updates[] = "password = ?";
                $params[] = $newPasswordHash;
                echo "<p>🔄 Password will be hashed</p>";
            } else {
                echo "<p>✅ Password already hashed</p>";
            }
        }
        
        // Check PIN hash
        if ($user['pin']) {
            $pinInfo = password_get_info($user['pin']);
            echo "<p>PIN algo: " . ($pinInfo['algo'] ?: 'UNKNOWN') . "</p>";
            
            if ($pinInfo['algo'] === 0) {
                // Not hashed, hash it
                $newPinHash = password_hash('123456', PASSWORD_DEFAULT);
                $updates[] = "pin = ?";
                $params[] = $newPinHash;
                echo "<p>🔄 PIN will be hashed (default: 123456)</p>";
            } else {
                echo "<p>✅ PIN already hashed</p>";
            }
        } else {
            // No PIN, create one
            $newPinHash = password_hash('123456', PASSWORD_DEFAULT);
            $updates[] = "pin = ?";
            $updates[] = "pin_created_at = NOW()";
            $params[] = $newPinHash;
            echo "<p>🆕 PIN will be created (default: 123456)</p>";
        }
        
        // Update if needed
        if (!empty($updates)) {
            $params[] = $user['id'];
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            
            $stmt = $db->getConnection()->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                echo "<p style='color: green;'>✅ Updated successfully</p>";
            } else {
                echo "<p style='color: red;'>❌ Update failed</p>";
            }
        } else {
            echo "<p>ℹ️ No updates needed</p>";
        }
        
        echo "<hr>";
    }
    
    echo "<h2>Verification Test</h2>";
    
    // Test karyawan1
    $testUser = $db->fetch("SELECT username, password, pin FROM users WHERE username = 'karyawan1'");
    
    if ($testUser) {
        echo "<h3>Testing karyawan1:</h3>";
        
        $passwordTest = password_verify('password', $testUser['password']);
        echo "<p>Password 'password': " . ($passwordTest ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
        
        if ($testUser['pin']) {
            $pinTest = password_verify('123456', $testUser['pin']);
            echo "<p>PIN '123456': " . ($pinTest ? '✅ SUCCESS' : '❌ FAILED') . "</p>";
        }
    }
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3 style='color: #155724; margin: 0 0 10px 0;'>✅ All Hashing Complete!</h3>";
    echo "<p style='color: #155724; margin: 0;'><strong>Default Credentials:</strong></p>";
    echo "<p style='color: #155724; margin: 5px 0;'>• Password: <strong>password</strong></p>";
    echo "<p style='color: #155724; margin: 5px 0;'>• PIN: <strong>123456</strong></p>";
    echo "</div>";
    
    echo "<p><a href='/sanipoint/karyawan/transfer' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Transfer Now</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>