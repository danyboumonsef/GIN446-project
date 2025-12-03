<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'db.php';
session_start();

//  Handle Firebase login 
$input = json_decode(file_get_contents("php://input"), true);

if ($input && isset($input['firebase_uid'])) {
    $uid = $input['firebase_uid'];
    $email = $input['email'];
    $name = $input['name'];

    // Check if user exists
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) == 0) {
        $insert = mysqli_prepare($conn, "INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $fakePass = password_hash($uid, PASSWORD_DEFAULT);
        mysqli_stmt_bind_param($insert, "sss", $name, $email, $fakePass);
        mysqli_stmt_execute($insert);
    }

    // Log in user
$stmt2 = mysqli_prepare($conn, "SELECT id, name, role FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt2, "s", $email);
    mysqli_stmt_execute($stmt2);
mysqli_stmt_bind_result($stmt2, $id, $username, $role);
    mysqli_stmt_fetch($stmt2);

    $_SESSION["user_id"] = $id;
    $_SESSION["user_name"] = $username;
$_SESSION["role"] = $role;

    echo "OK";
    exit(); 
}

// Handle traditional email/password login 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST["login"]);
    $password = $_POST["password"];

    // Check if login is email or username
    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
        $stmt = mysqli_prepare($conn, 
            "SELECT id, name, password, role FROM users WHERE email = ?"
        );
    } else {
        $stmt = mysqli_prepare($conn, 
            "SELECT id, name, password, role FROM users WHERE name = ?"
        );
    }

    mysqli_stmt_bind_param($stmt, "s", $login);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $name, $hashed_password, $role);
    mysqli_stmt_fetch($stmt);

    
    if ($id && password_verify($password, $hashed_password)) {

        
        $_SESSION["user_id"]   = $id;
        $_SESSION["user_name"] = $name;
        $_SESSION["role"]      = $role;   

        header("Location: home.html");
        exit();
    } 
    
    // Failed login
    else {
        header("Location: login.html?error=Invalid+login+or+password");
        exit();
    }
}

?>
