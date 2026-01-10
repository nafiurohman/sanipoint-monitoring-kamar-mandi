<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once '../../config/config.php';
require_once '../../core/Database.php';
require_once '../../core/Auth.php';

Auth::requireLogin();

try {
    $db = Database::getInstance();
    
    // Get real-time toilet status
    $toilets = $db->fetchAll("
        SELECT 
            id,
            name,
            status,
            gas_level,
            visitor_count,
            max_visitors,
            last_cleaned,
            last_updated
        FROM toilets 
        ORDER BY id
    ");
    
    // Get latest sensor readings (last 5 minutes)
    $recent_sensors = $db->fetchAll("
        SELECT 
            toilet_id,
            sensor_type,
            value,
            timestamp
        FROM sensor_data 
        WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ORDER BY timestamp DESC
    ");
    
    // Get active cleaning activities
    $active_cleaning = $db->fetchAll("
        SELECT 
            ca.*,
            u.name as employee_name,
            t.name as toilet_name
        FROM cleaning_activities ca
        JOIN users u ON ca.employee_id = u.id
        JOIN toilets t ON ca.toilet_id = t.id
        WHERE ca.end_time IS NULL
        ORDER BY ca.start_time DESC
    ");
    
    // Get today's statistics
    $today_stats = $db->fetch("
        SELECT 
            COUNT(DISTINCT ca.id) as total_cleanings,
            COUNT(DISTINCT ca.employee_id) as active_employees,
            SUM(CASE WHEN ca.end_time IS NOT NULL THEN 1 ELSE 0 END) as completed_cleanings,
            AVG(CASE WHEN ca.duration IS NOT NULL THEN ca.duration ELSE NULL END) as avg_duration
        FROM cleaning_activities ca
        WHERE DATE(ca.start_time) = CURDATE()
    ");
    
    echo json_encode([
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'toilets' => $toilets,
            'recent_sensors' => $recent_sensors,
            'active_cleaning' => $active_cleaning,
            'today_stats' => $today_stats
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Monitoring API error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>