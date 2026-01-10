<?php
session_start();

// No cache headers
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Load configuration and core files
require_once 'config/config.php';
require_once 'core/Database.php';
require_once 'core/Auth.php';

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        if (Auth::login($username, $password)) {
            // Force session write
            session_write_close();
            session_start();
            
            // Get user data after successful login
            $user = Auth::getUser();
            
            // Redirect based on role
            if ($user && $user['role'] === 'admin') {
                header('Location: admin/dashboard.php');
                exit;
            } elseif ($user && $user['role'] === 'karyawan') {
                header('Location: karyawan/dashboard.php');
                exit;
            }
        } else {
            $error = 'Username atau password salah';
        }
    }
}

// Check if already logged in
if (Auth::isLoggedIn()) {
    $user = Auth::getUser();
    if ($user && $user['role'] === 'admin') {
        header('Location: admin/dashboard.php');
        exit;
    } elseif ($user && $user['role'] === 'karyawan') {
        header('Location: karyawan/dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Login - SANIPOINT IoT Monitoring System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        .login-container {
            min-height: 100vh;
            background-color: white;
            display: flex;
        }
        
        .login-form-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .login-illustration-side {
            flex: 1;
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 3rem;
        }
        
        @media (min-width: 1024px) {
            .login-illustration-side {
                display: flex;
            }
        }
        
        .form-container {
            width: 100%;
            max-width: 28rem;
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-icon {
            width: 4rem;
            height: 4rem;
            background-color: #2563eb;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }
        
        .form-title {
            font-size: 1.875rem;
            font-weight: bold;
            color: #111827;
            margin-bottom: 0.5rem;
        }
        
        .form-subtitle {
            color: #6b7280;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn-primary {
            width: 100%;
            background-color: #2563eb;
            color: white;
            font-weight: 500;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
            font-size: 1rem;
        }
        
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        
        .btn-primary:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
        }
        
        .error-message {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .demo-info {
            margin-top: 2rem;
            padding: 1rem;
            background-color: #f9fafb;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        
        .quick-login {
            margin-top: 1rem;
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-quick {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background: white;
            color: #374151;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-quick:hover {
            background-color: #f3f4f6;
            border-color: #9ca3af;
        }
        
        .illustration-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            width: 20rem;
            height: 20rem;
            margin: 0 auto 2rem;
            position: relative;
        }
        
        .icon-box {
            width: 4rem;
            height: 4rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .icon-box.center {
            width: 5rem;
            height: 5rem;
            background-color: #2563eb;
            color: white;
            font-size: 1.875rem;
            transform: scale(1.1);
        }
        
        @media (max-width: 768px) {
            .login-form-side {
                padding: 1rem;
            }
            
            .form-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-form-side">
            <div class="form-container">
                <div class="logo-container">
                    <div class="logo-icon">🚿</div>
                    <h1 class="form-title">SANIPOINT</h1>
                    <p class="form-subtitle">IoT Bathroom Monitoring System</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <svg style="width: 1.25rem; height: 1.25rem; margin-right: 0.5rem;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" id="loginForm">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input 
                            id="username" 
                            name="username" 
                            type="text" 
                            required 
                            class="form-input"
                            placeholder="Masukkan username"
                            autocomplete="username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            required 
                            class="form-input"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                        >
                    </div>

                    <button type="submit" class="btn-primary" id="loginBtn">
                        Masuk ke Dashboard
                    </button>
                </form>
            </div>
        </div>

        <div class="login-illustration-side">
            <div style="max-width: 32rem; text-align: center;">
                <div class="illustration-grid">
                    <div class="icon-box">📡</div>
                    <div class="icon-box">🌡️</div>
                    <div class="icon-box">💨</div>
                    <div class="icon-box">🚿</div>
                    <div class="icon-box center">📊</div>
                    <div class="icon-box">🔒</div>
                    <div class="icon-box">💡</div>
                    <div class="icon-box">🔔</div>
                    <div class="icon-box">📱</div>
                </div>
                
                <h2 style="font-size: 1.875rem; font-weight: bold; color: #111827; margin-bottom: 1rem;">Smart Monitoring</h2>
                <p style="font-size: 1.125rem; color: #6b7280; margin-bottom: 1.5rem; line-height: 1.6;">
                    Sistem monitoring kamar mandi berbasis IoT dengan teknologi sensor canggih untuk menjaga kebersihan dan kenyamanan.
                </p>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; text-align: left;">
                    <div style="display: flex; align-items: center;">
                        <div style="width: 2rem; height: 2rem; background-color: #dcfce7; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                            <svg style="width: 1.25rem; height: 1.25rem; color: #16a34a;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span style="font-size: 0.875rem; color: #374151;">Real-time Monitoring</span>
                    </div>
                    <div style="display: flex; align-items: center;">
                        <div style="width: 2rem; height: 2rem; background-color: #dbeafe; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                            <svg style="width: 1.25rem; height: 1.25rem; color: #2563eb;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span style="font-size: 0.875rem; color: #374151;">IoT Integration</span>
                    </div>
                    <div style="display: flex; align-items: center;">
                        <div style="width: 2rem; height: 2rem; background-color: #f3e8ff; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                            <svg style="width: 1.25rem; height: 1.25rem; color: #9333ea;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span style="font-size: 0.875rem; color: #374151;">Reward System</span>
                    </div>
                    <div style="display: flex; align-items: center;">
                        <div style="width: 2rem; height: 2rem; background-color: #fef3c7; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                            <svg style="width: 1.25rem; height: 1.25rem; color: #d97706;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span style="font-size: 0.875rem; color: #374151;">Analytics Dashboard</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const submitBtn = document.getElementById('loginBtn');
            
            form.addEventListener('submit', function(e) {
                submitBtn.innerHTML = 'Memproses...';
                submitBtn.disabled = true;
            });
        });
    </script>
</body>
</html>