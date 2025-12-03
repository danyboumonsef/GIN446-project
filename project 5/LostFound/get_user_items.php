<?php
session_start();
header('Content-Type: text/xml; charset=utf-8');
include 'db.php';

if(!isset($_SESSION['user_id'])){
    die("<?xml version='1.0'?><items></items>");
}

$userId = $_SESSION['user_id'];

echo "<?xml version='1.0' encoding='UTF-8'?>";
echo "<items>";

$stmt = $conn->prepare("SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()){
    echo "<item>";
    foreach($row as $key => $value){
        echo "<$key>" . htmlspecialchars($value) . "</$key>";
    }
    echo "</item>";
}

$stmt->close();
$conn->close();
echo "</items>";
