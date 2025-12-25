<?php
if (!class_exists('Auth')) {
    require_once 'core/Auth.php';
}
$auth = new Auth();
$user = $auth->getUser();
$isAdmin = $auth->hasRole('admin');
?>

<aside id="sidebar" class="sidebar">
    <!-- Sidebar Header -->
    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-toilet text-white text-lg"></i>
            </div>
            <div class="sidebar-text">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">SANIPOINT</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">IoT Monitoring</p>
            </div>
        </div>
        <button id="sidebar-toggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <i class="fas fa-bars text-gray-600 dark:text-gray-400"></i>
        </button>
    </div>
    
    <!-- User Profile -->
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center space-x-3">
            <div class="relative">
                <div class="w-12 h-12 bg-gradient-to-br from-gray-300 to-gray-400 dark:from-gray-600 dark:to-gray-700 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user text-gray-600 dark:text-gray-300 text-lg"></i>
                </div>
                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white dark:border-gray-800"></div>
            </div>
            <div class="sidebar-text flex-1 min-w-0">
                <p class="font-semibold text-gray-900 dark:text-white truncate"><?= htmlspecialchars($user['full_name'] ?? 'Guest') ?></p>
                <p class="text-sm text-gray-500 dark:text-gray-400 capitalize"><?= $user['role'] ?? 'guest' ?></p>
                <?php if (isset($user['employee_code']) && $user['employee_code']): ?>
                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium"><?= htmlspecialchars($user['employee_code']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto scrollbar-hide">
        <?php if ($isAdmin): ?>
            <!-- Admin Navigation -->
            <div class="space-y-1">
                <div class="sidebar-text px-4 py-2">
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Admin Panel</p>
                </div>
                
                <a href="/sanipoint/admin/dashboard" class="nav-link <?= $_SERVER['REQUEST_URI'] == '/sanipoint/admin/dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
                
                <a href="/sanipoint/admin/kamar-mandi" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/kamar-mandi') !== false ? 'active' : '' ?>">
                    <i class="fas fa-toilet"></i>
                    <span class="sidebar-text">Kamar Mandi</span>
                </a>
                
                <a href="/sanipoint/admin/karyawan" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/karyawan') !== false ? 'active' : '' ?>">
                    <i class="fas fa-users"></i>
                    <span class="sidebar-text">Karyawan</span>
                </a>
                
                <a href="/sanipoint/admin/sensor" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/sensor') !== false ? 'active' : '' ?>">
                    <i class="fas fa-microchip"></i>
                    <span class="sidebar-text">Sensor IoT</span>
                </a>
                
                <a href="/sanipoint/admin/produk" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/produk') !== false ? 'active' : '' ?>">
                    <i class="fas fa-box"></i>
                    <span class="sidebar-text">Produk</span>
                </a>
                
                <a href="/sanipoint/admin/laporan" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/laporan') !== false ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span class="sidebar-text">Laporan</span>
                </a>
            </div>
        <?php else: ?>
            <!-- Employee Navigation -->
            <div class="space-y-1">
                <div class="sidebar-text px-4 py-2">
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Menu Utama</p>
                </div>
                
                <a href="/sanipoint/karyawan/dashboard" class="nav-link <?= $_SERVER['REQUEST_URI'] == '/sanipoint/karyawan/dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
                
                <a href="/sanipoint/karyawan/monitoring" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/karyawan/monitoring') !== false ? 'active' : '' ?>">
                    <i class="fas fa-toilet"></i>
                    <span class="sidebar-text">Monitoring</span>
                </a>
                
                <a href="/sanipoint/karyawan/poin" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/karyawan/poin') !== false ? 'active' : '' ?>">
                    <i class="fas fa-coins"></i>
                    <span class="sidebar-text">Poin Saya</span>
                </a>
                
                <a href="/sanipoint/karyawan/riwayat" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/karyawan/riwayat') !== false ? 'active' : '' ?>">
                    <i class="fas fa-history"></i>
                    <span class="sidebar-text">Riwayat</span>
                </a>
                
                <a href="/sanipoint/karyawan/marketplace" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/karyawan/marketplace') !== false ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="sidebar-text">Marketplace</span>
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Settings Section -->
        <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="sidebar-text px-4 py-2">
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Pengaturan</p>
            </div>
            
            <?php if (!$isAdmin): ?>
                <a href="/sanipoint/karyawan/pengaturan" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/karyawan/pengaturan') !== false ? 'active' : '' ?>">
                    <i class="fas fa-user-cog"></i>
                    <span class="sidebar-text">Profil & Keamanan</span>
                </a>
            <?php else: ?>
                <a href="/sanipoint/admin/pengaturan" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/admin/pengaturan') !== false ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i>
                    <span class="sidebar-text">Pengaturan Sistem</span>
                </a>
            <?php endif; ?>
            
            <button id="theme-toggle" class="nav-link w-full">
                <i id="theme-icon" class="fas fa-moon"></i>
                <span class="sidebar-text">Mode Gelap</span>
            </button>
        </div>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        <a href="/sanipoint/logout" class="nav-link text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
            <i class="fas fa-sign-out-alt"></i>
            <span class="sidebar-text">Logout</span>
        </a>
    </div>
</aside>

<script>
// Sidebar toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarTexts = document.querySelectorAll('.sidebar-text');
    
    let isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    
    function toggleSidebar() {
        isCollapsed = !isCollapsed;
        
        if (isCollapsed) {
            sidebar.classList.add('sidebar-collapsed');
            sidebarTexts.forEach(text => text.style.display = 'none');
            document.documentElement.style.setProperty('--sidebar-width', '80px');
        } else {
            sidebar.classList.remove('sidebar-collapsed');
            sidebarTexts.forEach(text => text.style.display = 'block');
            document.documentElement.style.setProperty('--sidebar-width', '280px');
        }
        
        localStorage.setItem('sidebar-collapsed', isCollapsed);
    }
    
    // Apply saved state
    if (isCollapsed) {
        toggleSidebar();
    }
    
    sidebarToggle.addEventListener('click', toggleSidebar);
});
</script>