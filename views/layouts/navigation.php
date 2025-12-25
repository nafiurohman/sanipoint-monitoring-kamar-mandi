<?php
$auth = new Auth();
$user = $auth->getUser();
$isAdmin = $auth->hasRole('admin');
?>

<nav class="fixed left-0 top-0 h-full w-64 bg-white shadow-lg border-r border-gray-200 z-40">
    <div class="p-6">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-toilet text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900">SANIPOINT</h1>
                <p class="text-sm text-gray-500">IoT Monitoring</p>
            </div>
        </div>
    </div>
    
    <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                <i class="fas fa-user text-gray-600"></i>
            </div>
            <div>
                <p class="font-medium text-gray-900"><?= htmlspecialchars($user['full_name']) ?></p>
                <p class="text-sm text-gray-500 capitalize"><?= $user['role'] ?></p>
            </div>
        </div>
    </div>
    
    <div class="px-3 py-4">
        <?php if ($isAdmin): ?>
            <!-- Admin Navigation -->
            <div class="space-y-1">
                <a href="/sanipoint/admin/dashboard" class="nav-link <?= $_SERVER['REQUEST_URI'] == '/sanipoint/admin/dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/sanipoint/admin/kamar-mandi" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/kamar-mandi') !== false ? 'active' : '' ?>">
                    <i class="fas fa-toilet"></i>
                    <span>Kamar Mandi</span>
                </a>
                <a href="/sanipoint/admin/karyawan" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/karyawan') !== false ? 'active' : '' ?>">
                    <i class="fas fa-users"></i>
                    <span>Karyawan</span>
                </a>
                <a href="/sanipoint/admin/sensor" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/sensor') !== false ? 'active' : '' ?>">
                    <i class="fas fa-microchip"></i>
                    <span>Sensor IoT</span>
                </a>
                <a href="/sanipoint/admin/produk" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/produk') !== false ? 'active' : '' ?>">
                    <i class="fas fa-box"></i>
                    <span>Produk</span>
                </a>
                <a href="/sanipoint/admin/laporan" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/laporan') !== false ? 'active' : '' ?>">
                    <i class="fas fa-file-alt"></i>
                    <span>Laporan</span>
                </a>
            </div>
        <?php else: ?>
            <!-- Employee Navigation -->
            <div class="space-y-1">
                <a href="/sanipoint/karyawan/dashboard" class="nav-link <?= $_SERVER['REQUEST_URI'] == '/sanipoint/karyawan/dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/sanipoint/karyawan/poin" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/karyawan/poin') !== false ? 'active' : '' ?>">
                    <i class="fas fa-coins"></i>
                    <span>Poin Saya</span>
                </a>
                <a href="/sanipoint/karyawan/marketplace" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/karyawan/marketplace') !== false ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Marketplace</span>
                </a>
                <a href="/sanipoint/karyawan/transfer" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/karyawan/transfer') !== false ? 'active' : '' ?>">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Transfer Poin</span>
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="absolute bottom-0 left-0 right-0 p-3 border-t border-gray-200">
        <a href="/sanipoint/logout" class="flex items-center space-x-3 px-3 py-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</nav>

<style>
.nav-link {
    @apply flex items-center space-x-3 px-3 py-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors;
}

.nav-link.active {
    @apply text-blue-600 bg-blue-50 font-medium;
}

.nav-link i {
    @apply w-5 text-center;
}
</style>