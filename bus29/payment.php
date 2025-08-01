<?php 
session_start();
include 'db.php';

if (!isset($_POST['bus_id']) || !isset($_POST['selected_seats'])) {
    die("Error: Invalid request! <a href='home.php'>Go back</a>");
}

$bus_id = $_POST['bus_id'];
$selected_seats = $_POST['selected_seats'];

$_SESSION['selected_bus_id'] = $bus_id;
$_SESSION['selected_seats'] = $selected_seats;

// ✅ Fetch the amount per seat from the buses table
$stmt = $conn->prepare("SELECT amount_per_seat FROM buses WHERE id = ?");
$stmt->bind_param("i", $bus_id);
$stmt->execute();
$result = $stmt->get_result();
$bus = $result->fetch_assoc();

if (!$bus) {
    die("Error: Bus not found! <a href='home.php'>Go back</a>");
}

$ticket_price = $bus['amount_per_seat'];
$total_price = count($selected_seats) * $ticket_price;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #121212;
            color: #ffffff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #1e1e1e;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.5);
            max-width: 400px;
            width: 100%;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #00ffcc;
        }

        p {
            font-size: 16px;
            margin: 10px 0;
            color: #ddd;
        }

        label {
            font-weight: bold;
            margin-top: 20px;
            display: block;
            color: #ccc;
        }

        .payment-method {
            margin: 10px 0;
        }

        .payment-method input {
            margin-right: 8px;
        }

        button {
            width: 100%;
            background: #00cc66;
            color: white;
            font-size: 16px;
            padding: 12px;
            margin-top: 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #00994d;
        }

        .summary {
            background: #2a2a2a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #333;
        }

        .summary span {
            font-weight: bold;
            color: #fff;
        }

        input[type="radio"] {
            accent-color: #00cc66;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Payment for Your Booking</h2>

    <div class="summary">
        <p><span>Seats Selected:</span> <?php echo implode(", ", $selected_seats); ?></p>
        <p><span>Price Per Seat:</span> ₹<?php echo $ticket_price; ?></p>
        <p><span>Total Amount:</span> ₹<?php echo $total_price; ?></p>
    </div>

    <form action="process_payment.php" method="post">
        <input type="hidden" name="bus_id" value="<?php echo $bus_id; ?>">
        <input type="hidden" name="total_amount" value="<?php echo $total_price; ?>">
        <input type="hidden" name="selected_seats" value="<?php echo implode(",", $selected_seats); ?>">

        <label>Select Payment Method:</label>
        <div class="payment-method">
            <input type="radio" name="payment_method" value="UPI" required> UPI
            <input type="radio" name="payment_method" value="Card" required> Card
            <input type="radio" name="payment_method" value="NetBanking" required> NetBanking
        </div>

        <button type="submit">Pay Now</button>
    </form>
</div>

</body>
</html>
