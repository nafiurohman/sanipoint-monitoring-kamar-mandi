<?php
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';

Auth::requireRole('admin');

$db = Database::getInstance();

// Handle RFID registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'register_rfid') {
        $employee_id = $_POST['employee_id'] ?? '';
        $rfid_code = trim($_POST['rfid_code'] ?? '');
        
        if (empty($employee_id) || empty($rfid_code)) {
            echo json_encode(['success' => false, 'message' => 'Employee ID and RFID code required']);
            exit;
        }
        
        // Check if RFID already exists
        $existing = $db->fetch("SELECT id, name FROM users WHERE rfid_code = ?", [$rfid_code]);
        if ($existing) {
            echo json_encode(['success' => false, 'message' => "RFID already registered to {$existing['name']}"]);
            exit;
        }
        
        // Update employee with RFID
        $result = $db->execute("UPDATE users SET rfid_code = ? WHERE id = ? AND role = 'karyawan'", [$rfid_code, $employee_id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'RFID registered successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to register RFID']);
        }
        exit;
    }
    
    if ($action === 'remove_rfid') {
        $employee_id = $_POST['employee_id'] ?? '';
        
        $result = $db->execute("UPDATE users SET rfid_code = NULL WHERE id = ?", [$employee_id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'RFID removed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove RFID']);
        }
        exit;
    }
}

// Get all employees
$employees = $db->fetchAll("SELECT id, name, email, rfid_code, points, created_at FROM users WHERE role = 'karyawan' ORDER BY name");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen RFID - SANIPOINT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .toast { animation: slideIn 0.3s ease-out; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
</head>
<body class="bg-gray-100">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Manajemen RFID</h1>
                <p class="text-gray-600 text-sm lg:text-base">Daftarkan kartu RFID untuk karyawan</p>
            </div>
            <button onclick="openModal('rfid-modal')" class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-id-card mr-2"></i>Daftar RFID Baru
            </button>
        </div>
        
        <!-- Employee List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Karyawan & RFID</h2>
            </div>
            
            <!-- Mobile View -->
            <div class="block lg:hidden">
                <?php foreach ($employees as $employee): ?>
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-medium text-gray-900"><?= htmlspecialchars($employee['name']) ?></h3>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($employee['email']) ?></p>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded-full <?= $employee['rfid_code'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $employee['rfid_code'] ? 'Terdaftar' : 'Belum Terdaftar' ?>
                            </span>
                        </div>
                        
                        <?php if ($employee['rfid_code']): ?>
                            <div class="mb-3">
                                <span class="text-sm text-gray-500">RFID:</span>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm"><?= htmlspecialchars($employee['rfid_code']) ?></code>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex space-x-2">
                            <?php if ($employee['rfid_code']): ?>
                                <button onclick="removeRfid('<?= $employee['id'] ?>')" class="flex-1 bg-red-50 text-red-600 px-3 py-2 rounded text-sm hover:bg-red-100">
                                    <i class="fas fa-trash mr-1"></i>Hapus RFID
                                </button>
                            <?php else: ?>
                                <button onclick="registerRfid('<?= $employee['id'] ?>', '<?= htmlspecialchars($employee['name']) ?>')" class="flex-1 bg-blue-50 text-blue-600 px-3 py-2 rounded text-sm hover:bg-blue-100">
                                    <i class="fas fa-id-card mr-1"></i>Daftar RFID
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Desktop View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RFID Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Poin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($employees as $employee): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-blue-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($employee['name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($employee['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($employee['rfid_code']): ?>
                                        <code class="bg-gray-100 px-2 py-1 rounded text-sm"><?= htmlspecialchars($employee['rfid_code']) ?></code>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-sm">Belum terdaftar</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= number_format($employee['points']) ?> pts
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $employee['rfid_code'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $employee['rfid_code'] ? 'Terdaftar' : 'Belum Terdaftar' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?php if ($employee['rfid_code']): ?>
                                        <button onclick="removeRfid('<?= $employee['id'] ?>')" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash mr-1"></i>Hapus
                                        </button>
                                    <?php else: ?>
                                        <button onclick="registerRfid('<?= $employee['id'] ?>', '<?= htmlspecialchars($employee['name']) ?>')" class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-id-card mr-1"></i>Daftar
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>
    
    <!-- RFID Registration Modal -->
    <div id="rfid-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Daftar RFID Karyawan</h3>
            <form id="rfid-form" class="space-y-4">
                <input type="hidden" name="action" value="register_rfid">
                <input type="hidden" id="employee_id" name="employee_id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Karyawan</label>
                    <select id="employee_select" name="employee_id_select" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih karyawan...</option>
                        <?php foreach ($employees as $emp): ?>
                            <?php if (!$emp['rfid_code']): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RFID Code</label>
                    <input type="text" id="rfid_code" name="rfid_code" required 
                           placeholder="Contoh: B490FBB0" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Masukkan kode RFID dari kartu karyawan</p>
                </div>
                
                <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4">
                    <button type="button" onclick="closeModal('rfid-modal')" class="w-full sm:w-auto px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Daftar RFID
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.body.style.overflow = 'auto';
            document.getElementById('rfid-form').reset();
        }
        
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            
            toast.className = `toast fixed top-4 right-4 ${bgColor} text-white p-4 rounded-lg shadow-lg z-50 max-w-sm`;
            toast.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                    <span class="flex-1">${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }
        
        function registerRfid(employeeId, employeeName) {
            document.getElementById('employee_id').value = employeeId;
            document.getElementById('employee_select').value = employeeId;
            openModal('rfid-modal');
        }
        
        document.getElementById('employee_select').addEventListener('change', function() {
            document.getElementById('employee_id').value = this.value;
        });
        
        document.getElementById('rfid-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.set('employee_id', document.getElementById('employee_id').value);
            
            try {
                const response = await fetch('rfid.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    closeModal('rfid-modal');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            }
        });
        
        async function removeRfid(employeeId) {
            if (!confirm('Yakin ingin menghapus RFID karyawan ini?')) return;
            
            const formData = new FormData();
            formData.append('action', 'remove_rfid');
            formData.append('employee_id', employeeId);
            
            try {
                const response = await fetch('rfid.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            }
        }
    </script>
</body>
</html>