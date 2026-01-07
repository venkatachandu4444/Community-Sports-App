<?php
// Test script to verify organizer registration with community code generation
header('Content-Type: application/json');

// Database connection (update these if needed)
$host = 'localhost';
$dbname = 'community_sports_app';
$username = 'root';
$password = '';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    
    // Test 1: Check if community_code column exists
    $result = $conn->query("SHOW COLUMNS FROM organizers LIKE 'community_code'");
    if ($result->num_rows == 0) {
        echo json_encode([
            'success' => false, 
            'error' => 'Column community_code does not exist! Please run the SQL to add it.',
            'step' => 'column_check'
        ]);
        exit();
    }
    
    // Test 2: Generate a test code
    $test_code = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database columns exist',
        'test_code' => $test_code,
        'column_exists' => true
    ]);
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
