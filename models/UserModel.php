<?php
class UserModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAllEmployees() {
        $sql = "SELECT u.*, p.current_balance, p.total_earned 
                FROM users u 
                LEFT JOIN points p ON u.id = p.user_id 
                WHERE u.role = 'karyawan' 
                ORDER BY u.full_name";
        return $this->db->fetchAll($sql);
    }
    
    public function countActiveEmployees() {
        return $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'karyawan' AND is_active = 1")['count'];
    }
    
    public function getPointsDistributedToday() {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total 
                FROM point_transactions 
                WHERE transaction_type = 'earned' 
                AND DATE(created_at) = CURDATE()";
        return $this->db->fetch($sql)['total'];
    }
    
    public function createEmployee($data) {
        $validation = Security::validateInput($data, [
            'username' => ['required' => true, 'min' => 3, 'max' => 50],
            'password' => ['required' => true, 'min' => 6],
            'full_name' => ['required' => true, 'min' => 3, 'max' => 100],
            'employee_code' => ['required' => true, 'min' => 3, 'max' => 20],
            'email' => ['email' => true],
            'phone' => ['min' => 10, 'max' => 20]
        ]);
        
        if (!empty($validation)) {
            return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation];
        }
        
        // Check if username or employee_code already exists
        $existing = $this->db->fetch("SELECT id FROM users WHERE username = ? OR employee_code = ?", 
                                   [$data['username'], $data['employee_code']]);
        if ($existing) {
            return ['success' => false, 'message' => 'Username or employee code already exists'];
        }
        
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Create user
            $this->db->insert('users', [
                'username' => $data['username'],
                'password' => Security::hashPassword($data['password']),
                'full_name' => $data['full_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'role' => 'karyawan',
                'employee_code' => $data['employee_code']
            ]);
            
            $user_id = $this->db->getConnection()->lastInsertId();
            
            // Initialize points
            $this->db->insert('points', [
                'user_id' => $user_id,
                'current_balance' => 0,
                'total_earned' => 0,
                'total_spent' => 0
            ]);
            
            $this->db->getConnection()->commit();
            return ['success' => true, 'message' => 'Employee created successfully'];
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            return ['success' => false, 'message' => 'Failed to create employee'];
        }
    }
    
    public function updateEmployee($id, $data) {
        $validation = Security::validateInput($data, [
            'full_name' => ['required' => true, 'min' => 3, 'max' => 100],
            'employee_code' => ['required' => true, 'min' => 3, 'max' => 20],
            'email' => ['email' => true],
            'phone' => ['min' => 10, 'max' => 20]
        ]);
        
        if (!empty($validation)) {
            return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation];
        }
        
        // Check if employee_code already exists for other users
        $existing = $this->db->fetch("SELECT id FROM users WHERE employee_code = ? AND id != ?", 
                                   [$data['employee_code'], $id]);
        if ($existing) {
            return ['success' => false, 'message' => 'Employee code already exists'];
        }
        
        try {
            $updateData = [
                'full_name' => $data['full_name'],
                'employee_code' => $data['employee_code'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null
            ];
            
            if (!empty($data['password'])) {
                $updateData['password'] = Security::hashPassword($data['password']);
            }
            
            $this->db->update('users', $updateData, 'id = ?', [$id]);
            return ['success' => true, 'message' => 'Employee updated successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update employee'];
        }
    }
    
    public function deleteEmployee($id) {
        try {
            // Soft delete - set is_active to false
            $this->db->update('users', ['is_active' => 0], 'id = ?', [$id]);
            return ['success' => true, 'message' => 'Employee deactivated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to deactivate employee'];
        }
    }
    
    public function getEmployeePerformance() {
        $sql = "SELECT 
                    u.full_name,
                    u.employee_code,
                    COUNT(cl.id) as total_cleanings,
                    AVG(cl.duration_minutes) as avg_duration,
                    SUM(cl.points_earned) as total_points_earned,
                    p.current_balance
                FROM users u
                LEFT JOIN cleaning_logs cl ON u.id = cl.user_id AND cl.status = 'completed'
                LEFT JOIN points p ON u.id = p.user_id
                WHERE u.role = 'karyawan' AND u.is_active = 1
                GROUP BY u.id
                ORDER BY total_cleanings DESC, total_points_earned DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function getPointDistribution() {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    transaction_type,
                    SUM(amount) as total_amount
                FROM point_transactions 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at), transaction_type
                ORDER BY date DESC";
        return $this->db->fetchAll($sql);
    }
    
    public function findByEmployeeCode($employee_code) {
        return $this->db->fetch("SELECT * FROM users WHERE employee_code = ? AND role = 'karyawan' AND is_active = 1", 
                               [$employee_code]);
    }
    
    public function getUserById($id) {
        return $this->db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
    }
    
    public function updateProfile($id, $data) {
        $validation = Security::validateInput($data, [
            'full_name' => ['required' => true, 'min' => 3, 'max' => 100],
            'email' => ['email' => true],
            'phone' => ['min' => 10, 'max' => 20]
        ]);
        
        if (!empty($validation)) {
            return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation];
        }
        
        try {
            $this->db->update('users', [
                'full_name' => $data['full_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null
            ], 'id = ?', [$id]);
            
            return ['success' => true, 'message' => 'Profile updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update profile'];
        }
    }
    
    public function updatePin($id, $pin) {
        try {
            $this->db->update('users', [
                'pin' => password_hash($pin, PASSWORD_DEFAULT),
                'pin_created_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            return ['success' => true, 'message' => 'PIN updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update PIN'];
        }
    }
    
    public function updatePassword($id, $password) {
        try {
            $this->db->update('users', [
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'last_password_change' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            return ['success' => true, 'message' => 'Password updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update password'];
        }
    }
}
?>