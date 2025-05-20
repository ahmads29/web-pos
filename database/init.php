<?php
require_once '../config/database.php';

// Read and execute the schema file
$sql = file_get_contents('schema.sql');

// Split the SQL file into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

// Execute each statement
foreach ($statements as $statement) {
    if (!empty($statement)) {
        if (!mysqli_query($conn, $statement)) {
            die("Error executing statement: " . mysqli_error($conn) . "\nStatement: " . $statement);
        }
    }
}

echo "Database initialized successfully!";
mysqli_close($conn);
?> 