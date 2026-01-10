<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once '../../config/config.php';
require_once '../../core/Database.php';
require_once '../../core/Auth.php';

Auth::requireRole('admin');

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $point_price = (int)($_POST['point_price'] ?? 0);
            $stock = (int)($_POST['stock'] ?? 0);
            $image_url = trim($_POST['image_url'] ?? '');
            
            if (empty($name) || empty($category) || $point_price <= 0) {
                echo json_encode(['success' => false, 'message' => 'Nama, kategori, dan harga poin wajib diisi']);
                exit;
            }
            
            try {
                // Get or create category
                $cat = $db->fetch("SELECT id FROM product_categories WHERE name = ?", [$category]);
                if (!$cat) {
                    $category_id = $db->insert('product_categories', [
                        'id' => uniqid('cat_', true),
                        'name' => $category,
                        'is_active' => 1
                    ]);
                } else {
                    $category_id = $cat['id'];
                }
                
                $product_id = $db->insert('products', [
                    'id' => uniqid('prd_', true),
                    'category_id' => $category_id,
                    'name' => $name,
                    'description' => $description,
                    'price_points' => $point_price,
                    'stock' => $stock,
                    'image_url' => $image_url,
                    'is_active' => 1
                ]);
                
                echo json_encode(['success' => true, 'message' => 'Produk berhasil ditambahkan']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Gagal menambahkan produk: ' . $e->getMessage()]);
            }
            break;
            
        case 'update':
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $point_price = (int)($_POST['point_price'] ?? 0);
            $stock = (int)($_POST['stock'] ?? 0);
            $image_url = trim($_POST['image_url'] ?? '');
            
            if (empty($id) || empty($name) || empty($category) || $point_price <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID, nama, kategori, dan harga poin wajib diisi']);
                exit;
            }
            
            try {
                // Get or create category
                $cat = $db->fetch("SELECT id FROM product_categories WHERE name = ?", [$category]);
                if (!$cat) {
                    $category_id = $db->insert('product_categories', [
                        'id' => uniqid('cat_', true),
                        'name' => $category,
                        'is_active' => 1
                    ]);
                } else {
                    $category_id = $cat['id'];
                }
                
                $db->execute(
                    "UPDATE products SET category_id = ?, name = ?, description = ?, price_points = ?, stock = ?, image_url = ? WHERE id = ?",
                    [$category_id, $name, $description, $point_price, $stock, $image_url, $id]
                );
                
                echo json_encode(['success' => true, 'message' => 'Produk berhasil diperbarui']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui produk: ' . $e->getMessage()]);
            }
            break;
            
        case 'toggle_status':
            $id = $_POST['id'] ?? '';
            
            try {
                $product = $db->fetch("SELECT is_active FROM products WHERE id = ?", [$id]);
                if (!$product) {
                    echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
                    exit;
                }
                
                $newStatus = $product['is_active'] ? 0 : 1;
                $db->execute("UPDATE products SET is_active = ? WHERE id = ?", [$newStatus, $id]);
                
                $message = $newStatus ? 'Produk diaktifkan' : 'Produk dinonaktifkan';
                echo json_encode(['success' => true, 'message' => $message]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Gagal mengubah status produk: ' . $e->getMessage()]);
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? '';
            
            try {
                $db->execute("DELETE FROM products WHERE id = ?", [$id]);
                echo json_encode(['success' => true, 'message' => 'Produk berhasil dihapus']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus produk: ' . $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    $id = $_GET['id'] ?? '';
    
    try {
        $product = $db->fetch("
            SELECT p.*, pc.name as category 
            FROM products p 
            LEFT JOIN product_categories pc ON p.category_id = pc.id 
            WHERE p.id = ?
        ", [$id]);
        
        if ($product) {
            echo json_encode(['success' => true, 'data' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal mengambil data produk: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
}
?>