<?php
// Database connection for bus booking system
$servername = "localhost";     // Default XAMPP MySQL server
$username = "root";            // Default username
$password = "";                // No password by default in XAMPP
$dbname = "bus29";             // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
