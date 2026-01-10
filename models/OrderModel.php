<?php
require_once 'ProductModel.php';
require_once 'PointModel.php';

class OrderModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createOrder($user_id, $product_id, $quantity = 1) {
        try {
            $this->db->beginTransaction();
            
            $productModel = new ProductModel();
            $pointModel = new PointModel();
            
            // Get product details
            $product = $productModel->getById($product_id);
            if (!$product) {
                throw new Exception('Product not found');
            }
            
            if ($product['stock'] < $quantity) {
                throw new Exception('Insufficient stock');
            }
            
            $total_points = $product['point_price'] * $quantity;
            
            // Check user points
            $user_points = $pointModel->getUserPoints($user_id);
            if ($user_points['current_balance'] < $total_points) {
                throw new Exception('Insufficient points');
            }
            
            // Generate order number
            $order_number = 'ORD' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Create order
            $order_id = $this->db->insert('orders', [
                'user_id' => $user_id,
                'order_number' => $order_number,
                'total_points' => $total_points,
                'status' => 'completed'
            ]);
            
            // Create order item
            $this->db->insert('order_items', [
                'order_id' => $order_id,
                'product_id' => $product_id,
                'quantity' => $quantity,
                'points_per_item' => $product['point_price'],
                'total_points' => $total_points
            ]);
            
            // Update product stock
            $productModel->updateStock($product_id, $quantity);
            
            // Deduct points
            $pointModel->deductPoints($user_id, $total_points, 'spent', $order_id, 'Purchase: ' . $product['name']);
            
            $this->db->commit();
            
            return [
                'success' => true, 
                'message' => 'Pembelian berhasil!',
                'order_id' => $order_id,
                'order_number' => $order_number
            ];
            
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function createOrderFromCart($user_id, $cart_items) {
        try {
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
            $order_id = $this->db->insert('orders', [
                'user_id' => $user_id,
                'order_number' => $order_number,
                'total_points' => $total_points,
                'status' => 'completed'
            ]);
            
            // Create order items
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
            
            // Log successful order creation
            error_log('✅ ORDER SUCCESS: Order ' . $order_number . ' created for user ' . $user_id . ' with ' . $total_points . ' points');
            
            return [
                'success' => true, 
                'message' => 'Pembelian berhasil! Order telah dibuat.',
                'order_id' => $order_id,
                'order_number' => $order_number,
                'total_points' => $total_points,
                'order_items' => $order_items
            ];
            
        } catch (Exception $e) {
            error_log('Order creation failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getUserOrders($user_id, $limit = null) {
        $sql = "SELECT o.id, o.order_number, o.total_points, o.status, o.created_at, o.received_at, o.cancelled_at,
                       COUNT(oi.id) as item_count
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = ?
                GROUP BY o.id, o.order_number, o.total_points, o.status, o.created_at, o.received_at, o.cancelled_at
                ORDER BY o.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
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
    
    public function cancelOrder($order_id, $user_id) {
        try {
            // Get order details
            $order = $this->db->fetch(
                "SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'completed'",
                [$order_id, $user_id]
            );
            
            if (!$order) {
                throw new Exception('Order not found or cannot be cancelled');
            }
            
            // Get order items
            $items = $this->db->fetchAll(
                "SELECT oi.*, p.name as product_name FROM order_items oi 
                 JOIN products p ON oi.product_id = p.id 
                 WHERE oi.order_id = ?",
                [$order_id]
            );
            
            // Restore stock
            $productModel = new ProductModel();
            foreach ($items as $item) {
                $productModel->restoreStock($item['product_id'], $item['quantity']);
            }
            
            // Refund points
            $pointModel = new PointModel();
            $pointModel->addPoints($user_id, $order['total_points'], 'refund', $order_id, 'Order cancelled: ' . $order['order_number']);
            
            // Update order status
            $this->db->update('orders', [
                'status' => 'cancelled',
                'cancelled_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$order_id]);
            
            return ['success' => true, 'message' => 'Order cancelled successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function confirmReceived($order_id, $user_id) {
        try {
            // Verify order belongs to user and is completed
            $order = $this->db->fetch(
                "SELECT * FROM orders WHERE id = ? AND user_id = ? AND status = 'completed' AND received_at IS NULL",
                [$order_id, $user_id]
            );
            
            if (!$order) {
                return ['success' => false, 'message' => 'Order not found or already confirmed'];
            }
            
            // Update received_at timestamp (status stays 'completed')
            $this->db->update('orders', [
                'received_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$order_id]);
            
            return ['success' => true, 'message' => 'Konfirmasi penerimaan barang berhasil'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getAllOrdersWithFilters($status = '', $date = '', $employee = '') {
        $sql = "SELECT o.*, u.full_name as user_name, COUNT(oi.id) as item_count,
                       o.received_at, o.cancelled_at
                FROM orders o
                JOIN users u ON o.user_id = u.id
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE 1=1";
        
        $params = [];
        
        if ($status) {
            if ($status === 'pending') {
                $sql .= " AND o.status = 'completed' AND o.received_at IS NULL";
            } elseif ($status === 'received') {
                $sql .= " AND o.status = 'completed' AND o.received_at IS NOT NULL";
            } else {
                $sql .= " AND o.status = ?";
                $params[] = $status;
            }
        }
        
        if ($date) {
            $sql .= " AND DATE(o.created_at) = ?";
            $params[] = $date;
        }
        
        if ($employee) {
            $sql .= " AND o.user_id = ?";
            $params[] = $employee;
        }
        
        $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getTransactionStats() {
        $stats = [];
        
        $stats['total_orders'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM orders WHERE status IN ('completed', 'cancelled')"
        )['count'];
        
        $stats['pending_pickup'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM orders WHERE status = 'completed' AND received_at IS NULL"
        )['count'];
        
        $stats['received'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM orders WHERE status = 'completed' AND received_at IS NOT NULL"
        )['count'];
        
        $stats['cancelled'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM orders WHERE status = 'cancelled'"
        )['count'];
        
        $stats['total_points'] = $this->db->fetch(
            "SELECT COALESCE(SUM(total_points), 0) as total FROM orders WHERE status = 'completed'"
        )['total'];
        
        return $stats;
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