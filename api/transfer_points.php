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
    $from_user_id = $_POST['from_user_id'] ?? '';
    $to_user_id = $_POST['to_user_id'] ?? '';
    $amount = (int)($_POST['amount'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    
    if (empty($from_user_id) || empty($to_user_id)) {
        throw new Exception('From User ID and To User ID are required');
    }
    
    if ($amount <= 0) {
        throw new Exception('Transfer amount must be greater than 0');
    }
    
    if ($from_user_id === $to_user_id) {
        throw new Exception('Cannot transfer points to yourself');
    }
    
    $db = Database::getInstance();
    
    // Verify both users exist and are active employees
    $from_user = $db->fetch("SELECT * FROM users WHERE id = ? AND role = 'karyawan' AND is_active = 1", [$from_user_id]);
    if (!$from_user) {
        throw new Exception('Sender not found or inactive');
    }
    
    $to_user = $db->fetch("SELECT * FROM users WHERE id = ? AND role = 'karyawan' AND is_active = 1", [$to_user_id]);
    if (!$to_user) {
        throw new Exception('Recipient not found or inactive');
    }
    
    $pointModel = new PointModel();
    
    // Check sender balance
    $sender_points = $pointModel->getUserPoints($from_user_id);
    if ($sender_points['current_balance'] < $amount) {
        throw new Exception('Insufficient points. Available: ' . $sender_points['current_balance']);
    }
    
    // Perform transfer
    $result = $pointModel->transferPoints(
        $from_user_id, 
        $to_user_id, 
        $amount, 
        $description ?: 'Point transfer from ' . $from_user['full_name'] . ' to ' . $to_user['full_name']
    );
    
    if (!$result['success']) {
        throw new Exception($result['message']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Points transferred successfully!',
        'data' => [
            'from_user' => $from_user['full_name'],
            'to_user' => $to_user['full_name'],
            'amount' => $amount,
            'new_balance' => $result['new_balance'],
            'description' => $description
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Transfer Error: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>