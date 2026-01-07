<?php
include("../config/db.php");

$conn->query("
INSERT INTO wickets
(ball_id,batsman_id,dismissal_type,fielder_id)
VALUES
($_POST[ball_id],$_POST[batsman],
 '$_POST[type]',$_POST[fielder])
");

echo json_encode(["success"=>true]);
?>
