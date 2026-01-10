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
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'karyawan' AND is_active = 1");
        return $result ? (int)$result['count'] : 0;
    }
    
    public function getPointsDistributedToday() {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total 
                FROM point_transactions 
                WHERE transaction_type = 'earned' 
                AND DATE(created_at) = CURDATE()";
        $result = $this->db->fetch($sql);
        return $result ? (int)$result['total'] : 0;
    }
    
    public function createEmployee($data) {
        $validation = Security::validateInput($data, [
            'full_name' => ['required' => true, 'min' => 3, 'max' => 100],
            'email' => ['email' => true],
            'phone' => ['min' => 10, 'max' => 20]
        ]);
        
        if (!empty($validation)) {
            return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation];
        }
        
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Generate auto credentials
            $username = $this->generateUsername();
            $employee_code = $this->generateEmployeeCode();
            $password = 'password'; // Default password
            
            // Create user
            $user_id = $this->db->insert('users', [
                'username' => $username,
                'password' => Security::hashPassword($password),
                'full_name' => $data['full_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'role' => 'karyawan',
                'employee_code' => $employee_code,
                'is_active' => 1
            ]);
            
            // Initialize points
            $this->db->insert('points', [
                'user_id' => $user_id,
                'current_balance' => 0,
                'total_earned' => 0,
                'total_spent' => 0
            ]);
            
            $this->db->getConnection()->commit();
            
            return [
                'success' => true, 
                'message' => 'Employee created successfully',
                'credentials' => [
                    'username' => $username,
                    'employee_code' => $employee_code,
                    'password' => $password
                ]
            ];
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            error_log('Create employee error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create employee: ' . $e->getMessage()];
        }
    }
    
    private function generateUsername() {
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'karyawan'");
        $count = $result ? (int)$result['count'] : 0;
        return 'karyawan' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }
    
    private function generateEmployeeCode() {
        do {
            $code = 'KAR-' . strtoupper(substr(md5(uniqid()), 0, 6));
            $exists = $this->db->fetch("SELECT id FROM users WHERE employee_code = ?", [$code]);
        } while ($exists);
        return $code;
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
            // Check if employee has any cleaning logs or transactions
            $result = $this->db->fetch(
                "SELECT COUNT(*) as count FROM cleaning_logs WHERE user_id = ?", 
                [$id]
            );
            $hasActivity = $result ? (int)$result['count'] > 0 : false;
            
            if ($hasActivity) {
                // Soft delete - set is_active to false
                $this->db->update('users', ['is_active' => 0], 'id = ?', [$id]);
                return ['success' => true, 'message' => 'Employee deactivated successfully'];
            } else {
                // Hard delete if no activity
                $this->db->getConnection()->beginTransaction();
                
                // Delete related records first
                $this->db->execute("DELETE FROM points WHERE user_id = ?", [$id]);
                $this->db->execute("DELETE FROM rfid_cards WHERE user_id = ?", [$id]);
                $this->db->execute("DELETE FROM users WHERE id = ?", [$id]);
                
                $this->db->getConnection()->commit();
                return ['success' => true, 'message' => 'Employee deleted successfully'];
            }
        } catch (Exception $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->getConnection()->rollBack();
            }
            return ['success' => false, 'message' => 'Failed to delete employee'];
        }
    }
    
    public function getEmployeeById($id) {
        return $this->db->fetch("SELECT * FROM users WHERE id = ? AND role = 'karyawan'", [$id]);
    }
    
    public function toggleEmployeeStatus($id) {
        try {
            $employee = $this->getEmployeeById($id);
            if (!$employee) {
                return ['success' => false, 'message' => 'Employee not found'];
            }
            
            $newStatus = $employee['is_active'] ? 0 : 1;
            $this->db->update('users', ['is_active' => $newStatus], 'id = ?', [$id]);
            
            $message = $newStatus ? 'Employee activated successfully' : 'Employee deactivated successfully';
            return ['success' => true, 'message' => $message];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update employee status'];
        }
    }
    
    public function getEmployeePerformance() {
        $sql = "SELECT 
                    u.id,
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
                GROUP BY u.id, u.full_name, u.employee_code, p.current_balance
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
        error_log('=== UPDATE PIN DEBUG ===');
        error_log('User ID: ' . $id);
        error_log('PIN: ' . $pin);
        
        try {
            $hashedPin = password_hash($pin, PASSWORD_DEFAULT);
            error_log('Hashed PIN: ' . substr($hashedPin, 0, 20) . '...');
            
            $result = $this->db->update('users', [
                'pin' => $hashedPin,
                'pin_created_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            error_log('Database update result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            
            return ['success' => true, 'message' => 'PIN updated successfully'];
        } catch (Exception $e) {
            error_log('PIN update exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update PIN'];
        }
    }
    
    public function updateUserPin($id, $hashedPin) {
        try {
            $result = $this->db->update('users', [
                'pin' => $hashedPin,
                'pin_created_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$id]);
            
            return $result;
        } catch (Exception $e) {
            error_log('PIN update exception: ' . $e->getMessage());
            return false;
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