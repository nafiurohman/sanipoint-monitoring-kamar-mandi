<?php
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';
require_once '../models/ProductModel.php';
require_once '../models/PointModel.php';
require_once '../models/OrderModel.php';

Auth::requireRole('karyawan');
$user = Auth::getUser();

$productModel = new ProductModel();
$pointModel = new PointModel();
$orderModel = new OrderModel();

// Handle purchase request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'buy_product') {
        $product_id = $_POST['product_id'] ?? '';
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        $result = $orderModel->createOrder($user['id'], $product_id, $quantity);
        echo json_encode($result);
        exit;
    }
}

$products = $productModel->getActiveProducts();
$user_points = $pointModel->getUserPoints($user['id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace - SANIPOINT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../views/layouts/karyawan_nav.php'; ?>
    
    <div class="p-6">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Marketplace</h1>
            <p class="text-gray-600">Tukar poin Anda dengan produk menarik</p>
        </div>
        
        <!-- Points Balance -->
        <div class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-2xl p-6 text-white mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Saldo Poin Anda</p>
                    <p class="text-4xl font-bold"><?= number_format($user_points['current_balance']) ?></p>
                </div>
                <div class="text-6xl opacity-20">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($products as $product): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($product['name']) ?></h3>
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                <?= htmlspecialchars($product['category'] ?? 'Produk') ?>
                            </span>
                        </div>
                        
                        <?php if ($product['description']): ?>
                            <p class="text-gray-600 text-sm mb-4"><?= htmlspecialchars($product['description']) ?></p>
                        <?php endif; ?>
                        
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-2xl font-bold text-purple-600">
                                <?= number_format($product['point_price'] ?? 0) ?> pts
                            </div>
                            <div class="text-sm text-gray-500">
                                Stok: <?= $product['stock'] ?>
                            </div>
                        </div>
                        
                        <?php if ($product['stock'] > 0): ?>
                            <?php if ($user_points['current_balance'] >= ($product['point_price'] ?? 0)): ?>
                                <button onclick="buyProduct('<?= $product['id'] ?>', '<?= htmlspecialchars($product['name']) ?>', <?= $product['point_price'] ?? 0 ?>)" 
                                        class="w-full bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 transition-colors">
                                    <i class="fas fa-shopping-cart mr-2"></i>Beli Sekarang
                                </button>
                            <?php else: ?>
                                <button disabled class="w-full bg-gray-300 text-gray-500 py-2 rounded-lg cursor-not-allowed">
                                    <i class="fas fa-coins mr-2"></i>Poin Tidak Cukup
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <button disabled class="w-full bg-red-300 text-red-700 py-2 rounded-lg cursor-not-allowed">
                                <i class="fas fa-times mr-2"></i>Stok Habis
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="text-center py-12">
                <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Belum Ada Produk</h3>
                <p class="text-gray-500">Produk akan segera tersedia</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Purchase Confirmation Modal -->
    <div id="purchase-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Konfirmasi Pembelian</h3>
            <div id="purchase-details" class="mb-4">
                <!-- Details will be filled by JavaScript -->
            </div>
            <div class="flex justify-end space-x-3">
                <button onclick="closeModal('purchase-modal')" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                    Batal
                </button>
                <button id="confirm-purchase" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                    Konfirmasi
                </button>
            </div>
        </div>
    </div>
    
    <script>
        let currentProduct = null;
        
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 p-4 rounded-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }
        
        function buyProduct(productId, productName, price) {
            currentProduct = { id: productId, name: productName, price: price };
            
            document.getElementById('purchase-details').innerHTML = `
                <div class="text-center">
                    <p class="text-gray-700 mb-2">Anda akan membeli:</p>
                    <p class="font-semibold text-lg">${productName}</p>
                    <p class="text-purple-600 font-bold text-xl">${price.toLocaleString()} pts</p>
                </div>
            `;
            
            document.getElementById('purchase-modal').classList.remove('hidden');
        }
        
        document.getElementById('confirm-purchase').addEventListener('click', async function() {
            if (!currentProduct) return;
            
            const formData = new FormData();
            formData.append('action', 'buy_product');
            formData.append('product_id', currentProduct.id);
            formData.append('quantity', 1);
            
            try {
                const response = await fetch('marketplace.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Pembelian berhasil!', 'success');
                    closeModal('purchase-modal');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            }
        });
    </script>
</div>
</body>
</html>