<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include 'db.php';

// Fetching all user receipt data from the database
// Modify the query to include bus date
$query = "SELECT p.id AS payment_id, u.name AS username, p.amount, p.payment_method, p.payment_date, b.name AS bus_name,
                 b.date AS journey_date,
                 GROUP_CONCAT(s.seat_number ORDER BY s.seat_number SEPARATOR ', ') AS seats
          FROM payments p
          JOIN bookings s ON p.id = s.payment_id
          JOIN users u ON p.user_id = u.id
          JOIN buses b ON s.bus_id = b.id
          GROUP BY p.id
          ORDER BY p.payment_date DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - View Receipts</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #121212;
            color: white;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 40px auto;
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 255, 204, 0.1);
        }

        h2 {
            text-align: center;
            color: #00ffc8;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #333;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #00b386;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #2a2a2a;
        }

        tr:nth-child(odd) {
            background-color: #1e1e1e;
        }

        .nav-links {
            text-align: center;
            margin-top: 20px;
        }

        .nav-links a {
            text-decoration: none;
            color: #00ffc8;
            padding: 10px 20px;
            background-color: #1e1e1e;
            border-radius: 5px;
            border: 2px solid #00ffc8;
            margin: 0 10px;
            font-size: 16px;
        }

        .nav-links a:hover {
            background-color: #00b386;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>User Receipts</h2>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Bus Name</th>
                    <th>User Name</th>
                    <th>Journey Date</th>
                    <th>Seats Booked</th>
                    <th>Amount Paid</th>
                    <th>Payment Date</th>
                    <th>View Receipt</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['bus_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['journey_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['seats']); ?></td>
                        <td>₹<?php echo htmlspecialchars($row['amount']); ?></td>
                        <td><?php echo htmlspecialchars($row['payment_date']); ?></td>
                        <td><a href="reciept1.php?payment_id=<?php echo $row['payment_id']; ?>" style="color: #00ffc8;">View Receipt</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; color: #e0e0e0;">No receipts available at the moment.</p>
    <?php endif; ?>

    <div class="nav-links">
        <a href="admin_dashboard.php">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
