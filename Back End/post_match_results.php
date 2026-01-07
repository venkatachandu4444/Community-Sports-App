<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

try {
    include("../config/db.php");
    
    $path = "../uploads/match_results/";
    if (!file_exists($path)) {
        @mkdir($path, 0777, true);
    }
    
    // Get POST data
    $match_id = isset($_POST['match_id']) ? intval($_POST['match_id']) : 0;
    $organizer_id = isset($_POST['organizer_id']) ? intval($_POST['organizer_id']) : 0;
    $winning_team = isset($_POST['winning_team']) ? $_POST['winning_team'] : '';
    $result_summary = isset($_POST['result_summary']) ? $_POST['result_summary'] : '';
    $first_prize = isset($_POST['first_prize']) ? $_POST['first_prize'] : '';
    $second_prize = isset($_POST['second_prize']) ? $_POST['second_prize'] : '';
    $third_prize = isset($_POST['third_prize']) ? $_POST['third_prize'] : '';
    
    // Validate
    if ($match_id <= 0 || $organizer_id <= 0) {
        echo json_encode(["success" => false, "error" => "Invalid match or organizer ID"]);
        exit();
    }
    
    // Handle image upload
    $result_images = [];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $file = uniqid() . "_" . basename($_FILES['image']['name']);
        $targetPath = $path . $file;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $result_images[] = "uploads/match_results/" . $file;
        }
    }
    
    $images_json = json_encode($result_images);
    
    // Insert result
    $stmt = $conn->prepare("INSERT INTO match_results (match_id, organizer_id, winning_team, result_summary, first_prize_winner, second_prize_winner, third_prize_winner, result_images, posted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iissssss", $match_id, $organizer_id, $winning_team, $result_summary, $first_prize, $second_prize, $third_prize, $images_json);
    
    if ($stmt->execute()) {
        // Update match status to completed
        $update_stmt = $conn->prepare("UPDATE matches SET status = 'completed' WHERE match_id = ?");
        $update_stmt->bind_param("i", $match_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        echo json_encode([
            "success" => true,
            "message" => "Results published successfully",
            "result_id" => $stmt->insert_id
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "Database error"]);
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Server error"]);
}
?>
