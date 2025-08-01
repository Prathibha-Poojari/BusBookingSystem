<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['bus_id']) || !isset($_POST['payment_method']) || !isset($_POST['selected_seats'])) {
    die("Error: Invalid payment request! <a href='home.php'>Go back</a>");
}

$user_id = $_SESSION['user_id'];
$bus_id = $_POST['bus_id'];
$payment_method = $_POST['payment_method'];
$selected_seats = explode(",", $_POST['selected_seats']);
$seat_count = count($selected_seats);

// ✅ Fetch the admin-set amount per seat from the buses table
$busQuery = $conn->prepare("SELECT amount_per_seat FROM buses WHERE id = ?");
$busQuery->bind_param("i", $bus_id);
$busQuery->execute();
$busResult = $busQuery->get_result();
$bus = $busResult->fetch_assoc();

if (!$bus) {
    die("Error: Bus not found!");
}

$amount_per_seat = $bus['amount_per_seat'];
$total_amount = $amount_per_seat * $seat_count;

$payment_status = "Success";

// ✅ Save payment info
$stmt = $conn->prepare("INSERT INTO payments (user_id, bus_id, amount, payment_method, payment_status, payment_date) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("iidss", $user_id, $bus_id, $total_amount, $payment_method, $payment_status);
$stmt->execute();
$payment_id = $stmt->insert_id;

// ✅ Save each booking
foreach ($selected_seats as $seat) {
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, bus_id, seat_number, booking_date, payment_id) VALUES (?, ?, ?, NOW(), ?)");
    $stmt->bind_param("iisi", $user_id, $bus_id, $seat, $payment_id);
    $stmt->execute();
}

// ✅ Clear session data
unset($_SESSION['selected_bus_id']);
unset($_SESSION['selected_seats']);

header("Location: receipt.php?payment_id=$payment_id");
exit();
?>
