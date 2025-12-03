<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?error=Please login first");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch user data from database
$stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($dbName, $dbEmail, $dbPhone);
$stmt->fetch();
$stmt->close();

// Update session values in case DB was edited manually
$_SESSION['user_name'] = $dbName;
$_SESSION['email'] = $dbEmail;
$_SESSION['phone'] = $dbPhone;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<header class="profile-header">
    <h2>My Profile</h2>
    <div class="header-buttons">
        <a href="home.html" class="profile-btn">Home</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</header>

<body class="profile-page">

<p class="profile-username">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>

<!-- User Info Section -->
<div class="info-container"> 
    <h3>Account Info</h3>
    <p><b>Username:</b> <?php echo htmlspecialchars($dbName); ?></p>
    <p><b>Email:</b> <?php echo htmlspecialchars($dbEmail); ?></p>
    <p><b>Phone:</b> <?php echo "+961 " . substr($dbPhone,0,2) . " " . substr($dbPhone,2,3) . " " . substr($dbPhone,5,3); ?></p>

    <button class="phone-edit-inline-btn" onclick="document.getElementById('phoneModal').style.display='flex'">Edit Phone</button>
</div>

<!-- Phone Edit Modal -->
<div id="phoneModal" class="modal" style="display:none;">
    <div class="modal-content" onclick="event.stopPropagation()">
        <span class="close" onclick="document.getElementById('phoneModal').style.display='none'">&times;</span>
        <h3>Edit Phone Number</h3>

        <form id="updatePhoneForm">
            <label>New Phone (8 digits):</label><br>
            <input type="text" name="phone" id="newPhoneField" required maxlength="8"
                   pattern="\d{8}" title="Phone must be exactly 8 digits"><br><br>

            <button type="submit" class="submit-btn">Save</button>
        </form>

        <p id="phone-feedback" style="color:green; margin-top:10px;"></p>
    </div>
</div>

<!-- Close modal when clicking outside -->
<script>
document.getElementById('phoneModal').addEventListener("click", () => {
    document.getElementById('phoneModal').style.display = "none";
});
</script>

<div class="your-posts-header">
  <h2>Your Posts</h2>
</div>
<div id="profile-items-container" class="items-grid"></div>
<div id="success-popup" class="popup">Item updated successfully!</div>

<script src="assets/profile.js"></script>




</body>
</html>
