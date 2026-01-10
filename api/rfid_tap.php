<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../models/PointModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $db = Database::getInstance();
    
    $uid = strtoupper(trim($_POST['uid'] ?? $_POST['rfid_code'] ?? ''));
    $bathroom_id = (int)($_POST['bathroom_id'] ?? $_POST['toilet_id'] ?? 1);
    
    if (empty($uid)) {
        throw new Exception('RFID code is required');
    }
    
    // Admin UIDs for reset
    $admin_codes = ['ADMIN001', 'RESET123', 'B490FBB0', 'C6861BFF'];
    if (in_array($uid, $admin_codes)) {
        $db->execute("UPDATE bathrooms SET current_visitors = 0, status = 'available'");
        
        echo json_encode([
            'success' => true,
            'action' => 'admin_reset',
            'message' => 'System reset by admin',
            'uid' => $uid
        ]);
        exit;
    }
    
    // Find RFID card from rfid_cards table
    $rfid_card = $db->fetch("SELECT * FROM rfid_cards WHERE uid = ? AND status = 'Aktif'", [$uid]);
    
    if (!$rfid_card) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid RFID card or card not active',
            'uid' => $uid
        ]);
        exit;
    }
    
    // Get user data if linked to rfid card
    $user = null;
    if ($rfid_card['user_id']) {
        $user = $db->fetch("SELECT * FROM users WHERE id = ? AND is_active = 1", [$rfid_card['user_id']]);
    }
    
    // Use rfid card data if no user linked
    if (!$user) {
        $user = [
            'id' => $rfid_card['id'],
            'full_name' => $rfid_card['nama_pemilik'],
            'name' => $rfid_card['nama_pemilik'],
            'role' => strtolower($rfid_card['peran'])
        ];
    }
    
    // Check bathroom exists and get valid bathroom_id
    $bathroom = $db->fetch("SELECT * FROM bathrooms WHERE id = ? AND is_active = 1", [$bathroom_id]);
    
    if (!$bathroom) {
        // If bathroom not found, use first available bathroom
        $bathroom = $db->fetch("SELECT * FROM bathrooms WHERE is_active = 1 ORDER BY id LIMIT 1");
        if (!$bathroom) {
            throw new Exception('No active bathrooms found');
        }
        $bathroom_id = $bathroom['id'];
    }
    
    // Check if user has ongoing cleaning session
    $ongoing = $db->fetch(
        "SELECT * FROM cleaning_logs WHERE bathroom_id = ? AND user_id = ? AND status = 'in_progress'",
        [$bathroom_id, $user['id']]
    );
    
    $db->beginTransaction();
    
    if ($ongoing) {
        // Finish cleaning - TAP KEDUA
        $end_time = date('Y-m-d H:i:s');
        $start_time = $ongoing['start_time'];
        $duration = (strtotime($end_time) - strtotime($start_time)) / 60;
        
        // Get points setting from database or use default
        $points_setting = $db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = 'points_per_cleaning'");
        $points = (int)($points_setting['setting_value'] ?? 10);
        
        // Update cleaning log
        $db->execute(
            "UPDATE cleaning_logs SET end_time = ?, duration_minutes = ?, points_earned = ?, status = 'completed' WHERE id = ?",
            [$end_time, (int)$duration, $points, $ongoing['id']]
        );
        
        // Add points to user balance using PointModel
        $pointModel = new PointModel();
        $pointResult = $pointModel->addPoints(
            $user['id'], 
            $points, 
            'cleaning', 
            $ongoing['id'], 
            'Points earned from cleaning ' . $bathroom['name']
        );
        
        if (!$pointResult['success']) {
            error_log('Failed to add points: ' . ($pointResult['message'] ?? 'Unknown error'));
            // Don't throw exception, just log the error and continue
        }
        
        // Update bathroom status - RESET PENGUNJUNG KE 0
        $db->execute(
            "UPDATE bathrooms SET status = 'available', current_visitors = 0, last_cleaned = ?, last_cleaned_by = ? WHERE id = ?",
            [$end_time, $user['id'], $bathroom_id]
        );
        
        $action = 'finish_cleaning';
        $message = 'Cleaning completed. Visitors reset to 0. Points awarded: ' . $points . '. New balance: ' . $pointResult['new_balance'];
        
    } else {
        // Start cleaning - TAP PERTAMA
        $log_id = 'cl_' . time() . '_' . rand(1000, 9999);
        $db->insert('cleaning_logs', [
            'id' => $log_id,
            'bathroom_id' => $bathroom_id,
            'user_id' => $user['id'],
            'start_time' => date('Y-m-d H:i:s'),
            'status' => 'in_progress'
        ]);
        
        // Update bathroom status to being cleaned
        $db->execute(
            "UPDATE bathrooms SET status = 'being_cleaned' WHERE id = ?",
            [$bathroom_id]
        );
        
        $action = 'start_cleaning';
        $message = 'Cleaning session started. Bathroom locked for cleaning.';
    }
    
    // Log RFID activity with proper ID
    $rfid_log_id = 'rfid_' . time() . '_' . rand(1000, 9999);
    $db->insert('rfid_logs', [
        'id' => $rfid_log_id,
        'bathroom_id' => $bathroom_id,
        'user_id' => $user['id'],
        'action' => $action,
        'rfid_code' => $uid,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'action' => $action,
        'message' => $message,
        'user_name' => $user['full_name'] ?? $user['name'],
        'user_role' => $user['role'],
        'uid' => $uid,
        'bathroom_id' => $bathroom_id,
        'bathroom_name' => $bathroom['name']
    ]);
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    error_log('RFID Tap Error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'uid' => $uid ?? 'unknown'
    ]);
}
?>