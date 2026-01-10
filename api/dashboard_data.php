<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Prevent any HTML output
ob_start();

try {
    require_once '../config/config.php';
    require_once '../core/Database.php';
    
    $db = Database::getInstance();
    
    // Clear any previous output
    ob_clean();
    
    // Get basic statistics
    $stats = [
        'total_bathrooms' => 0,
        'available' => 0,
        'needs_cleaning' => 0,
        'being_cleaned' => 0,
        'active_employees' => 0
    ];
    
    // Get bathroom counts
    $bathroom_stats = $db->fetchAll("
        SELECT 
            status,
            COUNT(*) as count
        FROM bathrooms 
        WHERE is_active = 1
        GROUP BY status
    ");
    
    $stats['total_bathrooms'] = array_sum(array_column($bathroom_stats, 'count'));
    
    foreach ($bathroom_stats as $stat) {
        switch ($stat['status']) {
            case 'available':
                $stats['available'] = $stat['count'];
                break;
            case 'needs_cleaning':
                $stats['needs_cleaning'] = $stat['count'];
                break;
            case 'being_cleaned':
                $stats['being_cleaned'] = $stat['count'];
                break;
        }
    }
    
    // Get active employees
    $employee_count = $db->fetch("
        SELECT COUNT(*) as count 
        FROM users 
        WHERE role = 'karyawan' AND is_active = 1
    ");
    $stats['active_employees'] = $employee_count['count'] ?? 0;
    
    // Get bathrooms with basic info
    $bathrooms = $db->fetchAll("
        SELECT 
            id,
            name,
            location,
            status,
            current_visitors,
            max_visitors,
            updated_at
        FROM bathrooms 
        WHERE is_active = 1
        ORDER BY name
    ");
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'stats' => $stats,
        'bathrooms' => $bathrooms
    ]);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

ob_end_flush();
?>