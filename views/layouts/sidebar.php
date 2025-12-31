<?php
if (!class_exists('Auth')) {
    require_once 'core/Auth.php';
}
require_once 'config/config.php';

$auth = new Auth();
$user = $auth->getUser();
$isAdmin = $auth->hasRole('admin');

$currentPath = $_SERVER['REQUEST_URI'];
?>

<aside id="sidebar" class="sidebar">
    <!-- Header -->
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="fas fa-toilet"></i>
            </div>
            <span class="brand-text">SANIPOINT</span>
        </div>
        <button id="sidebar-toggle" class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- User Info -->
    <div class="sidebar-user">
        <div class="user-avatar">
            <span><?= strtoupper(substr($user['full_name'] ?? 'G', 0, 1)) ?></span>
        </div>
        <div class="user-info">
            <div class="user-name"><?= htmlspecialchars($user['full_name'] ?? 'Guest') ?></div>
            <div class="user-role"><?= $user['role'] ?? 'guest' ?></div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <?php if ($isAdmin): ?>
            <div class="nav-section">
                <div class="nav-title">Admin Panel</div>
                <a href="<?= APP_URL ?>admin/dashboard" class="nav-item <?= strpos($currentPath, '/admin/dashboard') !== false ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= APP_URL ?>admin/karyawan" class="nav-item <?= strpos($currentPath, '/admin/karyawan') !== false ? 'active' : '' ?>">
                    <i class="fas fa-users"></i>
                    <span>Karyawan</span>
                </a>
                <a href="<?= APP_URL ?>admin/kamar-mandi" class="nav-item <?= strpos($currentPath, '/admin/kamar-mandi') !== false ? 'active' : '' ?>">
                    <i class="fas fa-restroom"></i>
                    <span>Kamar Mandi</span>
                </a>
                <a href="<?= APP_URL ?>admin/produk" class="nav-item <?= strpos($currentPath, '/admin/produk') !== false ? 'active' : '' ?>">
                    <i class="fas fa-box"></i>
                    <span>Produk</span>
                </a>
                <a href="<?= APP_URL ?>admin/sensor" class="nav-item <?= strpos($currentPath, '/admin/sensor') !== false ? 'active' : '' ?>">
                    <i class="fas fa-microchip"></i>
                    <span>Sensor IoT</span>
                </a>
                <a href="<?= APP_URL ?>admin/transaksi" class="nav-item <?= strpos($currentPath, '/admin/transaksi') !== false ? 'active' : '' ?>">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Transaksi</span>
                </a>
                <a href="<?= APP_URL ?>admin/laporan" class="nav-item <?= strpos($currentPath, '/admin/laporan') !== false ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Laporan</span>
                </a>
            </div>
        <?php else: ?>
            <div class="nav-section">
                <div class="nav-title">Menu Utama</div>
                <a href="<?= APP_URL ?>karyawan/dashboard" class="nav-item <?= strpos($currentPath, '/karyawan/dashboard') !== false ? 'active' : '' ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?= APP_URL ?>karyawan/poin" class="nav-item <?= strpos($currentPath, '/karyawan/poin') !== false ? 'active' : '' ?>">
                    <i class="fas fa-coins"></i>
                    <span>Poin Saya</span>
                </a>
                <a href="<?= APP_URL ?>karyawan/marketplace" class="nav-item <?= strpos($currentPath, '/karyawan/marketplace') !== false ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Marketplace</span>
                </a>
                <a href="<?= APP_URL ?>karyawan/transfer" class="nav-item <?= strpos($currentPath, '/karyawan/transfer') !== false ? 'active' : '' ?>">
                    <i class="fas fa-paper-plane"></i>
                    <span>Transfer Poin</span>
                </a>
                <a href="<?= APP_URL ?>karyawan/riwayat" class="nav-item <?= strpos($currentPath, '/karyawan/riwayat') !== false ? 'active' : '' ?>">
                    <i class="fas fa-history"></i>
                    <span>Riwayat</span>
                </a>
                <a href="<?= APP_URL ?>karyawan/monitoring" class="nav-item <?= strpos($currentPath, '/karyawan/monitoring') !== false ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Monitoring</span>
                </a>
            </div>
        <?php endif; ?>
        
        <div class="nav-section">
            <div class="nav-title">Pengaturan</div>
            <a href="<?= APP_URL ?><?= $isAdmin ? 'admin' : 'karyawan' ?>/profil" class="nav-item <?= strpos($currentPath, '/profil') !== false ? 'active' : '' ?>">
                <i class="fas fa-user"></i>
                <span>Profil</span>
            </a>
            <a href="<?= APP_URL ?><?= $isAdmin ? 'admin' : 'karyawan' ?>/pengaturan" class="nav-item <?= strpos($currentPath, '/pengaturan') !== false ? 'active' : '' ?>">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
            <button id="theme-toggle" class="nav-item">
                <i id="theme-icon" class="fas fa-moon"></i>
                <span class="sidebar-text">Mode Gelap</span>
            </button>
            <a href="<?= APP_URL ?>logout" class="nav-item logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</aside>

<style>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 280px;
    background: white;
    border-right: 1px solid #e5e7eb;
    display: flex;
    flex-direction: column;
    transition: width 0.3s ease;
    z-index: 1000;
}

.dark .sidebar {
    background: #1f2937;
    border-right-color: #374151;
}

.sidebar.collapsed {
    width: 80px;
}

.sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.dark .sidebar-header {
    border-bottom-color: #374151;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.brand-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.brand-text {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    transition: opacity 0.3s ease;
}

.dark .brand-text {
    color: white;
}

.sidebar.collapsed .brand-text {
    opacity: 0;
    width: 0;
    overflow: hidden;
}

.sidebar-toggle {
    width: 36px;
    height: 36px;
    border: none;
    background: #f3f4f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s;
}

.dark .sidebar-toggle {
    background: #374151;
    color: #d1d5db;
}

.sidebar-toggle:hover {
    background: #e5e7eb;
}

.dark .sidebar-toggle:hover {
    background: #4b5563;
}

.sidebar-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.dark .sidebar-user {
    border-bottom-color: #374151;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #10b981, #3b82f6);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.125rem;
}

.user-info {
    flex: 1;
    min-width: 0;
    transition: opacity 0.3s ease;
}

.sidebar.collapsed .user-info {
    opacity: 0;
    width: 0;
    overflow: hidden;
}

.user-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.875rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dark .user-name {
    color: white;
}

.user-role {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: capitalize;
}

.dark .user-role {
    color: #9ca3af;
}

.sidebar-nav {
    flex: 1;
    padding: 1rem 0;
    overflow-y: auto;
}

.nav-section {
    margin-bottom: 1.5rem;
}

.nav-title {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0 1rem;
    margin-bottom: 0.5rem;
    transition: opacity 0.3s ease;
}

.dark .nav-title {
    color: #9ca3af;
}

.sidebar.collapsed .nav-title {
    opacity: 0;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #6b7280;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
}

.dark .nav-item {
    color: #9ca3af;
}

.nav-item:hover {
    background: #f3f4f6;
    color: #1f2937;
}

.dark .nav-item:hover {
    background: #374151;
    color: white;
}

.nav-item.active {
    background: #eff6ff;
    color: #2563eb;
    border-right: 3px solid #2563eb;
}

.dark .nav-item.active {
    background: #1e3a8a;
    color: #93c5fd;
}

.nav-item.logout {
    color: #dc2626;
}

.dark .nav-item.logout {
    color: #f87171;
}

.nav-item.logout:hover {
    background: #fef2f2;
}

.dark .nav-item.logout:hover {
    background: #7f1d1d;
}

.nav-item i {
    width: 20px;
    text-align: center;
    font-size: 1.125rem;
}

.nav-item span {
    transition: opacity 0.3s ease;
    white-space: nowrap;
}

.sidebar.collapsed .nav-item span {
    opacity: 0;
    width: 0;
    overflow: hidden;
}

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid #e5e7eb;
}

.dark .sidebar-footer {
    border-top-color: #374151;
}

/* Main content adjustment */
.main-content {
    margin-left: 280px;
    transition: margin-left 0.3s ease;
    min-height: 100vh;
    padding: 2rem;
    background: #f9fafb;
}

.dark .main-content {
    background: #111827;
}

.main-content.sidebar-collapsed {
    margin-left: 80px;
}

@media (max-width: 1024px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.mobile-open {
        transform: translateX(0);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }
    
    .main-content {
        margin-left: 0 !important;
        padding: 1rem;
    }
}

@media (max-width: 640px) {
    .main-content {
        padding: 0.5rem;
    }
    
    .sidebar {
        width: 100vw;
        max-width: 320px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const themeToggle = document.getElementById('theme-toggle');
    const mainContent = document.querySelector('.main-content');
    
    console.log('📋 Sidebar system initialized');
    
    // Load saved state
    const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
    if (isCollapsed && window.innerWidth > 1024) {
        sidebar.classList.add('collapsed');
        if (mainContent) {
            mainContent.classList.add('sidebar-collapsed');
            console.log('📋 Sidebar restored to collapsed state');
        }
    }
    
    // Toggle sidebar
    sidebarToggle.addEventListener('click', function() {
        console.log('📋 Sidebar toggle clicked');
        
        if (window.innerWidth <= 1024) {
            // Mobile: toggle overlay
            sidebar.classList.toggle('mobile-open');
            console.log('📱 Mobile sidebar toggled');
        } else {
            // Desktop: toggle collapse
            sidebar.classList.toggle('collapsed');
            if (mainContent) {
                mainContent.classList.toggle('sidebar-collapsed');
            }
            
            const collapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebar-collapsed', collapsed);
            console.log(`🖥️ Desktop sidebar ${collapsed ? 'collapsed' : 'expanded'}`);
        }
    });
    
    // Theme toggle
    if (themeToggle) {
        themeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (window.themeManager) {
                window.themeManager.toggleTheme();
            } else {
                // Fallback
                document.documentElement.classList.toggle('dark');
                const isDark = document.documentElement.classList.contains('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                
                const icon = themeToggle.querySelector('i');
                const text = themeToggle.querySelector('span');
                
                if (isDark) {
                    icon.className = 'fas fa-sun';
                    text.textContent = 'Mode Terang';
                } else {
                    icon.className = 'fas fa-moon';
                    text.textContent = 'Mode Gelap';
                }
            }
            
            console.log(`🎨 Theme toggled`);
        });
    }
    
    // Close mobile sidebar on outside click
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 1024 && 
            !sidebar.contains(e.target) && 
            sidebar.classList.contains('mobile-open')) {
            sidebar.classList.remove('mobile-open');
            console.log('📱 Mobile sidebar closed by outside click');
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            sidebar.classList.remove('mobile-open');
        }
    });
});
</script>