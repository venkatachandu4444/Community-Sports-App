<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

try {
    include("../config/db.php");
    
    // Check all highlights in database
    $result = $conn->query("SELECT highlight_id, organizer_id, title, media_url, created_at FROM highlights ORDER BY created_at DESC LIMIT 10");
    
    $highlights = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $highlights[] = $row;
        }
    }
    
    echo json_encode([
        "success" => true,
        "total_highlights" => count($highlights),
        "highlights" => $highlights
    ]);
    
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => "Database error"]);
}
?>
