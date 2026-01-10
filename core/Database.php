<?php
class Database {
    private static $instance = null;
    private $pdo = null;
    
    private function __construct() {
        $this->initLocalDatabase();
    }
    
    private function initLocalDatabase() {
        $maxRetries = 3;
        $retryCount = 0;
        
        while ($retryCount < $maxRetries) {
            try {
                $host = DB_HOST;
                $dbname = DB_NAME;
                $username = DB_USER;
                $password = DB_PASS;
                
                $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ];
                
                $this->pdo = new PDO($dsn, $username, $password, $options);
                return; // Success, exit retry loop
                
            } catch (PDOException $e) {
                $retryCount++;
                if ($retryCount >= $maxRetries) {
                    error_log('Database connection failed after ' . $maxRetries . ' attempts: ' . $e->getMessage());
                    throw new Exception('Database connection failed: ' . $e->getMessage());
                }
                sleep(1); // Wait 1 second before retry
            }
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
    
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetch($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            error_log('Insert error: ' . $e->getMessage() . ' SQL: ' . $sql);
            throw $e;
        }
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        $paramIndex = 0;
        $allParams = [];
        
        // Build SET clause with positional parameters
        foreach ($data as $key => $value) {
            $setParts[] = "$key = ?";
            $allParams[] = $value;
        }
        $setClause = implode(', ', $setParts);
        
        // Add WHERE parameters
        foreach ($whereParams as $param) {
            $allParams[] = $param;
        }
        
        $sql = "UPDATE $table SET $setClause WHERE $where";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($allParams);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Update error: ' . $e->getMessage() . ' SQL: ' . $sql . ' Params: ' . json_encode($allParams));
            throw $e;
        }
    }
    
    public function delete($table, $where, $whereParams = []) {
        if ($table === 'bathrooms') {
            $this->beginTransaction();
            try {
                $this->execute("DELETE FROM usage_logs WHERE bathroom_id IN (SELECT id FROM bathrooms WHERE $where)", $whereParams);
                $this->execute("DELETE FROM cleaning_logs WHERE bathroom_id IN (SELECT id FROM bathrooms WHERE $where)", $whereParams);
                $this->execute("DELETE FROM rfid_logs WHERE bathroom_id IN (SELECT id FROM bathrooms WHERE $where)", $whereParams);
                $this->execute("DELETE FROM sensors WHERE bathroom_id IN (SELECT id FROM bathrooms WHERE $where)", $whereParams);
                $this->execute("DELETE FROM visitor_counter WHERE bathroom_id IN (SELECT id FROM bathrooms WHERE $where)", $whereParams);
                
                $sql = "DELETE FROM $table WHERE $where";
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute($whereParams);
                
                $this->commit();
                return $result;
            } catch (Exception $e) {
                $this->rollBack();
                throw $e;
            }
        } else {
            $sql = "DELETE FROM $table WHERE $where";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($whereParams);
        }
    }
    
    public function execute($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
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
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollBack() {
        return $this->pdo->rollBack();
    }
    
    public function inTransaction() {
        return $this->pdo->inTransaction();
    }
}

if (!function_exists('generateUUID')) {
    function generateUUID() {
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