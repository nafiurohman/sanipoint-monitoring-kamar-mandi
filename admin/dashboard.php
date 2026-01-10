<?php
session_start();

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';

if (!Auth::isLoggedIn() || !Auth::hasRole('admin')) {
    header('Location: ../login.php');
    exit;
}

$title = 'Dashboard Admin';
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
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="p-6">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Admin</h1>
            <p class="text-gray-600">Monitoring real-time sistem SANIPOINT</p>
            <div class="flex items-center mt-2">
                <div id="connection-status" class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-2"></div>
                <span class="text-sm text-gray-600">Live Data</span>
            </div>
        </div>
        
        <!-- Stats Cards Component -->
        <div id="stats-component" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="fas fa-toilet text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Kamar Mandi</p>
                        <p class="text-2xl font-bold text-gray-900" id="total-bathrooms">-</p>
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
                        <p class="text-2xl font-bold text-gray-900" id="active-employees">-</p>
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
                        <p class="text-2xl font-bold text-gray-900" id="cleaning-today">-</p>
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
                        <p class="text-2xl font-bold text-gray-900" id="points-today">-</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Real-time Status Components -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Bathroom Status Component -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Status Kamar Mandi Real-time</h2>
                    <p class="text-sm text-gray-600">Update otomatis setiap 3 detik</p>
                </div>
                <div class="p-6">
                    <div class="space-y-4" id="bathroom-status-component">
                        <div class="text-center py-8">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                            <p class="text-gray-500 mt-2">Loading...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activities Component -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Aktivitas Terbaru</h2>
                    <p class="text-sm text-gray-600">Update otomatis setiap 3 detik</p>
                </div>
                <div class="p-6">
                    <div class="text-center py-8">
                        <i class="fas fa-clock text-gray-400 text-2xl mb-2"></i>
                        <p class="text-gray-500">Belum ada aktivitas terbaru</p>
                        <p class="text-xs text-gray-400 mt-1">Aktivitas akan muncul saat ada pembersihan atau reset sistem</p>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
    
    <script>
        // Real-time Dashboard Components
        class DashboardComponents {
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
                    const response = await fetch('../api/dashboard_data.php', {
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
                    
                    const text = await response.text();
                    let data;
                    
                    try {
                        data = JSON.parse(text);
                    } catch (parseError) {
                        console.error('JSON Parse Error:', parseError);
                        console.error('Response text:', text);
                        throw new Error('Invalid JSON response');
                    }
                    
                    if (data.success) {
                        this.updateStatsComponent(data.stats);
                        this.updateBathroomStatusComponent(data.bathrooms);
                        this.setConnectionStatus(true);
                    } else {
                        throw new Error(data.message || 'Unknown error');
                    }
                } catch (error) {
                    console.error('Error updating dashboard:', error);
                    this.setConnectionStatus(false);
                }
            }
            
            updateStatsComponent(stats) {
                document.getElementById('total-bathrooms').textContent = stats.total_bathrooms || 0;
                document.getElementById('active-employees').textContent = stats.active_employees || 0;
                document.getElementById('cleaning-today').textContent = stats.needs_cleaning || 0;
                document.getElementById('points-today').textContent = stats.being_cleaned || 0;
            }
            
            updateBathroomStatusComponent(bathrooms) {
                const container = document.getElementById('bathroom-status-component');
                
                if (!bathrooms || bathrooms.length === 0) {
                    container.innerHTML = '<p class="text-gray-500 text-center py-4">Tidak ada data kamar mandi</p>';
                    return;
                }
                
                const html = bathrooms.map(bathroom => {
                    let statusClass, statusText;
                    
                    switch (bathroom.status) {
                        case 'needs_cleaning':
                            statusClass = 'bg-red-100 text-red-800';
                            statusText = 'Perlu Dibersihkan';
                            break;
                        case 'being_cleaned':
                            statusClass = 'bg-yellow-100 text-yellow-800';
                            statusText = 'Sedang Dibersihkan';
                            break;
                        default:
                            statusClass = 'bg-green-100 text-green-800';
                            statusText = 'Tersedia';
                    }
                    
                    return `
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <h3 class="font-medium text-gray-900">${this.escapeHtml(bathroom.name)}</h3>
                                <p class="text-sm text-gray-600">${this.escapeHtml(bathroom.location)}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Pengunjung: ${bathroom.current_visitors}/${bathroom.max_visitors}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 text-xs font-medium rounded-full ${statusClass}">
                                    ${statusText}
                                </span>
                                <p class="text-xs text-gray-500 mt-1">
                                    ${new Date(bathroom.updated_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})}
                                </p>
                            </div>
                        </div>
                    `;
                }).join('');
                
                container.innerHTML = html;
            }
            
            updateActivitiesComponent(bathrooms) {
                const container = document.getElementById('activities-component');
                
                // Collect recent logs from all bathrooms
                let allLogs = [];
                bathrooms.forEach(bathroom => {
                    if (bathroom.recent_logs) {
                        bathroom.recent_logs.forEach(log => {
                            allLogs.push({
                                ...log,
                                bathroom_name: bathroom.name
                            });
                        });
                    }
                });
                
                // Sort by timestamp descending
                allLogs.sort((a, b) => new Date(b.waktu) - new Date(a.waktu));
                
                // Take only the 10 most recent
                allLogs = allLogs.slice(0, 10);
                
                if (allLogs.length === 0) {
                    container.innerHTML = '<p class="text-gray-500 text-center py-4">Belum ada aktivitas</p>';
                    return;
                }
                
                const html = allLogs.map(log => {
                    let iconClass, iconColor, icon;
                    
                    switch (log.action_type) {
                        case 'admin_reset':
                            iconClass = 'bg-blue-100';
                            iconColor = 'text-blue-600';
                            icon = 'fa-shield-alt';
                            break;
                        case 'start_cleaning':
                            iconClass = 'bg-yellow-100';
                            iconColor = 'text-yellow-600';
                            icon = 'fa-broom';
                            break;
                        case 'finish_cleaning':
                            iconClass = 'bg-green-100';
                            iconColor = 'text-green-600';
                            icon = 'fa-check';
                            break;
                        default:
                            iconClass = 'bg-gray-100';
                            iconColor = 'text-gray-600';
                            icon = 'fa-info';
                    }
                    
                    return `
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 ${iconClass} rounded-full flex items-center justify-center">
                                    <i class="fas ${icon} ${iconColor} text-sm"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">
                                    ${this.escapeHtml(log.bathroom_name)}
                                </p>
                                <p class="text-sm text-gray-600">
                                    ${this.escapeHtml(log.keterangan || log.action_type)}
                                </p>
                            </div>
                            <div class="text-sm text-gray-500">
                                ${new Date(log.waktu).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})}
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
            dashboard = new DashboardComponents();
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