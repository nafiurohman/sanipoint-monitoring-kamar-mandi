<?php
require_once 'core/Auth.php';
require_once 'models/PointModel.php';
require_once 'models/ProductModel.php';
require_once 'models/OrderModel.php';
require_once 'models/BathroomModel.php';
require_once 'models/UserModel.php';

class KaryawanController {
    private $auth;
    private $db;
    private $user;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->auth->requireRole('karyawan');
        $this->db = Database::getInstance();
        $this->user = $this->auth->getUser();
    }
    
    public function dashboard() {
        $pointModel = new PointModel();
        $orderModel = new OrderModel();
        $bathroomModel = new BathroomModel();
        
        $points = $pointModel->getUserPoints($this->user['id']);
        $recent_transactions = $pointModel->getRecentTransactions($this->user['id'], 5);
        $recent_orders = $orderModel->getUserOrders($this->user['id'], 5);
        $cleaning_stats = $pointModel->getCleaningStats($this->user['id']);
        $bathrooms = $bathroomModel->getAllWithStatus();
        
        $this->render('karyawan/dashboard', compact('points', 'recent_transactions', 'recent_orders', 'cleaning_stats', 'bathrooms'));
    }
    
    public function poin() {
        $pointModel = new PointModel();
        
        $points = $pointModel->getUserPoints($this->user['id']);
        $transactions = $pointModel->getAllTransactions($this->user['id']);
        
        $this->render('karyawan/poin', compact('points', 'transactions'));
    }
    
    public function riwayat() {
        $pointModel = new PointModel();
        $orderModel = new OrderModel();
        
        $transactions = $pointModel->getAllTransactions($this->user['id']);
        $orders = $orderModel->getUserOrders($this->user['id']);
      //  $cleaning_history = $pointModel->getCleaningHistory($this->user['id']);
        
        $this->render('karyawan/riwayat', compact('transactions', 'orders', 'cleaning_history'));
    }
    
    public function marketplace() {
        $productModel = new ProductModel();
        $orderModel = new OrderModel();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleOrder($productModel, $orderModel);
            return;
        }
        
        $products = $productModel->getActiveProducts();
        $categories = $productModel->getCategories();
        $user_points = (new PointModel())->getUserPoints($this->user['id']);
        
        $this->render('karyawan/marketplace', compact('products', 'categories', 'user_points'));
    }
    
    public function transfer() {
        // Check if user has PIN
        if (empty($this->user['pin'])) {
            header('Location: /sanipoint/karyawan/pengaturan?setup_pin=1');
            exit;
        }
        
        $pointModel = new PointModel();
        $userModel = new UserModel();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePointTransfer($pointModel);
            return;
        }
        
        $points = $pointModel->getUserPoints($this->user['id']);
        $transfer_history = $pointModel->getTransferHistory($this->user['id']);
        $employees = $userModel->getAllEmployees();
        
        $this->render('karyawan/transfer', compact('points', 'transfer_history', 'employees'));
    }
    
    public function monitoring() {
        $bathroomModel = new BathroomModel();
        
        $bathrooms = $bathroomModel->getAllWithStatus();
        $cleaning_logs = $bathroomModel->getCleaningLogs($this->user['id']);
        $sensor_data = $bathroomModel->getRecentSensorData();
        
        $this->render('karyawan/monitoring', compact('bathrooms', 'cleaning_logs', 'sensor_data'));
    }
    
    public function pengaturan() {
        $userModel = new UserModel();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSettings($userModel);
            return;
        }
        
        $user = $userModel->getUserById($this->user['id']);
        $setup_pin = isset($_GET['setup_pin']) ? true : false;
        
        $this->render('karyawan/pengaturan', compact('user', 'setup_pin'));
    }
    
    private function handlePointTransfer($pointModel) {
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCSRFToken($csrf_token)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token']);
            return;
        }
        
        $pin = $_POST['pin'] ?? '';
        $to_user_id = $_POST['to_user_id'] ?? '';
        $amount = (int)($_POST['amount'] ?? 0);
        $description = Security::sanitizeInput($_POST['description'] ?? '');
        
        // Verify PIN
        if (!password_verify($pin, $this->user['pin'])) {
            $this->jsonResponse(['success' => false, 'message' => 'PIN salah']);
            return;
        }
        
        $result = $pointModel->transferPoints($this->user['id'], $to_user_id, $amount, $description);
        $this->jsonResponse($result);
    }
    
    private function handleOrder($productModel, $orderModel) {
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCSRFToken($csrf_token)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token']);
            return;
        }
        
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'checkout':
                $cart_items = json_decode($_POST['cart_items'], true);
                $result = $orderModel->createOrder($this->user['id'], $cart_items);
                $this->jsonResponse($result);
                break;
        }
    }
    
    private function handleSettings($userModel) {
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCSRFToken($csrf_token)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token']);
            return;
        }
        
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'update_profile':
                $data = [
                    'full_name' => Security::sanitizeInput($_POST['full_name'] ?? ''),
                    'email' => Security::sanitizeInput($_POST['email'] ?? ''),
                    'phone' => Security::sanitizeInput($_POST['phone'] ?? '')
                ];
                $result = $userModel->updateProfile($this->user['id'], $data);
                $this->jsonResponse($result);
                break;
                
            case 'create_pin':
            case 'change_pin':
                $current_password = $_POST['current_password'] ?? '';
                $new_pin = $_POST['new_pin'] ?? '';
                $confirm_pin = $_POST['confirm_pin'] ?? '';
                
                // Verify password
                if (!password_verify($current_password, $this->user['password'])) {
                    $this->jsonResponse(['success' => false, 'message' => 'Password salah']);
                    return;
                }
                
                // Verify PIN match
                if ($new_pin !== $confirm_pin) {
                    $this->jsonResponse(['success' => false, 'message' => 'PIN tidak cocok']);
                    return;
                }
                
                // Validate PIN format (6 digits)
                if (!preg_match('/^\d{6}$/', $new_pin)) {
                    $this->jsonResponse(['success' => false, 'message' => 'PIN harus 6 digit angka']);
                    return;
                }
                
                $result = $userModel->updatePin($this->user['id'], $new_pin);
                $this->jsonResponse($result);
                break;
                
            case 'change_password':
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                // Verify current password
                if (!password_verify($current_password, $this->user['password'])) {
                    $this->jsonResponse(['success' => false, 'message' => 'Password lama salah']);
                    return;
                }
                
                // Verify new password match
                if ($new_password !== $confirm_password) {
                    $this->jsonResponse(['success' => false, 'message' => 'Password baru tidak cocok']);
                    return;
                }
                
                // Validate password strength
                if (strlen($new_password) < 6) {
                    $this->jsonResponse(['success' => false, 'message' => 'Password minimal 6 karakter']);
                    return;
                }
                
                $result = $userModel->updatePassword($this->user['id'], $new_password);
                $this->jsonResponse($result);
                break;
        }
    }
    
    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    private function render($view, $data = []) {
        extract($data);
        include "views/{$view}.php";
    }
}
?>