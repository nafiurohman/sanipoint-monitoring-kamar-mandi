<?php
ob_start();
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Karyawan</h1>
            <p class="text-gray-600">Kelola data karyawan dan sistem poin</p>
        </div>
        <button data-modal="add-employee-modal" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>Tambah Karyawan
        </button>
    </div>

    <!-- Employee List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Daftar Karyawan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Karyawan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Poin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($employee['full_name']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($employee['username']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($employee['employee_code']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div><?= htmlspecialchars($employee['email'] ?? '-') ?></div>
                                <div><?= htmlspecialchars($employee['phone'] ?? '-') ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="font-medium"><?= number_format($employee['current_balance'] ?? 0) ?> pts</div>
                                <div class="text-xs text-gray-500">Total: <?= number_format($employee['total_earned'] ?? 0) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($employee['is_active']): ?>
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Aktif</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="editEmployee('<?= $employee['id'] ?>')" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                <button onclick="deleteEmployee('<?= $employee['id'] ?>')" class="text-red-600 hover:text-red-900">Hapus</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Employee Modal -->
<div id="add-employee-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Tambah Karyawan Baru</h3>
        <form class="ajax-form" action="/sanipoint/admin/karyawan" method="POST" data-reload="true">
            <input type="hidden" name="action" value="create">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" name="username" required class="input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" required class="input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                    <input type="text" name="full_name" required class="input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kode Karyawan</label>
                    <input type="text" name="employee_code" required class="input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" class="input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Telepon</label>
                    <input type="text" name="phone" class="input">
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
$title = 'Manajemen Karyawan';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>