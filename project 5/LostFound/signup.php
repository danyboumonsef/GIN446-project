<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $phone = preg_replace('/^\+961\s?/', '', $phone);
    if (strlen($phone) !== 8) {
    header("Location: signup.html?error=Phone must be 8 digits");
    exit();
}
    $password = $_POST["password"];

    // Check if username exists
    $checkName = mysqli_prepare($conn, "SELECT id FROM users WHERE name = ?");
    mysqli_stmt_bind_param($checkName, "s", $name);
    mysqli_stmt_execute($checkName);
    mysqli_stmt_store_result($checkName);
    if (mysqli_stmt_num_rows($checkName) > 0) {
        header("Location: signup.html?error=" . urlencode("Username already taken!"));
        exit();
    }

    // Check if email exists
    $checkEmail = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($checkEmail, "s", $email);
    mysqli_stmt_execute($checkEmail);
    mysqli_stmt_store_result($checkEmail);
    if (mysqli_stmt_num_rows($checkEmail) > 0) {
        header("Location: signup.html?error=" . urlencode("Email already registered!"));
        exit();
    }

    // Insert new user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $phone, $hashed_password);

    if (mysqli_stmt_execute($stmt)) {
        // Get the inserted user ID
        $user_id = mysqli_insert_id($conn);

        // Log in the new user
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['phone'] = $phone;

        header("Location: home.html"); // redirect on success
        exit();
    } else {
        header("Location: signup.html?error=" . urlencode("Signup failed! Please try again."));
        exit();
    }
}
?>
