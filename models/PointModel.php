<?php
class PointModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getUserPoints($user_id) {
        $points = $this->db->fetch("SELECT * FROM points WHERE user_id = ?", [$user_id]);
        if (!$points) {
            // Initialize points if not exists
            $this->db->insert('points', [
                'user_id' => $user_id,
                'current_balance' => 0,
                'total_earned' => 0,
                'total_spent' => 0
            ]);
            return $this->getUserPoints($user_id);
        }
        return $points;
    }
    
    public function addPoints($user_id, $amount, $reference_type, $reference_id = null, $description = null) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            $points = $this->getUserPoints($user_id);
            $new_balance = $points['current_balance'] + $amount;
            $new_total_earned = $points['total_earned'] + $amount;
            
            // Update points balance
            $this->db->update('points', [
                'current_balance' => $new_balance,
                'total_earned' => $new_total_earned
            ], 'user_id = ?', [$user_id]);
            
            // Record transaction
            $this->db->insert('point_transactions', [
                'user_id' => $user_id,
                'transaction_type' => 'earned',
                'amount' => $amount,
                'balance_after' => $new_balance,
                'reference_type' => $reference_type,
                'reference_id' => $reference_id,
                'description' => $description
            ]);
            
            $this->db->getConnection()->commit();
            return ['success' => true, 'new_balance' => $new_balance];
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            return ['success' => false, 'message' => 'Failed to add points'];
        }
    }
    
    public function deductPoints($user_id, $amount, $reference_type, $reference_id = null, $description = null) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            $points = $this->getUserPoints($user_id);
            if ($points['current_balance'] < $amount) {
                throw new Exception('Insufficient points');
            }
            
            $new_balance = $points['current_balance'] - $amount;
            $new_total_spent = $points['total_spent'] + $amount;
            
            // Update points balance
            $this->db->update('points', [
                'current_balance' => $new_balance,
                'total_spent' => $new_total_spent
            ], 'user_id = ?', [$user_id]);
            
            // Record transaction
            $this->db->insert('point_transactions', [
                'user_id' => $user_id,
                'transaction_type' => 'spent',
                'amount' => $amount,
                'balance_after' => $new_balance,
                'reference_type' => $reference_type,
                'reference_id' => $reference_id,
                'description' => $description
            ]);
            
            $this->db->getConnection()->commit();
            return ['success' => true, 'new_balance' => $new_balance];
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function transferPoints($from_user_id, $to_user_id, $amount, $description = null) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Find recipient user
            $to_user = $this->db->fetch("SELECT * FROM users WHERE id = ? AND role = 'karyawan' AND is_active = 1", [$to_user_id]);
            if (!$to_user) {
                throw new Exception('Employee not found');
            }
            
            if ($from_user_id == $to_user_id) {
                throw new Exception('Cannot transfer to yourself');
            }
            
            // Check sender balance
            $from_points = $this->getUserPoints($from_user_id);
            if ($from_points['current_balance'] < $amount) {
                throw new Exception('Insufficient points');
            }
            
            $to_points = $this->getUserPoints($to_user_id);
            
            // Update sender
            $new_from_balance = $from_points['current_balance'] - $amount;
            $this->db->update('points', [
                'current_balance' => $new_from_balance,
                'total_spent' => $from_points['total_spent'] + $amount
            ], 'user_id = ?', [$from_user_id]);
            
            // Update recipient
            $new_to_balance = $to_points['current_balance'] + $amount;
            $this->db->update('points', [
                'current_balance' => $new_to_balance,
                'total_earned' => $to_points['total_earned'] + $amount
            ], 'user_id = ?', [$to_user_id]);
            
            // Record transactions
            $this->db->insert('point_transactions', [
                'user_id' => $from_user_id,
                'transaction_type' => 'transfer_out',
                'amount' => $amount,
                'balance_after' => $new_from_balance,
                'reference_type' => 'transfer',
                'to_user_id' => $to_user_id,
                'description' => $description
            ]);
            
            $this->db->insert('point_transactions', [
                'user_id' => $to_user_id,
                'transaction_type' => 'transfer_in',
                'amount' => $amount,
                'balance_after' => $new_to_balance,
                'reference_type' => 'transfer',
                'from_user_id' => $from_user_id,
                'description' => $description
            ]);
            
            $this->db->getConnection()->commit();
            return ['success' => true, 'message' => 'Points transferred successfully', 'new_balance' => $new_from_balance];
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getRecentTransactions($user_id, $limit = 10) {
        $limit = (int)$limit; // Ensure it's an integer
        $sql = "SELECT pt.*, 
                       fu.full_name as from_user_name,
                       tu.full_name as to_user_name
                FROM point_transactions pt
                LEFT JOIN users fu ON pt.from_user_id = fu.id
                LEFT JOIN users tu ON pt.to_user_id = tu.id
                WHERE pt.user_id = ?
                ORDER BY pt.created_at DESC
                LIMIT $limit";
        return $this->db->fetchAll($sql, [$user_id]);
    }
    
    public function getAllTransactions($user_id) {
        $sql = "SELECT pt.*, 
                       fu.full_name as from_user_name,
                       tu.full_name as to_user_name
                FROM point_transactions pt
                LEFT JOIN users fu ON pt.from_user_id = fu.id
                LEFT JOIN users tu ON pt.to_user_id = tu.id
                WHERE pt.user_id = ?
                ORDER BY pt.created_at DESC";
        return $this->db->fetchAll($sql, [$user_id]);
    }
    
    public function getTransferHistory($user_id) {
        $sql = "SELECT pt.*, 
                       fu.full_name as from_user_name,
                       tu.full_name as to_user_name
                FROM point_transactions pt
                LEFT JOIN users fu ON pt.from_user_id = fu.id
                LEFT JOIN users tu ON pt.to_user_id = tu.id
                WHERE pt.user_id = ? AND pt.transaction_type IN ('transfer_in', 'transfer_out')
                ORDER BY pt.created_at DESC";
        return $this->db->fetchAll($sql, [$user_id]);
    }
    
    public function getCleaningStats($user_id) {
        $sql = "SELECT 
                    COUNT(*) as total_cleanings,
                    AVG(duration_minutes) as avg_duration,
                    SUM(points_earned) as total_points_from_cleaning,
                    MAX(created_at) as last_cleaning
                FROM cleaning_logs 
                WHERE user_id = ? AND status = 'completed'";
        return $this->db->fetch($sql, [$user_id]);
    }
    
    public function getCleaningHistory($user_id) {
        $sql = "SELECT cl.*, b.name as bathroom_name
                FROM cleaning_logs cl
                JOIN bathrooms b ON cl.bathroom_id = b.id
                WHERE cl.user_id = ?
                ORDER BY cl.created_at DESC";
        return $this->db->fetchAll($sql, [$user_id]);
    }
}
?>