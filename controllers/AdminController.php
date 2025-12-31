<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/BathroomModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/SensorModel.php';
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../core/Security.php';

class AdminController {
    private $auth;
    private $db;
    
    public function __construct() {
        console_log('🔐 AdminController initialized');
        $this->auth = new Auth();
        $this->auth->requireRole('admin');
        $this->db = Database::getInstance();
        console_log('✅ Admin authentication verified');
    }
    
    public function dashboard() {
        console_log('📊 Admin Dashboard accessed');
        $bathroomModel = new BathroomModel();
        $userModel = new UserModel();
        
        $stats = [
            'total_bathrooms' => $bathroomModel->count(),
            'active_employees' => $userModel->countActiveEmployees(),
            'cleaning_today' => $bathroomModel->getCleaningCountToday(),
            'points_distributed_today' => $userModel->getPointsDistributedToday()
        ];
        
        $bathrooms = $bathroomModel->getAllWithStatus();
        $recent_activities = $bathroomModel->getRecentActivities(10);
        
        $this->render('admin/dashboard', compact('stats', 'bathrooms', 'recent_activities'));
    }
    
    public function karyawan() {
        $userModel = new UserModel();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleKaryawanPost($userModel);
            return;
        }
        
        $employees = $userModel->getAllEmployees();
        $this->render('admin/karyawan', compact('employees'));
    }
    
    public function kamarMandi() {
        $bathroomModel = new BathroomModel();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleBathroomPost($bathroomModel);
            return;
        }
        
        $bathrooms = $bathroomModel->getAll();
        $this->render('admin/kamar-mandi', compact('bathrooms'));
    }
    
    public function produk() {
        $productModel = new ProductModel();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProductPost($productModel);
            return;
        }
        
        $products = $productModel->getAllWithCategories();
        $categories = $productModel->getCategories();
        $this->render('admin/produk', compact('products', 'categories'));
    }
    
    public function sensor() {
        $sensorModel = new SensorModel();
        $bathroomModel = new BathroomModel();
        
        $sensors = $sensorModel->getAllWithBathrooms();
        $bathrooms = $bathroomModel->getAll();
        $sensor_data = $sensorModel->getRecentData();
        
        $this->render('admin/sensor', compact('sensors', 'bathrooms', 'sensor_data'));
    }
    
    public function laporan() {
        $bathroomModel = new BathroomModel();
        $userModel = new UserModel();
        
        $cleaning_stats = $bathroomModel->getCleaningStats();
        $employee_performance = $userModel->getEmployeePerformance();
        $point_distribution = $userModel->getPointDistribution();
        
        $this->render('admin/laporan', compact('cleaning_stats', 'employee_performance', 'point_distribution'));
    }
    
    public function laporanPdf() {
        require_once 'core/PDFReportGenerator.php';
        
        $generator = new PDFReportGenerator();
        $pdf = $generator->generateEmployeeReport();
        
        $filename = 'Laporan_Performa_Karyawan_' . date('Y-m-d') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }
    
    public function transaksi() {
        $orderModel = new OrderModel();
        $userModel = new UserModel();
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
            if ($_GET['action'] === 'detail') {
                $this->handleOrderDetail($orderModel);
                return;
            }
        }
        
        // Get filters
        $status = $_GET['status'] ?? '';
        $date = $_GET['date'] ?? '';
        $employee = $_GET['employee'] ?? '';
        
        // Get orders with filters
        $orders = $orderModel->getAllOrdersWithFilters($status, $date, $employee);
        $employees = $userModel->getAllEmployees();
        $stats = $orderModel->getTransactionStats();
        
        $this->render('admin/transaksi', compact('orders', 'employees', 'stats'));
    }
    
    private function handleKaryawanPost($userModel) {
        $action = $_POST['action'] ?? '';
        $csrf_token = $_POST['csrf_token'] ?? '';
        
        if (!Security::validateCSRFToken($csrf_token)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token']);
            return;
        }
        
        switch ($action) {
            case 'create':
                $data = Security::sanitizeInput($_POST);
                $result = $userModel->createEmployee($data);
                
                if ($result['success'] && isset($result['credentials'])) {
                    $_SESSION['new_employee_credentials'] = $result['credentials'];
                }
                
                $this->jsonResponse($result);
                break;
            case 'update':
                $id = $_POST['id'] ?? '';
                $data = Security::sanitizeInput($_POST);
                $result = $userModel->updateEmployee($id, $data);
                $this->jsonResponse($result);
                break;
            case 'delete':
                $id = $_POST['id'] ?? '';
                $result = $userModel->deleteEmployee($id);
                $this->jsonResponse($result);
                break;
            case 'toggle_status':
                $id = $_POST['id'] ?? '';
                $result = $userModel->toggleEmployeeStatus($id);
                $this->jsonResponse($result);
                break;
            case 'get_employee':
                $id = $_POST['id'] ?? '';
                $employee = $userModel->getEmployeeById($id);
                if ($employee) {
                    $this->jsonResponse(['success' => true, 'employee' => $employee]);
                } else {
                    $this->jsonResponse(['success' => false, 'message' => 'Employee not found']);
                }
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Unknown action']);
        }
    }
    
    private function handleBathroomPost($bathroomModel) {
        $action = $_POST['action'] ?? '';
        $csrf_token = $_POST['csrf_token'] ?? '';
        
        if (!Security::validateCSRFToken($csrf_token)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token']);
            return;
        }
        
        switch ($action) {
            case 'create':
                $data = Security::sanitizeInput($_POST);
                $result = $bathroomModel->create($data);
                $this->jsonResponse($result);
                break;
            case 'update':
                $id = $_POST['id'] ?? '';
                $data = Security::sanitizeInput($_POST);
                $result = $bathroomModel->update($id, $data);
                $this->jsonResponse($result);
                break;
            case 'delete':
                $id = $_POST['id'] ?? '';
                $result = $bathroomModel->delete($id);
                $this->jsonResponse($result);
                break;
            case 'get_bathroom':
                $id = $_POST['id'] ?? '';
                $bathroom = $bathroomModel->getById($id);
                if ($bathroom) {
                    $this->jsonResponse(['success' => true, 'bathroom' => $bathroom]);
                } else {
                    $this->jsonResponse(['success' => false, 'message' => 'Bathroom not found']);
                }
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Unknown action']);
        }
    }
    
    private function handleProductPost($productModel) {
        $action = $_POST['action'] ?? '';
        $csrf_token = $_POST['csrf_token'] ?? '';
        
        if (!Security::validateCSRFToken($csrf_token)) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid token']);
            return;
        }
        
        switch ($action) {
            case 'create':
                $data = Security::sanitizeInput($_POST);
                $result = $productModel->create($data);
                $this->jsonResponse($result);
                break;
            case 'update':
                $id = $_POST['id'] ?? '';
                $data = Security::sanitizeInput($_POST);
                $result = $productModel->update($id, $data);
                $this->jsonResponse($result);
                break;
            case 'delete':
                $id = $_POST['id'] ?? '';
                $result = $productModel->delete($id);
                $this->jsonResponse($result);
                break;
            case 'get_product':
                $id = $_POST['id'] ?? '';
                $product = $productModel->getById($id);
                if ($product) {
                    $this->jsonResponse(['success' => true, 'product' => $product]);
                } else {
                    $this->jsonResponse(['success' => false, 'message' => 'Product not found']);
                }
                break;
            case 'toggle_status':
                $id = $_POST['id'] ?? '';
                $result = $productModel->toggleStatus($id);
                $this->jsonResponse($result);
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Unknown action']);
        }
    }
    
    private function handleOrderDetail($orderModel) {
        $order_id = $_GET['order_id'] ?? '';
        $order = $orderModel->getOrderDetails($order_id);
        
        if (!$order) {
            $this->jsonResponse(['success' => false, 'message' => 'Order not found']);
            return;
        }
        
        $html = $this->renderOrderDetailHtml($order);
        $this->jsonResponse(['success' => true, 'html' => $html]);
    }
    
    private function renderOrderDetailHtml($order) {
        ob_start();
        ?>
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">No. Order</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($order['order_number']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Karyawan</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($order['user_name']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Order</label>
                    <p class="text-sm text-gray-900"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <p class="text-sm text-gray-900">
                        <?php 
                        $status = trim($order['status'] ?? 'completed');
                        $is_received = !empty($order['received_at']);
                        
                        if ($is_received): ?>
                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                <i class="fas fa-check-circle mr-1"></i>Sudah Diterima
                            </span>
                        <?php elseif ($status === 'completed'): ?>
                            <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                <i class="fas fa-clock mr-1"></i>Menunggu Pengambilan
                            </span>
                        <?php elseif ($status === 'cancelled'): ?>
                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                <i class="fas fa-times-circle mr-1"></i>Dibatalkan
                            </span>
                        <?php else: ?>
                            <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                <i class="fas fa-question-circle mr-1"></i><?= ucfirst($status) ?>
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Items</label>
                <div class="bg-gray-50 rounded-lg p-4">
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                            <div>
                                <p class="font-medium"><?= htmlspecialchars($item['product_name']) ?></p>
                                <p class="text-sm text-gray-600"><?= number_format($item['points_per_item']) ?> pts × <?= $item['quantity'] ?></p>
                            </div>
                            <p class="font-medium"><?= number_format($item['total_points']) ?> pts</p>
                        </div>
                    <?php endforeach; ?>
                    <div class="flex justify-between items-center pt-2 font-bold">
                        <span>Total:</span>
                        <span><?= number_format($order['total_points']) ?> pts</span>
                    </div>
                </div>
            </div>
            
            <?php if ($order['received_at']): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Diterima</label>
                    <p class="text-sm text-gray-900"><?= date('d/m/Y H:i', strtotime($order['received_at'])) ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($order['cancelled_at']): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Dibatalkan</label>
                    <p class="text-sm text-red-600"><?= date('d/m/Y H:i', strtotime($order['cancelled_at'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
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