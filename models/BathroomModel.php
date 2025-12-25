<?php
class BathroomModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM bathrooms WHERE is_active = 1 ORDER BY name");
    }
    
    public function getAllWithStatus() {
        $sql = "SELECT b.*, u.full_name as last_cleaned_by_name,
                CASE 
                    WHEN b.current_visitors >= b.max_visitors THEN 'needs_cleaning'
                    ELSE b.status 
                END as computed_status
                FROM bathrooms b 
                LEFT JOIN users u ON b.last_cleaned_by = u.id 
                WHERE b.is_active = 1 
                ORDER BY b.name";
        return $this->db->fetchAll($sql);
    }
    
    public function count() {
        return $this->db->fetch("SELECT COUNT(*) as count FROM bathrooms WHERE is_active = 1")['count'];
    }
    
    public function getCleaningCountToday() {
        $sql = "SELECT COUNT(*) as count FROM cleaning_logs 
                WHERE DATE(created_at) = CURDATE() AND status = 'completed'";
        return $this->db->fetch($sql)['count'];
    }
    
    public function getRecentActivities($limit = 10) {
        $sql = "SELECT cl.*, b.name as bathroom_name, u.full_name as user_name
                FROM cleaning_logs cl
                JOIN bathrooms b ON cl.bathroom_id = b.id
                JOIN users u ON cl.user_id = u.id
                ORDER BY cl.created_at DESC
                LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    public function create($data) {
        $validation = Security::validateInput($data, [
            'name' => ['required' => true, 'min' => 3, 'max' => 100],
            'location' => ['required' => true, 'min' => 3, 'max' => 100],
            'max_visitors' => ['required' => true, 'numeric' => true]
        ]);
        
        if (!empty($validation)) {
            return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation];
        }
        
        try {
            $this->db->insert('bathrooms', [
                'name' => $data['name'],
                'location' => $data['location'],
                'max_visitors' => (int)$data['max_visitors']
            ]);
            return ['success' => true, 'message' => 'Bathroom created successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create bathroom'];
        }
    }
    
    public function update($id, $data) {
        $validation = Security::validateInput($data, [
            'name' => ['required' => true, 'min' => 3, 'max' => 100],
            'location' => ['required' => true, 'min' => 3, 'max' => 100],
            'max_visitors' => ['required' => true, 'numeric' => true]
        ]);
        
        if (!empty($validation)) {
            return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation];
        }
        
        try {
            $this->db->update('bathrooms', [
                'name' => $data['name'],
                'location' => $data['location'],
                'max_visitors' => (int)$data['max_visitors']
            ], 'id = ?', [$id]);
            return ['success' => true, 'message' => 'Bathroom updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update bathroom'];
        }
    }
    
    public function updateVisitorCount($bathroom_id, $count) {
        $bathroom = $this->db->fetch("SELECT * FROM bathrooms WHERE id = ?", [$bathroom_id]);
        if (!$bathroom) return false;
        
        $new_count = max(0, $bathroom['current_visitors'] + $count);
        $status = $new_count >= $bathroom['max_visitors'] ? 'needs_cleaning' : 'available';
        
        $this->db->update('bathrooms', [
            'current_visitors' => $new_count,
            'status' => $status
        ], 'id = ?', [$bathroom_id]);
        
        // Log visitor count
        $this->db->insert('visitor_counter', [
            'bathroom_id' => $bathroom_id,
            'current_occupancy' => $new_count
        ]);
        
        return true;
    }
    
    public function updateStatus($bathroom_id, $status) {
        $this->db->update('bathrooms', ['status' => $status], 'id = ?', [$bathroom_id]);
    }
    
    public function handleRFIDTap($bathroom_id, $user_id, $rfid_code) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Check current bathroom status
            $bathroom = $this->db->fetch("SELECT * FROM bathrooms WHERE id = ?", [$bathroom_id]);
            if (!$bathroom) {
                throw new Exception('Bathroom not found');
            }
            
            // Check if user has ongoing cleaning session
            $ongoing = $this->db->fetch(
                "SELECT * FROM cleaning_logs WHERE bathroom_id = ? AND user_id = ? AND status = 'in_progress'",
                [$bathroom_id, $user_id]
            );
            
            if ($ongoing) {
                // Finish cleaning
                $end_time = date('Y-m-d H:i:s');
                $start_time = $ongoing['start_time'];
                $duration = (strtotime($end_time) - strtotime($start_time)) / 60; // minutes
                
                // Get points per cleaning from settings
                $points_setting = $this->db->fetch("SELECT setting_value FROM system_settings WHERE setting_key = 'points_per_cleaning'");
                $points = (int)($points_setting['setting_value'] ?? 10);
                
                // Update cleaning log
                $this->db->update('cleaning_logs', [
                    'end_time' => $end_time,
                    'duration_minutes' => (int)$duration,
                    'points_earned' => $points,
                    'status' => 'completed'
                ], 'id = ?', [$ongoing['id']]);
                
                // Update bathroom status
                $this->db->update('bathrooms', [
                    'status' => 'available',
                    'current_visitors' => 0,
                    'last_cleaned' => $end_time,
                    'last_cleaned_by' => $user_id
                ], 'id = ?', [$bathroom_id]);
                
                // Award points
                $pointModel = new PointModel();
                $pointModel->addPoints($user_id, $points, 'cleaning', $ongoing['id']);
                
                $action = 'finish_cleaning';
                $message = 'Cleaning completed successfully. Points awarded: ' . $points;
                
            } else {
                // Start cleaning
                if ($bathroom['status'] !== 'needs_cleaning') {
                    throw new Exception('Bathroom does not need cleaning');
                }
                
                // Create cleaning log
                $this->db->insert('cleaning_logs', [
                    'bathroom_id' => $bathroom_id,
                    'user_id' => $user_id,
                    'start_time' => date('Y-m-d H:i:s'),
                    'status' => 'in_progress'
                ]);
                
                // Update bathroom status
                $this->db->update('bathrooms', [
                    'status' => 'being_cleaned'
                ], 'id = ?', [$bathroom_id]);
                
                $action = 'start_cleaning';
                $message = 'Cleaning session started';
            }
            
            // Log RFID tap
            $this->db->insert('rfid_logs', [
                'bathroom_id' => $bathroom_id,
                'user_id' => $user_id,
                'action' => $action,
                'rfid_code' => $rfid_code
            ]);
            
            $this->db->getConnection()->commit();
            return ['success' => true, 'message' => $message, 'action' => $action];
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getCleaningStats() {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_cleanings,
                    AVG(duration_minutes) as avg_duration,
                    SUM(points_earned) as total_points
                FROM cleaning_logs 
                WHERE status = 'completed' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC";
        return $this->db->fetchAll($sql);
    }
}
?>