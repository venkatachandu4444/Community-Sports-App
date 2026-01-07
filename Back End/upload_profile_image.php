<?php
header("Content-Type: application/json");
include("../config/db.php");

// Get user ID
$user_id = $_POST['user_id'] ?? '';

if (empty($user_id)) {
    echo json_encode(["success" => false, "error" => "User ID is required"]);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['profile_image'])) {
    echo json_encode(["success" => false, "error" => "No image file uploaded"]);
    exit;
}

$file = $_FILES['profile_image'];

// Validate file
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
$max_size = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(["success" => false, "error" => "Invalid file type. Only JPG and PNG allowed"]);
    exit;
}

if ($file['size'] > $max_size) {
    echo json_encode(["success" => false, "error" => "File too large. Max 5MB"]);
    exit;
}

// Create upload directory if it doesn't exist
$upload_dir = "../../uploads/profiles/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = "profile_" . $user_id . "_" . time() . "." . $extension;
$filepath = $upload_dir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Update database
    $db_path = "uploads/profiles/" . $filename;
    $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
    $stmt->bind_param("si", $db_path, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Profile image uploaded successfully",
            "image_url" => "http://localhost/CommunitySportsApp/" . $db_path
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to update database"]);
    }
    
    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "Failed to upload file"]);
}

$conn->close();
?>
