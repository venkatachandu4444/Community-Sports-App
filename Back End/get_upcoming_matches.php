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
    
    // Get upcoming matches with team and sport details
    $sql = "SELECT m.match_id, m.sport_id, m.match_type, m.venue, m.event_date, m.status, m.banner_image,
                   s.sport_name
            FROM matches m
            LEFT JOIN sports s ON m.sport_id = s.sport_id
            WHERE m.organizer_id = ? AND m.status = 'UPCOMING'
            ORDER BY m.event_date ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $organizer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $matches = [];
    while ($row = $result->fetch_assoc()) {
        // Get teams for this match
        $team_sql = "SELECT t.team_name 
                     FROM teams t
                     WHERE t.community_id = ? AND t.sport_id = ?
                     LIMIT 2";
        $team_stmt = $conn->prepare($team_sql);
        $community_id = $organizer_id; // Using organizer_id as community_id
        $team_stmt->bind_param("ii", $community_id, $row['sport_id']);
        $team_stmt->execute();
        $team_result = $team_stmt->get_result();
        
        $teams = [];
        while ($team = $team_result->fetch_assoc()) {
            $teams[] = $team['team_name'];
        }
        $team_stmt->close();
        
        // Build team names (Team A vs Team B)
        $team_a = isset($teams[0]) ? $teams[0] : "Team A";
        $team_b = isset($teams[1]) ? $teams[1] : "Team B";
        
        $matches[] = [
            "match_id" => $row['match_id'],
            "match_type" => $row['match_type'] ?? "Match",
            "sport_name" => $row['sport_name'],
            "team_a" => $team_a,
            "team_b" => $team_b,
            "venue" => $row['venue'],
            "event_date" => $row['event_date'],
            "status" => $row['status'],
            "banner_image" => $row['banner_image'] ?? ""
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
    echo json_encode(["success" => false, "error" => "Server error: " . $e->getMessage()]);
}
?>
