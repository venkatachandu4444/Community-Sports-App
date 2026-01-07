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

// Get count of highlights for organizer
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM highlights WHERE organizer_id = ?");
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
?>
