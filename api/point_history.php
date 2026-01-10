<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../models/PointModel.php';

try {
    $user_id = $_GET['user_id'] ?? $_POST['user_id'] ?? '';
    
    if (empty($user_id)) {
        throw new Exception('User ID is required');
    }
    
    $pointModel = new PointModel();
    $db = Database::getInstance();
    
    // Get current points
    $current_points = $pointModel->getUserPoints($user_id);
    
    // Get all transactions with detailed info
    $transactions = $pointModel->getAllTransactions($user_id);
    
    // Get cleaning history with points
    $cleaning_history = $db->fetchAll(
        "SELECT cl.*, b.name as bathroom_name, cl.points_earned,
                DATE_FORMAT(cl.start_time, '%d/%m/%Y %H:%i') as start_formatted,
                DATE_FORMAT(cl.end_time, '%d/%m/%Y %H:%i') as end_formatted
         FROM cleaning_logs cl
         JOIN bathrooms b ON cl.bathroom_id = b.id
         WHERE cl.user_id = ? AND cl.status = 'completed'
         ORDER BY cl.created_at DESC",
        [$user_id]
    );
    
    // Get purchase history
    $purchase_history = $db->fetchAll(
        "SELECT o.*, o.order_number, o.total_points,
                DATE_FORMAT(o.created_at, '%d/%m/%Y %H:%i') as order_date,
                GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, 'x)') SEPARATOR ', ') as items
         FROM orders o
         JOIN order_items oi ON o.id = oi.order_id
         JOIN products p ON oi.product_id = p.id
         WHERE o.user_id = ?
         GROUP BY o.id
         ORDER BY o.created_at DESC",
        [$user_id]
    );
    
    // Get transfer history
    $transfer_history = $pointModel->getTransferHistory($user_id);
    
    // Calculate summary statistics
    $stats = [
        'total_earned_from_cleaning' => 0,
        'total_spent_on_purchases' => 0,
        'total_transferred_out' => 0,
        'total_transferred_in' => 0,
        'total_cleanings' => count($cleaning_history),
        'total_purchases' => count($purchase_history),
        'total_transfers' => count($transfer_history)
    ];
    
    foreach ($cleaning_history as $cleaning) {
        $stats['total_earned_from_cleaning'] += $cleaning['points_earned'] ?? 0;
    }
    
    foreach ($purchase_history as $purchase) {
        if ($purchase['status'] === 'completed') {
            $stats['total_spent_on_purchases'] += $purchase['total_points'];
        }
    }
    
    foreach ($transfer_history as $transfer) {
        if ($transfer['transaction_type'] === 'transfer_out') {
            $stats['total_transferred_out'] += $transfer['amount'];
        } else {
            $stats['total_transferred_in'] += $transfer['amount'];
        }
    }
    
    // Format transactions for display
    $formatted_transactions = [];
    foreach ($transactions as $transaction) {
        $formatted_transactions[] = [
            'id' => $transaction['id'],
            'type' => $transaction['transaction_type'],
            'amount' => $transaction['amount'],
            'balance_after' => $transaction['balance_after'],
            'description' => $transaction['description'],
            'date' => date('d/m/Y H:i', strtotime($transaction['created_at'])),
            'from_user' => $transaction['from_user_name'] ?? null,
            'to_user' => $transaction['to_user_name'] ?? null
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'current_points' => $current_points,
            'statistics' => $stats,
            'transactions' => $formatted_transactions,
            'cleaning_history' => $cleaning_history,
            'purchase_history' => $purchase_history,
            'transfer_history' => $transfer_history
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Point History Error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>