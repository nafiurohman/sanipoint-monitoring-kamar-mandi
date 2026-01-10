<?php
// API Configuration for SANIPOINT IoT System
date_default_timezone_set('Asia/Jakarta');

// Database development configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'sanipoint_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Database production configuration
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'beznwebi_beznlabs_storage_241204_250305');
// define('DB_USER', 'beznwebi_beznlabs_storage_241204_250305_user');
// define('DB_PASS', 'beznlabs_storage_241204_250305_pw');

// API settings - get from database or use defaults
define('IOT_GAS_LIMIT', 400);
define('IOT_MAX_VISITORS', 5);
define('IOT_POINTS_PER_CLEANING', 10);
define('ADMIN_RFID_CODES', ['B490FBB0', 'C6861BFF']);

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/api-errors.log');

// Database class for API
class Database {
    private static $instance = null;
    private $pdo = null;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database connection failed']);
            exit;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function fetch($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function execute($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function insert($table, $data) {
        if (!isset($data['id'])) {
            $data['id'] = $this->generateUUID();
        }
        
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return $data['id'];
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "$key = :$key";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE $table SET $setClause WHERE $where";
        $stmt = $this->pdo->prepare($sql);
        $allParams = array_merge($data, $whereParams);
        $stmt->execute($allParams);
        return $stmt->rowCount() > 0;
    }
    
    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

// Point Model for API
class PointModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function addPoints($user_id, $amount, $reference_type, $reference_id = null) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            $points = $this->db->fetch("SELECT * FROM points WHERE user_id = ?", [$user_id]);
            if (!$points) {
                $this->db->insert('points', [
                    'user_id' => $user_id,
                    'current_balance' => $amount,
                    'total_earned' => $amount,
                    'total_spent' => 0
                ]);
                $new_balance = $amount;
            } else {
                $new_balance = $points['current_balance'] + $amount;
                $this->db->update('points', [
                    'current_balance' => $new_balance,
                    'total_earned' => $points['total_earned'] + $amount
                ], 'user_id = ?', [$user_id]);
            }
            
            $this->db->insert('point_transactions', [
                'user_id' => $user_id,
                'transaction_type' => 'earned',
                'amount' => $amount,
                'balance_after' => $new_balance,
                'reference_type' => $reference_type,
                'reference_id' => $reference_id
            ]);
            
            $this->db->getConnection()->commit();
            return ['success' => true, 'new_balance' => $new_balance];
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>