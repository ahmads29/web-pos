<?php
require_once 'config/database.php';
require_once 'includes/session.php';

if (!isLoggedIn() || !isset($_GET['sale_id'])) {
    die('Unauthorized access');
}

$sale_id = intval($_GET['sale_id']);

// Get sale details
$sql = "SELECT s.*, u.username 
        FROM sales s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $sale_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$sale = mysqli_fetch_assoc($result);

if (!$sale) {
    die('Sale not found');
}

// Get sale items
$sql = "SELECT si.*, p.name as product_name 
        FROM sale_items si 
        JOIN products p ON si.product_id = p.id 
        WHERE si.sale_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $sale_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo $sale_id; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .receipt {
            max-width: 300px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0;
        }
        .items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items th, .items td {
            padding: 5px;
            text-align: left;
        }
        .items th {
            border-bottom: 1px solid #ddd;
        }
        .total {
            text-align: right;
            margin-top: 20px;
        }
        .total p {
            margin: 5px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
        }
        @media print {
            body {
                padding: 0;
            }
            .receipt {
                width: 100%;
                max-width: none;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt">
        <div class="header">
            <img src="assets/images/products/logo4.png" alt="Nafahat Perfumes" style="width: 100px;">
            <h1>Nafahat Perfumes</h1>
            <p>Lebanon, Saida</p>
            <p>Phone: +961 81643233</p>
        </div>
        
        <div class="details">
            <p><strong>Receipt #:</strong> <?php echo $sale_id; ?></p>
            <p><strong>Date:</strong> <?php echo date('M d, Y H:i:s', strtotime($sale['created_at'])); ?></p>
            <p><strong>Cashier:</strong> <?php echo htmlspecialchars($sale['username']); ?></p>
        </div>
        
        <table class="items">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($item['price_per_unit'], 2); ?></td>
                    <td>$<?php echo number_format($item['total_price'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="total">
            <p><strong>Subtotal:</strong> $<?php echo number_format($sale['total_amount'], 2); ?></p>
            <?php if (isset($sale['delivery_cost'])): ?>
                <p><strong>Delivery:</strong> $<?php echo number_format($sale['delivery_cost'], 2); ?></p>
            <?php endif; ?>
            <p><strong>Total:</strong> $<?php echo number_format($sale['total_amount'], 2); ?></p>
            <p><strong>Amount Paid:</strong> $<?php echo number_format($sale['amount_paid'], 2); ?></p>
            <p><strong>Change:</strong> $<?php echo number_format($sale['change_amount'], 2); ?></p>
        </div>
        
        <div class="footer">
            <p>Thank you for your purchase!</p>
            <p>Please come again</p>
        </div>
    </div>
</body>
</html> 