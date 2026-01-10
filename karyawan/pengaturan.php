<?php
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';
require_once '../core/Security.php';
require_once '../models/UserModel.php';

Auth::requireRole('karyawan');
$user = Auth::getUser();

$userModel = new UserModel();

// Handle PIN setup/change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'set_pin') {
        $new_pin = $_POST['new_pin'] ?? '';
        $confirm_pin = $_POST['confirm_pin'] ?? '';
        $current_pin = $_POST['current_pin'] ?? '';
        
        // Validate PIN format
        if (!preg_match('/^\d{6}$/', $new_pin)) {
            echo json_encode(['success' => false, 'message' => 'PIN harus 6 digit angka']);
            exit;
        }
        
        if ($new_pin !== $confirm_pin) {
            echo json_encode(['success' => false, 'message' => 'Konfirmasi PIN tidak cocok']);
            exit;
        }
        
        // Get fresh user data
        $freshUser = $userModel->getUserById($user['id']);
        
        // If user already has PIN, verify current PIN
        if (!empty($freshUser['pin'])) {
            if (empty($current_pin)) {
                echo json_encode(['success' => false, 'message' => 'PIN lama diperlukan']);
                exit;
            }
            
            if (!password_verify($current_pin, $freshUser['pin'])) {
                echo json_encode(['success' => false, 'message' => 'PIN lama salah']);
                exit;
            }
        }
        
        // Hash and save new PIN
        $hashed_pin = password_hash($new_pin, PASSWORD_DEFAULT);
        $result = $userModel->updateUserPin($user['id'], $hashed_pin);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'PIN berhasil ' . (empty($freshUser['pin']) ? 'dibuat' : 'diubah')]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan PIN']);
        }
        exit;
    }
}

$has_pin = !empty($user['pin']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - SANIPOINT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../views/layouts/karyawan_nav.php'; ?>
    
    <div class="p-6">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Pengaturan</h1>
            <p class="text-gray-600">Kelola akun dan keamanan Anda</p>
        </div>
        
        <!-- Profile Info -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Informasi Profil</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" value="<?= htmlspecialchars($user['full_name']) ?>" disabled 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Karyawan</label>
                    <input type="text" value="<?= htmlspecialchars($user['employee_code']) ?>" disabled 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" value="<?= htmlspecialchars($user['email'] ?? '-') ?>" disabled 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                </div>
            </div>
        </div>
        
        <!-- PIN Security -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-semibold">Keamanan PIN</h2>
                    <p class="text-gray-600">PIN digunakan untuk konfirmasi transfer poin</p>
                </div>
                <div class="flex items-center">
                    <?php if ($has_pin): ?>
                        <span class="px-3 py-1 text-sm font-medium bg-green-100 text-green-800 rounded-full">
                            <i class="fas fa-check mr-1"></i>PIN Aktif
                        </span>
                    <?php else: ?>
                        <span class="px-3 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">
                            <i class="fas fa-times mr-1"></i>PIN Belum Dibuat
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <form id="pin-form" class="space-y-4">
                <input type="hidden" name="action" value="set_pin">
                
                <?php if ($has_pin): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PIN Lama</label>
                        <input type="password" name="current_pin" required maxlength="6" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Masukkan PIN lama">
                    </div>
                <?php endif; ?>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">PIN Baru</label>
                    <input type="password" name="new_pin" required maxlength="6" pattern="\d{6}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Masukkan 6 digit angka">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi PIN Baru</label>
                    <input type="password" name="confirm_pin" required maxlength="6" pattern="\d{6}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Ulangi PIN baru">
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-medium mb-1">Ketentuan PIN:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>PIN harus terdiri dari 6 digit angka</li>
                                <li>Jangan gunakan angka yang mudah ditebak (123456, 000000, dll)</li>
                                <li>PIN akan digunakan untuk konfirmasi transfer poin</li>
                                <li>Jaga kerahasiaan PIN Anda</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i><?= $has_pin ? 'Ubah PIN' : 'Buat PIN' ?>
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 p-4 rounded-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
        
        document.getElementById('pin-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
            
            try {
                const formData = new FormData(this);
                
                const response = await fetch('pengaturan.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
        
        // PIN input validation
        document.querySelectorAll('input[type="password"]').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').slice(0, 6);
            });
        });
    </script>
</div>
</body>
</html>