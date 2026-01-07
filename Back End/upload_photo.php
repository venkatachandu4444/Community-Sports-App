<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

try {
    include("../config/db.php");
    
    $path = "../uploads/highlights/";
    
    if (!file_exists($path)) {
        @mkdir($path, 0777, true);
    }
    
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file = uniqid() . "_" . basename($_FILES['file']['name']);
        $targetPath = $path . $file;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
            $media_url = "uploads/highlights/" . $file;
            $organizer_id = isset($_POST['organizer_id']) ? intval($_POST['organizer_id']) : 0;
            $match_id = NULL;
            $title = isset($_POST['title']) ? $_POST['title'] : 'Photo';
            $description = isset($_POST['description']) ? $_POST['description'] : '';
            $media_type = 'image';
            $duration = 0;
            $thumbnail_url = $media_url; // For images, thumbnail is same as media_url
            
            $stmt = $conn->prepare("INSERT INTO highlights (organizer_id, match_id, media_url, thumbnail_url, title, description, media_type, duration, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("iisssssi", $organizer_id, $match_id, $media_url, $thumbnail_url, $title, $description, $media_type, $duration);
            
            if ($stmt->execute()) {
                echo json_encode([
                    "success" => true,
                    "message" => "Photo uploaded successfully",
                    "highlight_id" => $stmt->insert_id
                ]);
            } else {
                echo json_encode(["success" => false, "error" => "Database insert failed"]);
            }
            $stmt->close();
        } else {
            echo json_encode(["success" => false, "error" => "File move failed"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "No file uploaded"]);
    }
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Server error"]);
}
?>
