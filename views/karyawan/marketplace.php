<?php
ob_start();
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Marketplace</h1>
            <p class="text-gray-600 dark:text-gray-400">Tukar poin Anda dengan produk menarik</p>
        </div>
        <div class="bg-blue-100 dark:bg-blue-900/30 px-4 py-2 rounded-lg">
            <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Saldo Poin: <?= number_format($user_points['current_balance']) ?></p>
        </div>
    </div>

    <!-- Category Filter -->
    <div class="mb-6">
        <div class="flex space-x-2 overflow-x-auto">
            <button class="category-filter active px-4 py-2 bg-blue-600 text-white rounded-lg whitespace-nowrap" data-category="all">
                Semua Produk
            </button>
            <?php foreach ($categories as $category): ?>
                <button class="category-filter px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 whitespace-nowrap" data-category="<?= $category['id'] ?>">
                    <?= htmlspecialchars($category['name']) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php foreach ($products as $product): ?>
            <div class="card product-card" data-category="<?= $product['category_id'] ?>">
                <!-- Product Image Placeholder -->
                <div class="w-full h-32 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-box text-gray-400 dark:text-gray-500 text-3xl"></i>
                </div>
                
                <!-- Product Info -->
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2"><?= htmlspecialchars($product['name']) ?></h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3"><?= htmlspecialchars($product['description'] ?? '') ?></p>
                
                <!-- Category Badge -->
                <span class="inline-block px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full mb-3">
                    <?= htmlspecialchars($product['category_name']) ?>
                </span>
                
                <!-- Price and Stock -->
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <p class="text-lg font-bold text-blue-600 dark:text-blue-400"><?= number_format($product['price_points']) ?> pts</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Stok: <?= number_format($product['stock']) ?></p>
                    </div>
                </div>
                
                <!-- Add to Cart Button -->
                <?php if ($product['stock'] > 0): ?>
                    <?php if ($user_points['current_balance'] >= $product['price_points']): ?>
                        <button class="add-to-cart w-full btn btn-primary" 
                                data-product-id="<?= $product['id'] ?>"
                                data-product-name="<?= htmlspecialchars($product['name']) ?>"
                                data-product-price="<?= $product['price_points'] ?>">
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
        <?php endforeach; ?>
    </div>

    <!-- Shopping Cart (Fixed Bottom) -->
    <div id="cart-summary" class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4 shadow-lg hidden z-40">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div>
                <p class="font-medium text-gray-900 dark:text-white">Keranjang: <span id="cart-count">0</span> item</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total: <span id="cart-total">0</span> poin</p>
            </div>
            <div class="flex space-x-3">
                <button id="view-cart-btn" class="btn btn-secondary">
                    <i class="fas fa-eye mr-2"></i>Lihat Keranjang
                </button>
                <button id="checkout-btn" class="btn btn-primary">
                    <i class="fas fa-shopping-cart mr-2"></i>Checkout
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cart Modal -->
<div id="cart-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Keranjang Belanja</h3>
                <button class="modal-close text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <div id="cart-items" class="space-y-4 mb-6">
                <!-- Cart items will be populated here -->
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="flex justify-between font-bold text-lg text-gray-900 dark:text-white">
                    <span>Total:</span>
                    <span id="modal-cart-total">0 poin</span>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button class="modal-close btn btn-secondary">Tutup</button>
                <button id="modal-checkout-btn" class="btn btn-primary">Checkout</button>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div id="checkout-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-md">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Konfirmasi Pembelian</h3>
        </div>
        <div class="p-6">
            <div id="checkout-items" class="space-y-2 mb-4">
                <!-- Cart items will be populated here -->
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <div class="flex justify-between font-bold text-lg text-gray-900 dark:text-white">
                    <span>Total:</span>
                    <span id="checkout-total">0 poin</span>
                </div>
            </div>
            <form id="checkout-form" class="mt-6">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                <input type="hidden" name="action" value="checkout">
                <input type="hidden" name="cart_items" id="cart-items-input">
                <div class="flex justify-end space-x-3">
                    <button type="button" class="modal-close btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary">Konfirmasi Pembelian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg w-full max-w-md">
        <div class="p-6 text-center">
            <div class="mb-4">
                <i class="fas fa-check-circle text-green-500 text-6xl mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">✅ Pembelian Berhasil!</h3>
                <div class="bg-green-50 dark:bg-green-900/30 p-4 rounded-lg mb-4">
                    <p class="text-gray-700 dark:text-gray-300 mb-2">Nomor Order:</p>
                    <p id="success-order-number" class="font-mono font-bold text-lg text-green-600 dark:text-green-400 mb-3"></p>
                    <p class="text-gray-700 dark:text-gray-300 mb-1">Total Poin Terpakai:</p>
                    <p id="success-total" class="font-bold text-xl text-red-600 dark:text-red-400"></p>
                </div>
            </div>
            
            <div class="bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg mb-6">
                <p class="text-sm text-blue-700 dark:text-blue-400">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Langkah selanjutnya:</strong><br>
                    Tunjukkan nomor order ini ke admin untuk mengambil barang Anda.
                </p>
            </div>
            
            <button onclick="closeSuccessModal()" class="btn btn-primary w-full py-3 text-lg">
                <i class="fas fa-thumbs-up mr-2"></i>Oke, Mengerti!
            </button>
        </div>
    </div>
</div>


<script>
// Shopping cart functionality
let cart = {};

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing marketplace...');
    
    // Category filter functionality
    const categoryFilters = document.querySelectorAll('.category-filter');
    categoryFilters.forEach(btn => {
        btn.addEventListener('click', function() {
            const category = this.dataset.category;
            console.log('Category filter clicked:', category);
            
            // Update active button
            categoryFilters.forEach(b => {
                b.classList.remove('active', 'bg-blue-600', 'text-white');
                b.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
            });
            this.classList.add('active', 'bg-blue-600', 'text-white');
            this.classList.remove('bg-gray-200', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
            
            // Filter products
            const productCards = document.querySelectorAll('.product-card');
            productCards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // Add to cart functionality
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productPrice = parseInt(this.dataset.productPrice);
            
            console.log('Adding to cart:', { productId, productName, productPrice });
            
            if (cart[productId]) {
                cart[productId].quantity++;
            } else {
                cart[productId] = {
                    name: productName,
                    price: productPrice,
                    quantity: 1
                };
            }
            
            console.log('Updated cart:', cart);
            updateCartSummary();
            showAlert('Produk ditambahkan ke keranjang', 'success');
        });
    });

    // Cart button functionality
    const viewCartBtn = document.getElementById('view-cart-btn');
    const checkoutBtn = document.getElementById('checkout-btn');
    const modalCheckoutBtn = document.getElementById('modal-checkout-btn');
    
    if (viewCartBtn) viewCartBtn.addEventListener('click', showCartModal);
    if (checkoutBtn) checkoutBtn.addEventListener('click', showCheckoutModal);
    if (modalCheckoutBtn) modalCheckoutBtn.addEventListener('click', showCheckoutModal);

    // Modal close functionality
    const modalCloseButtons = document.querySelectorAll('.modal-close');
    modalCloseButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.classList.add('hidden');
            });
        });
    });

    // Checkout form functionality
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('=== CHECKOUT FORM SUBMIT ===');
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            
            fetch('/sanipoint/karyawan/marketplace', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                // Hide checkout modal first
                document.getElementById('checkout-modal').classList.add('hidden');
                
                if (data.success) {
                    console.log('✅ Checkout successful! Showing success modal...');
                    
                    // Clear cart
                    cart = {};
                    updateCartSummary();
                    
                    // Show success modal
                    showSuccessModal(data.order_number, data.total_points);
                    
                } else {
                    console.log('❌ Checkout failed:', data.message);
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('❌ Fetch error:', error);
                alert('Terjadi kesalahan saat checkout');
            })
            .finally(() => {
                // Restore button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});

function updateCartSummary() {
    const cartCount = Object.values(cart).reduce((sum, item) => sum + item.quantity, 0);
    const cartTotal = Object.values(cart).reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    const cartCountEl = document.getElementById('cart-count');
    const cartTotalEl = document.getElementById('cart-total');
    const cartSummaryEl = document.getElementById('cart-summary');
    
    if (cartCountEl) cartCountEl.textContent = cartCount;
    if (cartTotalEl) cartTotalEl.textContent = cartTotal.toLocaleString();
    
    if (cartSummaryEl) {
        if (cartCount > 0) {
            cartSummaryEl.classList.remove('hidden');
        } else {
            cartSummaryEl.classList.add('hidden');
        }
    }
}

function showCartModal() {
    const cartItemsContainer = document.getElementById('cart-items');
    const modalTotal = document.getElementById('modal-cart-total');
    
    if (!cartItemsContainer || !modalTotal) return;
    
    cartItemsContainer.innerHTML = '';
    let total = 0;
    
    if (Object.keys(cart).length === 0) {
        cartItemsContainer.innerHTML = '<p class="text-gray-500 text-center py-4">Keranjang kosong</p>';
    } else {
        Object.entries(cart).forEach(([productId, item]) => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            
            cartItemsContainer.innerHTML += `
                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">${item.name}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">${item.price.toLocaleString()} pts × ${item.quantity}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button onclick="updateCartQuantity('${productId}', -1)" class="w-8 h-8 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center">
                            <i class="fas fa-minus text-xs"></i>
                        </button>
                        <span class="w-8 text-center text-gray-900 dark:text-white">${item.quantity}</span>
                        <button onclick="updateCartQuantity('${productId}', 1)" class="w-8 h-8 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full flex items-center justify-center">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                        <button onclick="removeFromCart('${productId}')" class="w-8 h-8 bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-400 rounded-full flex items-center justify-center ml-2">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
            `;
        });
    }
    
    modalTotal.textContent = total.toLocaleString() + ' poin';
    document.getElementById('cart-modal').classList.remove('hidden');
}

function showCheckoutModal() {
    const checkoutItemsContainer = document.getElementById('checkout-items');
    const checkoutTotal = document.getElementById('checkout-total');
    
    if (!checkoutItemsContainer || !checkoutTotal) return;
    
    checkoutItemsContainer.innerHTML = '';
    let total = 0;
    
    Object.entries(cart).forEach(([productId, item]) => {
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        checkoutItemsContainer.innerHTML += `
            <div class="flex justify-between">
                <span class="text-gray-900 dark:text-white">${item.name} × ${item.quantity}</span>
                <span class="text-gray-900 dark:text-white">${itemTotal.toLocaleString()} pts</span>
            </div>
        `;
    });
    
    checkoutTotal.textContent = total.toLocaleString() + ' poin';
    
    const cartData = Object.entries(cart).map(([productId, item]) => ({
        product_id: productId,
        quantity: item.quantity
    }));
    
    const cartItemsInput = document.getElementById('cart-items-input');
    if (cartItemsInput) {
        cartItemsInput.value = JSON.stringify(cartData);
    }
    
    document.getElementById('cart-modal').classList.add('hidden');
    document.getElementById('checkout-modal').classList.remove('hidden');
}

function updateCartQuantity(productId, change) {
    if (cart[productId]) {
        cart[productId].quantity += change;
        if (cart[productId].quantity <= 0) {
            delete cart[productId];
        }
        updateCartSummary();
        showCartModal(); // Refresh modal
    }
}

function removeFromCart(productId) {
    delete cart[productId];
    updateCartSummary();
    showCartModal(); // Refresh modal
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white`;
    alertDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

function showSuccessModal(orderNumber, totalPoints) {
    console.log('✅ showSuccessModal called with:', { orderNumber, totalPoints });
    
    // Hide all other modals first
    const allModals = document.querySelectorAll('.modal');
    allModals.forEach(modal => {
        modal.classList.add('hidden');
    });
    
    // Update modal content
    const successOrderNumber = document.getElementById('success-order-number');
    const successTotal = document.getElementById('success-total');
    
    if (successOrderNumber) {
        successOrderNumber.textContent = orderNumber;
        console.log('✅ Order number set:', orderNumber);
    } else {
        console.error('❌ success-order-number element not found');
    }
    
    if (successTotal) {
        successTotal.textContent = totalPoints.toLocaleString() + ' poin';
        console.log('✅ Total points set:', totalPoints);
    } else {
        console.error('❌ success-total element not found');
    }
    
    // Show success modal
    const successModal = document.getElementById('success-modal');
    if (successModal) {
        successModal.classList.remove('hidden');
        console.log('✅ Success modal shown');
    } else {
        console.error('❌ success-modal element not found');
    }
}

function closeSuccessModal() {
    console.log('✅ closeSuccessModal called');
    
    const successModal = document.getElementById('success-modal');
    if (successModal) {
        successModal.classList.add('hidden');
        console.log('✅ Success modal hidden');
    }
    
    // Reload page after short delay
    setTimeout(() => {
        console.log('✅ Reloading page...');
        location.reload();
    }, 500);
}
</script>

<?php
$content = ob_get_clean();
$title = 'Marketplace';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>