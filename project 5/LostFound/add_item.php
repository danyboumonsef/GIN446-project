<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'db.php'; // Your DB connection

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to post an item.");
}

$errorMsg = '';
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $old = $_POST; // save old values for repopulation

    $required = ['item_name','category','description','status','date','location'];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === "") {
            $errorMsg = "Missing required field: $field";
            break;
        }
    }

    // Only continue if no field errors
    if (!$errorMsg) {
        $item_name   = $_POST['item_name'];
        $category    = $_POST['category'];
        $description = $_POST['description'];
        $status      = $_POST['status'];
        $date        = $_POST['date'];
        $location    = $_POST['location'];
        $email       = $_SESSION['email'] ?? '';

        $photo_path = null;

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
        }
    }
}

        // Stop execution if there was a photo error
        if (!$errorMsg) {
$phone = $_SESSION['phone'] ?? '';

$stmt = $conn->prepare("
    INSERT INTO items 
    (item_name, category, description, status, date, location, email, photo, user_id)
    VALUES (?,?,?,?,?,?,?,?,?)
");
$stmt->bind_param("ssssssssi",
    $item_name,
    $category,
    $description,
    $status,
    $date,
    $location,
    $email,
    $photo_path,
    $_SESSION['user_id']
);


            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: home.html?submitted=1");
                exit();
            } else {
                $errorMsg = "Database error: " . $stmt->error;
            }

            $stmt->close();
        }
    }
    $conn->close();
}
?>


<!DOCTYPE html>
<html>
<head>
        <link rel="stylesheet" href="assets/style.css">
    <title>Post Lost/Found Item</title>

</head>
<body class="add-item-page">

<div class="form-container">

    <h2>Report Lost or Found Item</h2>

    <form action="" method="POST" enctype="multipart/form-data">

        <?php if (!empty($errorMsg)): ?>
            <div class="error"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>

        <label>Item Name:</label>
        <input type="text" name="item_name"
               value="<?php echo htmlspecialchars($old['item_name'] ?? ''); ?>" required>

        <label>Category:</label>
        <select name="category" required>
            <?php 
            $categories = ["Phone","Wallet","Keys","Bag","Clothing","Other"];
            $selectedCat = $old['category'] ?? '';
            foreach ($categories as $cat) {
                echo "<option value='$cat'".($selectedCat==$cat?" selected":"").">$cat</option>";
            }
            ?>
        </select>

        <label>Description:</label>
        <textarea name="description" required><?php echo htmlspecialchars($old['description'] ?? ''); ?></textarea>

        <label>Status:</label>
        <select name="status" required>
            <?php
            $statuses = ["Lost","Found"];
            $selectedStatus = $old['status'] ?? '';
            foreach ($statuses as $s) {
                echo "<option value='$s'".($selectedStatus==$s?" selected":"").">$s</option>";
            }
            ?>
        </select>

        <label>Date Lost/Found:</label>
        <input type="date" name="date"
               value="<?php echo htmlspecialchars($old['date'] ?? ''); ?>" required>

        <label>Location:</label>
        <input type="text" name="location"
               value="<?php echo htmlspecialchars($old['location'] ?? ''); ?>" required>

        <label>Upload Item Photo (optional):</label>
        <input type="file" name="photo">

        <button class="submit-btn" type="submit">Submit Item</button>
    </form>

    <a href="home.html" class="back-btn">← Back to Home</a>

</div>

</body>

</html>