<?php
header('Content-Type: application/json');
include("../config/db.php");

// Get POST data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$organization = isset($_POST['organization']) ? trim($_POST['organization']) : '';

// Validate inputs
if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($organization)) {
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email format']);
    exit();
}

// Validate password length
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
    exit();
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM organizers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Email already registered']);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// Generate unique 6-digit community code
$community_code = null;
$max_attempts = 10;
$attempt = 0;

while ($attempt < $max_attempts) {
    // Generate random 6-digit code
    $community_code = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    
    // Check if code already exists
    $stmt = $conn->prepare("SELECT id FROM organizers WHERE community_code = ?");
    $stmt->bind_param("s", $community_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        // Code is unique, break the loop
        $stmt->close();
        break;
    }
    $stmt->close();
    $attempt++;
}

if ($attempt >= $max_attempts) {
    echo json_encode(['success' => false, 'error' => 'Failed to generate unique code. Please try again.']);
    $conn->close();
    exit();
}

// Hash password (using plain text for now as per your original implementation)
// TODO: Implement proper password hashing with password_hash() in production
$community_name = $organization; // Use organization name as community name

// Insert new organizer with auto-generated community code
$stmt = $conn->prepare("INSERT INTO organizers (name, email, password_hash, phone, organization, community_name, community_code, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssssss", $name, $email, $password, $phone, $organization, $community_name, $community_code);

if ($stmt->execute()) {
    $organizer_id = $stmt->insert_id;
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'organizer_id' => $organizer_id,
        'name' => $name,
        'email' => $email,
        'community_code' => $community_code,
        'community_name' => $community_name
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Registration failed. Please try again.'
    ]);
}

$stmt->close();
$conn->close();
?>
