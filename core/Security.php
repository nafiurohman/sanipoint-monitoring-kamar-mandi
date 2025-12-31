<?php
class Security {
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateInput($data, $rules) {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? '';
            
            // Check required fields
            if (isset($rule['required']) && $rule['required']) {
                if (empty($value) && $value !== '0') {
                    $errors[$field] = "Field {$field} is required";
                    continue;
                }
            }
            
            // Skip validation if value is empty and not required
            if (empty($value) && $value !== '0') {
                continue;
            }
            
            // Length validation
            if (isset($rule['min']) && strlen($value) < $rule['min']) {
                $errors[$field] = "Field {$field} must be at least {$rule['min']} characters";
            }
            if (isset($rule['max']) && strlen($value) > $rule['max']) {
                $errors[$field] = "Field {$field} must not exceed {$rule['max']} characters";
            }
            
            // Email validation
            if (isset($rule['email']) && $rule['email'] && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "Field {$field} must be a valid email";
            }
            
            // Numeric validation
            if (isset($rule['numeric']) && $rule['numeric']) {
                if (!is_numeric($value) || (float)$value < 0) {
                    $errors[$field] = "Field {$field} must be a positive number";
                }
            }
        }
        return $errors;
    }
    
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
?>