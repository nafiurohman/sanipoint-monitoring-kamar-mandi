<?php
require_once 'models/SensorModel.php';
require_once 'models/BathroomModel.php';
require_once 'models/PointModel.php';

class ApiController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        header('Content-Type: application/json');
    }
    
    public function sensorData() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
            return;
        }
        
        $sensor_code = $_POST['sensor_code'] ?? '';
        $value = (float)($_POST['value'] ?? 0);
        $unit = $_POST['unit'] ?? '';
        
        if (empty($sensor_code)) {
            $this->jsonResponse(['error' => 'Sensor code required'], 400);
            return;
        }
        
        $sensorModel = new SensorModel();
        $result = $sensorModel->logData($sensor_code, $value, $unit);
        
        if ($result['success']) {
            // Check if bathroom needs cleaning based on sensor data
            $this->checkBathroomStatus($result['sensor_id'], $value);
        }
        
        $this->jsonResponse($result);
    }
    
    public function rfidTap() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
            return;
        }
        
        $rfid_code = $_POST['rfid_code'] ?? '';
        $bathroom_id = (int)($_POST['bathroom_id'] ?? 0);
        
        if (empty($rfid_code) || !$bathroom_id) {
            $this->jsonResponse(['error' => 'RFID code and bathroom ID required'], 400);
            return;
        }
        
        // Find user by RFID code (assuming RFID is stored in employee_code)
        $user = $this->db->fetch("SELECT * FROM users WHERE employee_code = ? AND role = 'karyawan'", [$rfid_code]);
        
        if (!$user) {
            $this->jsonResponse(['error' => 'Invalid RFID code'], 400);
            return;
        }
        
        $bathroomModel = new BathroomModel();
        $result = $bathroomModel->handleRFIDTap($bathroom_id, $user['id'], $rfid_code);
        
        $this->jsonResponse($result);
    }
    
    public function realtimeStatus() {
        $bathroomModel = new BathroomModel();
        $sensorModel = new SensorModel();
        
        $bathrooms = $bathroomModel->getAllWithStatus();
        $sensor_data = $sensorModel->getLatestData();
        
        $this->jsonResponse([
            'bathrooms' => $bathrooms,
            'sensors' => $sensor_data,
            'timestamp' => time()
        ]);
    }
    
    private function checkBathroomStatus($sensor_id, $value) {
        // Get sensor info
        $sensor = $this->db->fetch("SELECT * FROM sensors WHERE id = ?", [$sensor_id]);
        if (!$sensor) return;
        
        $bathroomModel = new BathroomModel();
        
        if ($sensor['sensor_type'] === 'ir') {
            // IR sensor - update visitor count
            $bathroomModel->updateVisitorCount($sensor['bathroom_id'], (int)$value);
        } elseif ($sensor['sensor_type'] === 'mq135') {
            // Gas sensor - check air quality
            if ($value > 400) { // High pollution level
                $bathroomModel->updateStatus($sensor['bathroom_id'], 'needs_cleaning');
            }
        }
    }
    
    private function jsonResponse($data, $status = 200) {
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
}
?>