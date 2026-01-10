<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../models/PointModel.php';

try {
    $pointModel = new PointModel();
    
    if (isset($_POST['user_id'])) {
        // Recalculate for specific user
        $user_id = $_POST['user_id'];
        $result = $pointModel->recalculateUserPoints($user_id);
        
        echo json_encode([
            'success' => true,
            'message' => 'Points recalculated for user',
            'user_id' => $user_id,
            'data' => $result
        ]);
    } else {
        // Recalculate for all users
        $result = $pointModel->recalculateAllUserPoints();
        
        echo json_encode([
            'success' => true,
            'message' => 'Points recalculated for all users',
            'data' => $result
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>