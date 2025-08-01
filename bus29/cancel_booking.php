<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];

    // Fetch booking details before deleting
    $booking = $conn->query("SELECT * FROM bookings WHERE id = $booking_id AND user_id = $user_id")->fetch_assoc();

    if ($booking) {
        // Insert into cancellations
        $conn->query("INSERT INTO cancellations (booking_id, user_id, bus_id, seat_number)
                      VALUES ($booking_id, $user_id, {$booking['bus_id']}, '{$booking['seat_number']}')");

        // Now delete from bookings
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $booking_id, $user_id);

        if ($stmt->execute()) {
            $message = "Booking cancelled successfully and logged!";
        } else {
            $message = "Error cancelling booking.";
        }
    } else {
        $message = "Booking not found.";
    }
}

// Get updated bookings
// Modify the SELECT query to include the date
$stmt = $conn->prepare("
    SELECT b.id, b.bus_id, b.seat_number, bu.name AS bus_name, bu.source, bu.destination, bu.timing, bu.date
    FROM bookings b
    JOIN buses bu ON b.bus_id = bu.id
    WHERE b.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cancel Booking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #111;
            color: #fff;
            padding: 40px;
        }
        table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
            background-color: #1e1e1e;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #444;
        }
        th {
            background-color: #333;
        }
        form button {
            padding: 6px 14px;
            background-color: #dc3545;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #c82333;
        }
        .message {
            color: #0f0;
            margin-top: 20px;
        }
        a.back-link {
            color: #00ffff;
            text-decoration: none;
        }
        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2>Your Bookings</h2>
    <a class="back-link" href="home.php">← Back to Home</a>

    <?php if (!empty($message)): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Bus</th>
                <th>Route</th>
                <th>Date</th>
                <th>Timing</th>
                <th>Seat</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['bus_name']) ?></td>
                    <td><?= htmlspecialchars($row['source']) ?> to <?= htmlspecialchars($row['destination']) ?></td>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td><?= htmlspecialchars($row['timing']) ?></td>
                    <td><?= htmlspecialchars($row['seat_number']) ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
                            <button type="submit" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No bookings found.</p>
    <?php endif; ?>
</body>
</html>
