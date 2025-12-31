<?php
session_start();
require_once 'config/config.php';
require_once 'core/Database.php';
require_once 'core/Auth.php';
require_once 'models/PointModel.php';

$auth = new Auth();
$auth->requireRole('karyawan');
$user = $auth->getUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (int)($_POST['amount'] ?? 0);
    $description = $_POST['description'] ?? 'Manual point addition';
    
    if ($amount > 0) {
        $pointModel = new PointModel();
        $result = $pointModel->addPoints($user['id'], $amount, 'cleaning', null, $description);
        
        if ($result['success']) {
            $message = "Berhasil menambah {$amount} poin!";
        } else {
            $error = "Gagal menambah poin!";
        }
    } else {
        $error = "Jumlah poin harus lebih dari 0!";
    }
}

$pointModel = new PointModel();
$points = $pointModel->getUserPoints($user['id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Poin Manual</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-md mx-auto mt-10 bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-6">Tambah Poin Manual</h1>
        
        <div class="mb-4 p-4 bg-blue-50 rounded">
            <p class="text-sm text-blue-600">Saldo Poin Saat Ini:</p>
            <p class="text-2xl font-bold text-blue-800"><?= number_format($points['current_balance']) ?></p>
        </div>
        
        <?php if (isset($message)): ?>
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded">
                <p class="text-green-800"><?= $message ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded">
                <p class="text-red-800"><?= $error ?></p>
            </div>
        <?php endif; ?>
        
        <form method="post" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Poin</label>
                <input type="number" name="amount" min="1" max="1000" required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                <input type="text" name="description" placeholder="Contoh: Bonus harian" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                Tambah Poin
            </button>
        </form>
        
        <div class="mt-6 pt-4 border-t">
            <a href="/sanipoint/karyawan/dashboard" class="text-blue-600 hover:underline">← Kembali ke Dashboard</a>
        </div>
        
        <div class="mt-4 p-3 bg-yellow-50 rounded text-sm text-yellow-800">
            <strong>Info:</strong> Ini adalah halaman testing untuk menambah poin manual. 
            Normalnya poin didapat dari pembersihan kamar mandi melalui sistem IoT.
        </div>
    </div>
</body>
</html>