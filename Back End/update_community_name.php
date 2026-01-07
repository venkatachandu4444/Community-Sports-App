<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    include("../config/db.php");
    
    // Get form data
    $organizer_id = isset($_POST['organizer_id']) ? intval($_POST['organizer_id']) : 0;
    $community_name = isset($_POST['community_name']) ? trim($_POST['community_name']) : '';
    
    // Validate input
    if ($organizer_id <= 0) {
        echo json_encode(["success" => false, "error" => "Invalid organizer ID"]);
        exit();
    }
    
    if (empty($community_name)) {
        echo json_encode(["success" => false, "error" => "Community name cannot be empty"]);
        exit();
    }
    
    // Update community name for the organizer
    $sql = "UPDATE communities c 
            JOIN organizers o ON c.community_id = o.community_id 
            SET c.community_name = ? 
            WHERE o.id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "SQL prepare failed: " . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("si", $community_name, $organizer_id);
    
    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "error" => "Failed to update: " . $stmt->error]);
        exit();
    }
    
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Community name updated successfully!",
            "community_name" => $community_name
        ]);
    } else {
        // No rows updated - might be because community doesn't exist or name is same
        echo json_encode(["success" => false, "error" => "No changes made. Community not found or name is the same."]);
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Exception: " . $e->getMessage()]);
}
?>
