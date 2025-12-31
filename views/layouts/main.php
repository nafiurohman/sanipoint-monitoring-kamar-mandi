<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Security::generateCSRFToken() ?>">
    <title><?= $title ?? 'SANIPOINT' ?> - IoT Bathroom Monitoring</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php 
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $cssPath = $basePath . '/assets/css/style.css';
    ?>
    <link href="<?= $cssPath ?>" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            let theme;
            
            if (!savedTheme) {
                const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                theme = systemDark ? 'dark' : 'light';
            } else {
                theme = savedTheme === 'dark' ? 'dark' : 'light';
            }
            
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
        
        window.basePath = '<?= $basePath ?>';
    </script>
    <style>
        .card-large {
            @apply bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-2xl border border-gray-200 dark:border-gray-700 transition-all duration-300 hover:shadow-3xl;
        }
        .btn-large {
            @apply px-8 py-4 rounded-2xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg;
        }
        .btn-primary {
            @apply bg-gradient-to-r from-blue-500 to-purple-600 text-white hover:from-blue-600 hover:to-purple-700;
        }
        .btn-secondary {
            @apply bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600;
        }
        .btn-success {
            @apply bg-gradient-to-r from-green-500 to-emerald-600 text-white hover:from-green-600 hover:to-emerald-700;
        }
        .btn-danger {
            @apply bg-gradient-to-r from-red-500 to-pink-600 text-white hover:from-red-600 hover:to-pink-700;
        }
        .main-content {
            padding: 2rem;
            min-height: 100vh;
            background: #f9fafb;
        }
        .dark .main-content {
            background: #111827;
        }
        @media (max-width: 1024px) {
            .main-content {
                padding: 1rem;
            }
        }
        @media (max-width: 640px) {
            .main-content {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <?php if (isset($show_nav) && $show_nav): ?>
        <?php include 'sidebar.php'; ?>
        <main class="main-content">
            <div class="animate-fade-in">
                <?= $content ?? '' ?>
            </div>
        </main>
    <?php else: ?>
        <main class="h-full">
            <?= $content ?? '' ?>
        </main>
    <?php endif; ?>
    
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
    
    <div id="confirmation-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-2xl max-w-md w-full mx-4">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-3xl bg-yellow-100 dark:bg-yellow-900/30 mb-6">
                    <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3" id="confirmation-title">Konfirmasi</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-8" id="confirmation-message">Apakah Anda yakin?</p>
                <div class="flex space-x-4">
                    <button id="confirmation-cancel" class="flex-1 btn-large btn-secondary">
                        Batal
                    </button>
                    <button id="confirmation-confirm" class="flex-1 btn-large btn-danger">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div id="loading-overlay" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-2xl">
            <div class="flex items-center space-x-4">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
                <p class="text-gray-700 dark:text-gray-300 font-medium text-lg">Loading...</p>
            </div>
        </div>
    </div>
    
    <?php 
    $jsBasePath = $basePath . '/assets/js';
    ?>
    <script src="<?= $jsBasePath ?>/notifications.js"></script>
    <script src="<?= $jsBasePath ?>/theme.js"></script>
    <script src="<?= $jsBasePath ?>/simple.js"></script>
    <script src="<?= $jsBasePath ?>/admin-crud.js"></script>
    <?= $scripts ?? '' ?>
</body>
</html>