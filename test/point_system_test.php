<?php
require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../models/PointModel.php';
require_once '../models/OrderModel.php';
require_once '../models/ProductModel.php';

echo "<h1>SANIPOINT Point System Test</h1>\n";
echo "<pre>\n";

$db = Database::getInstance();
$pointModel = new PointModel();

// Test 1: Get all users with RFID cards
echo "=== TEST 1: Users with RFID Cards ===\n";
$users = $db->fetchAll("
    SELECT u.id, u.full_name, u.role, r.uid as rfid_uid, r.nama_pemilik
    FROM users u 
    LEFT JOIN rfid_cards r ON u.id = r.user_id 
    WHERE u.role = 'karyawan' AND u.is_active = 1
    LIMIT 5
");

foreach ($users as $user) {
    echo "User: {$user['full_name']} (ID: {$user['id']}) - RFID: {$user['rfid_uid']}\n";
}

if (empty($users)) {
    echo "No employees found! Creating test employee...\n";
    
    // Create test employee
    $test_user_id = $db->insert('users', [
        'username' => 'test_employee',
        'password' => password_hash('password', PASSWORD_DEFAULT),
        'full_name' => 'Test Employee',
        'role' => 'karyawan',
        'employee_code' => 'TEST001',
        'is_active' => 1
    ]);
    
    // Create RFID card for test employee
    $db->insert('rfid_cards', [
        'uid' => 'TEST123',
        'nama_pemilik' => 'Test Employee',
        'peran' => 'Karyawan',
        'user_id' => $test_user_id,
        'status' => 'Aktif'
    ]);
    
    echo "Created test employee with ID: $test_user_id\n";
    $users = [['id' => $test_user_id, 'full_name' => 'Test Employee', 'rfid_uid' => 'TEST123']];
}

$test_user = $users[0];
echo "Using test user: {$test_user['full_name']} (ID: {$test_user['id']})\n\n";

// Test 2: Check current points
echo "=== TEST 2: Current Points ===\n";
$current_points = $pointModel->getUserPoints($test_user['id']);
echo "Current Balance: {$current_points['current_balance']}\n";
echo "Total Earned: {$current_points['total_earned']}\n";
echo "Total Spent: {$current_points['total_spent']}\n\n";

// Test 3: Simulate cleaning to earn points
echo "=== TEST 3: Simulate Cleaning (Earn Points) ===\n";

// Get or create bathroom
$bathroom = $db->fetch("SELECT * FROM bathrooms WHERE is_active = 1 LIMIT 1");
if (!$bathroom) {
    $bathroom_id = $db->insert('bathrooms', [
        'name' => 'Test Bathroom',
        'location' => 'Test Location',
        'max_visitors' => 10,
        'current_visitors' => 0,
        'status' => 'available',
        'is_active' => 1
    ]);
    echo "Created test bathroom with ID: $bathroom_id\n";
} else {
    $bathroom_id = $bathroom['id'];
    echo "Using existing bathroom: {$bathroom['name']} (ID: $bathroom_id)\n";
}

// Simulate cleaning session
$cleaning_id = 'cl_test_' . time();
$db->insert('cleaning_logs', [
    'id' => $cleaning_id,
    'bathroom_id' => $bathroom_id,
    'user_id' => $test_user['id'],
    'start_time' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
    'end_time' => date('Y-m-d H:i:s'),
    'duration_minutes' => 10,
    'points_earned' => 15,
    'status' => 'completed'
]);

// Add points using PointModel
$add_result = $pointModel->addPoints(
    $test_user['id'], 
    15, 
    'cleaning', 
    $cleaning_id, 
    'Points earned from cleaning Test Bathroom'
);

echo "Add Points Result: " . ($add_result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
if ($add_result['success']) {
    echo "New Balance: {$add_result['new_balance']}\n";
}
echo "\n";

// Test 4: Check points after earning
echo "=== TEST 4: Points After Earning ===\n";
$updated_points = $pointModel->getUserPoints($test_user['id']);
echo "Current Balance: {$updated_points['current_balance']}\n";
echo "Total Earned: {$updated_points['total_earned']}\n\n";

// Test 5: Get existing product for purchase
echo "=== TEST 5: Get Existing Product ===\n";

$existing_product = $db->fetch("SELECT * FROM products WHERE is_active = 1 AND stock > 0 LIMIT 1");
if (!$existing_product) {
    // Create category first
    $category_id = $db->insert('product_categories', [
        'name' => 'Test Category',
        'description' => 'Test category for testing'
    ]);
    
    // Then create product
    $product_id = $db->insert('products', [
        'name' => 'Test Product',
        'description' => 'Test product for point system testing',
        'category_id' => $category_id,
        'point_price' => 10,
        'stock' => 100,
        'is_active' => 1
    ]);
    echo "Created test product with ID: $product_id\n";
} else {
    $product_id = $existing_product['id'];
    echo "Using existing product: {$existing_product['name']} (ID: $product_id, Price: {$existing_product['point_price']} points)\n";
}

// Test 6: Purchase product (spend points)
echo "\n=== TEST 6: Purchase Product (Spend Points) ===\n";
$product_price = $existing_product['point_price'] ?? 10;
if ($updated_points['current_balance'] >= $product_price) {
    $orderModel = new OrderModel();
    $purchase_result = $orderModel->createOrder($test_user['id'], $product_id, 1);
    
    echo "Purchase Result: " . ($purchase_result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    if ($purchase_result['success']) {
        echo "Order Number: {$purchase_result['order_number']}\n";
        echo "Points Spent: $product_price\n";
    } else {
        echo "Error: {$purchase_result['message']}\n";
    }
} else {
    echo "Insufficient points for purchase (need $product_price, have {$updated_points['current_balance']})\n";
}

// Test 7: Check final points
echo "\n=== TEST 7: Final Points Check ===\n";
$final_points = $pointModel->getUserPoints($test_user['id']);
echo "Current Balance: {$final_points['current_balance']}\n";
echo "Total Earned: {$final_points['total_earned']}\n";
echo "Total Spent: {$final_points['total_spent']}\n\n";

// Test 8: Recalculate points from transactions
echo "=== TEST 8: Recalculate Points from Transactions ===\n";
$recalc_result = $pointModel->recalculateUserPoints($test_user['id']);
echo "Recalculation Result: " . ($recalc_result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
if ($recalc_result['success']) {
    echo "Calculated Balance: {$recalc_result['current_balance']}\n";
    echo "Calculated Earned: {$recalc_result['total_earned']}\n";
    echo "Calculated Spent: {$recalc_result['total_spent']}\n";
}

// Test 9: Transaction history
echo "\n=== TEST 9: Transaction History ===\n";
$transactions = $pointModel->getAllTransactions($test_user['id']);
echo "Total Transactions: " . count($transactions) . "\n";
foreach ($transactions as $i => $transaction) {
    if ($i < 5) { // Show only first 5
        echo "- {$transaction['transaction_type']}: {$transaction['amount']} points ({$transaction['description']})\n";
    }
}

echo "\n=== POINT SYSTEM TEST COMPLETED ===\n";
echo "The point system is working correctly if:\n";
echo "1. Points are earned from cleaning\n";
echo "2. Points are deducted from purchases\n";
echo "3. Balance calculations are accurate\n";
echo "4. Transaction history is recorded\n";
echo "5. Recalculation matches current balance\n";

echo "</pre>";
?>