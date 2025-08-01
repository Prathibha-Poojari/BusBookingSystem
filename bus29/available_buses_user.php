<?php
session_start();

// Redirect if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

require_once('db.php'); // Connect to your database

// Fetch all active, valid buses
// After database connection and before the main query
$current_date = date('Y-m-d');

// Update expired buses
$update_expired = $conn->prepare("UPDATE buses SET is_completed = 1 WHERE date <= ? AND is_deleted = 0 AND is_canceled = 0 AND is_completed = 0");
$update_expired->bind_param("s", $current_date);
$update_expired->execute();

// Modify the main query to only show future dates
$query = "SELECT * FROM buses 
          WHERE is_deleted = 0 
          AND is_canceled = 0 
          AND is_completed = 0 
          AND date > ?
          ORDER BY date ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $current_date);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Buses</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #121212;
            color: #fff;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: rgba(0, 0, 0, 0.6);
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            color: #00ffcc;
            margin: 0;
        }

        .nav-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 20px 0;
        }

        .nav-links a {
            text-decoration: none;
            color: #fff;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            background-color: #1e1e1e;
            transition: background 0.3s;
        }

        .nav-links a:hover {
            background-color: #00b386;
        }

        .bus-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .bus-item {
            background-color: rgba(0, 0, 0, 0.5);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0,255,204,0.1);
        }

        .bus-item h3 {
            color: #00ffcc;
            font-size: 24px;
        }

        .bus-item p {
            font-size: 18px;
            margin: 10px 0;
            color: #e6e6e6;
        }

        .bus-item p i {
            margin-right: 8px;
            color: #00ffcc;
        }

        .bus-item p:first-of-type {
            font-size: 20px;
            color: #00ffcc;
            margin: 15px 0;
        }

        .bus-item form button {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #00b386;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .bus-item form button:hover {
            background-color: #009970;
        }

        @media (max-width: 768px) {
            .bus-list {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .bus-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <h1>Available Buses</h1>
</div>

<!-- Navigation Links -->
<div class="nav-links">
    <a href="home.php">Home</a>
    <a href="my_booking.php">My Bookings</a>
    <a href="cancel_booking.php">Cancel Booking</a>
    <a href="user_logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
</div>

<!-- Bus List -->
<div class="bus-list">
    <?php
    if ($result->num_rows > 0) {
        while ($bus = $result->fetch_assoc()) {
            ?>
            <div class="bus-item">
                <h3><?php echo htmlspecialchars($bus['name']); ?></h3>
                <p><i class="fas fa-bus"></i> <strong>Type:</strong> <?php echo htmlspecialchars($bus['type']); ?></p>
                <p><strong>Route:</strong> <?php echo htmlspecialchars($bus['source'] . ' to ' . $bus['destination']); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($bus['date']); ?></p>
                <p><strong>Timing:</strong> <?php echo htmlspecialchars($bus['timing']); ?></p>
                <p><strong>Total Seats:</strong> <?php echo htmlspecialchars($bus['total_seats']); ?></p>
                <p><strong>Amount per Seat:</strong> ₹<?php echo htmlspecialchars($bus['amount_per_seat']); ?></p>
                
                <!-- Redirect to seat_selection.php -->
                <form action="seat_selection.php" method="get">
                    <input type="hidden" name="bus_id" value="<?php echo $bus['id']; ?>">
                    <button type="submit">Book Now</button>
                </form>
            </div>
            <?php
        }
    } else {
        echo "<p style='text-align:center;'>No buses available for booking at the moment.</p>";
    }
    ?>
</div>

</body>
</html>

<?php
$conn->close();
?>