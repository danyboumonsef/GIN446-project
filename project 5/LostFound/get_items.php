<?php
header('Content-Type: text/xml; charset=utf-8');
session_start();

include 'db.php';

// Check if current user is admin
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

echo "<?xml version='1.0' encoding='UTF-8'?>";
echo "<items>";

// Send admin flag to frontend
echo "<isAdmin>" . ($isAdmin ? "1" : "0") . "</isAdmin>";

// Fetch items
$result = $conn->query("
    SELECT 
        items.id, 
        items.item_name, 
        items.category, 
        items.description, 
        items.status, 
        items.date, 
        items.location, 
        items.photo, 
        items.returned,
        users.name AS poster_name, 
        users.email AS poster_email, 
        users.phone AS poster_phone
    FROM items
    JOIN users ON items.user_id = users.id
    ORDER BY items.created_at DESC
");

while ($row = $result->fetch_assoc()) {
    echo "<item>";
    foreach ($row as $key => $value) {
        echo "<$key>" . htmlspecialchars($value ?? '') . "</$key>";
    }
    echo "</item>";
}

echo "</items>";
$conn->close();
?>
