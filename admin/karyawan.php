<?php
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';

Auth::requireRole('admin');

$db = Database::getInstance();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $rfid_code = trim($_POST['rfid_code'] ?? '');
            
            if (empty($name) || empty($email) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Nama, email, dan password wajib diisi']);
                exit;
            }
            
            // Check if email exists
            $existing = $db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
                exit;
            }
            
            // Check RFID if provided
            if (!empty($rfid_code)) {
                $existing_rfid = $db->fetch("SELECT id FROM users WHERE rfid_code = ?", [$rfid_code]);
                if ($existing_rfid) {
                    echo json_encode(['success' => false, 'message' => 'RFID sudah terdaftar']);
                    exit;
                }
            }
            
            try {
                $employee_id = $db->insert('users', [
                    'id' => uniqid('usr_', true),
                    'username' => strtolower(str_replace(' ', '', $name)),
                    'full_name' => $name,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => 'karyawan',
                    'rfid_code' => !empty($rfid_code) ? strtoupper($rfid_code) : null,
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                // Create points record
                $db->insert('points', [
                    'id' => uniqid('pt_', true),
                    'user_id' => $employee_id,
                    'current_balance' => 0,
                    'total_earned' => 0,
                    'total_spent' => 0
                ]);
                
                echo json_encode(['success' => true, 'message' => 'Karyawan berhasil ditambahkan']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Gagal menambahkan karyawan: ' . $e->getMessage()]);
            }
            break;
            
        case 'update':
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $rfid_code = trim($_POST['rfid_code'] ?? '');
            
            if (empty($id) || empty($name) || empty($email)) {
                echo json_encode(['success' => false, 'message' => 'ID, nama, dan email wajib diisi']);
                exit;
            }
            
            // Check if email exists for other users
            $existing = $db->fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $id]);
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'Email sudah digunakan karyawan lain']);
                exit;
            }
            
            // Check RFID if provided
            if (!empty($rfid_code)) {
                $existing_rfid = $db->fetch("SELECT id FROM users WHERE rfid_code = ? AND id != ?", [$rfid_code, $id]);
                if ($existing_rfid) {
                    echo json_encode(['success' => false, 'message' => 'RFID sudah digunakan karyawan lain']);
                    exit;
                }
            }
            
            try {
                // Update user data with RFID
                $updateData = [
                    'full_name' => $name,
                    'email' => $email,
                    'rfid_code' => !empty($rfid_code) ? strtoupper($rfid_code) : null
                ];
                
                if (!empty($_POST['password'])) {
                    $updateData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }
                
                $db->execute(
                    "UPDATE users SET full_name = ?, email = ?, rfid_code = ?" . 
                    (!empty($_POST['password']) ? ", password = ?" : "") . 
                    " WHERE id = ?",
                    !empty($_POST['password']) ? 
                        [$name, $email, $updateData['rfid_code'], $updateData['password'], $id] :
                        [$name, $email, $updateData['rfid_code'], $id]
                );
                
                // Remove old RFID card references
                $db->execute("DELETE FROM rfid_cards WHERE user_id = ?", [$id]);
                
                echo json_encode(['success' => true, 'message' => 'Data karyawan berhasil diperbarui']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data karyawan: ' . $e->getMessage()]);
            }
            break;
            
        case 'toggle_status':
            $id = $_POST['id'] ?? '';
            
            try {
                $employee = $db->fetch("SELECT is_active FROM users WHERE id = ? AND role = 'karyawan'", [$id]);
                if (!$employee) {
                    echo json_encode(['success' => false, 'message' => 'Karyawan tidak ditemukan']);
                    exit;
                }
                
                $newStatus = $employee['is_active'] ? 0 : 1;
                $db->execute("UPDATE users SET is_active = ? WHERE id = ?", [$newStatus, $id]);
                
                $message = $newStatus ? 'Karyawan diaktifkan' : 'Karyawan dinonaktifkan';
                echo json_encode(['success' => true, 'message' => $message]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Gagal mengubah status karyawan']);
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? '';
            
            try {
                // Check if employee has cleaning activities
                $activities = null;
                try {
                    $activities = $db->fetch("SELECT COUNT(*) as count FROM cleaning_logs WHERE user_id = ?", [$id]);
                } catch (Exception $e) {
                    // Table doesn't exist, proceed with hard delete
                }
                
                if ($activities && $activities['count'] > 0) {
                    // Soft delete - deactivate instead
                    $db->execute("UPDATE users SET is_active = 0 WHERE id = ?", [$id]);
                    echo json_encode(['success' => true, 'message' => 'Karyawan dinonaktifkan (memiliki riwayat aktivitas)']);
                } else {
                    // Hard delete - remove from all related tables
                    $db->execute("DELETE FROM rfid_cards WHERE user_id = ?", [$id]);
                    $db->execute("DELETE FROM points WHERE user_id = ?", [$id]);
                    $db->execute("DELETE FROM users WHERE id = ? AND role = 'karyawan'", [$id]);
                    echo json_encode(['success' => true, 'message' => 'Karyawan berhasil dihapus']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus karyawan: ' . $e->getMessage()]);
            }
            break;
            
        case 'update_points_setting':
            $points = (int)($_POST['points_per_cleaning'] ?? 10);
            
            if ($points < 1 || $points > 1000) {
                echo json_encode(['success' => false, 'message' => 'Poin harus antara 1-1000']);
                exit;
            }
            
            try {
                // Check if setting exists
                $existing = $db->fetch("SELECT id FROM system_settings WHERE setting_key = 'points_per_cleaning'");
                
                if ($existing) {
                    $db->execute("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'points_per_cleaning'", [$points]);
                } else {
                    $db->insert('system_settings', [
                        'id' => uniqid('set_', true),
                        'setting_key' => 'points_per_cleaning',
                        'setting_value' => $points,
                        'description' => 'Points awarded per cleaning completion'
                    ]);
                }
                
                echo json_encode(['success' => true, 'message' => 'Setting poin berhasil diperbarui']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui setting: ' . $e->getMessage()]);
            }
            break;
            
        case 'get':
            $id = $_GET['id'] ?? $_POST['id'] ?? '';
            
            try {
                $employee = $db->fetch("SELECT * FROM users WHERE id = ? AND role = 'karyawan'", [$id]);
                if ($employee) {
                    echo json_encode(['success' => true, 'data' => $employee]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Karyawan tidak ditemukan']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Gagal mengambil data karyawan']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
    }
    exit;
}

// Handle GET request for employee data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    header('Content-Type: application/json');
    $id = $_GET['id'] ?? '';
    
    try {
        $employee = $db->fetch("SELECT * FROM users WHERE id = ? AND role = 'karyawan'", [$id]);
        if ($employee) {
            echo json_encode(['success' => true, 'data' => $employee]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Karyawan tidak ditemukan']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal mengambil data karyawan']);
    }
    exit;
}

// Get current points setting
$points_setting = $db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = 'points_per_cleaning'");
$current_points = (int)($points_setting['setting_value'] ?? 10);

// Get all employees with statistics (with error handling)
try {
    $employees = $db->fetchAll("
        SELECT 
            u.*,
            COALESCE(p.current_balance, 0) as points,
            COALESCE(cl.total_cleanings, 0) as total_cleanings,
            COALESCE(cl.total_points_earned, 0) as total_points_earned,
            r.uid as rfid_uid,
            r.nama_pemilik as rfid_name,
            r.status as rfid_status
        FROM users u
        LEFT JOIN points p ON u.id = p.user_id
        LEFT JOIN (
            SELECT 
                user_id,
                COUNT(*) as total_cleanings,
                SUM(points_earned) as total_points_earned
            FROM cleaning_logs 
            WHERE status = 'completed'
            GROUP BY user_id
        ) cl ON u.id = cl.user_id
        LEFT JOIN rfid_cards r ON u.id = r.user_id
        WHERE u.role = 'karyawan'
        ORDER BY u.created_at DESC
    ");
} catch (Exception $e) {
    // Fallback if tables don't exist
    $employees = $db->fetchAll("
        SELECT 
            *,
            0 as points,
            0 as total_cleanings,
            0 as total_points_earned,
            NULL as rfid_uid,
            NULL as rfid_name,
            NULL as rfid_status
        FROM users 
        WHERE role = 'karyawan'
        ORDER BY created_at DESC
    ");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Karyawan - SANIPOINT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .toast { animation: slideIn 0.3s ease-out; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .toast.hide { animation: slideOut 0.3s ease-in; }
        @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
    </style>
</head>
<body class="bg-gray-100">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="p-4 lg:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Manajemen Karyawan</h1>
                <p class="text-gray-600 text-sm lg:text-base">Kelola data karyawan dan RFID</p>
            </div>
            <div class="flex space-x-2">
                <button onclick="openModal('points-setting-modal')" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-cog mr-2"></i>Setting Poin
                </button>
                <button onclick="openModal('employee-modal')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-user-plus mr-2"></i>Tambah Karyawan
                </button>
            </div>
        </div>
        
        <!-- Employee Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-500">Total Karyawan</p>
                        <p class="text-lg font-semibold"><?= count($employees) ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="fas fa-user-check text-green-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-500">Aktif</p>
                        <p class="text-lg font-semibold"><?= count(array_filter($employees, fn($e) => ($e['is_active'] ?? 0))) ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <i class="fas fa-id-card text-purple-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-500">Terdaftar RFID</p>
                        <p class="text-lg font-semibold"><?= count(array_filter($employees, fn($e) => !empty($e['rfid_uid'] ?? ''))) ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <i class="fas fa-star text-yellow-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-500">Total Poin</p>
                        <p class="text-lg font-semibold"><?= number_format(array_sum(array_map(fn($e) => $e['points'] ?? 0, $employees))) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Employee List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Karyawan</h2>
            </div>
            
            <!-- Mobile View -->
            <div class="block lg:hidden">
                <?php foreach ($employees as $employee): ?>
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-blue-600"></i>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900"><?= htmlspecialchars($employee['full_name'] ?? $employee['name'] ?? 'N/A') ?></h3>
                                    <p class="text-sm text-gray-500"><?= htmlspecialchars($employee['email'] ?? 'N/A') ?></p>
                                </div>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded-full <?= ($employee['is_active'] ?? 0) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= ($employee['is_active'] ?? 0) ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-3 text-sm">
                            <div>
                                <span class="text-gray-500">RFID:</span>
                                <?php if (!empty($employee['rfid_uid'])): ?>
                                    <code class="bg-gray-100 px-1 rounded text-xs"><?= htmlspecialchars($employee['rfid_uid']) ?></code>
                                <?php else: ?>
                                    <span class="text-red-500">Belum terdaftar</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="text-gray-500">Poin:</span>
                                <span class="font-medium"><?= number_format($employee['points'] ?? 0) ?></span>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <button onclick="editEmployee('<?= $employee['id'] ?>')" class="flex-1 bg-blue-50 text-blue-600 px-3 py-2 rounded text-sm hover:bg-blue-100">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button onclick="toggleStatus('<?= $employee['id'] ?>')" class="flex-1 bg-yellow-50 text-yellow-600 px-3 py-2 rounded text-sm hover:bg-yellow-100">
                                <i class="fas fa-toggle-<?= ($employee['is_active'] ?? 0) ? 'on' : 'off' ?> mr-1"></i>Toggle
                            </button>
                            <button onclick="deleteEmployee('<?= $employee['id'] ?>')" class="bg-red-50 text-red-600 px-3 py-2 rounded text-sm hover:bg-red-100">
                                <i class="fas fa-trash"></i>
                            </button>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RFID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Poin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktivitas</th>
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
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($employee['full_name'] ?? $employee['name'] ?? 'N/A') ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($employee['email'] ?? 'N/A') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (!empty($employee['rfid_uid'])): ?>
                                        <code class="bg-gray-100 px-2 py-1 rounded text-sm"><?= htmlspecialchars($employee['rfid_uid']) ?></code>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($employee['rfid_name']) ?></div>
                                    <?php else: ?>
                                        <span class="text-red-500 text-sm">Belum terdaftar</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="font-medium"><?= number_format($employee['points'] ?? 0) ?> pts</div>
                                    <div class="text-xs text-gray-500">Earned: <?= number_format($employee['total_points_earned'] ?? 0) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div><?= $employee['total_cleanings'] ?? 0 ?> pembersihan</div>
                                    <div class="text-xs text-gray-500">Bergabung: <?= date('d/m/Y', strtotime($employee['created_at'] ?? date('Y-m-d'))) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= ($employee['is_active'] ?? 0) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= ($employee['is_active'] ?? 0) ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="editEmployee('<?= $employee['id'] ?>')" class="text-blue-600 hover:text-blue-900 p-1">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="toggleStatus('<?= $employee['id'] ?>')" class="text-yellow-600 hover:text-yellow-900 p-1">
                                            <i class="fas fa-toggle-<?= ($employee['is_active'] ?? 0) ? 'on' : 'off' ?>"></i>
                                        </button>
                                        <button onclick="deleteEmployee('<?= $employee['id'] ?>')" class="text-red-600 hover:text-red-900 p-1">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>
    
    <!-- Points Setting Modal -->
    <div id="points-setting-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Setting Poin Pembersihan</h3>
            <form id="points-setting-form" class="space-y-4">
                <input type="hidden" name="action" value="update_points_setting">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Poin per Pembersihan</label>
                    <input type="number" id="points_per_cleaning" name="points_per_cleaning" 
                           value="<?= $current_points ?>" min="1" max="1000" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Poin yang diberikan kepada karyawan setiap selesai membersihkan (1-1000)</p>
                </div>
                
                <div class="bg-blue-50 p-3 rounded-lg">
                    <p class="text-sm text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        Poin saat ini: <strong><?= $current_points ?> poin</strong> per pembersihan
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4">
                    <button type="button" onclick="closeModal('points-setting-modal')" class="w-full sm:w-auto px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                        Simpan Setting
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Employee Modal -->
    <div id="employee-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
            <h3 id="employee-modal-title" class="text-lg font-semibold mb-4">Tambah Karyawan Baru</h3>
            <form id="employee-form" class="space-y-4">
                <input type="hidden" name="action" value="create">
                <input type="hidden" id="employee_id" name="id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" id="employee_name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="employee_email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="employee_password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1" id="password-help">Minimal 6 karakter</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RFID Code (Opsional)</label>
                    <input type="text" id="employee_rfid" name="rfid_code" placeholder="Contoh: B490FBB0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika belum ada kartu RFID</p>
                </div>
                
                <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4">
                    <button type="button" onclick="closeModal('employee-modal')" class="w-full sm:w-auto px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-sm">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2" id="confirm-title">Konfirmasi</h3>
                <p class="text-sm text-gray-500 mb-6" id="confirm-message">Apakah Anda yakin?</p>
                <div class="flex space-x-3">
                    <button onclick="closeConfirm()" class="flex-1 px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button id="confirm-action" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let confirmCallback = null;
        
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            if (modalId === 'employee-modal') {
                // Reset form for new employee
                document.getElementById('employee-form').reset();
                document.getElementById('employee_id').value = '';
                document.getElementById('employee-modal-title').textContent = 'Tambah Karyawan Baru';
                document.querySelector('#employee-form input[name="action"]').value = 'create';
                document.getElementById('employee_password').required = true;
                document.getElementById('password-help').textContent = 'Minimal 6 karakter';
            }
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function showConfirm(title, message, callback, actionText = 'Ya, Lanjutkan') {
            document.getElementById('confirm-title').textContent = title;
            document.getElementById('confirm-message').textContent = message;
            document.getElementById('confirm-action').textContent = actionText;
            confirmCallback = callback;
            openModal('confirm-modal');
        }
        
        function closeConfirm() {
            closeModal('confirm-modal');
            confirmCallback = null;
        }
        
        document.getElementById('confirm-action').onclick = function() {
            if (confirmCallback) {
                confirmCallback();
                closeConfirm();
            }
        };
        
        function showToast(message, type = 'info', duration = 4000) {
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
            
            toast.className = `toast fixed top-4 right-4 ${bgColor} text-white p-4 rounded-lg shadow-lg z-50 max-w-sm`;
            toast.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="fas ${icon}"></i>
                    <span class="flex-1">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.classList.add('hide');
                    setTimeout(() => toast.remove(), 300);
                }
            }, duration);
        }
        
        function showLoading(show = true) {
            const buttons = document.querySelectorAll('button');
            buttons.forEach(btn => {
                if (show) {
                    btn.disabled = true;
                    btn.style.opacity = '0.6';
                } else {
                    btn.disabled = false;
                    btn.style.opacity = '1';
                }
            });
        }
        
        // Points setting form submission
        document.getElementById('points-setting-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            showLoading(true);
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('karyawan.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    closeModal('points-setting-modal');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            } finally {
                showLoading(false);
            }
        });
        
        // Employee form submission
        document.getElementById('employee-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            showLoading(true);
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('karyawan.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    closeModal('employee-modal');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            } finally {
                showLoading(false);
            }
        });
        
        // Edit employee
        async function editEmployee(id) {
            try {
                const response = await fetch(`karyawan.php?action=get&id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    
                    document.getElementById('employee_id').value = data.id;
                    document.getElementById('employee_name').value = data.full_name || data.name || '';
                    document.getElementById('employee_email').value = data.email || '';
                    document.getElementById('employee_rfid').value = data.rfid_code || '';
                    document.getElementById('employee_password').value = '';
                    document.getElementById('employee_password').required = false;
                    
                    document.getElementById('employee-modal-title').textContent = `Edit Karyawan: ${data.full_name || data.name}`;
                    document.querySelector('#employee-form input[name="action"]').value = 'update';
                    document.getElementById('password-help').textContent = 'Kosongkan jika tidak ingin mengubah password';
                    
                    openModal('employee-modal');
                } else {
                    showToast('Gagal memuat data karyawan', 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            }
        }
        
        // Toggle employee status
        async function toggleStatus(id) {
            showConfirm(
                'Ubah Status Karyawan',
                'Apakah Anda yakin ingin mengubah status karyawan ini?',
                async () => {
                    showLoading(true);
                    const formData = new FormData();
                    formData.append('action', 'toggle_status');
                    formData.append('id', id);
                    
                    try {
                        const response = await fetch('karyawan.php', {
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
                    } finally {
                        showLoading(false);
                    }
                },
                'Ya, Ubah Status'
            );
        }
        
        // Delete employee
        async function deleteEmployee(id) {
            showConfirm(
                'Hapus Karyawan',
                'Apakah Anda yakin ingin menghapus karyawan ini? Jika karyawan memiliki riwayat aktivitas, akun akan dinonaktifkan.',
                async () => {
                    showLoading(true);
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);
                    
                    try {
                        const response = await fetch('karyawan.php', {
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
                    } finally {
                        showLoading(false);
                    }
                },
                'Ya, Hapus'
            );
        }
        
        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
                const modals = ['employee-modal', 'confirm-modal', 'points-setting-modal'];
                modals.forEach(modalId => {
                    if (!document.getElementById(modalId).classList.contains('hidden')) {
                        closeModal(modalId);
                    }
                });
            }
        });
        
        // Handle escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modals = ['employee-modal', 'confirm-modal', 'points-setting-modal'];
                modals.forEach(modalId => {
                    if (!document.getElementById(modalId).classList.contains('hidden')) {
                        closeModal(modalId);
                    }
                });
            }
        });
    </script>
</body>
</html>