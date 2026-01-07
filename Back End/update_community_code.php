<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
$host = 'localhost';
$dbname = 'community_sports_db';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get POST data
    $organizer_id = $_POST['organizer_id'] ?? '';
    $community_code = $_POST['community_code'] ?? '';
    
    // Validate inputs
    if (empty($organizer_id) || empty($community_code)) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    // Validate code format (6 digits)
    if (!preg_match('/^\d{6}$/', $community_code)) {
        echo json_encode(['success' => false, 'error' => 'Code must be 6 digits']);
        exit;
    }
    
    // Check if code already exists for another organizer
    $stmt = $conn->prepare("SELECT organizer_id FROM organizers WHERE community_code = ? AND organizer_id != ?");
    $stmt->execute([$community_code, $organizer_id]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'error' => 'This code is already in use']);
        exit;
    }
    
    // Update the community code
    $stmt = $conn->prepare("UPDATE organizers SET community_code = ? WHERE organizer_id = ?");
    $stmt->execute([$community_code, $organizer_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Community code updated successfully',
        'community_code' => $community_code
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
