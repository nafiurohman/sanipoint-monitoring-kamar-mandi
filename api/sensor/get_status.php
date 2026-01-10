<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

try {
    $db = Database::getInstance();
    
    $bathroom_id = $_GET['toilet_id'] ?? $_POST['toilet_id'] ?? $_GET['bathroom_id'] ?? $_POST['bathroom_id'] ?? '';
    $rfid_code = $_GET['rfid_code'] ?? $_POST['rfid_code'] ?? '';
    
    // RFID validation endpoint
    if (!empty($rfid_code)) {
        $result = $db->fetch("
            SELECT 
                rc.uid as rfid_code,
                rc.status,
                u.id as user_id,
                u.full_name as name,
                u.role,
                u.is_active,
                COALESCE(p.current_balance, 0) as points
            FROM rfid_cards rc
            LEFT JOIN users u ON rc.user_id = u.id
            LEFT JOIN points p ON u.id = p.user_id
            WHERE rc.uid = ?
        ", [$rfid_code]);
        
        if (!$result || $result['status'] !== 'Aktif' || !$result['is_active']) {
            echo json_encode(['valid' => false, 'message' => 'RFID invalid or inactive']);
            exit;
        }
        
        echo json_encode([
            'valid' => true,
            'user_id' => $result['user_id'],
            'name' => $result['name'],
            'role' => $result['role'],
            'points' => $result['points']
        ]);
        exit;
    }
    
    // All bathrooms status
    if (empty($bathroom_id)) {
        $bathrooms = $db->fetchAll("
            SELECT 
                id,
                name,
                location,
                status,
                current_visitors,
                max_visitors,
                last_cleaned,
                last_cleaned_by,
                updated_at,
                CASE 
                    WHEN status = 'needs_cleaning' OR current_visitors >= max_visitors THEN 1
                    ELSE 0
                END as needs_cleaning,
                CASE 
                    WHEN status = 'being_cleaned' THEN 1
                    ELSE 0
                END as is_being_cleaned
            FROM bathrooms 
            WHERE is_active = 1
            ORDER BY id
        ");
        
        echo json_encode([
            'success' => true,
            'message' => 'All bathrooms status',
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $bathrooms
        ]);
        exit;
    }
    
    // Get bathroom_id from database based on toilet_id
    $bathroom = $db->fetch("SELECT id FROM bathrooms WHERE is_active = 1 ORDER BY id LIMIT 1 OFFSET ?", [$toilet_id - 1]);
    if (!$bathroom) {
        $bathroom = $db->fetch("SELECT id FROM bathrooms WHERE is_active = 1 ORDER BY id LIMIT 1");
        if (!$bathroom) {
            echo json_encode(['success' => false, 'message' => 'No active bathrooms found']);
            exit;
        }
    }
    $mapped_bathroom_id = $bathroom['id'];
    
    // Specific bathroom status
    $bathroom = $db->fetch("
        SELECT 
            id,
            name,
            location,
            status,
            current_visitors,
            max_visitors,
            last_cleaned,
            last_cleaned_by,
            updated_at,
            CASE 
                WHEN status = 'needs_cleaning' OR current_visitors >= max_visitors THEN 1
                ELSE 0
            END as needs_cleaning,
            CASE 
                WHEN status = 'being_cleaned' THEN 1
                ELSE 0
            END as is_being_cleaned
        FROM bathrooms 
        WHERE id = ? AND is_active = 1
    ", [$mapped_bathroom_id]);
    
    if (!$bathroom) {
        echo json_encode([
            'success' => false,
            'message' => 'Bathroom not found',
            'toilet_id' => $bathroom_id
        ]);
        exit;
    }
    
    // Current cleaning activity
    $current_cleaning = $db->fetch("
        SELECT 
            cl.*,
            u.full_name as employee_name,
            TIMESTAMPDIFF(SECOND, cl.start_time, NOW()) as duration_seconds
        FROM cleaning_logs cl
        JOIN users u ON cl.user_id = u.id
        WHERE cl.bathroom_id = ? AND cl.status = 'in_progress'
        ORDER BY cl.start_time DESC
        LIMIT 1
    ", [$mapped_bathroom_id]);
    
    // Recent sensor data
    $recent_sensors = $db->fetchAll("
        SELECT 
            sl.value,
            sl.unit,
            sl.recorded_at,
            s.sensor_type,
            TIMESTAMPDIFF(SECOND, sl.recorded_at, NOW()) as seconds_ago
        FROM sensor_logs sl
        JOIN sensors s ON sl.sensor_id = s.id
        WHERE s.bathroom_id = ? 
        AND sl.recorded_at >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        ORDER BY sl.recorded_at DESC
        LIMIT 20
    ", [$mapped_bathroom_id]);
    
    // Determine hardware control signals
    $servo_lock = $bathroom['needs_cleaning'] ? 1 : 0;
    $led_red = $bathroom['needs_cleaning'] ? 1 : 0;
    $led_green = $bathroom['status'] === 'available' ? 1 : 0;
    $led_yellow = $bathroom['is_being_cleaned'] ? 1 : 0;
    $buzzer = $bathroom['needs_cleaning'] ? 1 : 0;
    
    echo json_encode([
        'success' => true,
        'message' => 'Bathroom status retrieved',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'bathroom' => $bathroom,
            'toilet_id' => (int)$bathroom_id,
            'current_cleaning' => $current_cleaning,
            'recent_sensors' => $recent_sensors,
            'hardware_control' => [
                'servo_lock' => $servo_lock,
                'led_red' => $led_red,
                'led_green' => $led_green,
                'led_yellow' => $led_yellow,
                'buzzer' => $buzzer
            ]
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Get status error: ' . $e->getMessage(), 3, __DIR__ . '/api-errors.log');
    echo json_encode([
        'success' => false, 
        'message' => 'Database error',
        'error' => $e->getMessage()
    ]);
}
?>