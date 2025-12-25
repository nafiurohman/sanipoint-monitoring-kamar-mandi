<?php
ob_start();
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Monitoring Sensor IoT</h1>
            <p class="text-gray-600">Real-time monitoring sensor dan perangkat IoT</p>
        </div>
        <button data-modal="add-sensor-modal" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>Tambah Sensor
        </button>
    </div>

    <!-- Sensor Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <?php foreach ($sensors as $sensor): ?>
            <div class="bg-white rounded-lg shadow p-6" data-sensor-id="<?= $sensor['id'] ?>">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                            <?php if ($sensor['sensor_type'] == 'mq135'): ?>
                                <i class="fas fa-wind text-blue-600"></i>
                            <?php elseif ($sensor['sensor_type'] == 'ir'): ?>
                                <i class="fas fa-eye text-green-600"></i>
                            <?php else: ?>
                                <i class="fas fa-id-card text-purple-600"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900"><?= strtoupper($sensor['sensor_type']) ?></h3>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($sensor['sensor_code']) ?></p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                        <?= $sensor['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                </div>
                
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Lokasi:</span>
                        <span class="font-medium"><?= htmlspecialchars($sensor['bathroom_name']) ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Nilai Terakhir:</span>
                        <span class="sensor-value font-medium">-</span>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2 mt-4 pt-4 border-t border-gray-200">
                    <button onclick="viewSensorHistory('<?= $sensor['id'] ?>')" class="text-blue-600 hover:text-blue-900 text-sm">
                        <i class="fas fa-chart-line mr-1"></i>History
                    </button>
                    <button onclick="editSensor('<?= $sensor['id'] ?>')" class="text-green-600 hover:text-green-900 text-sm">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Recent Sensor Data -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Data Sensor Terbaru</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sensor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($sensor_data as $data): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="text-sm font-medium text-gray-900"><?= strtoupper($data['sensor_type']) ?></div>
                                    <div class="text-sm text-gray-500 ml-2"><?= htmlspecialchars($data['sensor_code']) ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= htmlspecialchars($data['bathroom_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                <?= $data['value'] ?> <?= $data['unit'] ?? '' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('d/m/Y H:i:s', strtotime($data['recorded_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $status = 'normal';
                                if ($data['sensor_type'] == 'mq135' && $data['value'] > 400) $status = 'warning';
                                if ($data['sensor_type'] == 'ir' && $data['value'] > 10) $status = 'warning';
                                ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?= $status == 'warning' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                    <?= $status == 'warning' ? 'Perhatian' : 'Normal' ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Sensor Modal -->
<div id="add-sensor-modal" class="modal hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Tambah Sensor Baru</h3>
        <form class="ajax-form" action="/sanipoint/admin/sensor" method="POST" data-reload="true">
            <input type="hidden" name="action" value="create">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kamar Mandi</label>
                    <select name="bathroom_id" required class="input">
                        <option value="">Pilih Kamar Mandi</option>
                        <?php foreach ($bathrooms as $bathroom): ?>
                            <option value="<?= $bathroom['id'] ?>"><?= htmlspecialchars($bathroom['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipe Sensor</label>
                    <select name="sensor_type" required class="input">
                        <option value="">Pilih Tipe</option>
                        <option value="mq135">MQ-135 (Gas/Udara)</option>
                        <option value="ir">IR (Infrared)</option>
                        <option value="rfid">RFID Reader</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kode Sensor</label>
                    <input type="text" name="sensor_code" required class="input" placeholder="MQ135_001">
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
$title = 'Monitoring Sensor IoT';
$show_nav = true;
include __DIR__ . '/../layouts/main.php';
?>