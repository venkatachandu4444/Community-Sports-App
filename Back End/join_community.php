<?php
include("../config/db.php");

$user_id = $_POST['user_id'];
$code = $_POST['community_code'];

$res = $conn->query(
    "SELECT community_id FROM community_codes 
     WHERE community_code='$code' AND is_active=1"
);

if ($row = $res->fetch_assoc()) {
    $cid = $row['community_id'];
    $conn->query(
        "INSERT INTO community_members (community_id,user_id)
         VALUES ($cid,$user_id)"
    );
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Invalid code"]);
}
?>
