<?php
include("../config/db.php");

$conn->query(
 "INSERT INTO ai_summaries (match_id,summary_text)
  VALUES ($_POST[match_id],'$_POST[summary]')"
);

echo json_encode(["success"=>true]);
?>
