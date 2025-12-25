<?php
ob_start();
?>

<div class="p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Poin Saya</h1>
        <p class="text-gray-600">Kelola dan pantau poin reward Anda</p>
    </div>

    <!-- Points Summary -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg shadow-lg p-8 mb-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm font-medium">Saldo Poin Saat Ini</p>
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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <a href="/sanipoint/karyawan/marketplace" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-shopping-cart text-green-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-lg font-bold text-gray-900">Tukar Poin</p>
                    <p class="text-sm text-gray-600">Belanja di marketplace</p>
                </div>
            </div>
        </a>
        
        <a href="/sanipoint/karyawan/transfer" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <i class="fas fa-exchange-alt text-purple-600 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-lg font-bold text-gray-900">Transfer Poin</p>
                    <p class="text-sm text-gray-600">Kirim ke karyawan lain</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Transaction History -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Riwayat Transaksi</h2>
        </div>
        <div class="divide-y divide-gray-200">
            <?php if (empty($transactions)): ?>
                <div class="p-6 text-center text-gray-500">
                    <i class="fas fa-history text-4xl mb-4"></i>
                    <p>Belum ada transaksi</p>
                </div>
            <?php else: ?>
                <?php foreach ($transactions as $transaction): ?>
                    <div class="p-6 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <?php if ($transaction['transaction_type'] == 'earned'): ?>
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-plus text-green-600"></i>
                                    </div>
                                <?php elseif ($transaction['transaction_type'] == 'spent'): ?>
                                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-shopping-cart text-red-600"></i>
                                    </div>
                                <?php elseif ($transaction['transaction_type'] == 'transfer_in'): ?>
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
                                        case 'earned':
                                            echo 'Poin Diperoleh';
                                            break;
                                        case 'spent':
                                            echo 'Pembelian Produk';
                                            break;
                                        case 'transfer_in':
                                            echo 'Transfer Masuk dari ' . htmlspecialchars($transaction['from_user_name'] ?? 'Unknown');
                                            break;
                                        case 'transfer_out':
                                            echo 'Transfer Keluar ke ' . htmlspecialchars($transaction['to_user_name'] ?? 'Unknown');
                                            break;
                                    }
                                    ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?= date('d M Y H:i', strtotime($transaction['created_at'])) ?>
                                </p>
                                <?php if ($transaction['description']): ?>
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($transaction['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-lg <?= in_array($transaction['transaction_type'], ['earned', 'transfer_in']) ? 'text-green-600' : 'text-red-600' ?>">
                                <?= in_array($transaction['transaction_type'], ['earned', 'transfer_in']) ? '+' : '-' ?><?= number_format($transaction['amount']) ?>
                            </p>
                            <p class="text-sm text-gray-500">
                                Saldo: <?= number_format($transaction['balance_after']) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Poin Saya';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>