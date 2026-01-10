<?php
require_once '../config/config.php';
require_once '../core/Database.php';

$db = Database::getInstance();

// Get real data from database
$users = $db->fetchAll("SELECT r.uid as rfid_code, r.nama_pemilik as name, r.peran as role, r.status as is_active FROM rfid_cards r WHERE r.status = 'Aktif' ORDER BY r.peran, r.nama_pemilik");
$bathrooms = $db->fetchAll("SELECT * FROM bathrooms WHERE is_active = 1 ORDER BY name");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP32 Simulator - SANIPOINT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto p-6">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">
                <i class="fas fa-microchip text-blue-600 mr-2"></i>
                ESP32 Simulator - SANIPOINT IoT
            </h1>
            <p class="text-gray-600">Simulasi sensor dan RFID untuk testing sistem real-time</p>
            
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="bg-blue-50 p-3 rounded">
                    <strong>RFID Users:</strong> <?= count($users) ?>
                </div>
                <div class="bg-green-50 p-3 rounded">
                    <strong>Bathrooms:</strong> <?= count($bathrooms) ?>
                </div>
                <div class="bg-purple-50 p-3 rounded">
                    <strong>Database:</strong> <?= DB_NAME ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Sensor Data Simulator -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">
                    <i class="fas fa-chart-line text-green-600 mr-2"></i>
                    Sensor Data Simulator
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bathroom</label>
                        <select id="bathroom_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <?php if (empty($bathrooms)): ?>
                                <option value="">No bathrooms found</option>
                            <?php else: ?>
                                <?php foreach ($bathrooms as $bathroom): ?>
                                    <option value="<?= $bathroom['id'] ?>">
                                        <?= htmlspecialchars($bathroom['name']) ?> - <?= htmlspecialchars($bathroom['location']) ?>
                                        (Current: <?= $bathroom['current_visitors'] ?>/<?= $bathroom['max_visitors'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Visitor Count</label>
                        <div class="flex items-center space-x-2">
                            <input type="range" id="visitor_count" min="0" max="15" value="0" class="flex-1">
                            <span id="visitor_display" class="w-12 text-center font-mono">0</span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gas Level (ppm)</label>
                        <div class="flex items-center space-x-2">
                            <input type="range" id="gas_level" min="100" max="1000" value="300" class="flex-1">
                            <span id="gas_display" class="w-16 text-center font-mono">300</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2">
                        <button onclick="addVisitor()" class="bg-orange-600 text-white py-2 px-4 rounded-md hover:bg-orange-700">
                            <i class="fas fa-plus mr-1"></i>Add Visitor
                        </button>
                        <button onclick="resetVisitors()" class="bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700">
                            <i class="fas fa-undo mr-1"></i>Reset Count
                        </button>
                    </div>
                    
                    <button onclick="sendSensorData()" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>Update Bathroom Status
                    </button>
                </div>
            </div>

            <!-- RFID Simulator -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">
                    <i class="fas fa-id-card text-purple-600 mr-2"></i>
                    RFID Simulator
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Database RFID Cards</label>
                        <div class="grid grid-cols-1 gap-2 max-h-64 overflow-y-auto">
                            <?php if (empty($users)): ?>
                                <div class="text-center py-4 text-gray-500">
                                    No RFID cards found in database
                                </div>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <button onclick="tapRFID('<?= htmlspecialchars($user['rfid_code']) ?>')" 
                                            class="<?= strtolower($user['role']) === 'admin' ? 'bg-red-100 text-red-800 hover:bg-red-200' : 'bg-blue-100 text-blue-800 hover:bg-blue-200' ?> py-2 px-4 rounded-md transition-colors text-left">
                                        <i class="fas fa-<?= strtolower($user['role']) === 'admin' ? 'user-shield' : 'user' ?> mr-2"></i>
                                        <strong><?= ucfirst(strtolower($user['role'])) ?>:</strong> <?= htmlspecialchars($user['rfid_code']) ?>
                                        <br><small><?= htmlspecialchars($user['name']) ?></small>
                                    </button>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Custom RFID UID</label>
                        <div class="flex space-x-2">
                            <input type="text" id="custom_rfid" placeholder="Enter RFID UID" class="flex-1 px-3 py-2 border border-gray-300 rounded-md">
                            <button onclick="tapCustomRFID()" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
                                <i class="fas fa-wifi"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 p-3 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Flow:</strong> Tap 1 = Start cleaning, Tap 2 = Finish + Get points
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-desktop text-indigo-600 mr-2"></i>
                Real-time System Status
            </h2>
            <div id="status_grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                <!-- Status will be loaded here -->
            </div>
            <button onclick="refreshStatus()" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                <i class="fas fa-sync mr-2"></i>Refresh Status
            </button>
        </div>

        <!-- Response Log -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-terminal text-gray-600 mr-2"></i>
                Response Log
            </h2>
            <div id="response_log" class="bg-gray-900 text-green-400 p-4 rounded-md font-mono text-sm h-64 overflow-y-auto">
                ESP32 Simulator ready. Use controls above to test the system.
            </div>
        </div>

        <!-- Auto Simulation -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-robot text-orange-600 mr-2"></i>
                Auto Simulation
            </h2>
            <div class="flex items-center space-x-4">
                <button id="auto_btn" onclick="toggleAutoSim()" class="bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700">
                    <i class="fas fa-play mr-2"></i>Start Auto Simulation
                </button>
                <span id="auto_status" class="text-gray-600">Stopped</span>
                <div class="text-sm text-gray-500">
                    Simulates random visitor entries and sensor readings every 10 seconds
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4">
                <i class="fas fa-bolt text-yellow-600 mr-2"></i>
                Quick Actions
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button onclick="simulateFullCycle()" class="bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700">
                    <i class="fas fa-recycle mr-2"></i>Full Cycle Test
                </button>
                <button onclick="resetAllBathrooms()" class="bg-red-600 text-white py-3 px-4 rounded-md hover:bg-red-700">
                    <i class="fas fa-refresh mr-2"></i>Reset All
                </button>
                <button onclick="triggerAlert()" class="bg-yellow-600 text-white py-3 px-4 rounded-md hover:bg-yellow-700">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Trigger Alert
                </button>
                <button onclick="exportLogs()" class="bg-green-600 text-white py-3 px-4 rounded-md hover:bg-green-700">
                    <i class="fas fa-download mr-2"></i>Export Logs
                </button>
            </div>
        </div>
    </div>

    <script>
        let autoSimInterval = null;
        let isAutoRunning = false;
        let logs = [];

        // Update displays
        document.getElementById('visitor_count').addEventListener('input', function() {
            document.getElementById('visitor_display').textContent = this.value;
        });

        document.getElementById('gas_level').addEventListener('input', function() {
            document.getElementById('gas_display').textContent = this.value;
        });

        async function addVisitor() {
            const currentCount = parseInt(document.getElementById('visitor_count').value);
            const newCount = Math.min(currentCount + 1, 15);
            
            document.getElementById('visitor_count').value = newCount;
            document.getElementById('visitor_display').textContent = newCount;
            
            await sendSensorData();
        }
        
        async function resetVisitors() {
            document.getElementById('visitor_count').value = 0;
            document.getElementById('visitor_display').textContent = 0;
            await sendSensorData();
        }

        async function sendSensorData() {
            const bathroomId = document.getElementById('bathroom_id').value;
            const count = document.getElementById('visitor_count').value;

            try {
                const formData = new FormData();
                formData.append('bathroom_id', bathroomId);
                formData.append('action', 'set');
                formData.append('count', count);

                const response = await fetch('../api/simulate_visitor.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                logResponse(`Sensor Update: ${result.message}`, result.success ? 'success' : 'error');
                refreshStatus();
            } catch (error) {
                logResponse(`Sensor Error: ${error.message}`, 'error');
            }
        }

        async function tapRFID(uid) {
            const bathroomId = document.getElementById('bathroom_id').value;

            const formData = new FormData();
            formData.append('uid', uid);
            formData.append('bathroom_id', bathroomId);

            try {
                const response = await fetch('../api/rfid_tap.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                logResponse(`RFID Tap (${uid}): ${result.message}`, result.success ? 'success' : 'error', result);
                refreshStatus();
            } catch (error) {
                logResponse(`RFID Error: ${error.message}`, 'error');
            }
        }

        function tapCustomRFID() {
            const uid = document.getElementById('custom_rfid').value.trim();
            if (uid) {
                tapRFID(uid);
                document.getElementById('custom_rfid').value = '';
            }
        }

        function logResponse(message, type = 'info', data = null) {
            const timestamp = new Date().toLocaleTimeString();
            const log = document.getElementById('response_log');
            
            const color = type === 'success' ? 'text-green-400' : type === 'error' ? 'text-red-400' : 'text-blue-400';
            
            let logEntry = `[${timestamp}] ${message}`;
            if (data && data.action) {
                logEntry += ` | Action: ${data.action}`;
            }
            if (data && data.user_name) {
                logEntry += ` | User: ${data.user_name}`;
            }
            
            logs.push({timestamp, message, type, data});
            log.innerHTML += `<div class="${color}">${logEntry}</div>`;
            log.scrollTop = log.scrollHeight;
        }

        async function refreshStatus() {
            try {
                const response = await fetch('../api/realtime_status.php');
                const data = await response.json();
                
                if (data.success && data.bathrooms) {
                    const statusGrid = document.getElementById('status_grid');
                    statusGrid.innerHTML = data.bathrooms.map(bathroom => {
                        const statusColor = bathroom.computed_status === 'available' ? 'bg-green-100 text-green-800' :
                                          bathroom.computed_status === 'being_cleaned' ? 'bg-yellow-100 text-yellow-800' :
                                          'bg-red-100 text-red-800';
                        
                        return `
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h3 class="font-medium text-gray-900">${bathroom.name}</h3>
                                <p class="text-sm text-gray-600">${bathroom.location}</p>
                                <div class="mt-2">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full ${statusColor}">
                                        ${bathroom.computed_status.replace('_', ' ').toUpperCase()}
                                    </span>
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    Visitors: ${bathroom.current_visitors}/${bathroom.max_visitors}<br>
                                    ${bathroom.cleaning_by ? `Cleaning by: ${bathroom.cleaning_by}` : ''}
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            } catch (error) {
                console.error('Failed to refresh status:', error);
            }
        }

        function toggleAutoSim() {
            if (isAutoRunning) {
                clearInterval(autoSimInterval);
                isAutoRunning = false;
                document.getElementById('auto_btn').innerHTML = '<i class="fas fa-play mr-2"></i>Start Auto Simulation';
                document.getElementById('auto_status').textContent = 'Stopped';
                logResponse('Auto simulation stopped', 'info');
            } else {
                isAutoRunning = true;
                document.getElementById('auto_btn').innerHTML = '<i class="fas fa-stop mr-2"></i>Stop Auto Simulation';
                document.getElementById('auto_status').textContent = 'Running...';
                logResponse('Auto simulation started', 'info');
                
                autoSimInterval = setInterval(() => {
                    const randomGas = Math.floor(Math.random() * 400) + 200;
                    const randomAction = Math.random();
                    
                    document.getElementById('gas_level').value = randomGas;
                    document.getElementById('gas_display').textContent = randomGas;
                    
                    if (randomAction < 0.3) {
                        addVisitor();
                    } else if (randomAction < 0.1) {
                        const rfidUsers = <?= json_encode(array_column($users, 'rfid_code')) ?>;
                        if (rfidUsers.length > 0) {
                            const randomRfid = rfidUsers[Math.floor(Math.random() * rfidUsers.length)];
                            tapRFID(randomRfid);
                        }
                    }
                }, 10000);
            }
        }

        async function simulateFullCycle() {
            logResponse('Starting full cycle simulation...', 'info');
            
            document.getElementById('visitor_count').value = 10;
            document.getElementById('visitor_display').textContent = 10;
            await sendSensorData();
            
            setTimeout(async () => {
                const rfidUsers = <?= json_encode(array_column($users, 'rfid_code')) ?>;
                if (rfidUsers.length > 0) {
                    await tapRFID(rfidUsers[0]);
                    
                    setTimeout(async () => {
                        await tapRFID(rfidUsers[0]);
                        logResponse('Full cycle simulation completed', 'success');
                    }, 3000);
                }
            }, 2000);
        }

        async function resetAllBathrooms() {
            try {
                const bathrooms = <?= json_encode(array_column($bathrooms, 'id')) ?>;
                for (const bathroomId of bathrooms) {
                    const formData = new FormData();
                    formData.append('bathroom_id', bathroomId);
                    formData.append('action', 'set');
                    formData.append('count', 0);
                    
                    await fetch('../api/simulate_visitor.php', {
                        method: 'POST',
                        body: formData
                    });
                }
                
                document.getElementById('visitor_count').value = 0;
                document.getElementById('visitor_display').textContent = 0;
                
                logResponse('All bathrooms reset to clean state', 'success');
                refreshStatus();
            } catch (error) {
                logResponse(`Reset Error: ${error.message}`, 'error');
            }
        }

        function triggerAlert() {
            document.getElementById('visitor_count').value = 15;
            document.getElementById('visitor_display').textContent = 15;
            document.getElementById('gas_level').value = 900;
            document.getElementById('gas_display').textContent = 900;
            
            sendSensorData();
            logResponse('Alert triggered - High visitor count and gas level', 'error');
        }

        function exportLogs() {
            const logData = logs.map(log => `${log.timestamp} [${log.type.toUpperCase()}] ${log.message}`).join('\n');
            const blob = new Blob([logData], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = `sanipoint-simulator-logs-${new Date().toISOString().split('T')[0]}.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            
            logResponse('Logs exported successfully', 'success');
        }

        // Initialize
        refreshStatus();
        setInterval(refreshStatus, 5000);
        logResponse('ESP32 Simulator initialized with enhanced features', 'success');
    </script>
</body>
</html>