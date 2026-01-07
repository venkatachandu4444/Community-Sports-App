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
    
    // Get completed matches with results
    $sql = "SELECT m.match_id, m.sport_id, m.venue, m.event_date, m.status,
                   s.sport_name, mr.result_id, mr.winning_team, mr.result_summary,
                   mr.first_prize_winner, mr.second_prize_winner, mr.third_prize_winner,
                   mr.result_images, mr.posted_at
            FROM matches m
            INNER JOIN match_results mr ON m.match_id = mr.match_id
            LEFT JOIN sports s ON m.sport_id = s.sport_id
            WHERE m.organizer_id = ?
            ORDER BY mr.posted_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $organizer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $matches = [];
    while ($row = $result->fetch_assoc()) {
        // Build match title from venue or use a generic title
        $match_title = "Match #" . $row['match_id'] . " - " . $row['sport_name'];
        
        $matches[] = [
            "match_id" => $row['match_id'],
            "match_title" => $match_title,
            "sport_name" => $row['sport_name'],
            "match_date" => $row['event_date'],
            "match_time" => "", // Not available in current schema
            "result_id" => $row['result_id'],
            "winning_team" => $row['winning_team'],
            "result_summary" => $row['result_summary'],
            "first_prize" => $row['first_prize_winner'],
            "second_prize" => $row['second_prize_winner'],
            "third_prize" => $row['third_prize_winner'],
            "result_images" => $row['result_images'],
            "posted_at" => $row['posted_at']
        ];
    }
    
    echo json_encode([
        "success" => true,
        "count" => count($matches),
        "matches" => $matches
    ]);
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Server error"]);
}
?>
