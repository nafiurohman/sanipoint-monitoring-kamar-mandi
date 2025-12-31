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
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-1">
            <nav class="flex space-x-3">
                <button class="tab-button active btn btn-primary flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200" data-tab="transactions">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center mr-3">
                        <i class="fas fa-coins text-white text-sm"></i>
                    </div>
                    <span>Transaksi Poin</span>
                    <span class="ml-2 px-2 py-0.5 text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full"><?= count($transactions) ?></span>
                </button>
                <button class="tab-button btn btn-secondary flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200" data-tab="orders">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-shopping-bag text-white text-sm"></i>
                    </div>
                    <span>Pembelian</span>
                    <span class="ml-2 px-2 py-0.5 text-xs bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full"><?= count($orders) ?></span>
                </button>
                <button class="tab-button btn btn-secondary flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all duration-200" data-tab="cleaning">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-broom text-white text-sm"></i>
                    </div>
                    <span>Pembersihan</span>
                    <span class="ml-2 px-2 py-0.5 text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-full"><?= count($cleaning_history) ?></span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Transactions Tab -->
    <div id="transactions-tab" class="tab-content active">
        <!-- Filter Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-filter mr-2 text-blue-500"></i>Jenis Transaksi
                    </label>
                    <select id="transaction-type-filter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Jenis</option>
                        <option value="earned">Diperoleh</option>
                        <option value="spent">Digunakan</option>
                        <option value="transfer_in">Transfer Masuk</option>
                        <option value="transfer_out">Transfer Keluar</option>
                    </select>
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-calendar mr-2 text-green-500"></i>Tanggal
                    </label>
                    <input type="date" id="transaction-date-filter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <button onclick="filterTransactions()" class="btn btn-primary">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                </div>
                <div>
                    <button onclick="clearTransactionFilters()" class="btn btn-secondary">
                        <i class="fas fa-times mr-2"></i>Reset
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Transaksi Poin</h2>
                <button onclick="showDateRangeModal('transactions')" class="btn btn-primary">
                    <i class="fas fa-calendar-alt mr-2"></i>Filter Tanggal
                </button>
            </div>
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
        <!-- Filter Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-tag mr-2 text-green-500"></i>Status Order
                    </label>
                    <select id="order-status-filter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu Konfirmasi</option>
                        <option value="received">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-calendar mr-2 text-purple-500"></i>Tanggal Order
                    </label>
                    <input type="date" id="order-date-filter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <button onclick="filterOrders()" class="btn btn-primary">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                </div>
                <div>
                    <button onclick="clearOrderFilters()" class="btn btn-secondary">
                        <i class="fas fa-times mr-2"></i>Reset
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Pembelian</h2>
                <button onclick="showDateRangeModal('orders')" class="btn btn-primary">
                    <i class="fas fa-calendar-alt mr-2"></i>Filter Tanggal
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No. Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Poin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada riwayat pembelian
                                </td>
                            </tr>
                        <?php else: ?>
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
                                    $order_status = trim($order['status'] ?? 'completed');
                                    $is_received = !empty($order['received_at']);
                                    
                                    if ($is_received) {
                                        echo '<span class="px-2 py-1 text-xs font-medium rounded-full text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400"><i class="fas fa-check-circle mr-1"></i>Selesai</span>';
                                    } elseif ($order_status === 'completed') {
                                        echo '<span class="px-2 py-1 text-xs font-medium rounded-full text-yellow-600 bg-yellow-100 dark:bg-yellow-900/30 dark:text-yellow-400"><i class="fas fa-clock mr-1"></i>Menunggu Konfirmasi</span>';
                                    } elseif ($order_status === 'cancelled') {
                                        echo '<span class="px-2 py-1 text-xs font-medium rounded-full text-red-600 bg-red-100 dark:bg-red-900/30 dark:text-red-400"><i class="fas fa-times-circle mr-1"></i>Dibatalkan</span>';
                                    } else {
                                        echo '<span class="px-2 py-1 text-xs font-medium rounded-full text-gray-600 bg-gray-100 dark:bg-gray-900/30 dark:text-gray-400"><i class="fas fa-question-circle mr-1"></i>Tidak Diketahui</span>';
                                    }
                                    ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex flex-wrap gap-2">
                                        <button onclick="viewOrderDetail('<?= $order['id'] ?>')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 rounded-md transition-colors">
                                            <i class="fas fa-eye mr-1.5"></i>Detail
                                        </button>
                                        <?php if ($order_status === 'completed' && empty($order['received_at'])): ?>
                                            <button onclick="confirmReceived('<?= $order['id'] ?>')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 hover:bg-green-100 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50 rounded-md transition-colors">
                                                <i class="fas fa-check mr-1.5"></i>Konfirmasi
                                            </button>
                                            <button onclick="cancelOrder('<?= $order['id'] ?>')" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 rounded-md transition-colors">
                                                <i class="fas fa-times mr-1.5"></i>Batal
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Cleaning Tab -->
    <div id="cleaning-tab" class="tab-content">
        <!-- Filter Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-map-marker-alt mr-2 text-purple-500"></i>Lokasi
                    </label>
                    <select id="cleaning-location-filter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Semua Lokasi</option>
                        <?php foreach (array_unique(array_column($cleaning_history, 'bathroom_name')) as $location): ?>
                            <option value="<?= htmlspecialchars($location) ?>"><?= htmlspecialchars($location) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <i class="fas fa-calendar mr-2 text-orange-500"></i>Tanggal
                    </label>
                    <input type="date" id="cleaning-date-filter" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <button onclick="filterCleaning()" class="btn btn-primary">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                </div>
                <div>
                    <button onclick="clearCleaningFilters()" class="btn btn-secondary">
                        <i class="fas fa-times mr-2"></i>Reset
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Riwayat Pembersihan</h2>
                <button onclick="showDateRangeModal('cleaning')" class="btn btn-primary">
                    <i class="fas fa-calendar-alt mr-2"></i>Filter Tanggal
                </button>
            </div>
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

<!-- Modal Konfirmasi -->
<div id="confirmModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-content">
            <div class="modal-icon modal-icon-warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="modal-title">Konfirmasi Aksi</h3>
            <p class="modal-message" id="confirmMessage">Apakah Anda yakin?</p>
            <div class="modal-actions">
                <button onclick="closeModal('confirmModal')" class="btn-secondary">
                    Batal
                </button>
                <button id="confirmButton" class="btn-primary">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Alert -->
<div id="alertModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-content">
            <div class="modal-icon" id="alertIcon">
                <i class="fas fa-check"></i>
            </div>
            <h3 class="modal-title" id="alertTitle">Berhasil</h3>
            <p class="modal-message" id="alertMessage">Operasi berhasil dilakukan</p>
            <div class="modal-actions">
                <button onclick="closeAlertModal()" class="btn-primary">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Order -->
<div id="orderDetailModal" class="modal-overlay">
    <div class="modal-container modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Detail Pesanan</h3>
                <button onclick="closeModal('orderDetailModal')" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Date Range Filter -->
<div id="dateRangeModal" class="modal-overlay">
    <div class="modal-container">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Filter Rentang Tanggal</h3>
                <button onclick="closeModal('dateRangeModal')" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div class="text-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-calendar-alt text-xl text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Pilih periode untuk memfilter data</p>
                </div>
                
                <!-- Quick Filter Buttons -->
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <button onclick="setDateRange('week')" class="btn btn-secondary">
                        <i class="fas fa-calendar-week mr-1"></i>Minggu Ini
                    </button>
                    <button onclick="setDateRange('month')" class="btn btn-secondary">
                        <i class="fas fa-calendar-alt mr-1"></i>Bulan Ini
                    </button>
                    <button onclick="setDateRange('custom')" class="btn btn-primary">
                        <i class="fas fa-cog mr-1"></i>Custom
                    </button>
                </div>
                
                <!-- Custom Date Inputs -->
                <div id="customDateInputs" class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tanggal Mulai
                        </label>
                        <input type="date" id="dateRangeStart" class="w-full px-2 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Tanggal Selesai
                        </label>
                        <input type="date" id="dateRangeEnd" class="w-full px-2 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                
                <div class="flex gap-2 pt-3">
                    <button onclick="applyDateRangeFilter()" class="btn btn-primary flex-1">
                        <i class="fas fa-filter mr-2"></i>Terapkan
                    </button>
                    <button onclick="clearDateRangeFilter()" class="btn btn-secondary flex-1">
                        <i class="fas fa-times mr-2"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tab-button {
    /* Base styles handled by btn component classes */
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: none;
}

.modal-overlay.active {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.modal-container {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    max-width: 28rem;
    width: 100%;
    margin: 0 1rem;
    transform: scale(0.95);
    transition: all 0.3s ease-out;
    z-index: 10000;
    position: relative;
}

.modal-overlay.active .modal-container {
    transform: scale(1);
}

.modal-large {
    max-width: 48rem;
}

.modal-content {
    padding: 1.5rem;
    position: relative;
    z-index: 10001;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-icon {
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.875rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

.modal-icon-warning {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #d97706;
}

.modal-icon-success {
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    color: #16a34a;
}

.modal-icon-error {
    background: linear-gradient(135deg, #fee2e2, #fecaca);
    color: #dc2626;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    text-align: center;
    margin-bottom: 0.5rem;
}

.modal-message {
    color: #6b7280;
    text-align: center;
    margin-bottom: 1.5rem;
    line-height: 1.625;
}

.modal-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}

.modal-close {
    color: #9ca3af;
    transition: color 0.2s;
    padding: 0.25rem;
    border-radius: 50%;
    background: none;
    border: none;
    cursor: pointer;
}

.modal-close:hover {
    color: #4b5563;
    background-color: #f3f4f6;
}

.modal-body {
    max-height: 24rem;
    overflow-y: auto;
}

.btn-primary {
    padding: 0.625rem 1.5rem;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: white;
    border-radius: 0.5rem;
    font-weight: 500;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    transition: all 0.2s;
    transform: scale(1);
    border: none;
    cursor: pointer;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    transform: scale(1.05);
}

.btn-secondary {
    padding: 0.625rem 1.5rem;
    background-color: #e5e7eb;
    color: #374151;
    border-radius: 0.5rem;
    font-weight: 500;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-secondary:hover {
    background-color: #d1d5db;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

/* Dark mode styles */
.dark .modal-container {
    background: #1f2937;
    color: white;
}

.dark .modal-header {
    border-bottom-color: #374151;
}

.dark .modal-title {
    color: white;
}

.dark .modal-message {
    color: #9ca3af;
}

.dark .modal-close:hover {
    color: #d1d5db;
    background-color: #374151;
}

.dark .btn-secondary {
    background-color: #374151;
    color: #d1d5db;
}

.dark .btn-secondary:hover {
    background-color: #4b5563;
}

.dark .modal-icon-warning {
    background: linear-gradient(135deg, rgba(217, 119, 6, 0.2), rgba(217, 119, 6, 0.3));
    color: #fbbf24;
}

.dark .modal-icon-success {
    background: linear-gradient(135deg, rgba(22, 163, 74, 0.2), rgba(22, 163, 74, 0.3));
    color: #4ade80;
}

.dark .modal-icon-error {
    background: linear-gradient(135deg, rgba(220, 38, 38, 0.2), rgba(220, 38, 38, 0.3));
    color: #f87171;
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
            
            // Remove active classes and add secondary
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-secondary');
            });
            
            // Remove active from tab contents
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active to clicked button
            button.classList.add('active');
            button.classList.remove('btn-secondary');
            button.classList.add('btn-primary');
            
            // Show corresponding tab content
            document.getElementById(tabName + '-tab').classList.add('active');
        });
    });
});

// Modal Functions
function showModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function showAlert(title, message, type = 'success') {
    const alertModal = document.getElementById('alertModal');
    const alertIcon = document.getElementById('alertIcon');
    const alertTitle = document.getElementById('alertTitle');
    const alertMessage = document.getElementById('alertMessage');
    
    // Set icon based on type
    alertIcon.className = 'modal-icon modal-icon-' + type;
    if (type === 'success') {
        alertIcon.innerHTML = '<i class="fas fa-check"></i>';
    } else if (type === 'error') {
        alertIcon.innerHTML = '<i class="fas fa-times"></i>';
    } else if (type === 'warning') {
        alertIcon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
    }
    
    alertTitle.textContent = title;
    alertMessage.textContent = message;
    showModal('alertModal');
}

function closeAlertModal() {
    closeModal('alertModal');
    location.reload();
}

function cancelOrder(orderId) {
    const confirmModal = document.getElementById('confirmModal');
    const confirmMessage = document.getElementById('confirmMessage');
    const confirmButton = document.getElementById('confirmButton');
    
    confirmMessage.textContent = 'Yakin ingin membatalkan order ini? Poin akan dikembalikan.';
    confirmButton.onclick = function() {
        closeModal('confirmModal');
        processCancelOrder(orderId);
    };
    
    showModal('confirmModal');
}

function processCancelOrder(orderId) {
    const formData = new FormData();
    formData.append('action', 'cancel_order');
    formData.append('order_id', orderId);
    formData.append('csrf_token', '<?= Security::generateCSRFToken() ?>');
    
    fetch('/sanipoint/karyawan/riwayat', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Berhasil', 'Order berhasil dibatalkan! Poin telah dikembalikan.', 'success');
        } else {
            showAlert('Gagal', data.message || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error', 'Terjadi kesalahan sistem', 'error');
    });
}

function confirmReceived(orderId) {
    const confirmModal = document.getElementById('confirmModal');
    const confirmMessage = document.getElementById('confirmMessage');
    const confirmButton = document.getElementById('confirmButton');
    
    confirmMessage.textContent = 'Konfirmasi bahwa Anda sudah menerima barang untuk order ini?';
    confirmButton.onclick = function() {
        closeModal('confirmModal');
        processConfirmReceived(orderId);
    };
    
    showModal('confirmModal');
}

function processConfirmReceived(orderId) {
    const formData = new FormData();
    formData.append('action', 'confirm_received');
    formData.append('order_id', orderId);
    formData.append('csrf_token', '<?= Security::generateCSRFToken() ?>');
    
    fetch('/sanipoint/karyawan/riwayat', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Berhasil', 'Konfirmasi penerimaan barang berhasil!', 'success');
        } else {
            showAlert('Gagal', data.message || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error', 'Terjadi kesalahan sistem', 'error');
    });
}

function viewOrderDetail(orderId) {
    // Show loading
    const orderDetailContent = document.getElementById('orderDetailContent');
    orderDetailContent.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-3xl text-blue-600"></i></div>';
    showModal('orderDetailModal');
    
    // Fetch order details
    fetch(`/sanipoint/karyawan/riwayat?action=get_order_detail&order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayOrderDetail(data.order);
            } else {
                orderDetailContent.innerHTML = '<div class="text-center py-8 text-red-600">Gagal memuat detail order</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            orderDetailContent.innerHTML = '<div class="text-center py-8 text-red-600">Terjadi kesalahan</div>';
        });
}

function displayOrderDetail(order) {
    const orderDetailContent = document.getElementById('orderDetailContent');
    
    let itemsHtml = '';
    if (order.items && order.items.length > 0) {
        order.items.forEach(item => {
            itemsHtml += `
                <div class="flex justify-between items-center py-3 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <i class="fas fa-box text-gray-500 dark:text-gray-400"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">${item.product_name}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Qty: ${item.quantity} × ${parseInt(item.points_per_item).toLocaleString()} pts</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900 dark:text-white">${parseInt(item.total_points).toLocaleString()} pts</p>
                    </div>
                </div>
            `;
        });
    } else {
        itemsHtml = '<div class="text-center py-8 text-gray-500 dark:text-gray-400">Tidak ada item</div>';
    }
    
    let statusBadge, statusIcon;
    const isReceived = order.received_at && order.received_at !== null;
    
    if (isReceived) {
        statusBadge = '<span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full text-green-700 bg-green-100 dark:bg-green-900/30 dark:text-green-400"><i class="fas fa-check-circle mr-2"></i>Selesai</span>';
        statusIcon = 'fas fa-check-circle text-green-500';
    } else if (order.status === 'completed') {
        statusBadge = '<span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full text-yellow-700 bg-yellow-100 dark:bg-yellow-900/30 dark:text-yellow-400"><i class="fas fa-clock mr-2"></i>Menunggu Konfirmasi</span>';
        statusIcon = 'fas fa-clock text-yellow-500';
    } else if (order.status === 'cancelled') {
        statusBadge = '<span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full text-red-700 bg-red-100 dark:bg-red-900/30 dark:text-red-400"><i class="fas fa-times-circle mr-2"></i>Dibatalkan</span>';
        statusIcon = 'fas fa-times-circle text-red-500';
    } else {
        statusBadge = '<span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full text-gray-700 bg-gray-100 dark:bg-gray-900/30 dark:text-gray-400"><i class="fas fa-question-circle mr-2"></i>Tidak Diketahui</span>';
        statusIcon = 'fas fa-question-circle text-gray-500';
    }
    
    const orderDate = new Date(order.created_at);
    const receivedDate = order.received_at ? new Date(order.received_at) : null;
    const cancelledDate = order.cancelled_at ? new Date(order.cancelled_at) : null;
    
    orderDetailContent.innerHTML = `
        <div class="space-y-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-600">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-receipt text-blue-600 dark:text-blue-400"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 dark:text-white">${order.order_number}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">${orderDate.toLocaleString('id-ID', { 
                                        weekday: 'long', 
                                        year: 'numeric', 
                                        month: 'long', 
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    })}</p>
                                </div>
                            </div>
                            ${receivedDate ? `<p class="text-sm text-green-600 dark:text-green-400 flex items-center"><i class="fas fa-check-circle mr-2"></i>Diterima: ${receivedDate.toLocaleString('id-ID')}</p>` : ''}
                            ${cancelledDate ? `<p class="text-sm text-red-600 dark:text-red-400 flex items-center"><i class="fas fa-times-circle mr-2"></i>Dibatalkan: ${cancelledDate.toLocaleString('id-ID')}</p>` : ''}
                        </div>
                        <div class="text-center sm:text-right">
                            ${statusBadge}
                            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-2">${parseInt(order.total_points).toLocaleString()}<span class="text-lg text-gray-500"> pts</span></p>
                        </div>
                    </div>
                </div>
            
            <!-- Items List -->
            <div>
                <h4 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <i class="fas fa-shopping-bag mr-2 text-blue-600"></i>
                    Item Pesanan (${order.items ? order.items.length : 0})
                </h4>
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 divide-y divide-gray-200 dark:divide-gray-600">
                    ${itemsHtml}
                </div>
            </div>
            
            <!-- Action Buttons -->
            ${order.status === 'completed' && !isReceived ? `
                <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200 dark:border-gray-600">
                    <button onclick="closeModal('orderDetailModal'); confirmReceived('${order.id}');" class="flex-1 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-6 py-3 rounded-lg font-medium transition-all shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center justify-center">
                        <i class="fas fa-check-circle mr-2"></i>Konfirmasi Terima
                    </button>
                    <button onclick="closeModal('orderDetailModal'); cancelOrder('${order.id}');" class="flex-1 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-6 py-3 rounded-lg font-medium transition-all shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center justify-center">
                        <i class="fas fa-times-circle mr-2"></i>Batalkan Order
                    </button>
                </div>
            ` : ''}
        </div>
    `;
}

// Date Range Filter Functions
let currentFilterTab = '';

function showDateRangeModal(tabType) {
    currentFilterTab = tabType;
    
    // Reset to custom mode
    setDateRange('custom');
    
    showModal('dateRangeModal');
}

function setDateRange(type) {
    const startInput = document.getElementById('dateRangeStart');
    const endInput = document.getElementById('dateRangeEnd');
    const today = new Date();
    
    // Reset button styles
    document.querySelectorAll('#dateRangeModal button[onclick^="setDateRange"]').forEach(btn => {
        btn.className = 'btn btn-secondary';
    });
    
    if (type === 'week') {
        // This week (Monday to Sunday)
        const monday = new Date(today);
        monday.setDate(today.getDate() - today.getDay() + 1);
        const sunday = new Date(monday);
        sunday.setDate(monday.getDate() + 6);
        
        startInput.value = monday.toISOString().split('T')[0];
        endInput.value = sunday.toISOString().split('T')[0];
        
        event.target.className = 'btn btn-primary';
    } else if (type === 'month') {
        // This month
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        startInput.value = firstDay.toISOString().split('T')[0];
        endInput.value = lastDay.toISOString().split('T')[0];
        
        event.target.className = 'btn btn-primary';
    } else {
        // Custom - clear inputs
        startInput.value = '';
        endInput.value = '';
        
        event.target.className = 'btn btn-primary';
    }
}

function applyDateRangeFilter() {
    const startDate = document.getElementById('dateRangeStart').value;
    const endDate = document.getElementById('dateRangeEnd').value;
    
    if (!startDate && !endDate) {
        clearDateRangeFilter();
        return;
    }
    
    const tabSelector = `#${currentFilterTab}-tab tbody tr`;
    const rows = document.querySelectorAll(tabSelector);
    
    rows.forEach(row => {
        if (row.cells.length === 0) return;
        
        const dateCell = row.cells[0]?.textContent.trim();
        if (!dateCell) return;
        
        // Parse date from "dd/mm/yyyy hh:mm" format
        const dateParts = dateCell.split(' ')[0].split('/');
        const rowDate = new Date(`${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`);
        
        let showRow = true;
        
        if (startDate) {
            const filterStartDate = new Date(startDate);
            if (rowDate < filterStartDate) {
                showRow = false;
            }
        }
        
        if (endDate && showRow) {
            const filterEndDate = new Date(endDate);
            filterEndDate.setHours(23, 59, 59, 999);
            if (rowDate > filterEndDate) {
                showRow = false;
            }
        }
        
        row.style.display = showRow ? '' : 'none';
    });
    
    closeModal('dateRangeModal');
    
    // Show success message
    const startStr = startDate ? new Date(startDate).toLocaleDateString('id-ID') : 'Awal';
    const endStr = endDate ? new Date(endDate).toLocaleDateString('id-ID') : 'Akhir';
    showAlert('Filter Diterapkan', `Data difilter dari ${startStr} sampai ${endStr}`, 'success');
}

function clearDateRangeFilter() {
    const tabSelector = `#${currentFilterTab}-tab tbody tr`;
    document.querySelectorAll(tabSelector).forEach(row => {
        row.style.display = '';
    });
    
    document.getElementById('dateRangeStart').value = '';
    document.getElementById('dateRangeEnd').value = '';
    
    if (currentFilterTab) {
        closeModal('dateRangeModal');
        showAlert('Filter Dihapus', 'Semua data ditampilkan kembali', 'success');
    }
}

// Filter Functions
function filterTransactions() {
    const typeFilter = document.getElementById('transaction-type-filter').value;
    const dateFilter = document.getElementById('transaction-date-filter').value;
    const rows = document.querySelectorAll('#transactions-tab tbody tr');
    
    rows.forEach(row => {
        const typeCell = row.cells[1]?.textContent.trim();
        const dateCell = row.cells[0]?.textContent.trim();
        
        let showRow = true;
        
        if (typeFilter && !typeCell.includes(getTypeLabel(typeFilter))) {
            showRow = false;
        }
        
        if (dateFilter && dateCell) {
            const rowDate = new Date(dateCell.split(' ')[0].split('/').reverse().join('-'));
            const filterDate = new Date(dateFilter);
            if (rowDate.toDateString() !== filterDate.toDateString()) {
                showRow = false;
            }
        }
        
        row.style.display = showRow ? '' : 'none';
    });
}

function getTypeLabel(type) {
    const labels = {
        'earned': 'Diperoleh',
        'spent': 'Digunakan', 
        'transfer_in': 'Transfer Masuk',
        'transfer_out': 'Transfer Keluar'
    };
    return labels[type] || '';
}

function clearTransactionFilters() {
    document.getElementById('transaction-type-filter').value = '';
    document.getElementById('transaction-date-filter').value = '';
    document.querySelectorAll('#transactions-tab tbody tr').forEach(row => {
        row.style.display = '';
    });
}

function filterOrders() {
    const statusFilter = document.getElementById('order-status-filter').value;
    const dateFilter = document.getElementById('order-date-filter').value;
    const rows = document.querySelectorAll('#orders-tab tbody tr');
    
    rows.forEach(row => {
        if (row.cells.length < 5) return;
        
        const statusCell = row.cells[3]?.textContent.trim();
        const dateCell = row.cells[0]?.textContent.trim();
        
        let showRow = true;
        
        if (statusFilter) {
            const statusLabel = getOrderStatusLabel(statusFilter);
            if (!statusCell.includes(statusLabel)) {
                showRow = false;
            }
        }
        
        if (dateFilter && dateCell) {
            const rowDate = new Date(dateCell.split(' ')[0].split('/').reverse().join('-'));
            const filterDate = new Date(dateFilter);
            if (rowDate.toDateString() !== filterDate.toDateString()) {
                showRow = false;
            }
        }
        
        row.style.display = showRow ? '' : 'none';
    });
}

function getOrderStatusLabel(status) {
    const labels = {
        'pending': 'Menunggu Konfirmasi',
        'received': 'Selesai',
        'cancelled': 'Dibatalkan'
    };
    return labels[status] || '';
}

function clearOrderFilters() {
    document.getElementById('order-status-filter').value = '';
    document.getElementById('order-date-filter').value = '';
    document.querySelectorAll('#orders-tab tbody tr').forEach(row => {
        row.style.display = '';
    });
}

function filterCleaning() {
    const locationFilter = document.getElementById('cleaning-location-filter').value;
    const dateFilter = document.getElementById('cleaning-date-filter').value;
    const rows = document.querySelectorAll('#cleaning-tab tbody tr');
    
    rows.forEach(row => {
        const locationCell = row.cells[1]?.textContent.trim();
        const dateCell = row.cells[0]?.textContent.trim();
        
        let showRow = true;
        
        if (locationFilter && locationCell !== locationFilter) {
            showRow = false;
        }
        
        if (dateFilter && dateCell) {
            const rowDate = new Date(dateCell.split(' ')[0].split('/').reverse().join('-'));
            const filterDate = new Date(dateFilter);
            if (rowDate.toDateString() !== filterDate.toDateString()) {
                showRow = false;
            }
        }
        
        row.style.display = showRow ? '' : 'none';
    });
}

function clearCleaningFilters() {
    document.getElementById('cleaning-location-filter').value = '';
    document.getElementById('cleaning-date-filter').value = '';
    document.querySelectorAll('#cleaning-tab tbody tr').forEach(row => {
        row.style.display = '';
    });
}

// Close modal when clicking outside
document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});
</script>

<?php
$content = ob_get_clean();
$title = 'Riwayat Aktivitas';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>