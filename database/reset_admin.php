<?php
require_once '../config/database.php';

// Reset admin password to 'admin123'
$password = password_hash('admin123', PASSWORD_DEFAULT);

// First, check if admin exists
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = 'admin'");
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    // Update existing admin
    $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE username = 'admin'");
    mysqli_stmt_bind_param($stmt, "s", $password);
} else {
    // Create new admin
    $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, role) VALUES ('admin', ?, 'admin')");
    mysqli_stmt_bind_param($stmt, "s", $password);
}

if (mysqli_stmt_execute($stmt)) {
    echo "Admin password has been reset successfully!<br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br>";
    echo "<a href='../login.php'>Go to Login</a>";
} else {
    echo "Error resetting admin password: " . mysqli_error($conn);
}

mysqli_close($conn);
?> 