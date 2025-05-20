<?php
$pageTitle = 'Expenses Management';
require_once '../includes/header.php';
requireAdmin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $amount = floatval($_POST['amount']);
                $date = mysqli_real_escape_string($conn, $_POST['date']);
                $notes = mysqli_real_escape_string($conn, $_POST['notes']);
                
                $sql = "INSERT INTO expenses (name, amount, date, notes) VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sdss", $name, $amount, $date, $notes);
                mysqli_stmt_execute($stmt);
                break;

            case 'delete':
                $id = intval($_POST['id']);
                mysqli_query($conn, "DELETE FROM expenses WHERE id = $id");
                break;
        }
    }
}

// Get expenses summary
$summary = [
    'total' => 0,
    'monthly' => 0,
    'yearly' => 0
];

$result = mysqli_query($conn, "SELECT SUM(amount) as total FROM expenses");
if ($row = mysqli_fetch_assoc($result)) {
    $summary['total'] = $row['total'] ?? 0;
}

$result = mysqli_query($conn, "
    SELECT SUM(amount) as total 
    FROM expenses 
    WHERE MONTH(date) = MONTH(CURRENT_DATE()) 
    AND YEAR(date) = YEAR(CURRENT_DATE())
");
if ($row = mysqli_fetch_assoc($result)) {
    $summary['monthly'] = $row['total'] ?? 0;
}

$result = mysqli_query($conn, "
    SELECT SUM(amount) as total 
    FROM expenses 
    WHERE YEAR(date) = YEAR(CURRENT_DATE())
");
if ($row = mysqli_fetch_assoc($result)) {
    $summary['yearly'] = $row['total'] ?? 0;
}

// Get all expenses
$expenses = [];
$result = mysqli_query($conn, "SELECT * FROM expenses ORDER BY date DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $expenses[] = $row;
}
?>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Expenses</h5>
                <h2 class="card-text">$<?php echo number_format($summary['total'], 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Monthly Expenses</h5>
                <h2 class="card-text">$<?php echo number_format($summary['monthly'], 2); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Yearly Expenses</h5>
                <h2 class="card-text">$<?php echo number_format($summary['yearly'], 2); ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Add Expense Button -->
<div class="mb-4">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
        <i class="bi bi-plus-circle"></i> Add New Expense
    </button>
</div>

<!-- Expenses Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($expense['date'])); ?></td>
                        <td><?php echo htmlspecialchars($expense['name']); ?></td>
                        <td>$<?php echo number_format($expense['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($expense['notes']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-danger delete-expense" 
                                    data-id="<?php echo $expense['id']; ?>">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" name="amount" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$extraScripts = <<<'EOT'
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-expense').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this expense?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type='hidden' name='action' value='delete'>
                    <input type='hidden' name='id' value='${this.dataset.id}'>
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
EOT;
require_once '../includes/footer.php';
?> 