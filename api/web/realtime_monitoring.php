<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Access-Control-Allow-Origin: *');

require_once '../config/config.php';
require_once '../core/Database.php';

// Prevent any output before JSON
ob_start();

try {
    $db = Database::getInstance();
    
    // Clear any previous output
    ob_clean();
    
    // Get all bathrooms with real-time data
    $bathrooms = $db->fetchAll("
        SELECT 
            b.*,
            CASE 
                WHEN b.status = 'needs_cleaning' THEN 1
                WHEN b.current_visitors >= b.max_visitors THEN 1
                ELSE 0
            END as needs_cleaning,
            CASE 
                WHEN b.status = 'being_cleaned' THEN 1
                ELSE 0
            END as is_being_cleaned
        FROM bathrooms b
        WHERE b.is_active = 1
        ORDER BY b.id
    ");
    
    // Get latest sensor data for each bathroom
    foreach ($bathrooms as &$bathroom) {
        // Get latest gas sensor reading
        $gas_data = $db->fetch("
            SELECT sl.value, sl.recorded_at
            FROM sensor_logs sl
            JOIN sensors s ON sl.sensor_id = s.id
            WHERE s.bathroom_id = ? AND s.sensor_type = 'mq135'
            ORDER BY sl.recorded_at DESC
            LIMIT 1
        ", [$bathroom['id']]);
        
        // Get latest visitor count
        $visitor_data = $db->fetch("
            SELECT sl.value, sl.recorded_at
            FROM sensor_logs sl
            JOIN sensors s ON sl.sensor_id = s.id
            WHERE s.bathroom_id = ? AND s.sensor_type = 'ir'
            ORDER BY sl.recorded_at DESC
            LIMIT 1
        ", [$bathroom['id']]);
        
        // Determine trigger reason
        $trigger_reason = null;
        if ($bathroom['needs_cleaning']) {
            if ($gas_data && $gas_data['value'] > 400) {
                $trigger_reason = 'gas_level';
            } elseif ($bathroom['current_visitors'] >= $bathroom['max_visitors']) {
                $trigger_reason = 'visitor_count';
            }
        }
        
        // Get current cleaning activity
        $current_cleaning = $db->fetch("
            SELECT 
                cl.*,
                u.full_name as employee_name,
                rc.uid as employee_rfid,
                TIMESTAMPDIFF(SECOND, cl.start_time, NOW()) as duration_seconds
            FROM cleaning_logs cl
            JOIN users u ON cl.user_id = u.id
            LEFT JOIN rfid_cards rc ON u.id = rc.user_id
            WHERE cl.bathroom_id = ? AND cl.end_time IS NULL
            ORDER BY cl.start_time DESC
            LIMIT 1
        ", [$bathroom['id']]);
        
        // Get recent usage logs (last 10)
        $recent_logs = $db->fetchAll("
            SELECT *
            FROM usage_logs
            WHERE bathroom_id = ?
            ORDER BY waktu DESC
            LIMIT 10
        ", [$bathroom['id']]);
        
        $bathroom['sensor_data'] = [
            'gas_level' => $gas_data ? (int)$gas_data['value'] : 0,
            'gas_timestamp' => $gas_data ? $gas_data['recorded_at'] : null,
            'visitor_count' => $visitor_data ? (int)$visitor_data['value'] : 0,
            'visitor_timestamp' => $visitor_data ? $visitor_data['recorded_at'] : null
        ];
        
        $bathroom['trigger_reason'] = $trigger_reason;
        $bathroom['current_cleaning'] = $current_cleaning;
        $bathroom['recent_logs'] = $recent_logs;
        
        // Hardware status
        $bathroom['hardware_status'] = [
            'servo_locked' => $bathroom['needs_cleaning'] ? 1 : 0,
            'buzzer_active' => $bathroom['needs_cleaning'] ? 1 : 0,
            'led_status' => [
                'red' => $bathroom['needs_cleaning'] ? 1 : 0,
                'green' => $bathroom['status'] === 'available' ? 1 : 0,
                'yellow' => $bathroom['is_being_cleaned'] ? 1 : 0
            ]
        ];
    }
    
    // Get system statistics
    $stats = [
        'total_bathrooms' => count($bathrooms),
        'available' => count(array_filter($bathrooms, fn($b) => $b['status'] === 'available')),
        'needs_cleaning' => count(array_filter($bathrooms, fn($b) => $b['needs_cleaning'])),
        'being_cleaned' => count(array_filter($bathrooms, fn($b) => $b['is_being_cleaned'])),
        'maintenance' => count(array_filter($bathrooms, fn($b) => $b['status'] === 'maintenance'))
    ];
    
    // Get active employees
    $active_employees = $db->fetchAll("
        SELECT 
            u.id,
            u.full_name as name,
            rc.uid as rfid_code,
            p.current_balance as points
        FROM users u
        LEFT JOIN rfid_cards rc ON u.id = rc.user_id
        LEFT JOIN points p ON u.id = p.user_id
        WHERE u.role = 'karyawan' AND u.is_active = 1
        ORDER BY u.full_name
    ");
    
    // Clear output buffer and send JSON
    ob_clean();
    echo json_encode([
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'bathrooms' => $bathrooms,
            'statistics' => $stats,
            'employees' => $active_employees
        ]
    ]);
    
} catch (Exception $e) {
    ob_clean();
    error_log('Real-time monitoring API error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'error' => $e->getMessage()
    ]);
}
ob_end_flush();
?>