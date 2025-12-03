<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    die("Invalid item.");
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("
    SELECT items.*, users.name AS poster_name, users.email AS poster_email,users.phone
    FROM items
    JOIN users ON items.user_id = users.id
    WHERE items.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Item not found.");
}

$item = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($item['item_name']); ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="item-detail-page">
    <div class="item-detail">
        <h2><?php echo htmlspecialchars($item['item_name']); ?></h2>
        <?php if ($item['photo']): ?>
            <img src="<?php echo htmlspecialchars($item['photo']); ?>" class="item-image">
        <?php endif; ?>
        <p><b>Posted by:</b> <?php echo htmlspecialchars($item['poster_name']); ?></p>
        <p><b>Email:</b> <?php echo htmlspecialchars($item['poster_email']); ?></p>
    
        <p><b>Category:</b> <?php echo htmlspecialchars($item['category']); ?></p>
        <p><b>Status:</b> <?php echo htmlspecialchars($item['status']); ?></p>
        <p><b>Description:</b> <?php echo htmlspecialchars($item['description']); ?></p>
        <p><b>Location:</b> <?php echo htmlspecialchars($item['location']); ?></p>
        <p><b>Date:</b> <?php echo htmlspecialchars($item['date']); ?></p>
        <a href="home.html" class="back-button">Back to Home</a>
    </div>
</body>
</html>
