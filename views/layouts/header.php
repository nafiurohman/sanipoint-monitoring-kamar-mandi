<?php
if (!class_exists('Auth')) {
    require_once 'core/Auth.php';
}
$auth = new Auth();
$user = $auth->getUser();
$isAdmin = $auth->hasRole('admin');

// Generate breadcrumbs
$uri = $_SERVER['REQUEST_URI'];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$cleanUri = str_replace($basePath, '', $uri);
$segments = explode('/', trim($cleanUri, '/'));
$breadcrumbs = [];

if (!empty($segments[0])) {
    if ($segments[0] === 'admin') {
        $breadcrumbs[] = ['name' => 'Admin', 'url' => $basePath . '/admin/dashboard'];
        if (isset($segments[1])) {
            $pageNames = [
                'dashboard' => 'Dashboard',
                'karyawan' => 'Karyawan',
                'kamar-mandi' => 'Kamar Mandi',
                'produk' => 'Produk',
                'sensor' => 'Sensor IoT',
                'laporan' => 'Laporan'
            ];
            $breadcrumbs[] = ['name' => $pageNames[$segments[1]] ?? ucfirst($segments[1]), 'url' => null];
        }
    } elseif ($segments[0] === 'karyawan') {
        $breadcrumbs[] = ['name' => 'Karyawan', 'url' => $basePath . '/karyawan/dashboard'];
        if (isset($segments[1])) {
            $pageNames = [
                'dashboard' => 'Dashboard',
                'poin' => 'Poin Saya',
                'marketplace' => 'Marketplace',
                'transfer' => 'Transfer Poin'
            ];
            $breadcrumbs[] = ['name' => $pageNames[$segments[1]] ?? ucfirst($segments[1]), 'url' => null];
        }
    }
}
?>

<header class="header flex items-center justify-between px-6">
    <!-- Left Section: Breadcrumbs -->
    <div class="flex items-center space-x-4">
        <?php if (!empty($breadcrumbs)): ?>
            <nav class="flex items-center space-x-2 text-sm">
                <i class="fas fa-home text-gray-400 dark:text-gray-500"></i>
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php if ($index > 0): ?>
                        <i class="fas fa-chevron-right text-gray-300 dark:text-gray-600 text-xs"></i>
                    <?php endif; ?>
                    <?php if ($crumb['url']): ?>
                        <a href="<?= $crumb['url'] ?>" class="text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            <?= $crumb['name'] ?>
                        </a>
                    <?php else: ?>
                        <span class="text-gray-900 dark:text-white font-medium"><?= $crumb['name'] ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>
    </div>
    
    <!-- Right Section: Actions -->
    <div class="flex items-center space-x-4">
        <!-- Real-time Status Indicator -->
        <div class="flex items-center space-x-2">
            <div id="connection-status" class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
            <span class="text-xs text-gray-500 dark:text-gray-400">Real-time</span>
        </div>
        
        <!-- Notifications -->
        <div class="relative">
            <button id="notifications-btn" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors relative">
                <i class="fas fa-bell text-gray-600 dark:text-gray-400"></i>
                <span id="notification-badge" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
            </button>
            
            <!-- Notifications Dropdown -->
            <div id="notifications-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 z-50">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Notifikasi</h3>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                        <i class="fas fa-bell-slash text-2xl mb-2"></i>
                        <p class="text-sm">Tidak ada notifikasi baru</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- User Menu -->
        <div class="relative">
            <button id="user-menu-btn" class="flex items-center space-x-3 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                    <span class="text-white text-sm font-semibold"><?= strtoupper(substr($user['full_name'] ?? 'G', 0, 1)) ?></span>
                </div>
                <div class="hidden md:block text-left">
                    <p class="text-sm font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($user['full_name'] ?? 'Guest') ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 capitalize"><?= $user['role'] ?? 'guest' ?></p>
                </div>
                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
            </button>
            
            <!-- User Dropdown -->
            <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 z-50">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                            <span class="text-white font-semibold"><?= strtoupper(substr($user['full_name'] ?? 'G', 0, 1)) ?></span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white"><?= htmlspecialchars($user['full_name'] ?? 'Guest') ?></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($user['email'] ?? $user['username'] ?? 'guest') ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="p-2">
                    <button id="profile-settings" class="w-full flex items-center space-x-3 px-3 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-colors">
                        <i class="fas fa-user-cog w-4"></i>
                        <span>Pengaturan Profil</span>
                    </button>
                    
                    <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                    
                    <a href="<?= $basePath ?>/logout" class="w-full flex items-center space-x-3 px-3 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors">
                        <i class="fas fa-sign-out-alt w-4"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Notifications dropdown
    const notificationsBtn = document.getElementById('notifications-btn');
    const notificationsDropdown = document.getElementById('notifications-dropdown');
    
    notificationsBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        notificationsDropdown.classList.toggle('hidden');
        document.getElementById('user-dropdown').classList.add('hidden');
    });
    
    // User menu dropdown
    const userMenuBtn = document.getElementById('user-menu-btn');
    const userDropdown = document.getElementById('user-dropdown');
    
    userMenuBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('hidden');
        notificationsDropdown.classList.add('hidden');
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        notificationsDropdown.classList.add('hidden');
        userDropdown.classList.add('hidden');
    });
    
    // Connection status simulation
    const connectionStatus = document.getElementById('connection-status');
    setInterval(() => {
        // Simulate connection check
        connectionStatus.classList.toggle('animate-pulse');
    }, 5000);
});
</script>
