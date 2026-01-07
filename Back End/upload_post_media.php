<?php
include("../config/db.php");

$target = "../uploads/posts/";
$file = uniqid() . "_" . $_FILES['media']['name'];
move_uploaded_file($_FILES['media']['tmp_name'], $target.$file);

$conn->query(
 "INSERT INTO post_media (post_id,media_url)
  VALUES ($_POST[post_id],'uploads/posts/$file')"
);

echo json_encode(["success"=>true]);
?>
