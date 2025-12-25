<?php
ob_start();
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Produk</h1>
            <p class="text-gray-600">Kelola produk marketplace untuk penukaran poin</p>
        </div>
        <button data-modal="add-product-modal" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>Tambah Produk
        </button>
    </div>

    <!-- Product List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Produk</h2>
        </div>
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
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-box text-gray-500"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($product['name']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($product['description'] ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($product['category_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                <?= number_format($product['price_points']) ?> pts
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="<?= $product['stock'] <= 10 ? 'text-red-600 font-medium' : '' ?>">
                                    <?= number_format($product['stock']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($product['is_active']): ?>
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Aktif</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="editProduct('<?= $product['id'] ?>')" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                <button onclick="toggleProduct('<?= $product['id'] ?>')" class="text-yellow-600 hover:text-yellow-900">Toggle</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div id="add-product-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Tambah Produk Baru</h3>
        <form class="ajax-form" action="/sanipoint/admin/produk" method="POST" data-reload="true">
            <input type="hidden" name="action" value="create">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Produk</label>
                    <input type="text" name="name" required class="input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kategori</label>
                    <select name="category_id" required class="input">
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="description" class="input" rows="3"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Harga Poin</label>
                    <input type="number" name="price_points" required class="input" min="1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Stok</label>
                    <input type="number" name="stock" required class="input" min="0">
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" class="modal-close btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Manajemen Produk';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>