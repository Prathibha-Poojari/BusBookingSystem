<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch booking history
$booking_history = $conn->query("
    SELECT 
        bookings.id AS booking_id,
        users.name AS user_name,
        buses.name AS bus_name,
        GROUP_CONCAT(bookings.seat_number ORDER BY bookings.seat_number SEPARATOR ', ') AS seats,
        bookings.status
    FROM bookings
    JOIN users ON bookings.user_id = users.id
    JOIN buses ON bookings.bus_id = buses.id
    GROUP BY bookings.id
");

// Fetch cancellation history (fix duplicates issue)
$cancellation_history = $conn->query("
    SELECT 
        cancellations.id AS cancel_id,
        users.name AS user_name,
        buses.name AS bus_name,
        cancellations.seat_number,
        cancellations.canceled_at
    FROM cancellations
    JOIN users ON cancellations.user_id = users.id
    JOIN buses ON cancellations.bus_id = buses.id
    GROUP BY cancellations.id
    ORDER BY cancellations.canceled_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Booking & Cancellation History</title>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: Arial, sans-serif;
            padding: 40px;
        }

        h2 {
            color: #00bcd4;
            text-align: center;
        }

        table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
            background-color: #1e1e1e;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.1);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #333;
        }

        th {
            background-color: #232323;
            color: #00bcd4;
        }

        tr:hover {
            background-color: #2a2a2a;
        }

        section {
            margin-bottom: 60px;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 30px;
            background-color: #00bcd4;
            color: #000;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        .back-btn:hover {
            background-color: #00ffff;
        }
    </style>
</head>
<body>

<a href="admin_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

<section>
    <h2>📘 Active User Bookings</h2>
    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>User Name</th>
                <th>Bus Name</th>
                <th>Seats</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $booking_history->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['booking_id']) ?></td>
                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                    <td><?= htmlspecialchars($row['bus_name']) ?></td>
                    <td><?= htmlspecialchars($row['seats']) ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</section>

<section>
    <h2>🗑️ Cancelled Bookings</h2>
    <table>
        <thead>
            <tr>
                <th>Cancel ID</th>
                <th>User Name</th>
                <th>Bus Name</th>
                <th>Seat Number</th>
                <th>Cancelled At</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $cancellation_history->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['cancel_id']) ?></td>
                    <td><?= htmlspecialchars($row['user_name']) ?></td>
                    <td><?= htmlspecialchars($row['bus_name']) ?></td>
                    <td><?= htmlspecialchars($row['seat_number']) ?></td>
                    <td><?= htmlspecialchars($row['canceled_at']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</section>

</body>
</html>
