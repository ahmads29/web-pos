<?php
$pageTitle = 'User Management';
require_once '../includes/header.php';
requireAdmin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Check if username already exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = 'Username already exists.';
        } else {
            // Create new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $username, $hashed_password, $role);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'User created successfully.';
            } else {
                $error = 'Error creating user.';
            }
        }
    }
}

// Get all users
$users = [];
$result = mysqli_query($conn, "SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Create New User</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="cashier">Cashier</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Create User</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">User List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 