<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
?>
<h2>Admin Dashboard</h2>
<a href="manage_buses.php">Manage Buses</a>
<a href="logout.php">Logout</a>
