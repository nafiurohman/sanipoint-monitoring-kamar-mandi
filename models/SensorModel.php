<?php
class SensorModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM sensors WHERE is_active = 1 ORDER BY sensor_code");
    }
    
    public function getAllWithBathrooms() {
        $sql = "SELECT s.*, b.name as bathroom_name, b.location as bathroom_location
                FROM sensors s
                JOIN bathrooms b ON s.bathroom_id = b.id
                WHERE s.is_active = 1
                ORDER BY b.name, s.sensor_type";
        return $this->db->fetchAll($sql);
    }
    
    public function getBySensorCode($sensor_code) {
        return $this->db->fetch("SELECT * FROM sensors WHERE sensor_code = ? AND is_active = 1", [$sensor_code]);
    }
    
    public function logData($sensor_code, $value, $unit = null) {
        $sensor = $this->getBySensorCode($sensor_code);
        if (!$sensor) {
            return ['success' => false, 'message' => 'Sensor not found'];
        }
        
        try {
            $this->db->insert('sensor_logs', [
                'sensor_id' => $sensor['id'],
                'value' => $value,
                'unit' => $unit
            ]);
            
            return [
                'success' => true, 
                'message' => 'Data logged successfully',
                'sensor_id' => $sensor['id'],
                'bathroom_id' => $sensor['bathroom_id']
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to log data'];
        }
    }
    
    public function getRecentData($limit = 100) {
        $sql = "SELECT sl.*, s.sensor_code, s.sensor_type, s.bathroom_id, b.name as bathroom_name
                FROM sensor_logs sl
                JOIN sensors s ON sl.sensor_id = s.id
                JOIN bathrooms b ON s.bathroom_id = b.id
                ORDER BY sl.recorded_at DESC
                LIMIT " . (int)$limit;
        return $this->db->fetchAll($sql);
    }
    
    public function getLatestData() {
        $sql = "SELECT s.id, s.sensor_code, s.sensor_type, s.bathroom_id, b.name as bathroom_name,
                       sl.value, sl.unit, sl.recorded_at
                FROM sensors s
                JOIN bathrooms b ON s.bathroom_id = b.id
                LEFT JOIN sensor_logs sl ON s.id = sl.sensor_id
                WHERE s.is_active = 1
                AND sl.id = (
                    SELECT MAX(id) FROM sensor_logs WHERE sensor_id = s.id
                )
                ORDER BY b.name, s.sensor_type";
        return $this->db->fetchAll($sql);
    }
    
    public function getSensorHistory($sensor_id, $hours = 24) {
        $sql = "SELECT * FROM sensor_logs 
                WHERE sensor_id = ? 
                AND recorded_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                ORDER BY recorded_at DESC";
        return $this->db->fetchAll($sql, [$sensor_id, $hours]);
    }
    
    public function getBathroomSensorData($bathroom_id, $hours = 24) {
        $sql = "SELECT sl.*, s.sensor_type, s.sensor_code
                FROM sensor_logs sl
                JOIN sensors s ON sl.sensor_id = s.id
                WHERE s.bathroom_id = ?
                AND sl.recorded_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                ORDER BY sl.recorded_at DESC";
        return $this->db->fetchAll($sql, [$bathroom_id, $hours]);
    }
    
    public function create($data) {
        $validation = Security::validateInput($data, [
            'bathroom_id' => ['required' => true, 'numeric' => true],
            'sensor_type' => ['required' => true],
            'sensor_code' => ['required' => true, 'min' => 3, 'max' => 50]
        ]);
        
        if (!empty($validation)) {
            return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation];
        }
        
        // Check if sensor code already exists
        $existing = $this->getBySensorCode($data['sensor_code']);
        if ($existing) {
            return ['success' => false, 'message' => 'Sensor code already exists'];
        }
        
        try {
            $this->db->insert('sensors', [
                'bathroom_id' => (int)$data['bathroom_id'],
                'sensor_type' => $data['sensor_type'],
                'sensor_code' => $data['sensor_code']
            ]);
            return ['success' => true, 'message' => 'Sensor created successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create sensor'];
        }
    }
    
    public function update($id, $data) {
        $validation = Security::validateInput($data, [
            'bathroom_id' => ['required' => true, 'numeric' => true],
            'sensor_type' => ['required' => true],
            'sensor_code' => ['required' => true, 'min' => 3, 'max' => 50]
        ]);
        
        if (!empty($validation)) {
            return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation];
        }
        
        // Check if sensor code already exists for other sensors
        $existing = $this->db->fetch("SELECT id FROM sensors WHERE sensor_code = ? AND id != ?", 
                                   [$data['sensor_code'], $id]);
        if ($existing) {
            return ['success' => false, 'message' => 'Sensor code already exists'];
        }
        
        try {
            $this->db->update('sensors', [
                'bathroom_id' => (int)$data['bathroom_id'],
                'sensor_type' => $data['sensor_type'],
                'sensor_code' => $data['sensor_code']
            ], 'id = ?', [$id]);
            return ['success' => true, 'message' => 'Sensor updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update sensor'];
        }
    }
    
    public function getAverageValues($bathroom_id, $sensor_type, $hours = 24) {
        $sql = "SELECT AVG(sl.value) as avg_value, COUNT(*) as reading_count
                FROM sensor_logs sl
                JOIN sensors s ON sl.sensor_id = s.id
                WHERE s.bathroom_id = ? AND s.sensor_type = ?
                AND sl.recorded_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)";
        return $this->db->fetch($sql, [$bathroom_id, $sensor_type, $hours]);
    }
    
    public function getAlerts() {
        // Get sensors with concerning values
        $sql = "SELECT s.sensor_code, s.sensor_type, b.name as bathroom_name,
                       sl.value, sl.recorded_at
                FROM sensors s
                JOIN bathrooms b ON s.bathroom_id = b.id
                JOIN sensor_logs sl ON s.id = sl.sensor_id
                WHERE sl.id = (SELECT MAX(id) FROM sensor_logs WHERE sensor_id = s.id)
                AND (
                    (s.sensor_type = 'mq135' AND sl.value > 400) OR
                    (s.sensor_type = 'ir' AND sl.value > 10)
                )
                ORDER BY sl.recorded_at DESC";
        return $this->db->fetchAll($sql);
    }
}
?>