<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bus_id = $_POST['bus_id'];
    $user_id = $_SESSION['user_id'];
    $seat_number = $_POST['seat_number'];

    // Insert booking
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, bus_id, seat_number) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $bus_id, $seat_number);
    $stmt->execute();

    echo "<script>alert('Booking Confirmed!'); window.location='my_bookings.php';</script>";
}
?>
