<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #121212;
            color: #ffffff;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            height: 100vh;
        }

        .dashboard-container {
            text-align: center;
            margin-top: 60px;
        }

        h2 {
            font-size: 32px;
            color: #00ffcc;
            margin-bottom: 40px;
        }

        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .nav-links a {
            text-decoration: none;
            color: #ffffff;
            background-color: #1e1e1e;
            padding: 14px 30px;
            border-radius: 10px;
            border: 2px solid #00ffcc;
            font-size: 18px;
            transition: background 0.3s, transform 0.2s;
        }

        .nav-links a:hover {
            background-color: #00b386;
            transform: translateY(-3px);
        }

        .footer {
            position: absolute;
            bottom: 20px;
            font-size: 14px;
            color: #777;
        }

    </style>
</head>
<body>

<div class="dashboard-container">
    <h2>Welcome, Admin</h2>
    <div class="nav-links">
        <a href="manage_buses.php">🚌 Manage Buses</a>
        <a href="user_history.php">📄 View User History</a>
        <a href="bus_history.php">🕒 Bus History</a> <!-- Added Bus History link -->
        <a href="admin_receipts.php">🧾 View Receipts</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
</div>

<div class="footer">© 2025 Bus Booking System</div>

</body>
</html>
