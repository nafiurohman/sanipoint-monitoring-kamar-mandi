<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../models/OrderModel.php';
require_once '../models/ProductModel.php';
require_once '../models/PointModel.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $user_id = $_POST['user_id'] ?? '';
    $product_id = $_POST['product_id'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if (empty($user_id) || empty($product_id)) {
        throw new Exception('User ID and Product ID are required');
    }
    
    if ($quantity <= 0) {
        throw new Exception('Quantity must be greater than 0');
    }
    
    $db = Database::getInstance();
    $db->beginTransaction();
    
    // Verify user exists and is active
    $user = $db->fetch("SELECT * FROM users WHERE id = ? AND is_active = 1", [$user_id]);
    if (!$user) {
        throw new Exception('User not found or inactive');
    }
    
    $orderModel = new OrderModel();
    $productModel = new ProductModel();
    $pointModel = new PointModel();
    
    // Get product details
    $product = $productModel->getById($product_id);
    if (!$product || !$product['is_active']) {
        throw new Exception('Product not found or inactive');
    }
    
    if ($product['stock'] < $quantity) {
        throw new Exception('Insufficient stock. Available: ' . $product['stock']);
    }
    
    $total_points = $product['point_price'] * $quantity;
    
    // Check user points
    $user_points = $pointModel->getUserPoints($user_id);
    if ($user_points['current_balance'] < $total_points) {
        throw new Exception('Insufficient points. Required: ' . $total_points . ', Available: ' . $user_points['current_balance']);
    }
    
    // Generate order number
    $order_number = 'ORD' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Create order
    $order_id = $db->insert('orders', [
        'user_id' => $user_id,
        'order_number' => $order_number,
        'total_points' => $total_points,
        'status' => 'completed'
    ]);
    
    // Create order item
    $db->insert('order_items', [
        'order_id' => $order_id,
        'product_id' => $product_id,
        'quantity' => $quantity,
        'points_per_item' => $product['point_price'],
        'total_points' => $total_points
    ]);
    
    // Update product stock
    $new_stock = $product['stock'] - $quantity;
    $db->update('products', ['stock' => $new_stock], 'id = ?', [$product_id]);
    
    // Deduct points using PointModel
    $pointResult = $pointModel->deductPoints(
        $user_id, 
        $total_points, 
        'purchase', 
        $order_id, 
        'Purchase: ' . $product['name'] . ' (x' . $quantity . ')'
    );
    
    if (!$pointResult['success']) {
        throw new Exception('Failed to deduct points: ' . $pointResult['message']);
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Purchase successful!',
        'data' => [
            'order_id' => $order_id,
            'order_number' => $order_number,
            'product_name' => $product['name'],
            'quantity' => $quantity,
            'total_points' => $total_points,
            'remaining_balance' => $pointResult['new_balance']
        ]
    ]);
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    error_log('Purchase Error: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>