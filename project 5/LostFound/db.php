<?php

$host = "sql203.infinityfree.com";
$db_name = "if0_39866449_lostfound";
$username = "if0_39866449";
$password = "Christi1292005";

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
