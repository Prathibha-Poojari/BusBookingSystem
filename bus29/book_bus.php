<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bus_id = intval($_POST['bus_id']);
    $user_id = $_SESSION['user_id']; // Ensure user is logged in
    $selected_seats = $_POST['selected_seats'] ?? [];

    if (empty($selected_seats)) {
        echo "<script>alert('No seat selected! Please select a seat.'); window.location='my_booking.php?bus_id=$bus_id';</script>";
        exit();
    }

    foreach ($selected_seats as $seat) {
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, bus_id, seat_number) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $bus_id, $seat);
        $stmt->execute();
    }

    // ✅ Show success message and redirect to home.php
    echo "<script>
            alert('Booking successful!');
            window.location='home.php';
          </script>";
    exit();
}
?>
