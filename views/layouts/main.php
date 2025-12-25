<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Security::generateCSRFToken() ?>">
    <title><?= $title ?? 'SANIPOINT' ?> - IoT Bathroom Monitoring</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="/sanipoint/assets/css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        // Early theme application - simplified to dark/light only
        (function() {
            const savedTheme = localStorage.getItem('theme');
            let theme;
            
            if (!savedTheme) {
                // Default to system preference on first visit
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
    </script>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
    <?php if (isset($show_nav) && $show_nav): ?>
        <div class="flex h-full">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="flex-1 flex flex-col" style="margin-left: var(--sidebar-width);">
                <!-- Header -->
                <?php include 'header.php'; ?>
                
                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto">
                    <div class="animate-fade-in">
                        <?= $content ?? '' ?>
                    </div>
                </main>
            </div>
        </div>
    <?php else: ?>
        <!-- Login Layout -->
        <main class="h-full">
            <?= $content ?? '' ?>
        </main>
    <?php endif; ?>
    
    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-2xl">
            <div class="flex items-center space-x-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="text-gray-700 dark:text-gray-300 font-medium">Loading...</p>
            </div>
        </div>
    </div>
    
    <script src="/sanipoint/assets/js/main.js"></script>
    <script src="/sanipoint/assets/js/theme.js"></script>
    <?= $scripts ?? '' ?>
</body>
</html>