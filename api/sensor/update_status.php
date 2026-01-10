<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $db = Database::getInstance();
    
    $toilet_id = (int)($_POST['toilet_id'] ?? 1);
    $count = (int)($_POST['count'] ?? $_POST['visitor_count'] ?? 0);
    $gas_level = (int)($_POST['gas_level'] ?? $_POST['gas'] ?? 0);
    $is_locked = (int)($_POST['is_locked'] ?? $_POST['servo_lock'] ?? 0);
    $timestamp = date('Y-m-d H:i:s');
    
    // Get bathroom_id from database based on toilet_id
    $bathroom = $db->fetch("SELECT id FROM bathrooms WHERE is_active = 1 ORDER BY id LIMIT 1 OFFSET ?", [$toilet_id - 1]);
    if (!$bathroom) {
        $bathroom = $db->fetch("SELECT id FROM bathrooms WHERE is_active = 1 ORDER BY id LIMIT 1");
        if (!$bathroom) {
            echo json_encode(['success' => false, 'message' => 'No active bathrooms found']);
            exit;
        }
    }
    $bathroom_id = $bathroom['id'];
    
    // Get bathroom info
    $bathroom = $db->fetch("SELECT max_visitors FROM bathrooms WHERE id = ?", [$bathroom_id]);
    $max_visitors = $bathroom['max_visitors'] ?? 5;
    
    // Insert sensor logs
    if ($gas_level > 0) {
        $gas_sensor = $db->fetch("SELECT id FROM sensors WHERE bathroom_id = ? AND sensor_type = 'mq135'", [$bathroom_id]);
        if ($gas_sensor) {
            $db->insert('sensor_logs', [
                'sensor_id' => $gas_sensor['id'],
                'value' => $gas_level,
                'unit' => 'ppm',
                'recorded_at' => $timestamp
            ]);
        }
    }
    
    if ($count >= 0) {
        $ir_sensor = $db->fetch("SELECT id FROM sensors WHERE bathroom_id = ? AND sensor_type = 'ir'", [$bathroom_id]);
        if ($ir_sensor) {
            $db->insert('sensor_logs', [
                'sensor_id' => $ir_sensor['id'],
                'value' => $count,
                'unit' => 'visitors',
                'recorded_at' => $timestamp
            ]);
        }
    }
    
    // Determine status and trigger
    $status = 'available';
    $trigger_reason = null;
    $needs_cleaning = false;
    
    // Gas level threshold (400 ppm)
    if ($gas_level > 400) {
        $status = 'needs_cleaning';
        $trigger_reason = 'gas_level';
        $needs_cleaning = true;
    }
    
    // Visitor count threshold
    if ($count >= $max_visitors) {
        $status = 'needs_cleaning';
        $trigger_reason = 'visitor_count';
        $needs_cleaning = true;
    }
    
    // Update bathroom status
    $db->execute(
        "UPDATE bathrooms SET current_visitors = ?, status = ?, updated_at = ? WHERE id = ?",
        [$count, $status, $timestamp, $bathroom_id]
    );
    
    // Log usage if cleaning needed
    if ($needs_cleaning) {
        $description = $trigger_reason == 'gas_level' ? 
            "Gas level high: {$gas_level} ppm" : 
            "Visitor limit reached: {$count}/{$max_visitors}";
            
        $db->insert('usage_logs', [
            'bathroom_id' => $bathroom_id,
            'uid_pengakses' => 'SENSOR',
            'keterangan' => $description,
            'action_type' => 'sensor_trigger',
            'waktu' => $timestamp
        ]);
    }
    
    // Hardware control signals
    $servo_lock = $needs_cleaning ? 1 : 0;
    $led_red = $needs_cleaning ? 1 : 0;
    $led_green = $status === 'available' ? 1 : 0;
    $led_yellow = 0; // Only during cleaning
    $buzzer = $needs_cleaning ? 1 : 0;
    
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully',
        'data' => [
            'bathroom_id' => $bathroom_id,
            'toilet_id' => $toilet_id,
            'status' => $status,
            'visitor_count' => $count,
            'gas_level' => $gas_level,
            'needs_cleaning' => $needs_cleaning,
            'trigger_reason' => $trigger_reason,
            'max_visitors' => $max_visitors,
            'hardware_control' => [
                'servo_lock' => $servo_lock,
                'led_red' => $led_red,
                'led_green' => $led_green,
                'led_yellow' => $led_yellow,
                'buzzer' => $buzzer
            ],
            'timestamp' => $timestamp
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Update status error: ' . $e->getMessage(), 3, __DIR__ . '/api-errors.log');
    echo json_encode([
        'success' => false, 
        'message' => 'Database error',
        'error' => $e->getMessage()
    ]);
}
?>