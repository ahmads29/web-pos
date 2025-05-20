<?php
$pageTitle = 'Dashboard';
require_once '../includes/header.php';
requireAdmin();

// Get summary statistics
$stats = [
    'total_sales' => 0,
    'total_products' => 0,
    'total_categories' => 0,
    'total_expenses' => 0
];

// Total sales
$result = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM sales");
if ($row = mysqli_fetch_assoc($result)) {
    $stats['total_sales'] = $row['total'] ?? 0;
}

// Total products
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
if ($row = mysqli_fetch_assoc($result)) {
    $stats['total_products'] = $row['total'];
}

// Total categories
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM categories");
if ($row = mysqli_fetch_assoc($result)) {
    $stats['total_categories'] = $row['total'];
}

// Total expenses
$result = mysqli_query($conn, "SELECT SUM(amount) as total FROM expenses");
if ($row = mysqli_fetch_assoc($result)) {
    $stats['total_expenses'] = $row['total'] ?? 0;
}

// Get recent sales data for chart
$sales_data = [];
$result = mysqli_query($conn, "
    SELECT DATE(created_at) as date, SUM(total_amount) as total 
    FROM sales 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
");

while ($row = mysqli_fetch_assoc($result)) {
    $sales_data[] = $row;
}

// Get top selling products
$top_products = [];
$result = mysqli_query($conn, "
    SELECT p.name, SUM(si.quantity) as total_quantity
    FROM sale_items si
    JOIN products p ON p.id = si.product_id
    GROUP BY p.id
    ORDER BY total_quantity DESC
    LIMIT 5
");

while ($row = mysqli_fetch_assoc($result)) {
    $top_products[] = $row;
}
?>

<!-- Summary Cards -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Sales</h5>
                <h2 class="card-text">$<?php echo number_format($stats['total_sales'], 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total Products</h5>
                <h2 class="card-text"><?php echo $stats['total_products']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Categories</h5>
                <h2 class="card-text"><?php echo $stats['total_categories']; ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Total Expenses</h5>
                <h2 class="card-text">$<?php echo number_format($stats['total_expenses'], 2); ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Sales Last 7 Days</h5>
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Top Selling Products</h5>
                <canvas id="productsChart"></canvas>
            </div>
        </div>
    </div>
</div>

<?php
$extraScripts = "
<script>
// Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: " . json_encode(array_column($sales_data, 'date')) . ",
        datasets: [{
            label: 'Daily Sales',
            data: " . json_encode(array_column($sales_data, 'total')) . ",
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Products Chart
const productsCtx = document.getElementById('productsChart').getContext('2d');
new Chart(productsCtx, {
    type: 'doughnut',
    data: {
        labels: " . json_encode(array_column($top_products, 'name')) . ",
        datasets: [{
            data: " . json_encode(array_column($top_products, 'total_quantity')) . ",
            backgroundColor: [
                'rgb(255, 99, 132)',
                'rgb(54, 162, 235)',
                'rgb(255, 205, 86)',
                'rgb(75, 192, 192)',
                'rgb(153, 102, 255)'
            ]
        }]
    },
    options: {
        responsive: true
    }
});
</script>
";
require_once '../includes/footer.php';
?> 