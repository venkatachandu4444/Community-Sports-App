<?php
// Post sport results - simplified version without match dependency
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    include("../config/db.php");
    
    // Get form data
    $sport_id = isset($_POST['sport_id']) ? intval($_POST['sport_id']) : 0;
    $organizer_id = isset($_POST['organizer_id']) ? intval($_POST['organizer_id']) : 0;
    $winning_team = isset($_POST['winning_team']) ? trim($_POST['winning_team']) : '';
    $result_summary = isset($_POST['result_summary']) ? trim($_POST['result_summary']) : '';
    $first_prize = isset($_POST['first_prize']) ? trim($_POST['first_prize']) : '';
    $second_prize = isset($_POST['second_prize']) ? trim($_POST['second_prize']) : '';
    $third_prize = isset($_POST['third_prize']) ? trim($_POST['third_prize']) : '';
    
    // Validate required fields
    if ($sport_id <= 0 || $organizer_id <= 0 || empty($winning_team) || empty($result_summary)) {
        echo json_encode(["success" => false, "error" => "Missing required fields"]);
        exit();
    }
    
    // Handle image uploads (if any)
    $result_images = [];
    // TODO: Implement image upload handling
    $result_images_json = json_encode($result_images);
    
    // Insert sport results
    $sql = "INSERT INTO sport_results (sport_id, organizer_id, winning_team, result_summary, 
                                       first_prize_winner, second_prize_winner, third_prize_winner, 
                                       result_images, posted_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "SQL prepare failed: " . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("iissssss", 
        $sport_id, 
        $organizer_id, 
        $winning_team, 
        $result_summary, 
        $first_prize, 
        $second_prize, 
        $third_prize, 
        $result_images_json
    );
    
    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "error" => "Failed to save results: " . $stmt->error]);
        exit();
    }
    
    echo json_encode([
        "success" => true,
        "message" => "Results posted successfully!",
        "result_id" => $stmt->insert_id
    ]);
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Exception: " . $e->getMessage()]);
}
?>
