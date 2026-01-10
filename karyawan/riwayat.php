<?php
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';
require_once '../models/PointModel.php';
require_once '../models/OrderModel.php';

Auth::requireRole('karyawan');
$user = Auth::getUser();

$pointModel = new PointModel();
$orderModel = new OrderModel();

$transactions = $pointModel->getAllTransactions($user['id']);
$orders = $orderModel->getUserOrders($user['id']);
$cleaning_history = $pointModel->getCleaningHistory($user['id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - SANIPOINT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../views/layouts/karyawan_nav.php'; ?>
    
    <div class="p-6">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Riwayat Transaksi</h1>
            <p class="text-gray-600">Lihat semua aktivitas poin dan pembelian Anda</p>
        </div>
        
        <!-- Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button onclick="showTab('points')" id="tab-points" class="tab-button active py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600">
                        <i class="fas fa-coins mr-2"></i>Transaksi Poin
                    </button>
                    <button onclick="showTab('orders')" id="tab-orders" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-shopping-bag mr-2"></i>Pembelian
                    </button>
                    <button onclick="showTab('cleaning')" id="tab-cleaning" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-broom mr-2"></i>Pembersihan
                    </button>
                </nav>
            </div>
        </div>
        
        <!-- Points Tab -->
        <div id="content-points" class="tab-content">
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold">Riwayat Poin</h2>
                </div>
                <div class="p-6">
                    <?php if (empty($transactions)): ?>
                        <p class="text-gray-500 text-center py-8">Belum ada transaksi poin</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($transactions as $transaction): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <?php if ($transaction['transaction_type'] === 'earned'): ?>
                                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-plus text-green-600"></i>
                                                </div>
                                            <?php elseif ($transaction['transaction_type'] === 'spent'): ?>
                                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-minus text-red-600"></i>
                                                </div>
                                            <?php elseif ($transaction['transaction_type'] === 'transfer_in'): ?>
                                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-arrow-down text-blue-600"></i>
                                                </div>
                                            <?php else: ?>
                                                <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-arrow-up text-orange-600"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">
                                                <?php
                                                switch ($transaction['transaction_type']) {
                                                    case 'earned': echo 'Poin Diterima'; break;
                                                    case 'spent': echo 'Poin Digunakan'; break;
                                                    case 'transfer_in': echo 'Transfer Masuk dari ' . htmlspecialchars($transaction['from_user_name']); break;
                                                    case 'transfer_out': echo 'Transfer Keluar ke ' . htmlspecialchars($transaction['to_user_name']); break;
                                                }
                                                ?>
                                            </p>
                                            <p class="text-sm text-gray-600"><?= htmlspecialchars($transaction['description'] ?? '') ?></p>
                                            <p class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($transaction['created_at'])) ?></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold <?= in_array($transaction['transaction_type'], ['earned', 'transfer_in']) ? 'text-green-600' : 'text-red-600' ?>">
                                            <?= in_array($transaction['transaction_type'], ['earned', 'transfer_in']) ? '+' : '-' ?><?= number_format($transaction['amount']) ?> pts
                                        </p>
                                        <p class="text-xs text-gray-500">Saldo: <?= number_format($transaction['balance_after']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Orders Tab -->
        <div id="content-orders" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold">Riwayat Pembelian</h2>
                </div>
                <div class="p-6">
                    <?php if (empty($orders)): ?>
                        <p class="text-gray-500 text-center py-8">Belum ada pembelian</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($orders as $order): ?>
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <p class="font-medium text-gray-900"><?= htmlspecialchars($order['order_number']) ?></p>
                                            <p class="text-sm text-gray-600"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-semibold text-purple-600"><?= number_format($order['total_points']) ?> pts</p>
                                            <span class="px-2 py-1 text-xs font-medium rounded-full <?= $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600"><?= $order['item_count'] ?> item(s)</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Cleaning Tab -->
        <div id="content-cleaning" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold">Riwayat Pembersihan</h2>
                </div>
                <div class="p-6">
                    <?php if (empty($cleaning_history)): ?>
                        <p class="text-gray-500 text-center py-8">Belum ada riwayat pembersihan</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($cleaning_history as $cleaning): ?>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <?php if ($cleaning['status'] === 'completed'): ?>
                                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-check text-green-600"></i>
                                                </div>
                                            <?php else: ?>
                                                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-clock text-yellow-600"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900"><?= htmlspecialchars($cleaning['bathroom_name']) ?></p>
                                            <p class="text-sm text-gray-600">
                                                <?= date('d/m/Y H:i', strtotime($cleaning['start_time'])) ?>
                                                <?php if ($cleaning['end_time']): ?>
                                                    - <?= date('H:i', strtotime($cleaning['end_time'])) ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <?php if ($cleaning['status'] === 'completed'): ?>
                                            <p class="font-semibold text-green-600">+<?= $cleaning['points_earned'] ?> pts</p>
                                            <p class="text-sm text-gray-500"><?= $cleaning['duration_minutes'] ?> menit</p>
                                        <?php else: ?>
                                            <p class="text-sm text-yellow-600 font-medium">Sedang berlangsung</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected tab content
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            // Add active class to selected tab button
            const activeButton = document.getElementById('tab-' + tabName);
            activeButton.classList.add('active', 'border-blue-500', 'text-blue-600');
            activeButton.classList.remove('border-transparent', 'text-gray-500');
        }
    </script>
</div>
</body>
</html>