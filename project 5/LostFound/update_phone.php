<?php
session_start();
include 'db.php';

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$phone = $data['phone'] ?? '';

if (!preg_match('/^\d{8}$/', $phone)) {
    echo json_encode(["success" => false, "error" => "Phone must be exactly 8 digits"]);
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("UPDATE users SET phone = ? WHERE id = ?");
$stmt->bind_param("si", $phone, $userId);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}

$stmt->close();
$conn->close();
?>
