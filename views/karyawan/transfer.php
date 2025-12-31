<?php
ob_start();
?>

<div class="p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Transfer Poin</h1>
        <p class="text-gray-600 dark:text-gray-400">Kirim poin ke karyawan lain dengan keamanan PIN</p>
    </div>

    <?php if (isset($setup_pin) && $setup_pin): ?>
        <!-- PIN Setup Required -->
        <div class="card mb-8">
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                        <i class="fas fa-lock text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-300">PIN Keamanan Diperlukan</h3>
                        <p class="text-yellow-700 dark:text-yellow-400">Anda perlu membuat PIN 6 digit untuk menggunakan fitur transfer poin.</p>
                        <a href="/sanipoint/karyawan/pengaturan?setup_pin=1" class="inline-block mt-3 btn btn-primary">
                            <i class="fas fa-cog mr-2"></i>Buat PIN Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Transfer Form -->
        <div class="card">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Kirim Poin</h2>
            
            <!-- Current Balance -->
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <i class="fas fa-coins text-blue-600 dark:text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Saldo Poin Anda</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-300"><?= number_format($points['current_balance']) ?></p>
                    </div>
                </div>
            </div>

            <?php if (!isset($setup_pin) || !$setup_pin): ?>
            <form id="transfer-form" class="ajax-form space-y-4">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                <input type="hidden" name="action" value="transfer_points">
                
                <div class="input-group">
                    <label class="label">Karyawan Tujuan</label>
                    <select name="to_user_id" required class="input">
                        <option value="">Pilih karyawan...</option>
                        <?php foreach ($employees as $employee): ?>
                            <?php if ($employee['id'] != $user['id']): ?>
                                <option value="<?= $employee['id'] ?>">
                                    <?= htmlspecialchars($employee['full_name']) ?> (<?= htmlspecialchars($employee['employee_code']) ?>)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="input-group">
                    <label class="label">Jumlah Poin</label>
                    <input type="number" name="amount" required class="input" min="1" max="<?= $points['current_balance'] ?>" placeholder="0">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maksimal: <?= number_format($points['current_balance']) ?> poin</p>
                </div>
                
                <div class="input-group">
                    <label class="label">PIN Keamanan</label>
                    <input type="password" name="pin" required class="input" maxlength="6" placeholder="Masukkan PIN 6 digit">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">PIN diperlukan untuk keamanan transfer</p>
                </div>
                
                <div class="input-group">
                    <label class="label">Catatan (Opsional)</label>
                    <textarea name="description" class="input" rows="3" placeholder="Tulis catatan untuk transfer ini..."></textarea>
                </div>
                
                <button type="submit" class="w-full btn btn-primary">
                    <i class="fas fa-paper-plane mr-2"></i>Kirim Poin
                </button>
            </form>
            <?php else: ?>
            <div class="text-center py-8">
                <i class="fas fa-lock text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                <p class="text-gray-500 dark:text-gray-400">Buat PIN terlebih dahulu untuk menggunakan fitur transfer</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Transfer History -->
        <div class="card">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Riwayat Transfer</h2>
            
            <div class="space-y-4">
                <?php if (empty($transfer_history)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-exchange-alt text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                        <p class="text-gray-500 dark:text-gray-400">Belum ada riwayat transfer</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($transfer_history as $transfer): ?>
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center">
                                    <?php if ($transfer['transaction_type'] == 'transfer_out'): ?>
                                        <div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-arrow-up text-red-600 dark:text-red-400 text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">Kirim ke <?= htmlspecialchars($transfer['to_user_name'] ?? 'Unknown') ?></p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400"><?= date('d M Y H:i', strtotime($transfer['created_at'])) ?></p>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-arrow-down text-green-600 dark:text-green-400 text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">Terima dari <?= htmlspecialchars($transfer['from_user_name'] ?? 'Unknown') ?></p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400"><?= date('d M Y H:i', strtotime($transfer['created_at'])) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold <?= $transfer['transaction_type'] == 'transfer_out' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' ?>">
                                        <?= $transfer['transaction_type'] == 'transfer_out' ? '-' : '+' ?><?= number_format($transfer['amount']) ?>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if ($transfer['description']): ?>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded p-2 mt-2">
                                    <p class="text-sm text-gray-600 dark:text-gray-300"><?= htmlspecialchars($transfer['description']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Transfer Buttons -->
    <div class="mt-8 card">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Transfer Cepat</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button onclick="quickTransfer(10)" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-center transition-colors">
                <p class="font-medium text-gray-900 dark:text-white">10 Poin</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Transfer cepat</p>
            </button>
            <button onclick="quickTransfer(25)" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-center transition-colors">
                <p class="font-medium text-gray-900 dark:text-white">25 Poin</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Transfer cepat</p>
            </button>
            <button onclick="quickTransfer(50)" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-center transition-colors">
                <p class="font-medium text-gray-900 dark:text-white">50 Poin</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Transfer cepat</p>
            </button>
            <button onclick="quickTransfer(100)" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-center transition-colors">
                <p class="font-medium text-gray-900 dark:text-white">100 Poin</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Transfer cepat</p>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== TRANSFER PAGE LOADED ===');
    
    const form = document.getElementById('transfer-form');
    if (form) {
        console.log('Transfer form found:', form);
        console.log('Form classes:', form.className);
        
        // Add event listener to log form submission
        form.addEventListener('submit', function(e) {
            console.log('=== TRANSFER FORM SUBMIT EVENT ===');
            console.log('Event:', e);
            console.log('Form data before submission:');
            
            const formData = new FormData(this);
            for (let [key, value] of formData.entries()) {
                if (key.includes('pin') || key.includes('password')) {
                    console.log(key + ':', value ? 'PROVIDED (' + value.length + ' chars)' : 'EMPTY');
                } else {
                    console.log(key + ':', value);
                }
            }
            
            console.log('Form will be handled by main AJAX system');
        });
    } else {
        console.log('Transfer form NOT found');
    }
});

function quickTransfer(amount) {
    console.log('=== QUICK TRANSFER CLICKED ===');
    console.log('Amount:', amount);
    
    const maxBalance = <?= $points['current_balance'] ?>;
    console.log('Max balance:', maxBalance);
    
    if (amount > maxBalance) {
        console.log('Insufficient balance for transfer');
        if (window.sanipoint) {
            window.sanipoint.showToast('Poin tidak mencukupi untuk transfer ' + amount + ' poin', 'error');
        } else {
            alert('Poin tidak mencukupi untuk transfer ' + amount + ' poin');
        }
        return;
    }
    
    const amountInput = document.querySelector('input[name="amount"]');
    const selectInput = document.querySelector('select[name="to_user_id"]');
    
    console.log('Amount input found:', !!amountInput);
    console.log('Select input found:', !!selectInput);
    
    if (amountInput) {
        amountInput.value = amount;
        console.log('Amount set to:', amount);
    }
    if (selectInput) {
        selectInput.focus();
        console.log('Select input focused');
    }
}
</script>

<?php
$content = ob_get_clean();
$title = 'Transfer Poin';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>