<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sanipoint_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== FIXING EMPTY STATUS ORDERS ===\n";
    
    // Find orders with empty status but have received_at
    $stmt = $pdo->prepare("SELECT id, order_number, status, received_at FROM orders WHERE (status = '' OR status IS NULL) AND received_at IS NOT NULL");
    $stmt->execute();
    $emptyStatusOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($emptyStatusOrders) . " orders with empty status but received_at set\n";
    
    foreach ($emptyStatusOrders as $order) {
        echo "Fixing order: " . $order['order_number'] . "\n";
        $updateStmt = $pdo->prepare("UPDATE orders SET status = 'received' WHERE id = ?");
        $updateStmt->execute([$order['id']]);
    }
    
    // Find orders with empty status and no received_at (should be completed)
    $stmt2 = $pdo->prepare("SELECT id, order_number, status FROM orders WHERE (status = '' OR status IS NULL) AND received_at IS NULL");
    $stmt2->execute();
    $incompleteOrders = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($incompleteOrders) . " orders with empty status and no received_at\n";
    
    foreach ($incompleteOrders as $order) {
        echo "Setting order to completed: " . $order['order_number'] . "\n";
        $updateStmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
        $updateStmt->execute([$order['id']]);
    }
    
    echo "✅ All orders fixed\n";
    
    // Show current status
    echo "\n=== CURRENT ORDER STATUS ===\n";
    $stmt3 = $pdo->prepare("SELECT order_number, status, received_at FROM orders ORDER BY created_at DESC LIMIT 5");
    $stmt3->execute();
    $orders = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($orders as $order) {
        echo "Order: " . $order['order_number'] . " | Status: '" . $order['status'] . "' | Received: " . ($order['received_at'] ?? 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>