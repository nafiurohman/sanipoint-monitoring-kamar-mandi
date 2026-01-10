<?php include '../views/layouts/sidebar.php'; ?>

<div class="main-content">
    <!-- Mobile header for sidebar toggle -->
    <div class="lg:hidden mb-4">
        <button onclick="document.getElementById('sidebar').classList.toggle('mobile-open')" 
                class="p-2 rounded-md bg-white shadow-sm border">
            <i class="fas fa-bars"></i>
        </button>
    </div>