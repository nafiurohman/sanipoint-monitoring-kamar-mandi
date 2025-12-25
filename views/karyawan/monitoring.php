<?php
ob_start();
?>

<div class="p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Monitoring Kamar Mandi</h1>
        <p class="text-gray-600 dark:text-gray-400">Status real-time kamar mandi dan aktivitas pembersihan</p>
    </div>

    <!-- Bathroom Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <?php foreach ($bathrooms as $bathroom): ?>
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($bathroom['name']) ?></h3>
                    <span class="status-badge status-<?= str_replace('_', '-', $bathroom['status']) ?>">
                        <?= ucfirst(str_replace('_', ' ', $bathroom['status'])) ?>
                    </span>
                </div>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Lokasi:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($bathroom['location']) ?></span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Pengunjung:</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            <?= $bathroom['current_visitors'] ?>/<?= $bathroom['max_visitors'] ?>
                        </span>
                    </div>
                    
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?= ($bathroom['current_visitors'] / $bathroom['max_visitors']) * 100 ?>%"></div>
                    </div>
                    
                    <?php if ($bathroom['last_cleaned']): ?>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Terakhir dibersihkan:</span>
                            <span class="text-sm text-gray-900 dark:text-white"><?= date('H:i', strtotime($bathroom['last_cleaned'])) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($bathroom['status'] === 'needs_cleaning'): ?>
                        <button class="btn btn-primary w-full mt-4" onclick="startCleaning('<?= $bathroom['id'] ?>')">
                            <i class="fas fa-broom mr-2"></i>
                            Mulai Pembersihan
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Cleaning History -->
    <div class="card mb-8">
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
                                    <?= ucfirst($log['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sensor Data -->
    <div class="card">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Data Sensor Terkini</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($sensor_data as $sensor): ?>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-medium text-gray-900 dark:text-white"><?= strtoupper($sensor['sensor_type']) ?></h4>
                        <i class="fas fa-microchip text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                        <?= $sensor['value'] ?> <?= $sensor['unit'] ?>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <?= date('H:i:s', strtotime($sensor['recorded_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function startCleaning(bathroomId) {
    if (confirm('Mulai pembersihan kamar mandi ini?')) {
        fetch('/sanipoint/api/start-cleaning', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `bathroom_id=${bathroomId}&csrf_token=${document.querySelector('meta[name="csrf-token"]').content}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Terjadi kesalahan');
            }
        });
    }
}

// Auto refresh every 30 seconds
setInterval(() => {
    location.reload();
}, 30000);
</script>

<?php
$content = ob_get_clean();
$title = 'Monitoring Kamar Mandi';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>