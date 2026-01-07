<?php
include("../config/db.php");

$match_id = $_POST['match_id'];

$conn->query(
 "INSERT INTO match_results (match_id,winner_name,result_summary)
  VALUES ($match_id,'$_POST[winner]','$_POST[summary]')"
);

foreach (['1','2','3'] as $pos) {
    if (!empty($_POST["p$pos"])) {
        $conn->query(
         "INSERT INTO prize_winners (match_id,position,winner_name)
          VALUES ($match_id,'$pos','".$_POST["p$pos"]."')"
        );
    }
}

echo json_encode(["success"=>true]);
?>
