<?php
ob_start();
?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto h-20 w-20 bg-blue-600 rounded-full flex items-center justify-center">
                <i class="fas fa-toilet text-white text-3xl"></i>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">SANIPOINT</h2>
            <p class="mt-2 text-sm text-gray-600">IoT Bathroom Monitoring System</p>
            <?php if (isset($error)): ?>
                <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
        </div>
        
        <form class="mt-8 space-y-6" action="/sanipoint/login" method="POST">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
            <div class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input id="username" name="username" type="text" required 
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                           placeholder="Masukkan username">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" required 
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                           placeholder="Masukkan password">
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-sign-in-alt text-blue-500 group-hover:text-blue-400"></i>
                    </span>
                    Masuk
                </button>
            </div>
        </form>
        
        <div class="text-center">
            <div class="text-sm text-gray-600">
                <p class="font-medium">Demo Accounts:</p>
                <p class="mt-1">Admin: <code class="bg-gray-100 px-2 py-1 rounded">admin / password</code></p>
                <p class="text-xs text-gray-500 mt-2">Sistem ini khusus untuk karyawan yang terdaftar</p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Login';
include __DIR__ . '/../layouts/main.php';
?>