<?php
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';
require_once '../models/BathroomModel.php';
require_once '../models/UserModel.php';
require_once '../models/PointModel.php';

Auth::requireRole('admin');

$bathroomModel = new BathroomModel();
$userModel = new UserModel();
$pointModel = new PointModel();

// Get real data from database
$cleaning_stats = $bathroomModel->getCleaningStats();
$point_stats = $pointModel->getPointStats();
$employee_performance = $userModel->getEmployeePerformance();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - SANIPOINT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="p-6">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Laporan</h1>
            <p class="text-gray-600">Analitik dan laporan sistem SANIPOINT</p>
        </div>
        
        <!-- Export Buttons -->
        <div class="mb-6 flex space-x-4">
            <button onclick="exportExcel()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-file-excel mr-2"></i>Export Excel
            </button>
            <button onclick="exportPDF()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-file-pdf mr-2"></i>Export PDF
            </button>
        </div>
        
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="fas fa-broom text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Pembersihan</p>
                        <p class="text-2xl font-bold text-gray-900"><?= array_sum(array_column($cleaning_stats, 'total_cleanings')) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="fas fa-coins text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Poin Distributed</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($point_stats['total_distributed']) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Rata-rata Durasi</p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php 
                            $total_duration = array_sum(array_column($cleaning_stats, 'avg_duration'));
                            $count = count($cleaning_stats);
                            echo $count > 0 ? round($total_duration / $count) : 0;
                            ?> min
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <i class="fas fa-shopping-cart text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Poin Ditukar</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($point_stats['total_spent']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pembersihan per Hari (30 Hari Terakhir)</h3>
                <canvas id="cleaningChart" width="400" height="200"></canvas>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Poin per Karyawan</h3>
                <canvas id="pointsChart" width="400" height="200"></canvas>
            </div>
        </div>
        
        <!-- Employee Performance Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Performa Karyawan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pembersihan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata Durasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Poin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Poin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Efisiensi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($employee_performance as $employee): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($employee['full_name'] ?? 'Unknown') ?></div>
                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($employee['employee_code'] ?? '') ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= $employee['total_cleanings'] ?? 0 ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= round($employee['avg_duration'] ?? 0) ?> min
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= number_format($employee['total_points_earned'] ?? 0) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= number_format($employee['current_balance'] ?? 0) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $efficiency = ($employee['avg_duration'] ?? 0) > 0 ? 
                                        min(100, round((15 / ($employee['avg_duration'] ?? 15)) * 100)) : 0;
                                    ?>
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $efficiency ?>%"></div>
                                        </div>
                                        <span class="text-sm text-gray-900"><?= $efficiency ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>
    
    <script>
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 p-4 rounded-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
        
        function exportExcel() {
            showToast('Fitur export Excel akan segera tersedia', 'info');
        }
        
        function exportPDF() {
            showToast('Fitur export PDF akan segera tersedia', 'info');
        }
        
        // Real chart data from database
        const cleaningData = <?= json_encode($cleaning_stats) ?>;
        const employeeData = <?= json_encode($employee_performance) ?>;
        
        // Cleaning Chart with real data
        const cleaningCtx = document.getElementById('cleaningChart').getContext('2d');
        new Chart(cleaningCtx, {
            type: 'line',
            data: {
                labels: cleaningData.map(item => item.date),
                datasets: [{
                    label: 'Pembersihan',
                    data: cleaningData.map(item => item.total_cleanings),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Points Chart with real data
        const pointsCtx = document.getElementById('pointsChart').getContext('2d');
        new Chart(pointsCtx, {
            type: 'doughnut',
            data: {
                labels: employeeData.map(emp => emp.full_name || 'Unknown'),
                datasets: [{
                    data: employeeData.map(emp => emp.total_points_earned || 0),
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)',
                        'rgb(34, 197, 94)'
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
</div>
</body>
</html>