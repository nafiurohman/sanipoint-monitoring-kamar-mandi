<?php
ob_start();
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Marketplace</h1>
            <p class="text-gray-600">Tukar poin Anda dengan produk menarik</p>
        </div>
        <div class="bg-blue-100 px-4 py-2 rounded-lg">
            <p class="text-sm text-blue-600 font-medium">Saldo Poin: <?= number_format($user_points['current_balance']) ?></p>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($products as $product): ?>
            <div class="card">
                <div class="w-full h-32 bg-gray-200 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-box text-gray-400 text-3xl"></i>
                </div>
                
                <h3 class="font-semibold text-gray-900 mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($product['description'] ?? '') ?></p>
                
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <p class="text-lg font-bold text-blue-600"><?= number_format($product['price_points']) ?> pts</p>
                        <p class="text-sm text-gray-500">Stok: <?= number_format($product['stock']) ?></p>
                    </div>
                </div>
                
                <?php if ($product['stock'] > 0 && $user_points['current_balance'] >= $product['price_points']): ?>
                    <button onclick="addToCart('<?= $product['id'] ?>', '<?= htmlspecialchars($product['name']) ?>', <?= $product['price_points'] ?>)" class="w-full btn btn-primary">
                        <i class="fas fa-cart-plus mr-2"></i>Tambah ke Keranjang
                    </button>
                <?php else: ?>
                    <button class="w-full btn btn-secondary" disabled>
                        <?= $product['stock'] <= 0 ? 'Stok Habis' : 'Poin Tidak Cukup' ?>
                    </button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Cart Summary -->
    <div id="cart-summary" class="fixed bottom-0 left-0 right-0 bg-white border-t p-4 shadow-lg hidden">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div>
                <p class="font-medium">Keranjang: <span id="cart-count">0</span> item</p>
                <p class="text-sm text-gray-600">Total: <span id="cart-total">0</span> poin</p>
            </div>
            <button onclick="checkout()" class="btn btn-primary">
                <i class="fas fa-shopping-cart mr-2"></i>Checkout
            </button>
        </div>
    </div>
</div>

<!-- QR Modal -->
<div id="qr-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md text-center">
        <h3 class="text-lg font-semibold mb-4 text-green-600">Pembelian Berhasil!</h3>
        <div class="mb-4">
            <i class="fas fa-check-circle text-green-500 text-4xl mb-2"></i>
            <p class="text-gray-600">Order: <span id="order-number"></span></p>
        </div>
        <div class="bg-white p-4 border-2 border-dashed border-gray-300 rounded mb-4">
            <div id="qrcode"></div>
        </div>
        <p class="text-sm text-gray-600 mb-4">Tunjukkan QR code ini ke kasir</p>
        <button onclick="closeQR()" class="btn btn-primary">Tutup</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script>
<script>
let cart = {};

function addToCart(productId, productName, productPrice) {
    console.log('Adding to cart:', productId, productName, productPrice);
    
    if (cart[productId]) {
        cart[productId].quantity++;
    } else {
        cart[productId] = {
            name: productName,
            price: productPrice,
            quantity: 1
        };
    }
    
    updateCartSummary();
    alert('Produk ditambahkan ke keranjang!');
}

function updateCartSummary() {
    const cartCount = Object.values(cart).reduce((sum, item) => sum + item.quantity, 0);
    const cartTotal = Object.values(cart).reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    document.getElementById('cart-count').textContent = cartCount;
    document.getElementById('cart-total').textContent = cartTotal.toLocaleString();
    
    if (cartCount > 0) {
        document.getElementById('cart-summary').classList.remove('hidden');
    } else {
        document.getElementById('cart-summary').classList.add('hidden');
    }
}

function checkout() {
    if (Object.keys(cart).length === 0) {
        alert('Keranjang kosong!');
        return;
    }
    
    const cartData = Object.entries(cart).map(([productId, item]) => ({
        product_id: productId,
        quantity: item.quantity
    }));
    
    const formData = new FormData();
    formData.append('csrf_token', '<?= Security::generateCSRFToken() ?>');
    formData.append('action', 'checkout');
    formData.append('cart_items', JSON.stringify(cartData));
    
    fetch('/sanipoint/karyawan/marketplace', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showQR(data.qr_code, data.order_number);
            cart = {};
            updateCartSummary();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan');
    });
}

function showQR(qrData, orderNumber) {
    document.getElementById('order-number').textContent = orderNumber;
    
    const qrContainer = document.getElementById('qrcode');
    qrContainer.innerHTML = '';
    
    new QRCode(qrContainer, {
        text: qrData,
        width: 200,
        height: 200
    });
    
    document.getElementById('qr-modal').classList.remove('hidden');
}

function closeQR() {
    document.getElementById('qr-modal').classList.add('hidden');
    location.reload();
}
</script>

<?php
$content = ob_get_clean();
$title = 'Marketplace';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>