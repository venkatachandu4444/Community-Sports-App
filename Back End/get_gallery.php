<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

try {
    include("../config/db.php");

    // Get GET parameter
    $organizer_id = isset($_GET['organizer_id']) ? intval($_GET['organizer_id']) : 0;

    // Validate input
    if ($organizer_id <= 0) {
        echo json_encode([
            "success" => false,
            "error" => "Valid organizer ID is required"
        ]);
        exit();
    }

    // Fetch gallery photos for organizer
    $stmt = $conn->prepare("SELECT photo_id, match_id, photo_url, caption, uploaded_at FROM gallery_photos WHERE organizer_id = ? ORDER BY uploaded_at DESC");
    
    if (!$stmt) {
        // Table might not exist, return empty array
        echo json_encode([
            "success" => true,
            "count" => 0,
            "photos" => []
        ]);
        exit();
    }
    
    $stmt->bind_param("i", $organizer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $photos = [];
    while ($row = $result->fetch_assoc()) {
        $photos[] = [
            "photo_id" => $row['photo_id'],
            "match_id" => $row['match_id'],
            "photo_url" => $row['photo_url'],
            "caption" => $row['caption'],
            "uploaded_at" => $row['uploaded_at']
        ];
    }

    echo json_encode([
        "success" => true,
        "count" => count($photos),
        "photos" => $photos
    ]);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => true,
        "count" => 0,
        "photos" => []
    ]);
}
?>
