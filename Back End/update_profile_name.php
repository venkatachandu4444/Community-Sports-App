<?php
header("Content-Type: application/json");
include("../config/db.php");

// Get input
$user_id = $_POST['user_id'] ?? '';
$name = $_POST['name'] ?? '';

// Validate
if (empty($user_id) || empty($name)) {
    echo json_encode(["success" => false, "error" => "User ID and name are required"]);
    exit;
}

// Update name in database
$stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
$stmt->bind_param("si", $name, $user_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Name updated successfully",
        "name" => $name
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "Failed to update name"
    ]);
}

$stmt->close();
$conn->close();
?>
