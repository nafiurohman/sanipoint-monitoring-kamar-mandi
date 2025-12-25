<?php
require_once 'core/Auth.php';
require_once 'models/BathroomModel.php';
require_once 'models/UserModel.php';
require_once 'models/ProductModel.php';
require_once 'models/SensorModel.php';

class AdminController {
    private $auth;
    private $db;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->auth->requireRole('admin');
        $this->db = Database::getInstance();
    }
    
    public function dashboard() {
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
                $this->jsonResponse($result);
                break;
            case 'update':
                $id = (int)$_POST['id'];
                $data = Security::sanitizeInput($_POST);
                $result = $userModel->updateEmployee($id, $data);
                $this->jsonResponse($result);
                break;
            case 'delete':
                $id = (int)$_POST['id'];
                $result = $userModel->deleteEmployee($id);
                $this->jsonResponse($result);
                break;
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
                $id = (int)$_POST['id'];
                $data = Security::sanitizeInput($_POST);
                $result = $bathroomModel->update($id, $data);
                $this->jsonResponse($result);
                break;
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
                $id = (int)$_POST['id'];
                $data = Security::sanitizeInput($_POST);
                $result = $productModel->update($id, $data);
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