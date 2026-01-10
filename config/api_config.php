<?php
// API Configuration for SANIPOINT IoT System
define('API_VERSION', '1.0');
define('API_TIMEZONE', 'Asia/Jakarta');

// Database configuration for API
define('API_DB_HOST', 'localhost');
define('API_DB_NAME', 'sanipoint_db');
define('API_DB_USER', 'root');
define('API_DB_PASS', '');

// API Security settings
define('API_RATE_LIMIT', 100); // requests per minute
define('API_TIMEOUT', 30); // seconds

// IoT Device settings
define('IOT_GAS_LIMIT', 1800);
define('IOT_MAX_VISITORS', 5);
define('IOT_POINTS_PER_CLEANING', 10);

// Admin RFID codes
define('ADMIN_RFID_CODES', ['B490FBB0', 'C6861BFF']);

// Set timezone
date_default_timezone_set(API_TIMEZONE);

// Error reporting for API
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in API responses
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/api-errors.log');

// API Response helper function
function apiResponse($success, $data = [], $message = '', $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    
    $response = [
        'success' => $success,
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => API_VERSION
    ];
    
    if ($message) {
        $response['message'] = $message;
    }
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response);
    exit;
}

// API Error handler
function apiError($message, $code = 500) {
    error_log("API Error: $message");
    apiResponse(false, [], $message, $code);
}

// Validate request method
function validateMethod($allowed_methods) {
    if (!in_array($_SERVER['REQUEST_METHOD'], $allowed_methods)) {
        apiError('Method not allowed', 405);
    }
}

// Simple rate limiting (file-based)
function checkRateLimit($identifier = null) {
    if (!$identifier) {
        $identifier = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    $rate_file = __DIR__ . '/../logs/rate_' . md5($identifier) . '.txt';
    $current_time = time();
    $rate_limit = API_RATE_LIMIT;
    
    if (file_exists($rate_file)) {
        $data = json_decode(file_get_contents($rate_file), true);
        if ($data && $current_time - $data['time'] < 60) {
            if ($data['count'] >= $rate_limit) {
                apiError('Rate limit exceeded', 429);
            }
            $data['count']++;
        } else {
            $data = ['time' => $current_time, 'count' => 1];
        }
    } else {
        $data = ['time' => $current_time, 'count' => 1];
    }
    
    file_put_contents($rate_file, json_encode($data));
}
?>