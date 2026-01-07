<?php
include("../config/db.php");

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role'];

$sql = "INSERT INTO users (name, email, password_hash, role)
        VALUES ('$name', '$email', '$password', '$role')";

echo $conn->query($sql)
    ? json_encode(["success" => true])
    : json_encode(["error" => "Registration failed"]);
?>
