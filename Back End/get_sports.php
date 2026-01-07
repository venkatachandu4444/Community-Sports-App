<?php
header('Content-Type: application/json');
include("config/db.php");

// Fetch all sports from database
$sql = "SELECT sport_id, sport_name, sport_type FROM sports ORDER BY sport_name ASC";
$result = $conn->query($sql);

if ($result) {
    $sports = array();
    
    while ($row = $result->fetch_assoc()) {
        $sports[] = array(
            'sport_id' => (int)$row['sport_id'],
            'sport_name' => $row['sport_name'],
            'sport_type' => $row['sport_type']
        );
    }
    
    echo json_encode(array(
        'success' => true,
        'sports' => $sports
    ));
} else {
    echo json_encode(array(
        'success' => false,
        'error' => 'Failed to fetch sports: ' . $conn->error
    ));
}

$conn->close();
?>
