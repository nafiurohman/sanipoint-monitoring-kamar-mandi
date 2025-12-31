<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../models/PointModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/BathroomModel.php';
require_once __DIR__ . '/../models/UserModel.php';

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
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'confirm_received') {
                $this->handleConfirmReceived($orderModel);
                return;
            } elseif ($action === 'cancel_order') {
                $this->handleCancelOrder($orderModel);
                return;
            }
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_order_detail') {
            $this->handleGetOrderDetail($orderModel);
            return;
        }
        
        $transactions = $pointModel->getAllTransactions($this->user['id']);
        $orders = $orderModel->getUserOrders($this->user['id']);
        $cleaning_history = $pointModel->getCleaningHistory($this->user['id']);
        
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
        $pointModel = new PointModel();
        $userModel = new UserModel();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'transfer_points') {
                $this->handlePointTransfer($pointModel);
                return;
            }
        }
        
        // Check if user has PIN - if not, redirect to setup
        if (empty($this->user['pin'])) {
            $setup_pin = true;
            $points = $pointModel->getUserPoints($this->user['id']);
            $transfer_history = [];
            $employees = $userModel->getAllEmployees();
            
            $this->render('karyawan/transfer', compact('points', 'transfer_history', 'employees', 'setup_pin', 'user'));
            return;
        }
        
        $points = $pointModel->getUserPoints($this->user['id']);
        $transfer_history = $pointModel->getTransferHistory($this->user['id']);
        $employees = $userModel->getAllEmployees();
        $setup_pin = false;
        
        $this->render('karyawan/transfer', compact('points', 'transfer_history', 'employees', 'setup_pin'));
    }
    
    public function monitoring() {
        $bathroomModel = new BathroomModel();
        
        $bathrooms = $bathroomModel->getIoTStatus();
        $cleaning_logs = $bathroomModel->getCleaningLogs($this->user['id']);
        $sensor_data = $bathroomModel->getLatestSensorReadings();
        $usage_logs = $bathroomModel->getUsageLogs(10);
        
        $this->render('karyawan/monitoring', compact('bathrooms', 'cleaning_logs', 'sensor_data', 'usage_logs'));
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
        error_log('=== HANDLE POINT TRANSFER DEBUG ===');
        error_log('POST data: ' . print_r($_POST, true));
        
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCSRFToken($csrf_token)) {
            error_log('CSRF validation failed');
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token']);
            return;
        }
        
        $pin = $_POST['pin'] ?? '';
        $to_user_id = $_POST['to_user_id'] ?? '';
        $amount = (int)($_POST['amount'] ?? 0);
        $description = Security::sanitizeInput($_POST['description'] ?? '');
        
        error_log('PIN provided: ' . (!empty($pin) ? 'YES' : 'NO'));
        error_log('To user ID: ' . $to_user_id);
        error_log('Amount: ' . $amount);
        error_log('Description: ' . $description);
        
        // Get fresh user data from database to ensure we have latest PIN
        $freshUser = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$this->user['id']]);
        if (!$freshUser) {
            error_log('Fresh user data not found');
            $this->jsonResponse(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        error_log('Fresh user PIN hash: ' . substr($freshUser['pin'] ?? 'NULL', 0, 20) . '...');
        
        // Check if user has PIN
        if (empty($freshUser['pin'])) {
            error_log('User has no PIN set');
            $this->jsonResponse(['success' => false, 'message' => 'PIN belum dibuat. Silakan buat PIN terlebih dahulu.']);
            return;
        }
        
        // Verify PIN with fresh user data
        $pinVerifyResult = password_verify($pin, $freshUser['pin']);
        error_log('PIN verification result: ' . ($pinVerifyResult ? 'SUCCESS' : 'FAILED'));
        error_log('Input PIN: ' . $pin);
        error_log('Stored PIN hash: ' . $freshUser['pin']);
        
        if (!$pinVerifyResult) {
            error_log('PIN verification FAILED');
            $this->jsonResponse(['success' => false, 'message' => 'PIN salah']);
            return;
        }
        error_log('PIN verification SUCCESS');
        
        // Validate amount
        if ($amount <= 0) {
            error_log('Invalid amount: ' . $amount);
            $this->jsonResponse(['success' => false, 'message' => 'Jumlah poin harus lebih dari 0']);
            return;
        }
        
        // Validate recipient
        if (empty($to_user_id)) {
            error_log('No recipient selected');
            $this->jsonResponse(['success' => false, 'message' => 'Pilih karyawan tujuan']);
            return;
        }
        
        $result = $pointModel->transferPoints($this->user['id'], $to_user_id, $amount, $description);
        error_log('Transfer result: ' . print_r($result, true));
        $this->jsonResponse($result);
    }
    
    private function handleOrder($productModel, $orderModel) {
        header('Content-Type: application/json');
        
        try {
            $csrf_token = $_POST['csrf_token'] ?? '';
            if (!Security::validateCSRFToken($csrf_token)) {
                throw new Exception('Invalid token');
            }
            
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'checkout':
                    $cart_items = json_decode($_POST['cart_items'], true);
                    if (!$cart_items) {
                        throw new Exception('Invalid cart data');
                    }
                    $result = $orderModel->createOrder($this->user['id'], $cart_items);
                    break;
                case 'cancel_order':
                    $order_id = $_POST['order_id'] ?? '';
                    if (!$order_id) {
                        throw new Exception('Order ID required');
                    }
                    $result = $orderModel->cancelOrder($order_id, $this->user['id']);
                    break;
                default:
                    throw new Exception('Invalid action');
            }
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    private function handleSettings($userModel) {
        error_log('=== HANDLE SETTINGS DEBUG ===');
        error_log('POST data: ' . print_r($_POST, true));
        
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCSRFToken($csrf_token)) {
            error_log('CSRF validation failed');
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token']);
            return;
        }
        
        $action = $_POST['action'] ?? '';
        error_log('Action: ' . $action);
        
        switch ($action) {
            case 'update_profile':
                error_log('Processing update_profile');
                $data = [
                    'full_name' => Security::sanitizeInput($_POST['full_name'] ?? ''),
                    'email' => Security::sanitizeInput($_POST['email'] ?? ''),
                    'phone' => Security::sanitizeInput($_POST['phone'] ?? '')
                ];
                $result = $userModel->updateProfile($this->user['id'], $data);
                error_log('Profile update result: ' . print_r($result, true));
                $this->jsonResponse($result);
                break;
                
            case 'create_pin':
            case 'change_pin':
                error_log('Processing PIN action: ' . $action);
                $current_password = $_POST['current_password'] ?? '';
                $new_pin = $_POST['new_pin'] ?? '';
                $confirm_pin = $_POST['confirm_pin'] ?? '';
                
                error_log('Current password provided: ' . (!empty($current_password) ? 'YES' : 'NO'));
                error_log('New PIN provided: ' . (!empty($new_pin) ? 'YES' : 'NO'));
                error_log('PIN length: ' . strlen($new_pin));
                error_log('User password hash: ' . substr($this->user['password'], 0, 20) . '...');
                
                // Verify password
                if (!password_verify($current_password, $this->user['password'])) {
                    error_log('Password verification FAILED');
                    $this->jsonResponse(['success' => false, 'message' => 'Password salah']);
                    return;
                }
                error_log('Password verification SUCCESS');
                
                // Verify PIN match
                if ($new_pin !== $confirm_pin) {
                    error_log('PIN match FAILED');
                    $this->jsonResponse(['success' => false, 'message' => 'PIN tidak cocok']);
                    return;
                }
                error_log('PIN match SUCCESS');
                
                // Validate PIN format (6 digits)
                if (!preg_match('/^\d{6}$/', $new_pin)) {
                    error_log('PIN format FAILED: ' . $new_pin);
                    $this->jsonResponse(['success' => false, 'message' => 'PIN harus 6 digit angka']);
                    return;
                }
                error_log('PIN format SUCCESS');
                
                $result = $userModel->updatePin($this->user['id'], $new_pin);
                error_log('PIN update result: ' . print_r($result, true));
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
    
    private function handleGetOrderDetail($orderModel) {
        $order_id = $_GET['order_id'] ?? '';
        if (empty($order_id)) {
            $this->jsonResponse(['success' => false, 'message' => 'Order ID required']);
            return;
        }
        
        $order = $orderModel->getOrderDetails($order_id, $this->user['id']);
        if (!$order) {
            $this->jsonResponse(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        $this->jsonResponse(['success' => true, 'order' => $order]);
    }
    
    private function handleCancelOrder($orderModel) {
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCSRFToken($csrf_token)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token']);
            return;
        }
        
        $order_id = $_POST['order_id'] ?? '';
        $result = $orderModel->cancelOrder($order_id, $this->user['id']);
        $this->jsonResponse($result);
    }
    
    private function handleConfirmReceived($orderModel) {
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCSRFToken($csrf_token)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token']);
            return;
        }
        
        $order_id = $_POST['order_id'] ?? '';
        $result = $orderModel->confirmReceived($order_id, $this->user['id']);
        $this->jsonResponse($result);
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