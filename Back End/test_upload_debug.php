<?php
header('Content-Type: application/json');

// Test database connection and table structure
include("../config/db.php");

// Check if highlights table exists and show structure
$result = $conn->query("DESCRIBE highlights");

if ($result) {
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row;
    }
    
    echo json_encode([
        "success" => true,
        "message" => "Database connected",
        "columns" => $columns,
        "post_data" => $_POST,
        "files" => isset($_FILES['file']) ? [
            "name" => $_FILES['file']['name'],
            "size" => $_FILES['file']['size'],
            "error" => $_FILES['file']['error']
        ] : "No file"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "Table query failed: " . $conn->error
    ]);
}

$conn->close();
?>
