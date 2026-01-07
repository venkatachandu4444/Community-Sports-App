<?php
include("../config/db.php");

$name = $_POST['community_name'];
$location = $_POST['location'];
$created_by = $_POST['user_id'];

$conn->query(
    "INSERT INTO communities (community_name, location, created_by)
     VALUES ('$name','$location',$created_by)"
);

$community_id = $conn->insert_id;
$code = strtoupper(substr(md5(rand()), 0, 6));

$conn->query(
    "INSERT INTO community_codes (community_id, community_code)
     VALUES ($community_id,'$code')"
);

echo json_encode(["community_code" => $code]);
?>
