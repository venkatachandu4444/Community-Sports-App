<?php
include("../config/db.php");

$email = $_POST['email'];
$password = $_POST['password'];

$result = $conn->query("SELECT * FROM users WHERE email='$email'");
$user = $result->fetch_assoc();

if ($user && $password === $user['password_hash']) {
    echo json_encode(["success" => true, "user" => $user]);
} else {
    echo json_encode(["error" => "Invalid credentials"]);
}
?>
