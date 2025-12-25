<?php
require_once 'models/ProductModel.php';
require_once 'models/PointModel.php';

class OrderModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createOrder($user_id, $cart_items) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            $productModel = new ProductModel();
            $pointModel = new PointModel();
            
            $total_points = 0;
            $order_items = [];
            
            // Validate cart items and calculate total
            foreach ($cart_items as $item) {
                $product = $productModel->getById($item['product_id']);
                if (!$product) {
                    throw new Exception('Product not found: ' . $item['product_id']);
                }
                
                if ($product['stock'] < $item['quantity']) {
                    throw new Exception('Insufficient stock for: ' . $product['name']);
                }
                
                $item_total = $product['price_points'] * $item['quantity'];
                $total_points += $item_total;
                
                $order_items[] = [
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'quantity' => $item['quantity'],
                    'points_per_item' => $product['price_points'],
                    'total_points' => $item_total
                ];
            }
            
            // Check user points
            $user_points = $pointModel->getUserPoints($user_id);
            if ($user_points['current_balance'] < $total_points) {
                throw new Exception('Insufficient points');
            }
            
            // Generate order number
            $order_number = 'ORD' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Create order
            $this->db->insert('orders', [
                'user_id' => $user_id,
                'order_number' => $order_number,
                'total_points' => $total_points,
                'status' => 'pending'
            ]);
            
            $order_id = $this->db->getConnection()->lastInsertId();
            
            // Create order items and update stock
            foreach ($order_items as $item) {
                $this->db->insert('order_items', [
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'points_per_item' => $item['points_per_item'],
                    'total_points' => $item['total_points']
                ]);
                
                // Update product stock
                $productModel->updateStock($item['product_id'], $item['quantity']);
            }
            
            // Deduct points
            $pointModel->deductPoints($user_id, $total_points, 'purchase', $order_id, 'Order: ' . $order_number);
            
            // Generate QR code
            $qr_code = $this->generateQRCode($order_id, $order_number);
            
            // Update order with QR code
            $this->db->update('orders', [
                'qr_code' => $qr_code,
                'status' => 'completed'
            ], 'id = ?', [$order_id]);
            
            // Save QR code record
            $this->db->insert('qr_codes', [
                'order_id' => $order_id,
                'qr_code' => $qr_code,
                'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days'))
            ]);
            
            $this->db->getConnection()->commit();
            
            // Clear cart
            unset($_SESSION['cart']);
            
            return [
                'success' => true, 
                'message' => 'Order created successfully',
                'order_id' => $order_id,
                'order_number' => $order_number,
                'qr_code' => $qr_code,
                'total_points' => $total_points
            ];
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getUserOrders($user_id, $limit = null) {
        $sql = "SELECT o.*, 
                       COUNT(oi.id) as item_count,
                       qr.is_used as qr_used,
                       qr.used_at as qr_used_at
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                LEFT JOIN qr_codes qr ON o.id = qr.order_id
                WHERE o.user_id = ?
                GROUP BY o.id
                ORDER BY o.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            return $this->db->fetchAll($sql, [$user_id, $limit]);
        }
        
        return $this->db->fetchAll($sql, [$user_id]);
    }
    
    public function getOrderDetails($order_id, $user_id = null) {
        $sql = "SELECT o.*, u.full_name as user_name
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.id = ?";
        
        $params = [$order_id];
        if ($user_id) {
            $sql .= " AND o.user_id = ?";
            $params[] = $user_id;
        }
        
        $order = $this->db->fetch($sql, $params);
        if (!$order) return null;
        
        // Get order items
        $items_sql = "SELECT oi.*, p.name as product_name, p.image_url
                      FROM order_items oi
                      JOIN products p ON oi.product_id = p.id
                      WHERE oi.order_id = ?";
        $order['items'] = $this->db->fetchAll($items_sql, [$order_id]);
        
        return $order;
    }
    
    public function validateQRCode($qr_code) {
        $qr = $this->db->fetch(
            "SELECT qr.*, o.order_number, o.total_points, u.full_name as user_name
             FROM qr_codes qr
             JOIN orders o ON qr.order_id = o.id
             JOIN users u ON o.user_id = u.id
             WHERE qr.qr_code = ? AND qr.is_used = 0 AND qr.expires_at > NOW()",
            [$qr_code]
        );
        
        if ($qr) {
            // Mark as used
            $this->db->update('qr_codes', [
                'is_used' => 1,
                'used_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$qr['id']]);
        }
        
        return $qr;
    }
    
    private function generateQRCode($order_id, $order_number) {
        // Simple QR code generation - in production, use a proper QR library
        $data = [
            'order_id' => $order_id,
            'order_number' => $order_number,
            'timestamp' => time(),
            'hash' => hash('sha256', $order_id . $order_number . APP_KEY)
        ];
        
        return base64_encode(json_encode($data));
    }
    
    public function getOrderStats() {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_orders,
                    SUM(total_points) as total_points_spent
                FROM orders 
                WHERE status = 'completed'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC";
        return $this->db->fetchAll($sql);
    }
}
?>