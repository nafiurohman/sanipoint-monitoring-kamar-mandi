<?php
ob_start();
?>

<div class="p-8 lg:ml-80">
    <div class="max-w-6xl mx-auto space-y-10">
        <!-- Header -->
        <div class="card-large">
            <div class="flex items-center space-x-6">
                <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-600 rounded-3xl flex items-center justify-center shadow-xl">
                    <i class="fas fa-cog text-white text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Pengaturan</h1>
                    <p class="text-xl text-gray-600 dark:text-gray-400">Kelola profil dan preferensi akun Anda</p>
                </div>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-500/20 border border-green-500/30 text-green-700 dark:text-green-300 px-8 py-6 rounded-3xl backdrop-blur-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-4 text-xl"></i>
                    <span class="text-lg"><?= htmlspecialchars($message) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-500/20 border border-red-500/30 text-red-700 dark:text-red-300 px-8 py-6 rounded-3xl backdrop-blur-sm">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-4 text-xl"></i>
                    <span class="text-lg"><?= htmlspecialchars($error) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-2 gap-10">
            <!-- Profile Settings -->
            <div class="card-large">
                <div class="flex items-center space-x-4 mb-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-3xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user text-white text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Profil Saya</h2>
                </div>

                <form method="POST" class="space-y-8">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="space-y-3">
                        <label class="block text-lg font-semibold text-gray-700 dark:text-gray-300">Nama Lengkap</label>
                        <input 
                            type="text" 
                            name="full_name" 
                            value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                            class="w-full px-6 py-4 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-3xl text-gray-900 dark:text-white text-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
                            required
                        >
                    </div>

                    <div class="space-y-3">
                        <label class="block text-lg font-semibold text-gray-700 dark:text-gray-300">Email</label>
                        <input 
                            type="email" 
                            name="email" 
                            value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                            class="w-full px-6 py-4 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-3xl text-gray-900 dark:text-white text-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
                        >
                    </div>

                    <button type="submit" class="w-full btn-large btn-primary">
                        <i class="fas fa-save mr-3"></i>
                        Simpan Profil
                    </button>
                </form>
            </div>

            <!-- Account Info -->
            <div class="card-large">
                <div class="flex items-center space-x-4 mb-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-gray-500 to-gray-700 rounded-3xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-info-circle text-white text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Informasi Akun</h2>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-lg font-semibold text-gray-500 dark:text-gray-400 mb-2">Username</label>
                        <p class="text-xl text-gray-900 dark:text-white font-medium"><?= htmlspecialchars($user['username'] ?? '') ?></p>
                    </div>
                    <div>
                        <label class="block text-lg font-semibold text-gray-500 dark:text-gray-400 mb-2">Role</label>
                        <span class="inline-flex items-center px-6 py-3 rounded-2xl text-lg font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 capitalize">
                            <?= htmlspecialchars($user['role'] ?? '') ?>
                        </span>
                    </div>
                    <div>
                        <label class="block text-lg font-semibold text-gray-500 dark:text-gray-400 mb-2">Status</label>
                        <span class="inline-flex items-center px-6 py-3 rounded-2xl text-lg font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            <i class="fas fa-circle text-sm mr-3"></i>
                            Aktif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Pengaturan - SANIPOINT';
include __DIR__ . '/../layouts/main.php';
?>