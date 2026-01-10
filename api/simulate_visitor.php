<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/config.php';
require_once '../core/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $db = Database::getInstance();
    
    $bathroom_id = (int)($_POST['bathroom_id'] ?? 1);
    $action = $_POST['action'] ?? 'add'; // add or reset
    
    $bathroom = $db->fetch("SELECT * FROM bathrooms WHERE id = ?", [$bathroom_id]);
    if (!$bathroom) {
        // Try to find any bathroom if specific ID not found
        $bathroom = $db->fetch("SELECT * FROM bathrooms WHERE is_active = 1 LIMIT 1");
        if (!$bathroom) {
            throw new Exception('No active bathrooms found in database');
        }
        $bathroom_id = $bathroom['id'];
    }
    
    if ($action === 'reset') {
        // Reset visitors to 0
        $db->execute("UPDATE bathrooms SET current_visitors = 0, status = 'available' WHERE id = ?", [$bathroom_id]);
        $message = 'Visitors reset to 0';
        $new_count = 0;
    } else {
        // Update visitor count in database directly
        $new_count = (int)($_POST['count'] ?? 0);
        $new_status = $new_count >= $bathroom['max_visitors'] ? 'needs_cleaning' : 'available';
        
        $db->execute("UPDATE bathrooms SET current_visitors = ?, status = ? WHERE id = ?", 
                    [$new_count, $new_status, $bathroom_id]);
        
        $message = "Visitor count set to {$new_count}/{$bathroom['max_visitors']}";
        if ($new_count >= $bathroom['max_visitors']) {
            $message .= " - Needs cleaning!";
        }
    }
    
    // Get updated bathroom data
    $updated_bathroom = $db->fetch("SELECT * FROM bathrooms WHERE id = ?", [$bathroom_id]);
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'bathroom' => $updated_bathroom,
        'action' => $action
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>