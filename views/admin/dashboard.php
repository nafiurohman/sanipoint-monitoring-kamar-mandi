<?php
ob_start();
?>

<div class="p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Admin</h1>
        <p class="text-gray-600">Monitoring real-time sistem SANIPOINT</p>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-toilet text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Kamar Mandi</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['total_bathrooms'] ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Karyawan Aktif</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['active_employees'] ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-broom text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pembersihan Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['cleaning_today'] ?></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-coins text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Poin Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['points_distributed_today'] ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Real-time Bathroom Status -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Status Kamar Mandi Real-time</h2>
                <p class="text-sm text-gray-600">Update otomatis setiap 5 detik</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php foreach ($bathrooms as $bathroom): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg" data-bathroom-id="<?= $bathroom['id'] ?>">
                            <div>
                                <h3 class="font-medium text-gray-900"><?= htmlspecialchars($bathroom['name']) ?></h3>
                                <p class="text-sm text-gray-600"><?= htmlspecialchars($bathroom['location']) ?></p>
                            </div>
                            <div class="text-right">
                                <span class="bathroom-status status-<?= $bathroom['computed_status'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $bathroom['computed_status'])) ?>
                                </span>
                                <p class="text-sm text-gray-600 mt-1">
                                    Pengunjung: <span class="visitor-count"><?= $bathroom['current_visitors'] ?>/<?= $bathroom['max_visitors'] ?></span>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Aktivitas Terbaru</h2>
                <p class="text-sm text-gray-600">10 aktivitas pembersihan terakhir</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <?php if ($activity['status'] == 'completed'): ?>
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check text-green-600 text-sm"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-clock text-yellow-600 text-sm"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($activity['user_name']) ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <?= htmlspecialchars($activity['bathroom_name']) ?>
                                    <?php if ($activity['status'] == 'completed'): ?>
                                        - <?= $activity['duration_minutes'] ?> menit
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?= date('H:i', strtotime($activity['created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Aksi Cepat</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="/sanipoint/admin/karyawan" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                    <i class="fas fa-user-plus text-blue-600 text-xl mr-3"></i>
                    <div>
                        <p class="font-medium text-blue-900">Tambah Karyawan</p>
                        <p class="text-sm text-blue-600">Daftarkan karyawan baru</p>
                    </div>
                </a>
                
                <a href="/sanipoint/admin/kamar-mandi" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                    <i class="fas fa-plus text-green-600 text-xl mr-3"></i>
                    <div>
                        <p class="font-medium text-green-900">Tambah Kamar Mandi</p>
                        <p class="text-sm text-green-600">Daftarkan lokasi baru</p>
                    </div>
                </a>
                
                <a href="/sanipoint/admin/produk" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                    <i class="fas fa-box text-purple-600 text-xl mr-3"></i>
                    <div>
                        <p class="font-medium text-purple-900">Kelola Produk</p>
                        <p class="text-sm text-purple-600">Update marketplace</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Dashboard Admin';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>