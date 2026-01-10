<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once '../config/config.php';
require_once '../core/Database.php';

try {
    $db = Database::getInstance();
    
    // Get bathrooms with computed status
    $bathrooms = $db->fetchAll("
        SELECT b.*, 
               COALESCE(cl.user_name, '') as cleaning_by,
               CASE 
                   WHEN cl.status = 'in_progress' THEN 'being_cleaned'
                   WHEN b.current_visitors >= b.max_visitors THEN 'needs_cleaning'
                   ELSE 'available'
               END as computed_status
        FROM bathrooms b
        LEFT JOIN (
            SELECT bathroom_id, u.full_name as user_name, status
            FROM cleaning_logs cl
            JOIN users u ON cl.user_id = u.id
            WHERE cl.status = 'in_progress'
        ) cl ON b.id = cl.bathroom_id
        WHERE b.is_active = 1
        ORDER BY b.id
    ");
    
    // Simulate sensor data
    $sensors = [];
    foreach ($bathrooms as $bathroom) {
        $sensors[] = [
            'bathroom_id' => $bathroom['id'],
            'bathroom_name' => $bathroom['name'],
            'sensor_type' => 'mq135',
            'value' => rand(200, 500),
            'unit' => 'ppm',
            'recorded_at' => date('Y-m-d H:i:s')
        ];
        
        $sensors[] = [
            'bathroom_id' => $bathroom['id'],
            'bathroom_name' => $bathroom['name'],
            'sensor_type' => 'ir',
            'value' => $bathroom['current_visitors'],
            'unit' => 'count',
            'recorded_at' => date('Y-m-d H:i:s')
        ];
    }
    
    echo json_encode([
        'success' => true,
        'bathrooms' => $bathrooms,
        'sensors' => $sensors,
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