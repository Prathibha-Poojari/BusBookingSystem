<?php
session_start();

if (isset($_GET['logout']) && $_GET['logout'] === 'yes') {
    session_destroy();
    echo "<script>
        alert('Logout successful!');
        window.location.href = 'admin.php';
    </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Logout</title>
    <script>
        // Show confirmation on page load
        if (confirm("Are you sure you want to logout?")) {
            // If YES, redirect to logout.php with ?logout=yes
            window.location.href = "logout.php?logout=yes";
        } else {
            // If NO, send back to admin dashboard instead of login
            window.location.href = "admin_dashboard.php"; // <-- corrected this line
        }
    </script>
</head>
<body>
</body>
</html>
