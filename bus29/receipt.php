<?php
session_start();
include 'db.php';

if (!isset($_GET['payment_id'])) {
    die("Error: Invalid receipt request! <a href='home.php'>Go back</a>");
}

$payment_id = intval($_GET['payment_id']);

// Fetch booking details
// Modify the SELECT query to include b.date
$stmt = $conn->prepare("
    SELECT 
        u.name, 
        p.amount, 
        p.payment_method, 
        p.payment_date, 
        b.id AS bus_id, 
        b.name AS bus_name, 
        b.source, 
        b.destination,
        b.date AS bus_date, 
        GROUP_CONCAT(s.seat_number ORDER BY s.seat_number SEPARATOR ', ') AS seats
    FROM payments p
    JOIN bookings s ON p.id = s.payment_id
    JOIN users u ON p.user_id = u.id
    JOIN buses b ON s.bus_id = b.id
    WHERE p.id = ?
    GROUP BY p.id
");

if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}

$stmt->bind_param("i", $payment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    die('MySQL execute error: ' . $conn->error);
}

$receipt = $result->fetch_assoc();

if (!$receipt) {
    die("Error: No receipt found! <a href='home.php'>Go back</a>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Receipt</title>
    <style>
        body {
            background-color: #121212;
            font-family: 'Segoe UI', sans-serif;
            color: #ffffff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .receipt-box {
            background-color: #1e1e1e;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 255, 204, 0.1);
            width: 100%;
            max-width: 500px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .receipt-logo {
            width: 120px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }

        h2 {
            text-align: center;
            color: #00ffc8;
            margin-bottom: 25px;
        }

        .info {
            margin-bottom: 15px;
            font-size: 16px;
            color: #e0e0e0;
        }

        .info strong {
            display: inline-block;
            width: 140px;
            color: #bbbbbb;
        }

        .print-btn {
            text-align: center;
            margin-top: 30px;
        }

        .print-btn button {
            background-color: #00ffc8;
            color: #000;
            border: none;
            padding: 10px 25px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }

        .print-btn button:hover {
            background-color: #00caa3;
        }

        .back-link {
            text-align: center;
            margin-top: 15px;
        }

        .back-link a {
            color: #00ffc8;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        @media print {
            .print-btn, .back-link {
                display: none;
            }
            body {
                background: white;
                color: black;
            }
            .receipt-box {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

<div class="receipt-box">
    <div class="logo-container">
        <img src="images/b29.jpg" alt="Logo" class="receipt-logo">
    </div>

    <h2>Booking Receipt</h2>

    <div class="info"><strong>Name:</strong> <?php echo htmlspecialchars($receipt['name']); ?></div>
    <div class="info"><strong>Bus Name:</strong> <?php echo htmlspecialchars($receipt['bus_name']); ?></div>
    <div class="info"><strong>Source:</strong> <?php echo !empty($receipt['source']) ? htmlspecialchars($receipt['source']) : 'N/A'; ?></div>
    <div class="info"><strong>Destination:</strong> <?php echo !empty($receipt['destination']) ? htmlspecialchars($receipt['destination']) : 'N/A'; ?></div>
    <div class="info"><strong>Journey Date:</strong> <?php echo htmlspecialchars($receipt['bus_date']); ?></div>
    <div class="info"><strong>Seats Booked:</strong> <?php echo htmlspecialchars($receipt['seats']); ?></div>
    <div class="info"><strong>Total Amount:</strong> ₹<?php echo htmlspecialchars($receipt['amount']); ?></div>
    <div class="info"><strong>Payment Method:</strong> <?php echo htmlspecialchars($receipt['payment_method']); ?></div>
    <div class="info"><strong>Payment Date:</strong> <?php echo htmlspecialchars($receipt['payment_date']); ?></div>

    <div class="print-btn">
        <button onclick="window.print()">🖨️ Print Receipt</button>
    </div>

    <div class="back-link">
        <a href="home.php">← Back to Home</a>
    </div>
</div>

</body>
</html>
