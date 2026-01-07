<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

try {
    include("../config/db.php");
    
    $organizer_id = isset($_GET['organizer_id']) ? intval($_GET['organizer_id']) : 0;
    
    if ($organizer_id <= 0) {
        echo json_encode(["success" => false, "error" => "Invalid organizer ID"]);
        exit();
    }
    
    // Count upcoming matches (status = 'UPCOMING')
    $sql = "SELECT COUNT(*) as count 
            FROM matches 
            WHERE organizer_id = ? AND status = 'UPCOMING'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $organizer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo json_encode([
        "success" => true,
        "count" => intval($row['count'])
    ]);
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Server error", "count" => 0]);
}
?>
