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
    $pointModel = new PointModel();
    
    $uid = strtoupper(trim($_POST['uid'] ?? $_POST['rfid_code'] ?? ''));
    $toilet_id = (int)($_POST['toilet_id'] ?? $_POST['bathroom_id'] ?? 1);
    $timestamp = date('Y-m-d H:i:s');
    
    if (empty($uid)) {
        echo json_encode(['success' => false, 'message' => 'RFID UID required']);
        exit;
    }
    
    // Get bathroom_id from database based on toilet_id
    $bathroom = $db->fetch("SELECT id FROM bathrooms WHERE is_active = 1 ORDER BY id LIMIT 1 OFFSET ?", [$toilet_id - 1]);
    if (!$bathroom) {
        // Fallback to first bathroom if toilet_id not found
        $bathroom = $db->fetch("SELECT id FROM bathrooms WHERE is_active = 1 ORDER BY id LIMIT 1");
        if (!$bathroom) {
            throw new Exception('No active bathrooms found');
        }
    }
    $bathroom_id = $bathroom['id'];
    
    // Admin reset functionality
    if (in_array($uid, ADMIN_RFID_CODES)) {
        $db->execute(
            "UPDATE bathrooms SET status = 'available', current_visitors = 0, updated_at = ? WHERE is_active = 1",
            [$timestamp]
        );
        
        echo json_encode([
            'success' => true,
            'action' => 'admin_reset',
            'message' => 'All systems reset by admin',
            'uid' => $uid,
            'timestamp' => $timestamp
        ]);
        exit;
    }
    
    // Find employee by RFID
    $employee = $db->fetch("
        SELECT 
            u.id as user_id,
            u.full_name as name,
            u.role,
            u.is_active,
            rc.uid as rfid_code,
            COALESCE(p.current_balance, 0) as points
        FROM rfid_cards rc
        JOIN users u ON rc.user_id = u.id
        LEFT JOIN points p ON u.id = p.user_id
        WHERE rc.uid = ? AND rc.status = 'Aktif' AND u.is_active = 1
    ", [$uid]);
    
    if (!$employee || $employee['role'] !== 'karyawan') {
        echo json_encode([
            'success' => false, 
            'message' => 'Employee not found or inactive',
            'uid' => $uid
        ]);
        exit;
    }
    
    // Check ongoing cleaning
    $ongoing = $db->fetch(
        "SELECT * FROM cleaning_logs WHERE user_id = ? AND bathroom_id = ? AND status = 'in_progress' ORDER BY start_time DESC LIMIT 1",
        [$employee['user_id'], $bathroom_id]
    );
    
    if ($ongoing) {
        // Finish cleaning
        $start_time = new DateTime($ongoing['start_time']);
        $end_time = new DateTime($timestamp);
        $duration = $end_time->getTimestamp() - $start_time->getTimestamp();
        $points = min(50, max(10, floor($duration / 60)));
        
        $db->execute(
            "UPDATE cleaning_logs SET end_time = ?, duration_minutes = ?, status = 'completed', points_earned = ? WHERE id = ?",
            [$timestamp, floor($duration / 60), $points, $ongoing['id']]
        );
        
        // Add points
        $pointModel->addPoints($employee['user_id'], $points, 'cleaning', $ongoing['id']);
        
        // Reset bathroom
        $db->execute(
            "UPDATE bathrooms SET status = 'available', current_visitors = 0, last_cleaned = ?, last_cleaned_by = ?, updated_at = ? WHERE id = ?",
            [$timestamp, $employee['user_id'], $timestamp, $bathroom_id]
        );
        
        // Log RFID action
        $db->insert('rfid_logs', [
            'bathroom_id' => $bathroom_id,
            'user_id' => $employee['user_id'],
            'action' => 'finish_cleaning',
            'rfid_code' => $uid,
            'timestamp' => $timestamp
        ]);
        
        echo json_encode([
            'success' => true,
            'action' => 'finish_cleaning',
            'message' => 'Cleaning completed successfully',
            'data' => [
                'employee_name' => $employee['name'],
                'duration_seconds' => $duration,
                'points_earned' => $points,
                'new_balance' => $employee['points'] + $points,
                'bathroom_id' => $bathroom_id,
                'toilet_id' => $toilet_id
            ]
        ]);
        
    } else {
        // Start cleaning
        $activity_id = 'cl_' . time() . '_' . rand(1000, 9999);
        $db->insert('cleaning_logs', [
            'id' => $activity_id,
            'bathroom_id' => $bathroom_id,
            'user_id' => $employee['user_id'],
            'start_time' => $timestamp,
            'status' => 'in_progress'
        ]);
        
        $db->execute(
            "UPDATE bathrooms SET status = 'being_cleaned', updated_at = ? WHERE id = ?",
            [$timestamp, $bathroom_id]
        );
        
        // Log RFID action
        $db->insert('rfid_logs', [
            'bathroom_id' => $bathroom_id,
            'user_id' => $employee['user_id'],
            'action' => 'start_cleaning',
            'rfid_code' => $uid,
            'timestamp' => $timestamp
        ]);
        
        echo json_encode([
            'success' => true,
            'action' => 'start_cleaning',
            'message' => 'Cleaning started successfully',
            'data' => [
                'employee_name' => $employee['name'],
                'activity_id' => $activity_id,
                'bathroom_id' => $bathroom_id,
                'toilet_id' => $toilet_id,
                'start_time' => $timestamp
            ]
        ]);
    }
    
} catch (Exception $e) {
    error_log('RFID tap error: ' . $e->getMessage(), 3, __DIR__ . '/api-errors.log');
    echo json_encode([
        'success' => false, 
        'message' => 'System error',
        'debug' => $e->getMessage()
    ]);
}
?>