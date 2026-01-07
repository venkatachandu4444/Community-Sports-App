<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    include("../config/db.php");
    
    $organizer_id = isset($_GET['organizer_id']) ? intval($_GET['organizer_id']) : 0;
    
    if ($organizer_id <= 0) {
        echo json_encode(["success" => false, "error" => "Invalid organizer ID"]);
        exit();
    }
    
    // Get matches that don't have results yet
    // Use LEFT JOIN and check if result_id IS NULL to find matches without results
    $sql = "SELECT m.match_id, m.match_title, m.sport_id, m.match_date, m.match_time, s.sport_name 
            FROM matches m 
            LEFT JOIN sports s ON m.sport_id = s.sport_id 
            LEFT JOIN match_results mr ON m.match_id = mr.match_id
            WHERE m.organizer_id = ? AND mr.result_id IS NULL
            ORDER BY m.match_date DESC, m.match_time DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "SQL prepare failed: " . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("i", $organizer_id);
    
    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "error" => "SQL execute failed: " . $stmt->error]);
        exit();
    }
    
    $result = $stmt->get_result();
    
    $matches = [];
    while ($row = $result->fetch_assoc()) {
        $matches[] = [
            "match_id" => $row['match_id'],
            "match_title" => $row['match_title'],
            "sport_id" => $row['sport_id'],
            "sport_name" => $row['sport_name'] ?? 'Unknown Sport',
            "match_date" => $row['match_date'],
            "match_time" => $row['match_time']
        ];
    }
    
    echo json_encode([
        "success" => true,
        "matches" => $matches
    ]);
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Exception: " . $e->getMessage()]);
}
?>
