<?php
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';

// Allow both admin and karyawan to access monitoring
if (!Auth::isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$user = Auth::getUser();
$db = Database::getInstance();

// Get bathrooms with real-time status
$bathrooms = $db->fetchAll("
    SELECT b.*, 
           COALESCE(cl.user_name, '') as cleaning_by,
           CASE 
               WHEN cl.status = 'in_progress' THEN 'being_cleaned'
               WHEN b.current_visitors >= b.max_visitors THEN 'needs_cleaning'
               ELSE 'available'
           END as computed_status
    FROM bathrooms b
    LEFT JOIN (
        SELECT bathroom_id, u.full_name as user_name, status
        FROM cleaning_logs cl
        JOIN users u ON cl.user_id = u.id
        WHERE cl.status = 'in_progress'
    ) cl ON b.id = cl.bathroom_id
    WHERE b.is_active = 1
    ORDER BY b.id
");

// Get user's cleaning history if karyawan
$cleaning_logs = [];
if ($user['role'] === 'karyawan') {
    $cleaning_logs = $db->fetchAll("
        SELECT cl.*, b.name as bathroom_name
        FROM cleaning_logs cl
        JOIN bathrooms b ON cl.bathroom_id = b.id
        WHERE cl.user_id = ?
        ORDER BY cl.created_at DESC
        LIMIT 10
    ", [$user['id']]);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Real-time - SANIPOINT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../views/layouts/karyawan_nav.php'; ?>
    
    <div class="p-6">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Monitoring Real-time</h1>
            <p class="text-gray-600">Status kamar mandi dan sensor IoT</p>
            <div class="flex items-center mt-2">
                <div id="connection-status" class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-2"></div>
                <span class="text-sm text-gray-600">Connected</span>
            </div>
        </div>
        
        <!-- Toilet Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <?php foreach ($bathrooms as $bathroom): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden" data-bathroom-id="<?= $bathroom['id'] ?>">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($bathroom['name']) ?></h3>
                            <div class="status-indicator">
                                <?php
                                $statusClass = 'bg-gray-100 text-gray-800';
                                $statusIcon = 'fas fa-question-circle';
                                
                                switch ($bathroom['computed_status']) {
                                    case 'available':
                                        $statusClass = 'bg-green-100 text-green-800';
                                        $statusIcon = 'fas fa-check-circle';
                                        break;
                                    case 'needs_cleaning':
                                        $statusClass = 'bg-red-100 text-red-800';
                                        $statusIcon = 'fas fa-exclamation-triangle';
                                        break;
                                    case 'being_cleaned':
                                        $statusClass = 'bg-yellow-100 text-yellow-800';
                                        $statusIcon = 'fas fa-broom';
                                        break;
                                }
                                ?>
                                <span class="px-3 py-1 text-sm font-medium rounded-full <?= $statusClass ?>">
                                    <i class="<?= $statusIcon ?> mr-1"></i>
                                    <?= ucfirst(str_replace('_', ' ', $bathroom['computed_status'])) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600 visitor-count">
                                    <?= $bathroom['current_visitors'] ?>/<?= $bathroom['max_visitors'] ?>
                                </div>
                                <div class="text-sm text-blue-600">Pengunjung</div>
                            </div>
                            
                            <div class="text-center p-3 bg-purple-50 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600 gas-level">
                                    <?= rand(200, 400) ?>
                                </div>
                                <div class="text-sm text-purple-600">Gas Level (ppm)</div>
                            </div>
                        </div>
                        
                        <?php if ($bathroom['last_cleaned']): ?>
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-clock mr-1"></i>
                                Terakhir dibersihkan: <?= date('d/m H:i', strtotime($bathroom['last_cleaned'])) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($bathroom['cleaning_by']): ?>
                            <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-center">
                                    <i class="fas fa-user text-yellow-600 mr-2"></i>
                                    <span class="text-sm text-yellow-800">Sedang dibersihkan oleh <?= htmlspecialchars($bathroom['cleaning_by']) ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Sensor Data -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold">Data Sensor Simulasi</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <?php foreach ($bathrooms as $bathroom): ?>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-gray-900"><?= htmlspecialchars($bathroom['name']) ?></h4>
                                <i class="fas fa-wind text-gray-500"></i>
                            </div>
                            <div class="text-2xl font-bold text-blue-600">
                                <?= rand(200, 500) ?> ppm
                            </div>
                            <div class="text-sm text-gray-600">MQ-135 Gas Sensor</div>
                            <div class="text-xs text-gray-500 mt-1">
                                <?= date('H:i:s') ?>
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-gray-900"><?= htmlspecialchars($bathroom['name']) ?></h4>
                                <i class="fas fa-eye text-gray-500"></i>
                            </div>
                            <div class="text-2xl font-bold text-green-600">
                                <?= $bathroom['current_visitors'] ?>
                            </div>
                            <div class="text-sm text-gray-600">IR Visitor Count</div>
                            <div class="text-xs text-gray-500 mt-1">
                                <?= date('H:i:s') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- My Cleaning History -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold">Riwayat Pembersihan Saya</h2>
            </div>
            <div class="p-6">
                <?php if (empty($cleaning_logs)): ?>
                    <p class="text-gray-500 text-center py-8">Belum ada riwayat pembersihan</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach (array_slice($cleaning_logs, 0, 10) as $log): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <?php if ($log['status'] === 'completed'): ?>
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-check text-green-600"></i>
                                            </div>
                                        <?php elseif ($log['status'] === 'in_progress'): ?>
                                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-clock text-yellow-600"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                                <i class="fas fa-times text-red-600"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900"><?= htmlspecialchars($log['bathroom_name']) ?></p>
                                        <p class="text-sm text-gray-600">
                                            <?= date('d/m/Y H:i', strtotime($log['start_time'])) ?>
                                            <?php if ($log['end_time']): ?>
                                                - <?= date('H:i', strtotime($log['end_time'])) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <?php if ($log['status'] === 'completed'): ?>
                                        <p class="font-semibold text-green-600">+<?= $log['points_earned'] ?> pts</p>
                                        <p class="text-sm text-gray-500"><?= $log['duration_minutes'] ?> menit</p>
                                    <?php elseif ($log['status'] === 'in_progress'): ?>
                                        <p class="text-sm text-yellow-600 font-medium">Sedang berlangsung</p>
                                    <?php else: ?>
                                        <p class="text-sm text-red-600 font-medium">Dibatalkan</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Real-time updates
        function updateRealtimeData() {
            fetch('../api/realtime_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update bathroom status
                        data.bathrooms.forEach(bathroom => {
                            const element = document.querySelector(`[data-bathroom-id="${bathroom.id}"]`);
                            if (element) {
                                // Update visitor count
                                const visitorElement = element.querySelector('.visitor-count');
                                if (visitorElement) {
                                    visitorElement.textContent = `${bathroom.current_visitors}/${bathroom.max_visitors}`;
                                }
                                
                                // Update status indicator
                                const statusElement = element.querySelector('.status-indicator span');
                                if (statusElement) {
                                    let statusClass = 'bg-gray-100 text-gray-800';
                                    let statusIcon = 'fas fa-question-circle';
                                    
                                    switch (bathroom.computed_status) {
                                        case 'available':
                                            statusClass = 'bg-green-100 text-green-800';
                                            statusIcon = 'fas fa-check-circle';
                                            break;
                                        case 'needs_cleaning':
                                            statusClass = 'bg-red-100 text-red-800';
                                            statusIcon = 'fas fa-exclamation-triangle';
                                            break;
                                        case 'being_cleaned':
                                            statusClass = 'bg-yellow-100 text-yellow-800';
                                            statusIcon = 'fas fa-broom';
                                            break;
                                    }
                                    
                                    statusElement.className = `px-3 py-1 text-sm font-medium rounded-full ${statusClass}`;
                                    statusElement.innerHTML = `<i class="${statusIcon} mr-1"></i>${bathroom.computed_status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}`;
                                }
                            }
                        });
                        
                        // Update sensor data
                        data.sensors.forEach(sensor => {
                            const element = document.querySelector(`[data-sensor-id="${sensor.bathroom_id}-${sensor.sensor_type}"]`);
                            if (element) {
                                const valueElement = element.querySelector('.sensor-value');
                                if (valueElement && sensor.value !== null) {
                                    valueElement.textContent = `${Math.round(sensor.value)} ${sensor.unit || ''}`;
                                }
                            }
                        });
                        
                        // Update connection status
                        document.getElementById('connection-status').className = 'w-2 h-2 bg-green-500 rounded-full animate-pulse mr-2';
                    }
                })
                .catch(error => {
                    console.error('Error fetching real-time data:', error);
                    document.getElementById('connection-status').className = 'w-2 h-2 bg-red-500 rounded-full mr-2';
                });
        }
        
        // Update every 5 seconds
        setInterval(updateRealtimeData, 5000);
        
        // Initial update
        updateRealtimeData();
    </script>
</div>
</body>
</html>