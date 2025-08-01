<?php
session_start();
include 'db.php';

if (!isset($_GET['bus_id'])) {
    echo "Invalid Request!";
    exit();
}

$bus_id = $_GET['bus_id'];

// Fetch Bus Details
$busQuery = $conn->query("SELECT * FROM buses WHERE id = $bus_id");
$bus = $busQuery->fetch_assoc();

// Fetch Booked Seats
$bookedSeatsQuery = $conn->query("SELECT seat_number FROM bookings WHERE bus_id = $bus_id");
$bookedSeats = [];
while ($row = $bookedSeatsQuery->fetch_assoc()) {
    $bookedSeats[] = $row['seat_number'];
}
?>

<h2>Book Seats for <?= $bus['name'] ?></h2>

<form method="post" action="confirm_booking.php">
    <input type="hidden" name="bus_id" value="<?= $bus_id ?>">
    
    <h3>Select Your Seat</h3>
    <?php for ($i = 1; $i <= $bus['total_seats']; $i++) { ?>
        <label>
            <input type="radio" name="seat_number" value="<?= $i ?>" 
                <?= in_array($i, $bookedSeats) ? 'disabled' : '' ?> 
                required>
            <span style="color: <?= in_array($i, $bookedSeats) ? 'red' : 'green' ?>">
                Seat <?= $i ?>
            </span>
        </label><br>
    <?php } ?>

    <button type="submit">Confirm Booking</button>
</form>
