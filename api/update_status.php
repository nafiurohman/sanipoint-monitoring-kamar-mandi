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
    
    $toilet_id = (int)($_POST['toilet_id'] ?? 0);
    $count = (int)($_POST['count'] ?? 0);
    $gas_level = (int)($_POST['gas_level'] ?? 0);
    $is_locked = (int)($_POST['is_locked'] ?? 0);
    
    if ($toilet_id < 1 || $toilet_id > 2) {
        throw new Exception('Invalid toilet_id. Must be 1 or 2');
    }
    
    // Update bathroom status
    $status = 'available';
    if ($count >= IOT_MAX_VISITORS || $gas_level > IOT_GAS_LIMIT || $is_locked) {
        $status = 'needs_cleaning';
    }
    
    // Check if bathroom exists, if not create it
    $bathroom = $db->fetch("SELECT * FROM bathrooms WHERE id = ?", [$toilet_id]);
    
    if (!$bathroom) {
        $db->insert('bathrooms', [
            'id' => $toilet_id,
            'name' => 'Toilet ' . $toilet_id,
            'location' => 'Minimarket Floor 1',
            'max_visitors' => IOT_MAX_VISITORS,
            'current_visitors' => $count,
            'status' => $status,
            'is_active' => 1
        ]);
    } else {
        $db->update('bathrooms', [
            'current_visitors' => $count,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$toilet_id]);
    }
    
    // Log sensor data
    $sensor_code = 'MQ135_T' . $toilet_id;
    $sensor = $db->fetch("SELECT * FROM sensors WHERE sensor_code = ?", [$sensor_code]);
    
    if (!$sensor) {
        $sensor_id = $db->insert('sensors', [
            'bathroom_id' => $toilet_id,
            'sensor_type' => 'mq135',
            'sensor_code' => $sensor_code,
            'is_active' => 1
        ]);
    } else {
        $sensor_id = $sensor['id'];
    }
    
    $db->insert('sensor_logs', [
        'sensor_id' => $sensor_id,
        'value' => $gas_level,
        'unit' => 'ppm'
    ]);
    
    $db->insert('visitor_counter', [
        'bathroom_id' => $toilet_id,
        'count_in' => $count,
        'current_occupancy' => $count
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully',
        'toilet_id' => $toilet_id,
        'status' => $status,
        'count' => $count,
        'gas_level' => $gas_level,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>