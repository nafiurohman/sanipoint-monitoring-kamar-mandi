<?php
ob_start();
?>

<div class="p-6">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Laporan & Analitik</h1>
            <p class="text-gray-600 dark:text-gray-400">Analisis performa sistem dan distribusi poin</p>
        </div>
        <div class="flex space-x-3">
            <button id="refresh-data" class="btn btn-secondary">
                <i class="fas fa-sync-alt mr-2"></i>
                Refresh Data
            </button>
            <button id="download-pdf" class="btn btn-primary">
                <i class="fa-solid fa-download"></i>
                Download PDF
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="card">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                    <i class="fas fa-broom text-blue-600 dark:text-blue-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Pembersihan</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= array_sum(array_column($cleaning_stats ?? [], 'total_cleanings')) ?></p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-xl">
                    <i class="fas fa-coins text-green-600 dark:text-green-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Poin Dibagikan</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= array_sum(array_column($cleaning_stats ?? [], 'total_points')) ?></p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl">
                    <i class="fas fa-clock text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Rata-rata Durasi</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        <?php 
                        $stats = $cleaning_stats ?? [];
                        $avg = count($stats) > 0 ? round(array_sum(array_column($stats, 'avg_duration')) / count($stats)) : 0;
                        echo $avg;
                        ?> min
                    </p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-xl">
                    <i class="fas fa-users text-purple-600 dark:text-purple-400 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Karyawan Aktif</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= count($employee_performance ?? []) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Employee Performance Chart -->
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Perbandingan Performa Karyawan</h3>
            <div class="relative h-64">
                <canvas id="employeeChart"></canvas>
            </div>
        </div>

        <!-- Point Distribution Chart -->
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Distribusi Poin Karyawan</h3>
            <div class="relative h-64">
                <canvas id="pointChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Employee Performance Table -->
    <div class="card mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Performa Karyawan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ranking</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Karyawan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Pembersihan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rata-rata Durasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Poin Diperoleh</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Saldo Poin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Performance</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php 
                    $employees = $employee_performance ?? [];
                    // Sort by total cleanings for ranking
                    usort($employees, function($a, $b) {
                        return ($b['total_cleanings'] ?? 0) - ($a['total_cleanings'] ?? 0);
                    });
                    
                    foreach ($employees as $index => $employee): 
                        $rank = $index + 1;
                        $performance_score = ($employee['total_cleanings'] ?? 0) * 10 + ($employee['total_points_earned'] ?? 0) / 10;
                        $performance_level = $performance_score > 100 ? 'Excellent' : ($performance_score > 50 ? 'Good' : 'Average');
                        $performance_color = $performance_score > 100 ? 'text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400' : 
                                           ($performance_score > 50 ? 'text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400' : 
                                            'text-gray-600 bg-gray-100 dark:bg-gray-700 dark:text-gray-400');
                    ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if ($rank <= 3): ?>
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center <?= $rank == 1 ? 'bg-yellow-100 dark:text-yellow-400' : ($rank == 2 ? 'bg-gray-100 dark:bg-gray-700 dark:text-gray-400' : 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400') ?>">
                                            <i class="fas fa-trophy text-sm"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400"><?= $rank ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                                        <span class="text-white font-medium text-sm"><?= strtoupper(substr($employee['full_name'] ?? '', 0, 2)) ?></span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($employee['full_name'] ?? '') ?></div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($employee['employee_code'] ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white"><?= $employee['total_cleanings'] ?? 0 ?></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">pembersihan</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white"><?= round($employee['avg_duration'] ?? 0) ?> menit</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white"><?= number_format($employee['total_points_earned'] ?? 0) ?> pts</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-white"><?= number_format($employee['current_balance'] ?? 0) ?> pts</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?= $performance_color ?>">
                                    <?= $performance_level ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get employee data safely
    const employeeData = <?= json_encode($employee_performance ?? []) ?>;
    
    if (employeeData && employeeData.length > 0) {
        // Get theme colors
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#fff' : '#374151';
        const gridColor = isDark ? '#374151' : '#E5E7EB';
        
        // Employee Performance Chart
        const ctx1 = document.getElementById('employeeChart');
        if (ctx1) {
            window.employeeChart = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: employeeData.map(emp => (emp.full_name || '').split(' ')[0] || 'Unknown'),
                    datasets: [{
                        label: 'Total Pembersihan',
                        data: employeeData.map(emp => emp.total_cleanings || 0),
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: textColor,
                                font: {
                                    size: 12
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: textColor,
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                color: gridColor
                            }
                        },
                        x: {
                            ticks: {
                                color: textColor,
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                color: gridColor
                            }
                        }
                    }
                }
            });
        }

        // Point Distribution Chart
        const ctx2 = document.getElementById('pointChart');
        if (ctx2) {
            window.pointChart = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: employeeData.map(emp => (emp.full_name || '').split(' ')[0] || 'Unknown'),
                    datasets: [{
                        data: employeeData.map(emp => emp.total_points_earned || 0),
                        backgroundColor: [
                            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                            '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6366F1'
                        ],
                        borderWidth: 2,
                        borderColor: isDark ? '#1F2937' : '#FFFFFF'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                padding: 15,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        }
    } else {
        // Show no data message
        document.getElementById('employeeChart').parentElement.innerHTML = 
            '<div class="h-64 flex items-center justify-center"><p class="text-gray-500 dark:text-gray-400">Tidak ada data untuk ditampilkan</p></div>';
        document.getElementById('pointChart').parentElement.innerHTML = 
            '<div class="h-64 flex items-center justify-center"><p class="text-gray-500 dark:text-gray-400">Tidak ada data untuk ditampilkan</p></div>';
    }

    // PDF Download
    const downloadBtn = document.getElementById('download-pdf');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            window.open('/sanipoint/admin/laporan/pdf', '_blank');
        });
    }

    // Refresh Data
    const refreshBtn = document.getElementById('refresh-data');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            location.reload();
        });
    }
});
</script>

<?php
$content = ob_get_clean();
$title = 'Laporan & Analitik';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>