<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Read JSON input
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['item_id'])) {
    echo json_encode(['success' => false, 'message' => 'No item ID']);
    exit();
}

$item_id = (int)$data['item_id'];
$user_id = $_SESSION['user_id'];

// First, get current returned value
$stmt = $conn->prepare("SELECT returned FROM items WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $item_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $current = (int)$row['returned'];
    $newValue = $current === 1 ? 0 : 1;

    // Update returned
    $update = $conn->prepare("UPDATE items SET returned=? WHERE id=? AND user_id=?");
    $update->bind_param("iii", $newValue, $item_id, $user_id);
    if ($update->execute()) {
        echo json_encode(['success' => true, 'returned' => $newValue]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update']);
    }
    $update->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Item not found']);
}

$stmt->close();
$conn->close();
