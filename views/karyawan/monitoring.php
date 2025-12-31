<?php
ob_start();
?>

<div class="p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Monitoring IoT Kamar Mandi</h1>
        <p class="text-gray-600 dark:text-gray-400">Status real-time kamar mandi dan sensor IoT</p>
        <div class="mt-2 flex items-center text-sm text-green-600 dark:text-green-400">
            <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
            <span id="connection-status">Terhubung ke sistem IoT</span>
            <span class="ml-4 text-gray-500 dark:text-gray-400" id="last-update">Terakhir update: <span id="update-time">-</span></span>
        </div>
    </div>

    <!-- IoT Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <?php foreach ($bathrooms as $bathroom): ?>
            <div class="card border-l-4 <?= $bathroom['status'] === 'needs_cleaning' ? 'border-red-500' : ($bathroom['status'] === 'being_cleaned' ? 'border-yellow-500' : 'border-green-500') ?>">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($bathroom['name']) ?></h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($bathroom['location']) ?></p>
                    </div>
                    <div class="text-right">
                        <span class="status-badge status-<?= str_replace('_', '-', $bathroom['status']) ?>">
                            <?php 
                            $status_labels = [
                                'available' => 'Tersedia',
                                'needs_cleaning' => 'Perlu Dibersihkan',
                                'being_cleaned' => 'Sedang Dibersihkan',
                                'maintenance' => 'Maintenance'
                            ];
                            echo $status_labels[$bathroom['status']] ?? ucfirst($bathroom['status']);
                            ?>
                        </span>
                        <?php if ($bathroom['is_being_cleaned'] > 0): ?>
                            <div class="mt-1 text-xs text-yellow-600 dark:text-yellow-400">
                                <i class="fas fa-broom mr-1"></i>Sedang dibersihkan
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Sensor Data -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <!-- Visitor Counter -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-300">Pengunjung</span>
                            <i class="fas fa-users text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div class="text-xl font-bold text-blue-900 dark:text-blue-100">
                            <?= $bathroom['current_visitors'] ?>/<?= $bathroom['max_visitors'] ?>
                        </div>
                        <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2 mt-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                 style="width: <?= min(100, ($bathroom['current_visitors'] / $bathroom['max_visitors']) * 100) ?>%"></div>
                        </div>
                    </div>
                    
                    <!-- Gas Level -->
                    <div class="bg-<?= ($bathroom['gas_level'] ?? 0) > 1800 ? 'red' : 'green' ?>-50 dark:bg-<?= ($bathroom['gas_level'] ?? 0) > 1800 ? 'red' : 'green' ?>-900/20 rounded-lg p-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-<?= ($bathroom['gas_level'] ?? 0) > 1800 ? 'red' : 'green' ?>-700 dark:text-<?= ($bathroom['gas_level'] ?? 0) > 1800 ? 'red' : 'green' ?>-300">Kualitas Udara</span>
                            <i class="fas fa-wind text-<?= ($bathroom['gas_level'] ?? 0) > 1800 ? 'red' : 'green' ?>-600 dark:text-<?= ($bathroom['gas_level'] ?? 0) > 1800 ? 'red' : 'green' ?>-400"></i>
                        </div>
                        <div class="text-xl font-bold text-<?= ($bathroom['gas_level'] ?? 0) > 1800 ? 'red' : 'green' ?>-900 dark:text-<?= ($bathroom['gas_level'] ?? 0) > 1800 ? 'red' : 'green' ?>-100">
                            <?= $bathroom['gas_level'] ?? 0 ?> ppm
                        </div>
                        <div class="text-xs text-<?= ($bathroom['gas_level'] ?? 0) > 1800 ? 'red' : 'green' ?>-600 dark:text-<?= ($bathroom['gas_level'] ?? 0) > 1800 ? 'red' : 'green' ?>-400 mt-1">
                            <?= ($bathroom['gas_level'] ?? 0) > 1800 ? 'Buruk' : 'Baik' ?>
                        </div>
                    </div>
                </div>
                
                <!-- Last Update Info -->
                <div class="flex justify-between items-center text-sm text-gray-500 dark:text-gray-400 mb-4">
                    <span>Update terakhir:</span>
                    <span><?= $bathroom['updated_at'] ? date('H:i:s', strtotime($bathroom['updated_at'])) : '-' ?></span>
                </div>
                
                <!-- Action Buttons -->
                <?php if ($bathroom['status'] === 'needs_cleaning' && $bathroom['is_being_cleaned'] == 0): ?>
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-red-800 dark:text-red-200">Perlu Pembersihan</p>
                                <p class="text-xs text-red-600 dark:text-red-400">Tap kartu RFID untuk mulai</p>
                            </div>
                            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
                        </div>
                    </div>
                <?php elseif ($bathroom['is_being_cleaned'] > 0): ?>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Sedang Dibersihkan</p>
                                <p class="text-xs text-yellow-600 dark:text-yellow-400">Tap kartu RFID untuk selesai</p>
                            </div>
                            <i class="fas fa-broom text-yellow-500 text-xl animate-pulse"></i>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-800 dark:text-green-200">Siap Digunakan</p>
                                <p class="text-xs text-green-600 dark:text-green-400">Kondisi normal</p>
                            </div>
                            <i class="fas fa-check-circle text-green-500 text-xl"></i>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Real-time Sensor Data -->
    <div class="card mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Data Sensor Real-time</h2>
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Live</span>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php 
            $sensor_types = ['ir' => 'Sensor Pengunjung', 'mq135' => 'Sensor Gas', 'rfid' => 'RFID Reader'];
            $sensor_icons = ['ir' => 'fa-eye', 'mq135' => 'fa-wind', 'rfid' => 'fa-id-card'];
            $grouped_sensors = [];
            
            foreach ($sensor_data as $sensor) {
                $grouped_sensors[$sensor['sensor_type']][] = $sensor;
            }
            
            foreach ($sensor_types as $type => $label):
                $sensors = $grouped_sensors[$type] ?? [];
            ?>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-medium text-gray-900 dark:text-white"><?= $label ?></h4>
                        <i class="fas <?= $sensor_icons[$type] ?> text-blue-600 dark:text-blue-400"></i>
                    </div>
                    
                    <?php if (!empty($sensors)): ?>
                        <?php foreach ($sensors as $sensor): ?>
                            <div class="mb-3 last:mb-0">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($sensor['bathroom_name']) ?></span>
                                    <span class="text-xs text-gray-500 dark:text-gray-500">
                                        <?= $sensor['recorded_at'] ? date('H:i:s', strtotime($sensor['recorded_at'])) : '-' ?>
                                    </span>
                                </div>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">
                                    <?= $sensor['value'] ?? 0 ?> <?= $sensor['unit'] ?? '' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                            <i class="fas fa-exclamation-circle mb-2"></i>
                            <p class="text-sm">Tidak ada data</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Usage Logs -->
    <?php if (!empty($usage_logs)): ?>
    <div class="card mb-8">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Log Aktivitas IoT</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengguna</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($usage_logs as $log): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <?= date('d/m/Y H:i:s', strtotime($log['waktu'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <?= htmlspecialchars($log['bathroom_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $action_colors = [
                                    'enter' => 'text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400',
                                    'exit' => 'text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400',
                                    'admin_reset' => 'text-red-600 bg-red-100 dark:bg-red-900/30 dark:text-red-400',
                                    'start_cleaning' => 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    'finish_cleaning' => 'text-purple-600 bg-purple-100 dark:bg-purple-900/30 dark:text-purple-400'
                                ];
                                $action_labels = [
                                    'enter' => 'Masuk',
                                    'exit' => 'Keluar',
                                    'admin_reset' => 'Reset Admin',
                                    'start_cleaning' => 'Mulai Bersih',
                                    'finish_cleaning' => 'Selesai Bersih'
                                ];
                                ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?= $action_colors[$log['action_type']] ?? 'text-gray-600 bg-gray-100 dark:bg-gray-900/30 dark:text-gray-400' ?>">
                                    <?= $action_labels[$log['action_type']] ?? ucfirst($log['action_type']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <?= htmlspecialchars($log['user_name'] ?? 'System') ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                <?= htmlspecialchars($log['keterangan'] ?? '-') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Cleaning History -->
    <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Riwayat Pembersihan Saya</h2>
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
                    <?php if (!empty($cleaning_logs)): ?>
                        <?php foreach ($cleaning_logs as $log): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?= date('d/m/Y H:i', strtotime($log['start_time'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?= htmlspecialchars($log['bathroom_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    <?= $log['duration_minutes'] ?? 0 ?> menit
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">
                                    +<?= $log['points_earned'] ?? 0 ?> pts
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="status-badge status-<?= $log['status'] ?>">
                                        <?php
                                        $status_labels = [
                                            'in_progress' => 'Berlangsung',
                                            'completed' => 'Selesai',
                                            'cancelled' => 'Dibatalkan'
                                        ];
                                        echo $status_labels[$log['status']] ?? ucfirst($log['status']);
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-broom text-4xl mb-4 opacity-50"></i>
                                <p>Belum ada riwayat pembersihan</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Auto refresh every 5 seconds for real-time updates
let refreshInterval;

function startAutoRefresh() {
    refreshInterval = setInterval(() => {
        updateMonitoringData();
    }, 5000);
}

function updateMonitoringData() {
    fetch('/sanipoint/karyawan/monitoring', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.ok) {
            document.getElementById('update-time').textContent = new Date().toLocaleTimeString();
            // You can add more specific updates here without full page reload
        }
    })
    .catch(error => {
        console.error('Error updating monitoring data:', error);
        document.getElementById('connection-status').textContent = 'Koneksi terputus';
        document.getElementById('connection-status').previousElementSibling.classList.remove('bg-green-500', 'animate-pulse');
        document.getElementById('connection-status').previousElementSibling.classList.add('bg-red-500');
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('update-time').textContent = new Date().toLocaleTimeString();
    startAutoRefresh();
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
</script>

<?php
$content = ob_get_clean();
$title = 'Monitoring IoT Kamar Mandi';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>