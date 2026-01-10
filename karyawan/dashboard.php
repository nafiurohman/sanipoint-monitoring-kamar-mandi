<?php
session_start();

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';

if (!Auth::isLoggedIn() || !Auth::hasRole('karyawan')) {
    header('Location: ../login.php');
    exit;
}

$user = Auth::getUser();
$title = 'Dashboard Karyawan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= $title ?> - SANIPOINT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../views/layouts/karyawan_nav.php'; ?>
    
    <div class="p-6">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Karyawan</h1>
            <p class="text-gray-600">Selamat datang, <span id="user-name"><?= htmlspecialchars($user['full_name']) ?></span></p>
            <div class="flex items-center mt-2">
                <div id="connection-status" class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-2"></div>
                <span class="text-sm text-gray-600">Live Data</span>
            </div>
        </div>
        
        <!-- Points Card Component -->
        <div id="points-component" class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl p-6 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Saldo Poin Anda</p>
                    <p class="text-4xl font-bold" id="current-balance">-</p>
                    <p class="text-blue-100 text-sm mt-2">
                        Total Earned: <span id="total-earned">-</span> | 
                        Total Spent: <span id="total-spent">-</span>
                    </p>
                </div>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>
        
        <!-- Stats Cards Component -->
        <div id="stats-component" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="fas fa-broom text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Pembersihan</p>
                        <p class="text-2xl font-bold text-gray-900" id="total-cleanings">-</p>
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
                        <p class="text-2xl font-bold text-gray-900" id="avg-duration">-</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <i class="fas fa-star text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Poin dari Cleaning</p>
                        <p class="text-2xl font-bold text-gray-900" id="cleaning-points">-</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <a href="marketplace.php" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Marketplace</h3>
                    <p class="text-sm text-gray-600">Tukar poin dengan produk</p>
                </div>
            </a>
            
            <a href="transfer.php" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-exchange-alt text-green-600 text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Transfer Poin</h3>
                    <p class="text-sm text-gray-600">Kirim poin ke rekan</p>
                </div>
            </a>
            
            <a href="riwayat.php" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="text-center">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-history text-yellow-600 text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Riwayat</h3>
                    <p class="text-sm text-gray-600">Lihat transaksi</p>
                </div>
            </a>
            
            <a href="monitoring.php" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Monitoring</h3>
                    <p class="text-sm text-gray-600">Status real-time</p>
                </div>
            </a>
        </div>
        
        <!-- Recent Transactions Component -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Transaksi Terbaru</h2>
                <p class="text-sm text-gray-600">Update otomatis setiap 3 detik</p>
            </div>
            <div class="p-6">
                <div class="space-y-4" id="transactions-component">
                    <div class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                        <p class="text-gray-500 mt-2">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Real-time Karyawan Dashboard Components
        class KaryawanDashboard {
            constructor() {
                this.updateInterval = null;
                this.isConnected = true;
                this.init();
            }
            
            init() {
                this.loadInitialData();
                this.startRealTimeUpdates();
            }
            
            async loadInitialData() {
                await this.updateDashboardData();
            }
            
            startRealTimeUpdates() {
                // Update every 3 seconds
                this.updateInterval = setInterval(() => {
                    this.updateDashboardData();
                }, 3000);
            }
            
            async updateDashboardData() {
                try {
                    const response = await fetch('../api/karyawan_data.php', {
                        method: 'GET',
                        cache: 'no-cache',
                        headers: {
                            'Cache-Control': 'no-cache',
                            'Pragma': 'no-cache'
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.updatePointsComponent(data.points);
                        this.updateStatsComponent(data.cleaning_stats);
                        this.updateTransactionsComponent(data.recent_transactions);
                        this.setConnectionStatus(true);
                    } else {
                        throw new Error(data.error || 'Unknown error');
                    }
                } catch (error) {
                    console.error('Error updating dashboard:', error);
                    this.setConnectionStatus(false);
                }
            }
            
            updatePointsComponent(points) {
                document.getElementById('current-balance').textContent = this.formatNumber(points.current_balance || 0);
                document.getElementById('total-earned').textContent = this.formatNumber(points.total_earned || 0);
                document.getElementById('total-spent').textContent = this.formatNumber(points.total_spent || 0);
            }
            
            updateStatsComponent(stats) {
                document.getElementById('total-cleanings').textContent = stats.total_cleanings || 0;
                document.getElementById('avg-duration').textContent = Math.round(stats.avg_duration || 0) + ' min';
                document.getElementById('cleaning-points').textContent = this.formatNumber(stats.total_points_from_cleaning || 0);
            }
            
            updateTransactionsComponent(transactions) {
                const container = document.getElementById('transactions-component');
                
                if (!transactions || transactions.length === 0) {
                    container.innerHTML = '<p class="text-gray-500 text-center py-4">Belum ada transaksi</p>';
                    return;
                }
                
                const html = transactions.map(transaction => {
                    const isEarned = transaction.transaction_type === 'earned';
                    const iconClass = isEarned ? 'bg-green-100' : 'bg-red-100';
                    const iconColor = isEarned ? 'text-green-600' : 'text-red-600';
                    const amountColor = isEarned ? 'text-green-600' : 'text-red-600';
                    const sign = isEarned ? '+' : '-';
                    
                    return `
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 ${iconClass} rounded-full flex items-center justify-center">
                                        <i class="fas fa-${isEarned ? 'plus' : 'minus'} ${iconColor} text-sm"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        ${transaction.transaction_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                    </p>
                                    <p class="text-sm text-gray-600">${this.escapeHtml(transaction.description || '')}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium ${amountColor}">
                                    ${sign}${this.formatNumber(transaction.amount)}
                                </p>
                                <p class="text-xs text-gray-500">${this.formatDateTime(transaction.created_at)}</p>
                            </div>
                        </div>
                    `;
                }).join('');
                
                container.innerHTML = html;
            }
            
            setConnectionStatus(connected) {
                const statusElement = document.getElementById('connection-status');
                if (connected) {
                    statusElement.className = 'w-2 h-2 bg-green-500 rounded-full animate-pulse mr-2';
                    this.isConnected = true;
                } else {
                    statusElement.className = 'w-2 h-2 bg-red-500 rounded-full mr-2';
                    this.isConnected = false;
                }
            }
            
            formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(num);
            }
            
            formatDateTime(dateStr) {
                const date = new Date(dateStr);
                return date.toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit'}) + ' ' +
                       date.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
            }
            
            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            destroy() {
                if (this.updateInterval) {
                    clearInterval(this.updateInterval);
                }
            }
        }
        
        // Initialize dashboard when page loads
        let dashboard;
        document.addEventListener('DOMContentLoaded', function() {
            dashboard = new KaryawanDashboard();
        });
        
        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (dashboard) {
                dashboard.destroy();
            }
        });
    </script>
</div>
</body>
</html>