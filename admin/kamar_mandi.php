<?php
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';
require_once '../core/Security.php';
require_once '../models/BathroomModel.php';

Auth::requireRole('admin');

$bathroomModel = new BathroomModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'location' => trim($_POST['location'] ?? ''),
                'max_visitors' => (int)($_POST['max_visitors'] ?? 5)
            ];
            $result = $bathroomModel->create($data);
            echo json_encode($result);
            break;
        case 'update':
            $id = $_POST['id'] ?? '';
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'location' => trim($_POST['location'] ?? ''),
                'max_visitors' => (int)($_POST['max_visitors'] ?? 5)
            ];
            
            if (empty($id) || empty($data['name']) || empty($data['location'])) {
                echo json_encode(['success' => false, 'message' => 'ID, nama, dan lokasi wajib diisi']);
                exit;
            }
            
            $result = $bathroomModel->update($id, $data);
            echo json_encode($result);
            break;
            
        case 'toggle_status':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID kamar mandi diperlukan']);
                exit;
            }
            
            try {
                $db = Database::getInstance();
                $bathroom = $db->fetch("SELECT is_active FROM bathrooms WHERE id = ?", [$id]);
                if (!$bathroom) {
                    echo json_encode(['success' => false, 'message' => 'Kamar mandi tidak ditemukan']);
                    exit;
                }
                
                $newStatus = $bathroom['is_active'] ? 0 : 1;
                $db->execute("UPDATE bathrooms SET is_active = ? WHERE id = ?", [$newStatus, $id]);
                
                $message = $newStatus ? 'Kamar mandi diaktifkan' : 'Kamar mandi dinonaktifkan';
                echo json_encode(['success' => true, 'message' => $message]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Gagal mengubah status: ' . $e->getMessage()]);
            }
            break;
        case 'delete':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID kamar mandi diperlukan']);
                exit;
            }
            
            try {
                $db = Database::getInstance();
                // Check if bathroom has cleaning logs
                $logs = $db->fetch("SELECT COUNT(*) as count FROM cleaning_logs WHERE bathroom_id = ?", [$id]);
                
                if ($logs && $logs['count'] > 0) {
                    // Soft delete - deactivate instead
                    $db->execute("UPDATE bathrooms SET is_active = 0 WHERE id = ?", [$id]);
                    echo json_encode(['success' => true, 'message' => 'Kamar mandi dinonaktifkan (memiliki riwayat pembersihan)']);
                } else {
                    // Hard delete
                    $db->execute("DELETE FROM bathrooms WHERE id = ?", [$id]);
                    echo json_encode(['success' => true, 'message' => 'Kamar mandi berhasil dihapus']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus kamar mandi: ' . $e->getMessage()]);
            }
            break;
            
        case 'get':
            $id = $_GET['id'] ?? $_POST['id'] ?? '';
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID kamar mandi diperlukan']);
                exit;
            }
            
            try {
                $db = Database::getInstance();
                $bathroom = $db->fetch("SELECT * FROM bathrooms WHERE id = ?", [$id]);
                if ($bathroom) {
                    echo json_encode(['success' => true, 'data' => $bathroom]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Kamar mandi tidak ditemukan']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Gagal mengambil data kamar mandi']);
            }
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}

$bathrooms = $bathroomModel->getAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kamar Mandi - SANIPOINT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../views/layouts/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Manajemen Kamar Mandi</h1>
                <p class="text-gray-600">Kelola lokasi dan pengaturan kamar mandi</p>
            </div>
            <button onclick="openModal('add-bathroom-modal')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Tambah Kamar Mandi
            </button>
        </div>
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max Pengunjung</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($bathrooms as $bathroom): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($bathroom['name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($bathroom['location']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= $bathroom['max_visitors'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $bathroom['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $bathroom['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="editBathroom('<?= $bathroom['id'] ?>')" class="text-blue-600 hover:text-blue-900 p-1">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="toggleStatus('<?= $bathroom['id'] ?>')" class="text-yellow-600 hover:text-yellow-900 p-1">
                                        <i class="fas fa-toggle-<?= $bathroom['is_active'] ? 'on' : 'off' ?>"></i>
                                    </button>
                                    <button onclick="deleteBathroom('<?= $bathroom['id'] ?>')" class="text-red-600 hover:text-red-900 p-1">
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
    
    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-sm">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
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
    
    <!-- Edit Modal -->
    <div id="edit-bathroom-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Edit Kamar Mandi</h3>
            <form id="edit-bathroom-form" class="space-y-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="edit_bathroom_id" name="id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                    <input type="text" id="edit_location" name="location" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Pengunjung</label>
                    <input type="number" id="edit_max_visitors" name="max_visitors" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeModal('edit-bathroom-modal')" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add Modal -->
    <div id="add-bathroom-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Tambah Kamar Mandi</h3>
            <form id="add-bathroom-form" class="space-y-4">
                <input type="hidden" name="action" value="create">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                    <input type="text" name="location" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Pengunjung</label>
                    <input type="number" name="max_visitors" value="5" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeModal('add-bathroom-modal')" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let confirmCallback = null;
        
        function openModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
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
            
            toast.className = `fixed top-4 right-4 ${bgColor} text-white p-4 rounded-lg shadow-lg z-50 max-w-sm`;
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
                    toast.remove();
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
        
        // Add bathroom form
        document.getElementById('add-bathroom-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            showLoading(true);
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('kamar_mandi.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    closeModal('add-bathroom-modal');
                    this.reset();
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
        
        // Edit bathroom form
        document.getElementById('edit-bathroom-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            showLoading(true);
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('kamar_mandi.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    closeModal('edit-bathroom-modal');
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
        
        // Edit bathroom function
        async function editBathroom(id) {
            try {
                const response = await fetch(`kamar_mandi.php?action=get&id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    document.getElementById('edit_bathroom_id').value = data.id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_location').value = data.location;
                    document.getElementById('edit_max_visitors').value = data.max_visitors;
                    openModal('edit-bathroom-modal');
                } else {
                    showToast('Gagal memuat data kamar mandi', 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            }
        }
        
        // Toggle status function
        async function toggleStatus(id) {
            showConfirm(
                'Ubah Status Kamar Mandi',
                'Apakah Anda yakin ingin mengubah status kamar mandi ini?',
                async () => {
                    showLoading(true);
                    const formData = new FormData();
                    formData.append('action', 'toggle_status');
                    formData.append('id', id);
                    
                    try {
                        const response = await fetch('kamar_mandi.php', {
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
        
        // Delete bathroom function
        async function deleteBathroom(id) {
            showConfirm(
                'Hapus Kamar Mandi',
                'Apakah Anda yakin ingin menghapus kamar mandi ini? Jika memiliki riwayat pembersihan, kamar mandi akan dinonaktifkan.',
                async () => {
                    showLoading(true);
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);
                    
                    try {
                        const response = await fetch('kamar_mandi.php', {
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
        
        // Make functions global
        window.editBathroom = editBathroom;
        window.toggleStatus = toggleStatus;
        window.deleteBathroom = deleteBathroom;
    </script>
</div>
</body>
</html>