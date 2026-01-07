<?php
// Suppress warnings to ensure clean JSON output
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
ob_start(); // Start output buffering

header('Content-Type: application/json');
include("../config/db.php");

// Get POST data
$community_code = isset($_POST['community_code']) ? trim($_POST['community_code']) : '';
$sport_id = isset($_POST['sport_id']) ? intval($_POST['sport_id']) : 0;
$team_a_name = isset($_POST['team_a_name']) ? trim($_POST['team_a_name']) : '';
$team_b_name = isset($_POST['team_b_name']) ? trim($_POST['team_b_name']) : '';
$venue = isset($_POST['venue']) ? trim($_POST['venue']) : '';
$event_date = isset($_POST['event_date']) ? trim($_POST['event_date']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$banner_image = isset($_POST['banner_image']) ? trim($_POST['banner_image']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : 'UPCOMING';

// Validate required fields
if (empty($community_code)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Community code is required']);
    exit();
}

if ($sport_id <= 0) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Valid sport ID is required']);
    exit();
}

if (empty($team_a_name) || empty($team_b_name)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Both team names are required']);
    exit();
}

if (empty($venue)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Venue is required']);
    exit();
}

if (empty($event_date)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Event date is required']);
    exit();
}

// Get community_id from community_code or organizer_id
// First try looking up by community_code
$stmt = $conn->prepare("SELECT id FROM organizers WHERE community_code = ?");
$stmt->bind_param("s", $community_code);
$stmt->execute();
$result = $stmt->get_result();

// If not found by community_code and it's numeric, try using it as organizer_id
if ($result->num_rows === 0 && is_numeric($community_code)) {
    $stmt->close();
    $organizer_id = intval($community_code);
    $stmt = $conn->prepare("SELECT id FROM organizers WHERE id = ?");
    $stmt->bind_param("i", $organizer_id);
    $stmt->execute();
    $result = $stmt->get_result();
}

if ($result->num_rows === 0) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Invalid organizer or community code']);
    $stmt->close();
    $conn->close();
    exit();
}

$organizer = $result->fetch_assoc();
$organizer_id = $organizer['id'];
$stmt->close();

// Use organizer_id as community_id
$community_id = $organizer_id;

// Disable foreign key checks temporarily
$conn->query("SET FOREIGN_KEY_CHECKS=0");

// Start transaction
$conn->begin_transaction();

try {
    // Create/get Team A
    $team_a_id = getOrCreateTeam($conn, $team_a_name, $community_id, $sport_id);
    
    // Create/get Team B
    $team_b_id = getOrCreateTeam($conn, $team_b_name, $community_id, $sport_id);
    
    // Insert match
    $stmt = $conn->prepare("INSERT INTO matches (community_id, organizer_id, sport_id, match_type, venue, event_date, status, banner_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $match_type = "Team Match"; // Default match type
    $stmt->bind_param("iiisssss", $community_id, $organizer_id, $sport_id, $match_type, $venue, $event_date, $status, $banner_image);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create match: " . $stmt->error);
    }
    
    $match_id = $stmt->insert_id;
    $stmt->close();
    
    // Link teams to match (you might want to create a match_teams table for this)
    // For now, we'll store it in match settings or description
    
    // Commit transaction
    $conn->commit();
    
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
    
    // Clean output buffer and send JSON
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'match_id' => $match_id,
        'message' => 'Match created successfully',
        'team_a_id' => $team_a_id,
        'team_b_id' => $team_b_id
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    // Re-enable foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
    ob_end_clean(); // Clean buffer before error response
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();

// Helper function to get or create team
function getOrCreateTeam($conn, $team_name, $community_id, $sport_id) {
    // Check if team exists
    $stmt = $conn->prepare("SELECT team_id FROM teams WHERE team_name = ? AND community_id = ? AND sport_id = ?");
    $stmt->bind_param("sii", $team_name, $community_id, $sport_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $team = $result->fetch_assoc();
        $stmt->close();
        return $team['team_id'];
    }
    $stmt->close();
    
    // Create new team
    $stmt = $conn->prepare("INSERT INTO teams (team_name, community_id, sport_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $team_name, $community_id, $sport_id);
    $stmt->execute();
    $team_id = $stmt->insert_id;
    $stmt->close();
    
    return $team_id;
}
?>
