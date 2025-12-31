<?php
ob_start();
?>

<div class="p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Monitoring Transaksi</h1>
        <p class="text-gray-600">Pantau semua transaksi marketplace dan konfirmasi penerimaan barang</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="card">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Order</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['total_orders'] ?></p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Menunggu Pengambilan</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['pending_pickup'] ?></p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Sudah Diterima</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['received'] ?></p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-full">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Dibatalkan</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['cancelled'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-coins text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total Poin Terpakai</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_points']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card mb-6">
        <div class="flex flex-wrap gap-4 items-center">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status-filter" class="input">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu Pengambilan</option>
                    <option value="received">Sudah Diterima</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                <input type="date" id="date-filter" class="input">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Karyawan</label>
                <select id="employee-filter" class="input">
                    <option value="">Semua Karyawan</option>
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?= $employee['id'] ?>"><?= htmlspecialchars($employee['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="applyFilters()" class="btn btn-primary">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Transaksi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Poin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diterima</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="transactions-tbody">
                    <?php foreach ($orders as $order): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($order['order_number']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($order['user_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= $order['item_count'] ?> item
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= number_format($order['total_points']) ?> pts
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $status = trim($order['status'] ?? 'completed');
                                $is_received = !empty($order['received_at']);
                                
                                if ($is_received): ?>
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                        <i class="fas fa-check-circle mr-1"></i>Sudah Diterima
                                    </span>
                                <?php elseif ($status === 'completed'): ?>
                                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                        <i class="fas fa-clock mr-1"></i>Menunggu Pengambilan
                                    </span>
                                <?php elseif ($status === 'cancelled'): ?>
                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                        <i class="fas fa-times-circle mr-1"></i>Dibatalkan
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                        <i class="fas fa-question-circle mr-1"></i><?= ucfirst($status) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php 
                                if (isset($order['received_at']) && $order['received_at']) {
                                    echo date('d/m/Y H:i', strtotime($order['received_at']));
                                } elseif (isset($order['cancelled_at']) && $order['cancelled_at']) {
                                    echo '<span class="text-red-600">Dibatalkan: ' . date('d/m/Y H:i', strtotime($order['cancelled_at'])) . '</span>';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="viewOrderDetail('<?= $order['id'] ?>')" class="text-blue-600 hover:text-blue-900">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div id="order-detail-modal" class="modal-overlay">
    <div class="modal-container modal-large">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Detail Order</h3>
                <button onclick="closeModal('order-detail-modal')" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="order-detail-content">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<style>
/* Modal Styles */
.modal-overlay {
    @apply fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4;
    backdrop-filter: blur(4px);
}

.modal-overlay.active {
    @apply flex;
}

.modal-container {
    @apply bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all;
    animation: modalSlideIn 0.3s ease-out;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-large {
    @apply max-w-3xl;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-content {
    @apply p-6;
}

.modal-header {
    @apply flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-600;
}

.modal-title {
    @apply text-xl font-bold text-gray-900 dark:text-white;
}

.modal-close {
    @apply text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700;
}

.modal-body {
    @apply max-h-96 overflow-y-auto;
}
</style>

<script>
function applyFilters() {
    const status = document.getElementById('status-filter').value;
    const date = document.getElementById('date-filter').value;
    const employee = document.getElementById('employee-filter').value;
    
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (date) params.append('date', date);
    if (employee) params.append('employee', employee);
    
    window.location.href = '/sanipoint/admin/transaksi?' + params.toString();
}

function showModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function viewOrderDetail(orderId) {
    const orderDetailContent = document.getElementById('order-detail-content');
    orderDetailContent.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-3xl text-blue-600"></i><p class="mt-2 text-gray-600">Memuat detail order...</p></div>';
    showModal('order-detail-modal');
    
    fetch('/sanipoint/admin/transaksi?action=detail&order_id=' + orderId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                orderDetailContent.innerHTML = data.html;
            } else {
                orderDetailContent.innerHTML = '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-2"></i><p>Gagal memuat detail order</p><p class="text-sm">' + (data.message || 'Terjadi kesalahan') + '</p></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            orderDetailContent.innerHTML = '<div class="text-center py-8 text-red-600"><i class="fas fa-exclamation-triangle text-3xl mb-2"></i><p>Terjadi kesalahan sistem</p></div>';
        });
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});
</script>

<?php
$content = ob_get_clean();
$title = 'Monitoring Transaksi';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>