<?php
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Auth.php';
require_once '../models/ProductModel.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

Auth::requireRole('admin');

$productModel = new ProductModel();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'category' => trim($_POST['category'] ?? ''),
                'point_price' => (int)($_POST['point_price'] ?? 0),
                'stock' => (int)($_POST['stock'] ?? 0),
                'image_url' => trim($_POST['image_url'] ?? '')
            ];
            $result = $productModel->create($data);
            echo json_encode($result);
            break;
            
        case 'update':
            $id = $_POST['id'] ?? '';
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'category' => trim($_POST['category'] ?? ''),
                'point_price' => (int)($_POST['point_price'] ?? 0),
                'stock' => (int)($_POST['stock'] ?? 0),
                'image_url' => trim($_POST['image_url'] ?? '')
            ];
            $result = $productModel->update($id, $data);
            echo json_encode($result);
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? '';
            $result = $productModel->delete($id);
            echo json_encode($result);
            break;
            
        case 'toggle_status':
            $id = $_POST['id'] ?? '';
            $result = $productModel->toggleStatus($id);
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'get') {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            exit;
        }
        
        try {
            $product = $productModel->getById($id);
            if ($product) {
                echo json_encode(['success' => true, 'data' => $product]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>