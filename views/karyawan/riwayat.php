<?php
ob_start();
?>

<div class="p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Riwayat Aktivitas</h1>
        <p class="text-gray-600 dark:text-gray-400">Riwayat transaksi poin, pembelian, dan pembersihan</p>
    </div>

    <!-- Filter Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <button class="tab-button active" data-tab="transactions">
                    <i class="fas fa-coins mr-2"></i>
                    Transaksi Poin
                </button>
                <button class="tab-button" data-tab="orders">
                    <i class="fas fa-shopping-bag mr-2"></i>
                    Pembelian
                </button>
                <button class="tab-button" data-tab="cleaning">
                    <i class="fas fa-broom mr-2"></i>
                    Pembersihan
                </button>
            </nav>
        </div>
    </div>

    <!-- Transactions Tab -->
    <div id="transactions-tab" class="tab-content active">
        <div class="card">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Riwayat Transaksi Poin</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Saldo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($transactions as $transaction): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?= date('d/m/Y H:i', strtotime($transaction['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $type_colors = [
                                        'earned' => 'text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400',
                                        'spent' => 'text-red-600 bg-red-100 dark:bg-red-900/30 dark:text-red-400',
                                        'transfer_in' => 'text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400',
                                        'transfer_out' => 'text-orange-600 bg-orange-100 dark:bg-orange-900/30 dark:text-orange-400'
                                    ];
                                    $type_labels = [
                                        'earned' => 'Diperoleh',
                                        'spent' => 'Digunakan',
                                        'transfer_in' => 'Transfer Masuk',
                                        'transfer_out' => 'Transfer Keluar'
                                    ];
                                    ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $type_colors[$transaction['transaction_type']] ?>">
                                        <?= $type_labels[$transaction['transaction_type']] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <span class="<?= in_array($transaction['transaction_type'], ['earned', 'transfer_in']) ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' ?>">
                                        <?= in_array($transaction['transaction_type'], ['earned', 'transfer_in']) ? '+' : '-' ?><?= number_format($transaction['amount']) ?> pts
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?= number_format($transaction['balance_after']) ?> pts
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($transaction['description'] ?? '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Orders Tab -->
    <div id="orders-tab" class="tab-content">
        <div class="card">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Riwayat Pembelian</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No. Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Poin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">QR Code</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($orders as $order): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($order['order_number']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?= number_format($order['total_points']) ?> pts
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php
                                    $status_colors = [
                                        'pending' => 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        'completed' => 'text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400',
                                        'cancelled' => 'text-red-600 bg-red-100 dark:bg-red-900/30 dark:text-red-400'
                                    ];
                                    ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $status_colors[$order['status']] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($order['qr_code'] && $order['status'] === 'pending'): ?>
                                        <button class="btn btn-secondary btn-sm" onclick="showQR('<?= $order['qr_code'] ?>')">
                                            <i class="fas fa-qrcode mr-1"></i>
                                            Lihat QR
                                        </button>
                                    <?php else: ?>
                                        <span class="text-gray-400 dark:text-gray-500">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cleaning Tab -->
    <div id="cleaning-tab" class="tab-content">
        <div class="card">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Riwayat Pembersihan</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Durasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Poin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($cleaning_history as $cleaning): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?= date('d/m/Y H:i', strtotime($cleaning['start_time'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($cleaning['bathroom_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?= $cleaning['duration_minutes'] ?? 0 ?> menit
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">
                                    +<?= $cleaning['points_earned'] ?? 0 ?> pts
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="status-badge status-<?= $cleaning['status'] ?>">
                                        <?= ucfirst($cleaning['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.tab-button {
    @apply py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600 transition-colors;
}

.tab-button.active {
    @apply border-blue-500 text-blue-600 dark:text-blue-400;
}

.tab-content {
    @apply hidden;
}

.tab-content.active {
    @apply block;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tabName = button.dataset.tab;
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            button.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        });
    });
});

function showQR(qrCode) {
    // Implementation for showing QR code modal
    alert('QR Code: ' + qrCode);
}
</script>

<?php
$content = ob_get_clean();
$title = 'Riwayat Aktivitas';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>