<?php
require_once '../config/database.php';
require_once '../includes/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Insert sale record
    $sql = "INSERT INTO sales (user_id, total_amount, payment_method, amount_paid, change_amount) 
            VALUES (?, ?, 'cash', ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iddd", $_SESSION['user_id'], $data['total'], $data['amount_paid'], $data['change']);
    mysqli_stmt_execute($stmt);
    
    $sale_id = mysqli_insert_id($conn);
    
    // Insert sale items and update inventory
    foreach ($data['items'] as $item) {
        // Insert sale item
        $sql = "INSERT INTO sale_items (sale_id, product_id, quantity, price_per_unit, total_price) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        $total_price = $item['price'] * $item['quantity'];
        mysqli_stmt_bind_param($stmt, "iiidd", $sale_id, $item['id'], $item['quantity'], $item['price'], $total_price);
        mysqli_stmt_execute($stmt);
        
        // Update product quantity
        $sql = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $item['quantity'], $item['id']);
        mysqli_stmt_execute($stmt);
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode([
        'success' => true,
        'sale_id' => $sale_id
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error processing sale: ' . $e->getMessage()
    ]);
}
?> 