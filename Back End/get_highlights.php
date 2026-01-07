<?php
header('Content-Type: application/json');
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

// Fetch highlights for organizer
$stmt = $conn->prepare("SELECT highlight_id, match_id, media_url, thumbnail_url, title, description, media_type, duration, created_at FROM highlights WHERE organizer_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$result = $stmt->get_result();

$highlights = [];
while ($row = $result->fetch_assoc()) {
    $highlights[] = [
        "highlight_id" => $row['highlight_id'],
        "match_id" => $row['match_id'],
        "media_url" => $row['media_url'],
        "thumbnail_url" => $row['thumbnail_url'],
        "title" => $row['title'],
        "description" => $row['description'],
        "media_type" => $row['media_type'],
        "duration" => $row['duration'],
        "created_at" => $row['created_at']
    ];
}

echo json_encode([
    "success" => true,
    "count" => count($highlights),
    "highlights" => $highlights
]);

$stmt->close();
$conn->close();
?>
