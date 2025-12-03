<?php
session_start();
header("Content-Type: application/json");
include "db.php";

// Must be logged in
if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Not logged in"
    ]);
    exit;
}

// Read JSON body
$input = json_decode(file_get_contents("php://input"), true);
$item_id = $input["item_id"] ?? null;

if (!$item_id) {
    echo json_encode([
        "success" => false,
        "message" => "Missing item ID"
    ]);
    exit;
}

// Get item owner
$stmt = $conn->prepare("SELECT user_id FROM items WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$stmt->bind_result($owner_id);

if (!$stmt->fetch()) {
    $stmt->close();
    echo json_encode([
        "success" => false,
        "message" => "Item not found"
    ]);
    exit;
}
$stmt->close();

// Permission checks
$isAdmin = (isset($_SESSION["role"]) && $_SESSION["role"] === "admin");
$isOwner = ($_SESSION["user_id"] == $owner_id);

if (!$isAdmin && !$isOwner) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}

// Delete item
$stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
$stmt->bind_param("i", $item_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Item deleted"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Database error"
    ]);
}

$stmt->close();
$conn->close();
?>
