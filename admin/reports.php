<?php
$pageTitle = 'Reports';
require_once '../includes/header.php';
requireAdmin();

// --- Summary Cards ---
$stats = [
    'total_sales' => 0,
    'total_expenses' => 0,
    'total_profit' => 0
];

// Total sales
$result = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM sales");
if ($row = mysqli_fetch_assoc($result)) {
    $stats['total_sales'] = $row['total'] ?? 0;
}
// Total expenses
$result = mysqli_query($conn, "SELECT SUM(amount) as total FROM expenses");
if ($row = mysqli_fetch_assoc($result)) {
    $stats['total_expenses'] = $row['total'] ?? 0;
}
// Profit
$stats['total_profit'] = $stats['total_sales'] - $stats['total_expenses'];

// --- Sales & Expenses Over Time (last 14 days) ---
$dates = [];
$sales_over_time = [];
$expenses_over_time = [];
for ($i = 13; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = $date;
    $sales_over_time[$date] = 0;
    $expenses_over_time[$date] = 0;
}
// Sales
$result = mysqli_query($conn, "SELECT DATE(created_at) as date, SUM(total_amount) as total FROM sales WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) GROUP BY DATE(created_at)");
while ($row = mysqli_fetch_assoc($result)) {
    $sales_over_time[$row['date']] = (float)$row['total'];
}
// Expenses
$result = mysqli_query($conn, "SELECT date, SUM(amount) as total FROM expenses WHERE date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) GROUP BY date");
while ($row = mysqli_fetch_assoc($result)) {
    $expenses_over_time[$row['date']] = (float)$row['total'];
}

// --- Top Selling Products ---
$top_products = [];
$result = mysqli_query($conn, "SELECT p.name, SUM(si.quantity) as total_quantity FROM sale_items si JOIN products p ON p.id = si.product_id GROUP BY p.id ORDER BY total_quantity DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($result)) {
    $top_products[] = $row;
}

// --- Recent Sales ---
$recent_sales = [];
$result = mysqli_query($conn, "SELECT s.id, s.total_amount, s.created_at, u.username FROM sales s JOIN users u ON u.id = s.user_id ORDER BY s.created_at DESC LIMIT 10");
while ($row = mysqli_fetch_assoc($result)) {
    $recent_sales[] = $row;
}

// --- Recent Expenses ---
$recent_expenses = [];
$result = mysqli_query($conn, "SELECT * FROM expenses ORDER BY date DESC LIMIT 10");
while ($row = mysqli_fetch_assoc($result)) {
    $recent_expenses[] = $row;
}
?>
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Sales</h5>
                <h2 class="card-text">$<?php echo number_format($stats['total_sales'], 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Total Expenses</h5>
                <h2 class="card-text">$<?php echo number_format($stats['total_expenses'], 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total Profit</h5>
                <h2 class="card-text">$<?php echo number_format($stats['total_profit'], 2); ?></h2>
            </div>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Sales & Expenses (Last 14 Days)</h5>
                <canvas id="salesExpensesChart" style="min-height:300px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Top Selling Products</h5>
                <canvas id="topProductsChart" style="min-height:300px;"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Recent Sales</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_sales as $sale): ?>
                            <tr>
                                <td><?php echo $sale['id']; ?></td>
                                <td><?php echo htmlspecialchars($sale['username']); ?></td>
                                <td>$<?php echo number_format($sale['total_amount'], 2); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($sale['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Recent Expenses</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_expenses as $expense): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($expense['name']); ?></td>
                                <td>$<?php echo number_format($expense['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($expense['date'])); ?></td>
                                <td><?php echo htmlspecialchars($expense['notes']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<canvas id="salesExpensesChart"></canvas>
<canvas id="topProductsChart"></canvas>
<?php if (isset($extraScripts)): ?>
    <?php echo $extraScripts; ?>
<?php endif; ?>
<?php require_once '../includes/footer.php'; ?> 