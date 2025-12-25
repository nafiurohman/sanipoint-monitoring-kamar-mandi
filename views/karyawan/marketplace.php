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

    <!-- Category Filter -->
    <div class="mb-6">
        <div class="flex space-x-2 overflow-x-auto">
            <button class="category-filter active px-4 py-2 bg-blue-600 text-white rounded-lg whitespace-nowrap" data-category="all">
                Semua Produk
            </button>
            <?php foreach ($categories as $category): ?>
                <button class="category-filter px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 whitespace-nowrap" data-category="<?= $category['id'] ?>">
                    <?= htmlspecialchars($category['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($products as $product): ?>
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow product-card" data-category="<?= $product['category_id'] ?>">
                <div class="p-4">
                    <!-- Product Image Placeholder -->
                    <div class="w-full h-32 bg-gray-200 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-box text-gray-400 text-3xl"></i>
                    </div>
                    
                    <!-- Product Info -->
                    <h3 class="font-semibold text-gray-900 mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($product['description'] ?? '') ?></p>
                    
                    <!-- Category Badge -->
                    <span class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full mb-3">
                        <?= htmlspecialchars($product['category_name']) ?>
                    </span>
                    
                    <!-- Price and Stock -->
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <p class="text-lg font-bold text-blue-600"><?= number_format($product['price_points']) ?> pts</p>
                            <p class="text-sm text-gray-500">Stok: <?= number_format($product['stock']) ?></p>
                        </div>
                    </div>
                    
                    <!-- Add to Cart Button -->
                    <?php if ($product['stock'] > 0): ?>
                        <?php if ($user_points['current_balance'] >= $product['price_points']): ?>
                            <button class="add-to-cart w-full btn btn-primary" data-product-id="<?= $product['id'] ?>">
                                <i class="fas fa-cart-plus mr-2"></i>Tambah ke Keranjang
                            </button>
                        <?php else: ?>
                            <button class="w-full btn btn-secondary" disabled>
                                <i class="fas fa-coins mr-2"></i>Poin Tidak Cukup
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="w-full btn btn-secondary" disabled>
                            <i class="fas fa-times mr-2"></i>Stok Habis
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Shopping Cart (Fixed Bottom) -->
    <div id="cart-summary" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-lg hidden">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div>
                <p class="font-medium">Keranjang: <span id="cart-count">0</span> item</p>
                <p class="text-sm text-gray-600">Total: <span id="cart-total">0</span> poin</p>
            </div>
            <button id="checkout-btn" class="btn btn-primary">
                <i class="fas fa-shopping-cart mr-2"></i>Checkout
            </button>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div id="checkout-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Konfirmasi Pembelian</h3>
        <div id="checkout-items" class="space-y-2 mb-4">
            <!-- Cart items will be populated here -->
        </div>
        <div class="border-t pt-4">
            <div class="flex justify-between font-bold text-lg">
                <span>Total:</span>
                <span id="checkout-total">0 poin</span>
            </div>
        </div>
        <form class="ajax-form mt-6" action="/sanipoint/karyawan/marketplace" method="POST">
            <input type="hidden" name="action" value="checkout">
            <input type="hidden" name="cart_items" id="cart-items-input">
            <div class="flex justify-end space-x-3">
                <button type="button" class="modal-close btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">Konfirmasi Pembelian</button>
            </div>
        </form>
    </div>
</div>

<script>
// Shopping cart functionality
let cart = {};

document.addEventListener('DOMContentLoaded', function() {
    // Category filter
    document.querySelectorAll('.category-filter').forEach(btn => {
        btn.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Update active button
            document.querySelectorAll('.category-filter').forEach(b => {
                b.classList.remove('active', 'bg-blue-600', 'text-white');
                b.classList.add('bg-gray-200', 'text-gray-700');
            });
            this.classList.add('active', 'bg-blue-600', 'text-white');
            this.classList.remove('bg-gray-200', 'text-gray-700');
            
            // Filter products
            document.querySelectorAll('.product-card').forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Add to cart
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            if (cart[productId]) {
                cart[productId]++;
            } else {
                cart[productId] = 1;
            }
            updateCartSummary();
        });
    });

    // Checkout
    document.getElementById('checkout-btn').addEventListener('click', function() {
        showCheckoutModal();
    });
});

function updateCartSummary() {
    const cartCount = Object.values(cart).reduce((sum, qty) => sum + qty, 0);
    
    if (cartCount > 0) {
        document.getElementById('cart-summary').classList.remove('hidden');
        document.getElementById('cart-count').textContent = cartCount;
        // Calculate total points (simplified)
        document.getElementById('cart-total').textContent = cartCount * 5; // Placeholder calculation
    } else {
        document.getElementById('cart-summary').classList.add('hidden');
    }
}

function showCheckoutModal() {
    document.getElementById('cart-items-input').value = JSON.stringify(cart);
    document.getElementById('checkout-modal').classList.remove('hidden');
}
</script>

<?php
$content = ob_get_clean();
$title = 'Marketplace';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>