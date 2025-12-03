<?php
require 'db.php';
session_start();

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);

$name = $data["name"];
$email = $data["email"];
$uid = $data["uid"];

// Check if user already exists
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) == 0) {
    // Insert new user with fake password (uid hashed)
    $insert = mysqli_prepare($conn, "INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $fakePass = password_hash($uid, PASSWORD_DEFAULT);
    mysqli_stmt_bind_param($insert, "sss", $name, $email, $fakePass);
    mysqli_stmt_execute($insert);
}

// Get user info
$stmt2 = mysqli_prepare($conn, "SELECT id, name FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt2, "s", $email);
mysqli_stmt_execute($stmt2);
mysqli_stmt_bind_result($stmt2, $id, $username);
mysqli_stmt_fetch($stmt2);

// Login user
$_SESSION["user_id"] = $id;
$_SESSION["user_name"] = $username;

echo "OK";
?>
