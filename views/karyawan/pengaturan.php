<?php
ob_start();
?>

<div class="p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Profil & Keamanan</h1>
        <p class="text-gray-600 dark:text-gray-400">Kelola profil dan pengaturan keamanan akun Anda</p>
    </div>

    <?php if ($setup_pin): ?>
        <!-- PIN Setup Alert -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
            <div class="flex">
                <i class="fas fa-exclamation-triangle text-yellow-400 mr-3 mt-1"></i>
                <div>
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">PIN Diperlukan</h3>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                        Anda perlu membuat PIN 6 digit untuk menggunakan fitur transfer poin.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Profile Settings -->
        <div class="card">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Informasi Profil</h2>
            
            <form id="profile-form" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="input-group">
                    <label class="label">Nama Lengkap</label>
                    <input type="text" name="full_name" class="input" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
                
                <div class="input-group">
                    <label class="label">Kode Karyawan</label>
                    <input type="text" class="input bg-gray-100 dark:bg-gray-700" value="<?= htmlspecialchars($user['employee_code']) ?>" readonly>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kode karyawan tidak dapat diubah</p>
                </div>
                
                <div class="input-group">
                    <label class="label">Email</label>
                    <input type="email" name="email" class="input" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                </div>
                
                <div class="input-group">
                    <label class="label">Nomor Telepon</label>
                    <input type="tel" name="phone" class="input" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Profil
                </button>
            </form>
        </div>

        <!-- Security Settings -->
        <div class="space-y-6">
            <!-- PIN Settings -->
            <div class="card">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Pengaturan PIN</h2>
                
                <?php if (empty($user['pin'])): ?>
                    <!-- Create PIN -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-4">
                        <div class="flex">
                            <i class="fas fa-info-circle text-blue-400 mr-3 mt-1"></i>
                            <div>
                                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">Buat PIN Keamanan</h3>
                                <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                    PIN 6 digit diperlukan untuk transfer poin dan transaksi penting lainnya.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <form id="create-pin-form" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                        <input type="hidden" name="action" value="create_pin">
                        
                        <div class="input-group">
                            <label class="label">Password Saat Ini</label>
                            <input type="password" name="current_password" class="input" required>
                        </div>
                        
                        <div class="input-group">
                            <label class="label">PIN Baru (6 digit)</label>
                            <input type="password" name="new_pin" class="input" maxlength="6" pattern="\d{6}" required>
                        </div>
                        
                        <div class="input-group">
                            <label class="label">Konfirmasi PIN</label>
                            <input type="password" name="confirm_pin" class="input" maxlength="6" pattern="\d{6}" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Buat PIN
                        </button>
                    </form>
                <?php else: ?>
                    <!-- Change PIN -->
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-4">
                        <div class="flex">
                            <i class="fas fa-check-circle text-green-400 mr-3 mt-1"></i>
                            <div>
                                <h3 class="text-sm font-medium text-green-800 dark:text-green-200">PIN Aktif</h3>
                                <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                                    PIN dibuat pada <?= date('d/m/Y H:i', strtotime($user['pin_created_at'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <form id="change-pin-form" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                        <input type="hidden" name="action" value="change_pin">
                        
                        <div class="input-group">
                            <label class="label">Password Saat Ini</label>
                            <input type="password" name="current_password" class="input" required>
                        </div>
                        
                        <div class="input-group">
                            <label class="label">PIN Baru (6 digit)</label>
                            <input type="password" name="new_pin" class="input" maxlength="6" pattern="\d{6}" required>
                        </div>
                        
                        <div class="input-group">
                            <label class="label">Konfirmasi PIN Baru</label>
                            <input type="password" name="confirm_pin" class="input" maxlength="6" pattern="\d{6}" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key mr-2"></i>
                            Ubah PIN
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Password Settings -->
            <div class="card">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Ubah Password</h2>
                
                <form id="change-password-form" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="input-group">
                        <label class="label">Password Lama</label>
                        <input type="password" name="current_password" class="input" required>
                    </div>
                    
                    <div class="input-group">
                        <label class="label">Password Baru</label>
                        <input type="password" name="new_password" class="input" minlength="6" required>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Minimal 6 karakter</p>
                    </div>
                    
                    <div class="input-group">
                        <label class="label">Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" class="input" minlength="6" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-lock mr-2"></i>
                        Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile form
    document.getElementById('profile-form').addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, 'Profil berhasil diperbarui');
    });
    
    // PIN forms
    const createPinForm = document.getElementById('create-pin-form');
    if (createPinForm) {
        createPinForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, 'PIN berhasil dibuat');
        });
    }
    
    const changePinForm = document.getElementById('change-pin-form');
    if (changePinForm) {
        changePinForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this, 'PIN berhasil diubah');
        });
    }
    
    // Password form
    document.getElementById('change-password-form').addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this, 'Password berhasil diubah');
    });
    
    function submitForm(form, successMessage) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
        
        fetch('/sanipoint/karyawan/pengaturan', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(successMessage, 'success');
                if (form.id.includes('pin') || form.id.includes('password')) {
                    form.reset();
                }
                if (form.id.includes('create-pin')) {
                    setTimeout(() => location.reload(), 1500);
                }
            } else {
                showAlert(data.message || 'Terjadi kesalahan', 'error');
            }
        })
        .catch(error => {
            showAlert('Terjadi kesalahan jaringan', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
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
});
</script>

<?php
$content = ob_get_clean();
$title = 'Profil & Keamanan';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>