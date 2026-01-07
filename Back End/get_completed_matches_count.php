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
    
    // Count completed matches (matches with results)
    $sql = "SELECT COUNT(DISTINCT mr.match_id) as count 
            FROM match_results mr 
            INNER JOIN matches m ON mr.match_id = m.match_id 
            WHERE m.organizer_id = ?";
    
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
