<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Security.php';
require_once __DIR__ . '/../models/UserModel.php';

class SettingsController {
    private $auth;
    private $userModel;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->userModel = new UserModel();
    }
    
    public function pengaturan() {
        if (!$this->auth->isLoggedIn()) {
            header('Location: /login');
            exit;
        }
        
        $user = $this->auth->getUser();
        $message = '';
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid security token';
            } else {
                switch ($action) {
                    case 'update_profile':
                        $result = $this->updateProfile($user['id'], $_POST);
                        if ($result['success']) {
                            $message = $result['message'];
                            $user = $this->userModel->getById($user['id']); // Refresh user data
                        } else {
                            $error = $result['message'];
                        }
                        break;
                        
                    case 'change_password':
                        $result = $this->changePassword($user['id'], $_POST);
                        if ($result['success']) {
                            $message = $result['message'];
                        } else {
                            $error = $result['message'];
                        }
                        break;
                        
                    case 'update_preferences':
                        $result = $this->updatePreferences($user['id'], $_POST);
                        if ($result['success']) {
                            $message = $result['message'];
                        } else {
                            $error = $result['message'];
                        }
                        break;
                }
            }
        }
        
        $this->render('settings/pengaturan', [
            'user' => $user,
            'message' => $message,
            'error' => $error
        ]);
    }
    
    private function updateProfile($userId, $data) {
        $validation = Security::validateInput($data, [
            'full_name' => ['required' => true, 'min' => 2, 'max' => 100],
            'email' => ['email' => true],
            'phone' => ['min' => 10, 'max' => 15]
        ]);
        
        if (!empty($validation)) {
            return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation];
        }
        
        return $this->userModel->updateProfile($userId, [
            'full_name' => $data['full_name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null
        ]);
    }
    
    private function changePassword($userId, $data) {
        if (empty($data['current_password']) || empty($data['new_password']) || empty($data['confirm_password'])) {
            return ['success' => false, 'message' => 'Semua field password harus diisi'];
        }
        
        if ($data['new_password'] !== $data['confirm_password']) {
            return ['success' => false, 'message' => 'Password baru dan konfirmasi tidak cocok'];
        }
        
        if (strlen($data['new_password']) < 6) {
            return ['success' => false, 'message' => 'Password baru minimal 6 karakter'];
        }
        
        return $this->userModel->changePassword($userId, $data['current_password'], $data['new_password']);
    }
    
    private function updatePreferences($userId, $data) {
        $preferences = [
            'theme' => $data['theme'] ?? 'light',
            'notifications' => isset($data['notifications']) ? 1 : 0,
            'email_notifications' => isset($data['email_notifications']) ? 1 : 0,
            'language' => $data['language'] ?? 'id'
        ];
        
        return $this->userModel->updatePreferences($userId, $preferences);
    }
    
    private function render($view, $data = []) {
        extract($data);
        $show_nav = true;
        include "views/{$view}.php";
    }
}

// Handle the request
$controller = new SettingsController();
$controller->pengaturan();
?>