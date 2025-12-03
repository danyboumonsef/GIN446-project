<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'db.php'; // Your DB connection

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to edit an item.");
}

$errorMsg = '';
$old = [];
$item_id = $_GET['item_id'] ?? null;

if (!$item_id) {
    die("No item specified.");
}

// Fetch existing item data
$stmt = $conn->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $item_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if (!$item) {
    die("Item not found or you don't have permission to edit it.");
}

// Populate old data for form
$old = $item;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old = $_POST; // save old values for repopulation

    $required = ['item_name','category','description','status','date','location'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === "") {
            $errorMsg = "Missing required field: $field";
            break;
        }
    }

    if (!$errorMsg) {
        $item_name   = $_POST['item_name'];
        $category    = $_POST['category'];
        $description = $_POST['description'];
        $status      = $_POST['status'];
        $date        = $_POST['date'];
        $location    = $_POST['location'];
        $email       = $_SESSION['email'] ?? '';

        $photo_path = $item['photo']; // keep old photo by default

        if (!empty($_FILES["photo"]["name"])) {
            $allowedTypes = ["jpg","jpeg","png","gif"];
            $ext = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedTypes)) {
                $errorMsg = "Only JPG, JPEG, PNG & GIF images are allowed.";
            } elseif (!isset($_FILES["photo"]["tmp_name"]) || !is_uploaded_file($_FILES["photo"]["tmp_name"])) {
                $errorMsg = "File upload failed.";
            } elseif ($_FILES["photo"]["size"] > 4.5 * 1024 * 1024) { // 4.5 MB in bytes
                $errorMsg = "File size must be less than 4.5 MB.";
            } else {
                $targetDir = "uploads/";
                if (!is_dir($targetDir)) mkdir($targetDir);
                $fileName = time() . "_" . basename($_FILES["photo"]["name"]);
                $targetFile = $targetDir . $fileName;

                if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
                    $errorMsg = "Error uploading image.";
                } else {
                    $photo_path = $targetFile;
                    // delete old photo
                    if ($item['photo'] && file_exists($item['photo'])) {
                        unlink($item['photo']);
                    }
                }
            }
        }

        if (!$errorMsg) {
            $stmt = $conn->prepare("
                UPDATE items SET 
                    item_name=?, category=?, description=?, status=?, date=?, location=?, photo=?
                WHERE id=? AND user_id=?
            ");
            $stmt->bind_param(
                "sssssssii",
                $item_name,
                $category,
                $description,
                $status,
                $date,
                $location,
                $photo_path,
                $item_id,
                $_SESSION['user_id']
            );

            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: profile.php?edited=1");
                exit();
            } else {
                $errorMsg = "Database error: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/style.css">
    <title>Edit Lost/Found Item</title>
</head>
<body class="add-item-page">

<div class="form-container">

    <h2>Edit Lost or Found Item</h2>

    <form action="" method="POST" enctype="multipart/form-data">

        <?php if (!empty($errorMsg)): ?>
            <div class="error"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>

        <!-- Item Name -->
        <label>Item Name:</label>
        <input type="text" name="item_name"
               value="<?php echo htmlspecialchars($old['item_name'] ?? ''); ?>" required>

        <!-- Category -->
        <label>Category:</label>
        <select name="category" required>
            <?php 
            $categories = ["Phone","Wallet","Keys","Bag","Clothing","Other"];
            $selectedCat = $old['category'] ?? '';
            foreach ($categories as $cat) {
                echo "<option value='$cat'".($selectedCat==$cat ? " selected" : "").">$cat</option>";
            }
            ?>
        </select>

        <!-- Description -->
        <label>Description:</label>
        <textarea name="description" required><?php 
            echo htmlspecialchars($old['description'] ?? ''); 
        ?></textarea>

        <!-- Status -->
        <label>Status:</label>
        <select name="status" required>
            <?php
            $statuses = ["Lost","Found"];
            $selectedStatus = $old['status'] ?? '';
            foreach ($statuses as $s) {
                echo "<option value='$s'".($selectedStatus==$s ? " selected" : "").">$s</option>";
            }
            ?>
        </select>

        <!-- Date -->
        <label>Date Lost/Found:</label>
        <input type="date" name="date"
               value="<?php echo htmlspecialchars($old['date'] ?? ''); ?>" required>

        <!-- Location -->
        <label>Location:</label>
        <input type="text" name="location"
               value="<?php echo htmlspecialchars($old['location'] ?? ''); ?>" required>

        <!-- Upload New Photo -->
        <label>Upload Item Photo (optional):</label>
        <input type="file" name="photo">

        <!-- CURRENT PHOTO PREVIEW -->
        <?php if (!empty($item['photo']) && file_exists($item['photo'])): ?>
            <div class="current-photo">
                Current photo:
                <img src="<?php echo $item['photo']; ?>">
            </div>
        <?php endif; ?>

        <!-- Submit -->
        <button class="submit-btn" type="submit">Update Item</button>
    </form>

    <a href="profile.php" class="back-btn">← Back to Profile</a>

</div>

</body>

</html>
