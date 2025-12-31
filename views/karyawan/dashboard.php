<?php
ob_start();
?>

<div class="p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Karyawan</h1>
        <p class="text-gray-600">Selamat datang kembali!</p>
    </div>
    
    <!-- Points Card -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-lg p-8 mb-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm font-medium">Saldo Poin Anda</p>
                <p class="text-5xl font-bold mt-2"><?= number_format($points['current_balance']) ?></p>
                <p class="text-blue-100 text-sm mt-2">Poin</p>
            </div>
            <div class="text-right">
                <div class="bg-white bg-opacity-20 rounded-lg p-4">
                    <i class="fas fa-coins text-4xl"></i>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-blue-400">
            <div>
                <p class="text-blue-100 text-sm">Total Diperoleh</p>
                <p class="text-2xl font-bold"><?= number_format($points['total_earned']) ?></p>
            </div>
            <div>
                <p class="text-blue-100 text-sm">Total Digunakan</p>
                <p class="text-2xl font-bold"><?= number_format($points['total_spent']) ?></p>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="marketplace" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-shopping-cart text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Marketplace</p>
                    <p class="text-lg font-bold text-gray-900">Tukar Poin</p>
                </div>
            </div>
        </a>
        
        <a href="transfer" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <i class="fas fa-exchange-alt text-purple-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Transfer</p>
                    <p class="text-lg font-bold text-gray-900">Kirim Poin</p>
                </div>
            </div>
        </a>
        
        <a href="poin" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-history text-blue-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Riwayat</p>
                    <p class="text-lg font-bold text-gray-900">Lihat Detail</p>
                </div>
            </div>
        </a>
    </div>
    
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Pembersihan</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?= $cleaning_stats['total_cleanings'] ?? 0 ?></p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <i class="fas fa-broom text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Rata-rata Durasi</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?= round($cleaning_stats['avg_duration'] ?? 0) ?></p>
                    <p class="text-sm text-gray-500">menit</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-clock text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Poin dari Cleaning</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2"><?= $cleaning_stats['total_points_from_cleaning'] ?? 0 ?></p>
                </div>
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-star text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Transactions and Orders -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Transaksi Terbaru</h2>
            </div>
            <div class="p-6">
                <?php if (empty($recent_transactions)): ?>
                    <p class="text-gray-500 text-center py-4">Belum ada transaksi</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recent_transactions as $transaction): ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <?php if ($transaction['transaction_type'] == 'earned'): ?>
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-plus text-green-600 text-sm"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-minus text-red-600 text-sm"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 capitalize">
                                            <?= str_replace('_', ' ', $transaction['transaction_type']) ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?= date('d M Y H:i', strtotime($transaction['created_at'])) ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold <?= $transaction['transaction_type'] == 'earned' ? 'text-green-600' : 'text-red-600' ?>">
                                        <?= $transaction['transaction_type'] == 'earned' ? '+' : '-' ?><?= number_format($transaction['amount']) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Pesanan Terbaru</h2>
            </div>
            <div class="p-6">
                <?php if (empty($recent_orders)): ?>
                    <p class="text-gray-500 text-center py-4">Belum ada pesanan</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recent_orders as $order): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($order['order_number']) ?></p>
                                    <p class="text-xs text-gray-500"><?= $order['item_count'] ?> item</p>
                                    <p class="text-xs text-gray-500"><?= date('d M Y', strtotime($order['created_at'])) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900"><?= number_format($order['total_points']) ?> pts</p>
                                    <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Dashboard Karyawan';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>