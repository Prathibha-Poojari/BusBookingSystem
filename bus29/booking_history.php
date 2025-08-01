<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch booking history for the logged-in user
$stmt = $conn->prepare("
    SELECT p.id AS payment_id, p.amount, p.payment_method, p.payment_date, b.name AS bus_name,
           GROUP_CONCAT(s.seat_number ORDER BY s.seat_number SEPARATOR ', ') AS seats
    FROM payments p
    JOIN bookings s ON p.id = s.payment_id
    JOIN buses b ON s.bus_id = b.id
    WHERE p.user_id = ?
    GROUP BY p.id
    ORDER BY p.payment_date DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            font-family: 'Poppins', sans-serif;
            color: white;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .history-container {
            width: 100%;
            max-width: 800px;
            padding: 40px;
            background-color: #1e1e1e;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 255, 204, 0.1);
        }

        h2 {
            text-align: center;
            color: #00ffc8;
            margin-bottom: 20px;
        }

        .history-item {
            background-color: #2c2c2c;
            margin: 10px 0;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .history-item strong {
            color: #00ffc8;
        }

        .history-item p {
            font-size: 16px;
            color: #e0e0e0;
        }

        .history-item a {
            color: #00ffc8;
            text-decoration: none;
        }

        .history-item a:hover {
            text-decoration: underline;
        }

        .back-link {
            text-align: center;
            margin-top: 30px;
        }

        .back-link a {
            color: #00ffc8;
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="history-container">
    <h2>My Account</h2>

    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="history-item">
            <p><strong>Bus Name:</strong> <?php echo htmlspecialchars($row['bus_name']); ?></p>
            <p><strong>Seats Booked:</strong> <?php echo htmlspecialchars($row['seats']); ?></p>
            <p><strong>Total Amount:</strong> ₹<?php echo htmlspecialchars($row['amount']); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($row['payment_method']); ?></p>
            <p><strong>Payment Date:</strong> <?php echo htmlspecialchars($row['payment_date']); ?></p>
            <p><a href="receipt.php?payment_id=<?php echo $row['payment_id']; ?>">View Receipt</a></p>
        </div>
    <?php endwhile; ?>

    <div class="back-link">
        <a href="home.php">← Back to Home</a>
    </div>
</div>

</body>
</html>
