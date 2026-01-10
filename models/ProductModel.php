<?php
class ProductModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM products WHERE is_active = 1 ORDER BY name");
    }
    
    public function getAllWithCategories() {
        $sql = "SELECT p.*, pc.name as category_name 
                FROM products p 
                JOIN product_categories pc ON p.category_id = pc.id 
                WHERE p.is_active = 1 
                ORDER BY pc.name, p.name";
        return $this->db->fetchAll($sql);
    }
    
    public function getActiveProducts() {
        return $this->db->fetchAll("SELECT * FROM products WHERE is_active = 1 AND stock > 0 ORDER BY name");
    }
    
    public function getCategories() {
        return $this->db->fetchAll("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY name");
    }
    
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM products WHERE id = ?", [$id]);
    }
    
    public function create($data) {
        $validation = Security::validateInput($data, [
            'name' => ['required' => true, 'min' => 3, 'max' => 200],
            'category' => ['required' => true, 'min' => 3, 'max' => 50],
            'point_price' => ['required' => true, 'numeric' => true],
            'stock' => ['required' => true, 'numeric' => true]
        ]);
        
        if (!empty($validation)) {
            return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation];
        }
        
        try {
            $this->db->insert('products', [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'],
                'point_price' => (int)$data['point_price'],
                'stock' => (int)$data['stock'],
                'image_url' => $data['image_url'] ?? null
            ]);
            return ['success' => true, 'message' => 'Product created successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create product'];
        }
    }
    
    public function update($id, $data) {
        $validation = Security::validateInput($data, [
            'name' => ['required' => true, 'min' => 3, 'max' => 200],
            'category' => ['required' => true, 'min' => 3, 'max' => 50],
            'point_price' => ['required' => true, 'numeric' => true],
            'stock' => ['required' => true, 'numeric' => true]
        ]);
        
        if (!empty($validation)) {
            return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation];
        }
        
        try {
            $this->db->update('products', [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category' => $data['category'],
                'point_price' => (int)$data['point_price'],
                'stock' => (int)$data['stock'],
                'image_url' => $data['image_url'] ?? null
            ], 'id = ?', [$id]);
            return ['success' => true, 'message' => 'Product updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update product'];
        }
    }
    
    public function updateStock($id, $quantity) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            $product = $this->db->fetch("SELECT * FROM products WHERE id = ? FOR UPDATE", [$id]);
            if (!$product) {
                throw new Exception('Product not found');
            }
            
            $new_stock = $product['stock'] - $quantity;
            if ($new_stock < 0) {
                throw new Exception('Insufficient stock');
            }
            
            $this->db->update('products', ['stock' => $new_stock], 'id = ?', [$id]);
            $this->db->getConnection()->commit();
            
            return ['success' => true, 'new_stock' => $new_stock];
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function restoreStock($id, $quantity) {
        $product = $this->getById($id);
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        $new_stock = $product['stock'] + $quantity;
        
        try {
            $this->db->update('products', ['stock' => $new_stock], 'id = ?', [$id]);
            return ['success' => true, 'new_stock' => $new_stock];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to restore stock'];
        }
    }
    
    public function delete($id) {
        try {
            // Check if product has any order items
            $result = $this->db->fetch(
                "SELECT COUNT(*) as count FROM order_items WHERE product_id = ?", 
                [$id]
            );
            $hasOrders = $result ? (int)$result['count'] > 0 : false;
            
            if ($hasOrders) {
                // Soft delete - set is_active to false
                $this->db->update('products', ['is_active' => 0], 'id = ?', [$id]);
                return ['success' => true, 'message' => 'Product deactivated successfully'];
            } else {
                // Hard delete if no orders
                $this->db->execute("DELETE FROM products WHERE id = ?", [$id]);
                return ['success' => true, 'message' => 'Product deleted successfully'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to delete product'];
        }
    }
    
    public function toggleStatus($id) {
        try {
            $product = $this->getById($id);
            if (!$product) {
                return ['success' => false, 'message' => 'Product not found'];
            }
            
            $newStatus = $product['is_active'] ? 0 : 1;
            $this->db->update('products', ['is_active' => $newStatus], 'id = ?', [$id]);
            
            $message = $newStatus ? 'Product activated successfully' : 'Product deactivated successfully';
            return ['success' => true, 'message' => $message];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update product status'];
        }
    }
}
?>