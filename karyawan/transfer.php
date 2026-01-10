<?php
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';
require_once '../core/Security.php';
require_once '../models/PointModel.php';
require_once '../models/UserModel.php';

Auth::requireRole('karyawan');
$user = Auth::getUser();

$pointModel = new PointModel();
$userModel = new UserModel();

// Handle transfer request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'transfer_points') {
        $pin = $_POST['pin'] ?? '';
        $to_user_id = $_POST['to_user_id'] ?? '';
        $amount = (int)($_POST['amount'] ?? 0);
        $description = Security::sanitizeInput($_POST['description'] ?? '');
        
        // Get fresh user data
        $freshUser = $userModel->getUserById($user['id']);
        
        if (empty($freshUser['pin'])) {
            echo json_encode(['success' => false, 'message' => 'PIN belum dibuat. Silakan buat PIN terlebih dahulu.']);
            exit;
        }
        
        if (!password_verify($pin, $freshUser['pin'])) {
            echo json_encode(['success' => false, 'message' => 'PIN salah']);
            exit;
        }
        
        if ($amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Jumlah poin harus lebih dari 0']);
            exit;
        }
        
        if (empty($to_user_id)) {
            echo json_encode(['success' => false, 'message' => 'Pilih karyawan tujuan']);
            exit;
        }
        
        $result = $pointModel->transferPoints($user['id'], $to_user_id, $amount, $description);
        echo json_encode($result);
        exit;
    }
}

$points = $pointModel->getUserPoints($user['id']);
$transfer_history = $pointModel->getTransferHistory($user['id']);
$employees = $userModel->getAllEmployees();
$setup_pin = empty($user['pin']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Poin - SANIPOINT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../views/layouts/karyawan_nav.php'; ?>
    
    <div class="p-6">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Transfer Poin</h1>
            <p class="text-gray-600">Kirim poin ke rekan kerja Anda</p>
        </div>
        
        <!-- Current Balance -->
        <div class="bg-gradient-to-r from-green-500 to-blue-600 rounded-2xl p-6 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Saldo Poin Anda</p>
                    <p class="text-4xl font-bold"><?= number_format($points['current_balance']) ?></p>
                </div>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>
        
        <?php if ($setup_pin): ?>
            <!-- PIN Setup Required -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl mr-4"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-yellow-800">PIN Diperlukan</h3>
                        <p class="text-yellow-700">Anda perlu membuat PIN terlebih dahulu untuk melakukan transfer poin.</p>
                        <a href="pengaturan.php" class="inline-block mt-2 bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700">
                            Buat PIN Sekarang
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Transfer Form -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Kirim Poin</h2>
                <form id="transfer-form" class="space-y-4">
                    <input type="hidden" name="action" value="transfer_points">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Karyawan Tujuan</label>
                        <select name="to_user_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih karyawan...</option>
                            <?php foreach ($employees as $employee): ?>
                                <?php if ($employee['id'] !== $user['id']): ?>
                                    <option value="<?= $employee['id'] ?>"><?= htmlspecialchars($employee['full_name']) ?> (<?= htmlspecialchars($employee['employee_code']) ?>)</option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Poin</label>
                        <input type="number" name="amount" min="1" max="<?= $points['current_balance'] ?>" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Masukkan jumlah poin">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan (Opsional)</label>
                        <textarea name="description" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Tulis keterangan transfer..."></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">PIN Konfirmasi</label>
                        <input type="password" name="pin" required maxlength="6" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Masukkan PIN 6 digit">
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700">
                        <i class="fas fa-paper-plane mr-2"></i>Kirim Poin
                    </button>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- Transfer History -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold">Riwayat Transfer</h2>
            </div>
            <div class="p-6">
                <?php if (empty($transfer_history)): ?>
                    <p class="text-gray-500 text-center py-8">Belum ada riwayat transfer</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($transfer_history as $transaction): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <?php if ($transaction['transaction_type'] === 'transfer_out'): ?>
                                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-arrow-up text-red-600"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-arrow-down text-green-600"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            <?php if ($transaction['transaction_type'] === 'transfer_out'): ?>
                                                Kirim ke <?= htmlspecialchars($transaction['to_user_name']) ?>
                                            <?php else: ?>
                                                Terima dari <?= htmlspecialchars($transaction['from_user_name']) ?>
                                            <?php endif; ?>
                                        </p>
                                        <p class="text-sm text-gray-600"><?= htmlspecialchars($transaction['description'] ?? '') ?></p>
                                        <p class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($transaction['created_at'])) ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold <?= $transaction['transaction_type'] === 'transfer_out' ? 'text-red-600' : 'text-green-600' ?>">
                                        <?= $transaction['transaction_type'] === 'transfer_out' ? '-' : '+' ?><?= number_format($transaction['amount']) ?> pts
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
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
        
        document.getElementById('transfer-form')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            
            try {
                const formData = new FormData(this);
                
                const response = await fetch('transfer.php', {
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
    </script>
</div>
</body>
</html>