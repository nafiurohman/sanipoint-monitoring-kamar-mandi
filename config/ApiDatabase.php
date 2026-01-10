<?php
class ApiDatabase {
    private static $instance = null;
    private $pdo = null;
    
    private function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host=" . API_DB_HOST . ";dbname=" . API_DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => API_TIMEOUT
            ];
            
            $this->pdo = new PDO($dsn, API_DB_USER, API_DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('API Database connection failed: ' . $e->getMessage());
            apiError('Database connection failed');
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
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('API Query error: ' . $e->getMessage());
            apiError('Database query failed');
        }
    }
    
    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('API Query error: ' . $e->getMessage());
            apiError('Database query failed');
        }
    }
    
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('API Execute error: ' . $e->getMessage());
            apiError('Database execute failed');
        }
    }
    
    public function insert($table, $data) {
        if (!isset($data['id'])) {
            $data['id'] = $this->generateUUID();
        }
        
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            return $data['id'];
        } catch (PDOException $e) {
            error_log('API Insert error: ' . $e->getMessage());
            apiError('Database insert failed');
        }
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach ($data as $key => $value) {
            $setParts[] = "$key = :$key";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE $table SET $setClause WHERE $where";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $allParams = array_merge($data, $whereParams);
            $stmt->execute($allParams);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('API Update error: ' . $e->getMessage());
            apiError('Database update failed');
        }
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
?>