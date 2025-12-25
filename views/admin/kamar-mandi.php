<?php
ob_start();
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Kamar Mandi</h1>
            <p class="text-gray-600">Kelola lokasi dan pengaturan kamar mandi</p>
        </div>
        <button data-modal="add-bathroom-modal" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>Tambah Kamar Mandi
        </button>
    </div>

    <!-- Bathroom List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($bathrooms as $bathroom): ?>
            <div class="bg-white rounded-lg shadow p-6" data-bathroom-id="<?= $bathroom['id'] ?>">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($bathroom['name']) ?></h3>
                    <span class="bathroom-status status-<?= $bathroom['status'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $bathroom['status'])) ?>
                    </span>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-map-marker-alt w-4 mr-2"></i>
                        <?= htmlspecialchars($bathroom['location']) ?>
                    </div>
                    
                    <div class="flex items-center text-sm text-gray-600">
                        <i class="fas fa-users w-4 mr-2"></i>
                        Pengunjung: <span class="visitor-count ml-1"><?= $bathroom['current_visitors'] ?>/<?= $bathroom['max_visitors'] ?></span>
                    </div>
                    
                    <?php if ($bathroom['last_cleaned']): ?>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-clock w-4 mr-2"></i>
                            Terakhir dibersihkan: <?= date('d/m/Y H:i', strtotime($bathroom['last_cleaned'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex justify-end space-x-2 mt-4 pt-4 border-t border-gray-200">
                    <button onclick="editBathroom('<?= $bathroom['id'] ?>')" class="text-blue-600 hover:text-blue-900 text-sm">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button onclick="resetCounter('<?= $bathroom['id'] ?>')" class="text-green-600 hover:text-green-900 text-sm">
                        <i class="fas fa-redo mr-1"></i>Reset
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Bathroom Modal -->
<div id="add-bathroom-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Tambah Kamar Mandi Baru</h3>
        <form class="ajax-form" action="/sanipoint/admin/kamar-mandi" method="POST" data-reload="true">
            <input type="hidden" name="action" value="create">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Kamar Mandi</label>
                    <input type="text" name="name" required class="input" placeholder="Toilet Lantai 1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Lokasi</label>
                    <input type="text" name="location" required class="input" placeholder="Lantai 1, Dekat Kasir">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Maksimal Pengunjung</label>
                    <input type="number" name="max_visitors" required class="input" value="10" min="1">
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
$title = 'Manajemen Kamar Mandi';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>