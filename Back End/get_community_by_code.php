<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    include("../config/db.php");
    
    // Get community code from request
    $community_code = isset($_GET['community_code']) ? trim($_GET['community_code']) : '';
    
    // Validate input
    if (empty($community_code)) {
        echo json_encode(["success" => false, "error" => "Community code is required"]);
        exit();
    }
    
    // Query organizers table for matching community code
    $sql = "SELECT id, community_name, organization, community_code 
            FROM organizers 
            WHERE community_code = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "SQL prepare failed: " . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("s", $community_code);
    
    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "error" => "SQL execute failed: " . $stmt->error]);
        exit();
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Community code found
        $row = $result->fetch_assoc();
        
        echo json_encode([
            "success" => true,
            "organizer_id" => (string)$row['id'],
            "community_name" => $row['community_name'],
            "organization" => $row['organization'] ?? '',
            "community_code" => $row['community_code']
        ]);
    } else {
        // Invalid community code
        echo json_encode([
            "success" => false,
            "error" => "Invalid community code. Please check and try again."
        ]);
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Exception: " . $e->getMessage()]);
}
?>
