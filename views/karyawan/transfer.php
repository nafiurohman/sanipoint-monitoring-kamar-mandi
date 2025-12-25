<?php
ob_start();
?>

<div class="p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Transfer Poin</h1>
        <p class="text-gray-600">Kirim poin ke karyawan lain</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Transfer Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Kirim Poin</h2>
            
            <!-- Current Balance -->
            <div class="bg-blue-50 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="fas fa-coins text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-600 font-medium">Saldo Poin Anda</p>
                        <p class="text-2xl font-bold text-blue-900"><?= number_format($points['current_balance']) ?></p>
                    </div>
                </div>
            </div>

            <form class="ajax-form" action="/sanipoint/karyawan/transfer" method="POST">
                <input type="hidden" name="action" value="transfer">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kode Karyawan Tujuan</label>
                        <input type="text" name="employee_code" required class="input" placeholder="Masukkan kode karyawan (contoh: EMP002)">
                        <p class="text-xs text-gray-500 mt-1">Masukkan kode karyawan yang akan menerima poin</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Poin</label>
                        <input type="number" name="amount" required class="input" min="1" max="<?= $points['current_balance'] ?>" placeholder="0">
                        <p class="text-xs text-gray-500 mt-1">Maksimal: <?= number_format($points['current_balance']) ?> poin</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                        <textarea name="description" class="input" rows="3" placeholder="Tulis catatan untuk transfer ini..."></textarea>
                    </div>
                </div>
                
                <button type="submit" class="w-full btn btn-primary mt-6">
                    <i class="fas fa-paper-plane mr-2"></i>Kirim Poin
                </button>
            </form>
        </div>

        <!-- Transfer History -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Riwayat Transfer</h2>
            
            <div class="space-y-4">
                <?php if (empty($transfer_history)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-exchange-alt text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">Belum ada riwayat transfer</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($transfer_history as $transfer): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center">
                                    <?php if ($transfer['transaction_type'] == 'transfer_out'): ?>
                                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-arrow-up text-red-600 text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">Kirim ke <?= htmlspecialchars($transfer['to_user_name'] ?? 'Unknown') ?></p>
                                            <p class="text-sm text-gray-500"><?= date('d M Y H:i', strtotime($transfer['created_at'])) ?></p>
                                        </div>
                                    <?php else: ?>
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-arrow-down text-green-600 text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">Terima dari <?= htmlspecialchars($transfer['from_user_name'] ?? 'Unknown') ?></p>
                                            <p class="text-sm text-gray-500"><?= date('d M Y H:i', strtotime($transfer['created_at'])) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold <?= $transfer['transaction_type'] == 'transfer_out' ? 'text-red-600' : 'text-green-600' ?>">
                                        <?= $transfer['transaction_type'] == 'transfer_out' ? '-' : '+' ?><?= number_format($transfer['amount']) ?>
                                    </p>
                                </div>
                            </div>
                            
                            <?php if ($transfer['description']): ?>
                                <div class="bg-gray-50 rounded p-2 mt-2">
                                    <p class="text-sm text-gray-600"><?= htmlspecialchars($transfer['description']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Transfer Buttons -->
    <div class="mt-8 bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Transfer Cepat</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button onclick="quickTransfer(10)" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <p class="font-medium text-gray-900">10 Poin</p>
                <p class="text-sm text-gray-500">Transfer cepat</p>
            </button>
            <button onclick="quickTransfer(25)" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <p class="font-medium text-gray-900">25 Poin</p>
                <p class="text-sm text-gray-500">Transfer cepat</p>
            </button>
            <button onclick="quickTransfer(50)" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <p class="font-medium text-gray-900">50 Poin</p>
                <p class="text-sm text-gray-500">Transfer cepat</p>
            </button>
            <button onclick="quickTransfer(100)" class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 text-center">
                <p class="font-medium text-gray-900">100 Poin</p>
                <p class="text-sm text-gray-500">Transfer cepat</p>
            </button>
        </div>
    </div>
</div>

<script>
function quickTransfer(amount) {
    const maxBalance = <?= $points['current_balance'] ?>;
    if (amount > maxBalance) {
        alert('Poin tidak mencukupi untuk transfer ' + amount + ' poin');
        return;
    }
    
    document.querySelector('input[name="amount"]').value = amount;
    document.querySelector('input[name="employee_code"]').focus();
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.ajax-form');
    const amountInput = document.querySelector('input[name="amount"]');
    const maxBalance = <?= $points['current_balance'] ?>;
    
    amountInput.addEventListener('input', function() {
        const value = parseInt(this.value);
        if (value > maxBalance) {
            this.setCustomValidity('Jumlah poin melebihi saldo Anda');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>

<?php
$content = ob_get_clean();
$title = 'Transfer Poin';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>