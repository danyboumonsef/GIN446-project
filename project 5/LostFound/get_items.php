<?php
header('Content-Type: text/xml; charset=utf-8');  //tells the browser to expect XML
session_start();

include 'db.php'; //database connection

// Check if current user is admin
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'); // this variable will be used later to tell the frontend if the user is admin

echo "<?xml version='1.0' encoding='UTF-8'?>";
echo "<items>";

// Send admin flag to frontend. will echo <isAdmin>1</isAdmin> if admin
echo "<isAdmin>" . ($isAdmin ? "1" : "0") . "</isAdmin>";

// fetch items, conn is created in db.php
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



/*This final part loops through every row returned by the SQL query.
For each row, I open an <item> tag, then loop through every column and convert it into an XML tag dynamically.
For example, item_name becomes <item_name>Wallet</item_name>.
After finishing that item, I close the tag and move to the next row.
At the end, I close the <items> root tag and close the database connection.
This creates a complete XML document that JavaScript can read.*/

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
