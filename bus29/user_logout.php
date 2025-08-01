<?php
session_start();
include 'db.php';

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Optional: Update user status
    $stmt = $conn->prepare("UPDATE users SET is_logged_out = 1 WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
}

session_unset();
session_destroy();

// New session to store message
session_start();
$_SESSION['logout_success'] = "Logout successful!";
header("Location: login.php");
exit();
?>
