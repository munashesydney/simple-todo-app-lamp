<?php
// Establish a global database connection
$mysqli = mysqli_connect("localhost", "root", "letmein", "todolistdb");

if (!$mysqli) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    die();
}

echo "Connected";
?>