<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

try {
    $db = Database::getInstance();
    
    $bathrooms = $db->fetchAll("
        SELECT 
            id,
            name,
            location,
            current_visitors,
            max_visitors,
            status,
            last_cleaned,
            updated_at
        FROM bathrooms 
        WHERE is_active = 1 
        ORDER BY id
    ");
    
    $sensors = $db->fetchAll("
        SELECT 
            s.sensor_code,
            s.sensor_type,
            s.bathroom_id,
            sl.value,
            sl.unit,
            sl.recorded_at
        FROM sensors s
        LEFT JOIN sensor_logs sl ON s.id = sl.sensor_id
        WHERE s.is_active = 1
        AND sl.id = (
            SELECT MAX(id) FROM sensor_logs WHERE sensor_id = s.id
        )
        ORDER BY s.bathroom_id, s.sensor_type
    ");
    
    $active_cleanings = $db->fetchAll("
        SELECT 
            cl.bathroom_id,
            cl.user_id,
            cl.start_time,
            u.full_name as user_name
        FROM cleaning_logs cl
        JOIN users u ON cl.user_id = u.id
        WHERE cl.status = 'in_progress'
    ");
    
    echo json_encode([
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'bathrooms' => $bathrooms,
        'sensors' => $sensors,
        'active_cleanings' => $active_cleanings
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>