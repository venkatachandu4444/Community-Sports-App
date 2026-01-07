<?php
header('Content-Type: application/json');
include("../config/db.php");

// Get POST data
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Validate input
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Email and password are required']);
    exit();
}

// Query organizer by email
$stmt = $conn->prepare("SELECT * FROM organizers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
    $stmt->close();
    $conn->close();
    exit();
}

$organizer = $result->fetch_assoc();
$stmt->close();

// Verify password (plain text comparison)
if ($password !== $organizer['password_hash']) {
    echo json_encode(['success' => false, 'error' => 'Invalid email or password']);
    $conn->close();
    exit();
}

// Login successful - return organizer data
echo json_encode([
    'success' => true,
    'organizer_id' => (string)$organizer['id'], // Cast to string for compatibility
    'name' => $organizer['name'],
    'email' => $organizer['email'],
    'phone' => $organizer['phone'] ?? '',
    'organization' => $organizer['organization'] ?? '',
    'community_name' => $organizer['community_name'] ?? 'Community',
    'community_code' => $organizer['community_code'] ? (string)$organizer['community_code'] : '', // Cast to string
    'message' => 'Login successful'
]);

$conn->close();
?>
