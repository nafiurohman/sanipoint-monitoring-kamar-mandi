<?php
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';
require_once '../models/ProductModel.php';

Auth::requireRole('admin');

$productModel = new ProductModel();

// Handle AJAX requests
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
                'description' => trim($_POST['description'] ?? ''),
                'category' => trim($_POST['category'] ?? ''),
                'point_price' => (int)($_POST['point_price'] ?? 0),
                'stock' => (int)($_POST['stock'] ?? 0),
                'image_url' => trim($_POST['image_url'] ?? '')
            ];
            $result = $productModel->create($data);
            echo json_encode($result);
            break;
        case 'update':
            $id = $_POST['id'] ?? '';
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'category' => trim($_POST['category'] ?? ''),
                'point_price' => (int)($_POST['point_price'] ?? 0),
                'stock' => (int)($_POST['stock'] ?? 0),
                'image_url' => trim($_POST['image_url'] ?? '')
            ];
            $result = $productModel->update($id, $data);
            echo json_encode($result);
            break;
        case 'delete':
            $id = $_POST['id'] ?? '';
            $result = $productModel->delete($id);
            echo json_encode($result);
            break;
        case 'toggle_status':
            $id = $_POST['id'] ?? '';
            $result = $productModel->toggleStatus($id);
            echo json_encode($result);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}

// Handle GET requests for fetching product data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit;
    }
    
    try {
        $product = $productModel->getById($id);
        if ($product) {
            echo json_encode(['success' => true, 'data' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

// Get all products with categories
try {
    $db = Database::getInstance();
    $products = $db->fetchAll("
        SELECT 
            p.*,
            pc.name as category
        FROM products p
        LEFT JOIN product_categories pc ON p.category_id = pc.id
        ORDER BY p.created_at DESC
    ");
} catch (Exception $e) {
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - SANIPOINT</title>
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
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Manajemen Produk</h1>
                <p class="text-gray-600 text-sm lg:text-base">Kelola produk marketplace untuk penukaran poin</p>
            </div>
            <button onclick="openModal('add-product-modal')" class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Tambah Produk
            </button>
        </div>
        
        <!-- Mobile Card View -->
        <div class="block lg:hidden space-y-4">
            <?php foreach ($products as $product): ?>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                <i class="fas fa-box text-gray-500"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900"><?= htmlspecialchars($product['name']) ?></h3>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($product['category'] ?? 'Tidak ada kategori') ?></p>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full <?= $product['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= $product['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                        <div>
                            <span class="text-gray-500">Harga:</span>
                            <span class="font-medium"><?= number_format($product['price_points'] ?? 0) ?> pts</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Stok:</span>
                            <span class="font-medium <?= $product['stock'] <= 10 ? 'text-red-600' : '' ?>"><?= $product['stock'] ?></span>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="editProduct('<?= $product['id'] ?>')" class="flex-1 bg-blue-50 text-blue-600 px-3 py-2 rounded text-sm hover:bg-blue-100 transition-colors">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </button>
                        <button onclick="toggleStatus('<?= $product['id'] ?>')" class="flex-1 bg-yellow-50 text-yellow-600 px-3 py-2 rounded text-sm hover:bg-yellow-100 transition-colors">
                            <i class="fas fa-toggle-<?= $product['is_active'] ? 'on' : 'off' ?> mr-1"></i>Toggle
                        </button>
                        <button onclick="deleteProduct('<?= $product['id'] ?>')" class="bg-red-50 text-red-600 px-3 py-2 rounded text-sm hover:bg-red-100 transition-colors">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Desktop Table View -->
        <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Poin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($products as $product): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-gray-500"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($product['name']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($product['description'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($product['category'] ?? 'Tidak ada kategori') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    <?= number_format($product['price_points'] ?? 0) ?> pts
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="<?= $product['stock'] <= 10 ? 'text-red-600 font-medium' : '' ?>">
                                        <?= $product['stock'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?= $product['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $product['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="editProduct('<?= $product['id'] ?>')" class="text-blue-600 hover:text-blue-900 p-1">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="toggleStatus('<?= $product['id'] ?>')" class="text-yellow-600 hover:text-yellow-900 p-1">
                                            <i class="fas fa-toggle-<?= $product['is_active'] ? 'on' : 'off' ?>"></i>
                                        </button>
                                        <button onclick="deleteProduct('<?= $product['id'] ?>')" class="text-red-600 hover:text-red-900 p-1">
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
    </div>
    
    <!-- Product Detail Modal -->
    <div id="product-detail-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Detail Produk</h3>
                <button onclick="closeModal('product-detail-modal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="text-center mb-4">
                    <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-box text-gray-500 text-2xl"></i>
                    </div>
                    <h4 id="detail-name" class="text-xl font-semibold text-gray-900"></h4>
                </div>
                
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Kategori:</span>
                        <p id="detail-category" class="font-medium"></p>
                    </div>
                    <div>
                        <span class="text-gray-500">Harga:</span>
                        <p id="detail-price" class="font-medium text-blue-600"></p>
                    </div>
                    <div>
                        <span class="text-gray-500">Stok:</span>
                        <p id="detail-stock" class="font-medium"></p>
                    </div>
                    <div>
                        <span class="text-gray-500">Status:</span>
                        <p id="detail-status" class="font-medium"></p>
                    </div>
                </div>
                
                <div>
                    <span class="text-gray-500 text-sm">Deskripsi:</span>
                    <p id="detail-description" class="text-gray-700 mt-1"></p>
                </div>
                
                <div class="flex space-x-3 pt-4">
                    <button onclick="closeModal('product-detail-modal')" class="flex-1 px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                        Tutup
                    </button>
                    <button id="edit-from-detail" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Product Modal -->
    <div id="add-product-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-semibold mb-4">Tambah Produk Baru</h3>
            <form id="add-product-form" class="space-y-4">
                <input type="hidden" name="action" value="create">
                <input type="hidden" id="product_id" name="id" value="">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Produk</label>
                    <input type="text" id="product_name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea id="product_description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kategori</label>
                    <select id="product_category" name="category" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih kategori...</option>
                        <option value="Makanan">Makanan</option>
                        <option value="Minuman">Minuman</option>
                        <option value="Elektronik">Elektronik</option>
                        <option value="Voucher">Voucher</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Harga Poin</label>
                    <input type="number" id="product_point_price" name="point_price" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Stok</label>
                    <input type="number" id="product_stock" name="stock" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">URL Gambar (Opsional)</label>
                    <input type="url" id="product_image_url" name="image_url" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4">
                    <button type="button" onclick="closeModal('add-product-modal')" class="w-full sm:w-auto px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Custom Confirmation Modal -->
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
            if (modalId === 'add-product-modal') {
                // Reset form for new product
                document.getElementById('add-product-form').reset();
                document.getElementById('product_id').value = '';
                document.querySelector('#add-product-modal h3').textContent = 'Tambah Produk Baru';
                document.querySelector('#add-product-form input[name="action"]').value = 'create';
            }
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
        
        document.getElementById('add-product-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            showLoading(true);
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('../api/web/product_api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    closeModal('add-product-modal');
                    this.reset();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message || 'Gagal menyimpan produk', 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            } finally {
                showLoading(false);
            }
        });
        
        async function toggleStatus(id) {
            showConfirm(
                'Ubah Status Produk',
                'Apakah Anda yakin ingin mengubah status produk ini?',
                async () => {
                    showLoading(true);
                    const formData = new FormData();
                    formData.append('action', 'toggle_status');
                    formData.append('id', id);
                    
                    try {
                        const response = await fetch('../api/web/product_api.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            showToast(result.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast(result.message || 'Gagal mengubah status produk', 'error');
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
        
        async function deleteProduct(id) {
            showConfirm(
                'Hapus Produk',
                'Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan.',
                async () => {
                    showLoading(true);
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);
                    
                    try {
                        const response = await fetch('../api/web/product_api.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            showToast(result.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast(result.message || 'Gagal menghapus produk', 'error');
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
        
        async function editProduct(id) {
            try {
                const response = await fetch(`../api/web/product_api.php?action=get&id=${id}`);
                const product = await response.json();
                
                if (product.success) {
                    const data = product.data;
                    
                    // Show product details first
                    document.getElementById('detail-name').textContent = data.name;
                    document.getElementById('detail-category').textContent = data.category || 'Tidak ada kategori';
                    document.getElementById('detail-price').textContent = `${(data.price_points || 0).toLocaleString()} pts`;
                    document.getElementById('detail-stock').textContent = data.stock;
                    document.getElementById('detail-status').textContent = data.is_active ? 'Aktif' : 'Nonaktif';
                    document.getElementById('detail-status').className = `font-medium ${data.is_active ? 'text-green-600' : 'text-red-600'}`;
                    document.getElementById('detail-description').textContent = data.description || 'Tidak ada deskripsi';
                    
                    // Set up edit button
                    document.getElementById('edit-from-detail').onclick = function() {
                        closeModal('product-detail-modal');
                        
                        // Populate edit form with original data
                        document.getElementById('product_id').value = data.id;
                        document.getElementById('product_name').value = data.name;
                        document.getElementById('product_description').value = data.description || '';
                        document.getElementById('product_category').value = data.category || '';
                        document.getElementById('product_point_price').value = data.price_points || 0;
                        document.getElementById('product_stock').value = data.stock;
                        document.getElementById('product_image_url').value = data.image_url || '';
                        
                        // Show original data as placeholders
                        document.getElementById('product_name').placeholder = `Saat ini: ${data.name}`;
                        document.getElementById('product_description').placeholder = `Saat ini: ${data.description || 'Tidak ada deskripsi'}`;
                        document.getElementById('product_point_price').placeholder = `Saat ini: ${data.price_points || 0}`;
                        document.getElementById('product_stock').placeholder = `Saat ini: ${data.stock}`;
                        
                        document.querySelector('#add-product-modal h3').textContent = `Edit Produk: ${data.name}`;
                        document.querySelector('#add-product-form input[name="action"]').value = 'update';
                        openModal('add-product-modal');
                    };
                    
                    openModal('product-detail-modal');
                } else {
                    showToast('Gagal memuat data produk', 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            }
        }
        
        // Close modals when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
                const modals = ['add-product-modal', 'confirm-modal', 'product-detail-modal'];
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
                const modals = ['add-product-modal', 'confirm-modal', 'product-detail-modal'];
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