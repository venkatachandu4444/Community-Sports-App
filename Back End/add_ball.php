<?php
include("../config/db.php");

$conn->query("
INSERT INTO balls
(over_id,ball_number,runs,extra_type,is_wicket,batsman_id,bowler_id)
VALUES
($_POST[over_id],$_POST[ball],$_POST[runs],
 '$_POST[extra]',$_POST[is_wicket],
 $_POST[batsman],$_POST[bowler])
");

echo json_encode(["success"=>true]);
?>
