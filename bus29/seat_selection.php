<?php 
session_start();
include 'db.php';

if (!isset($_GET['bus_id']) || !is_numeric($_GET['bus_id'])) {
    die("<p style='color:red;'>Error: No bus selected. <a href='home.php'>Go back</a></p>");
}

$bus_id = intval($_GET['bus_id']);

$bus_query = $conn->prepare("SELECT name, type, total_seats FROM buses WHERE id = ?");
$bus_query->bind_param("i", $bus_id);
$bus_query->execute();
$bus_result = $bus_query->get_result();
$bus = $bus_result->fetch_assoc();

if (!$bus) {
    die("<p style='color:red;'>Error: Bus not found! <a href='home.php'>Go back</a></p>");
}

$total_seats = $bus['total_seats'];

$booked_seats_query = $conn->prepare("SELECT seat_number FROM bookings WHERE bus_id = ?");
$booked_seats_query->bind_param("i", $bus_id);
$booked_seats_query->execute();
$booked_seats_result = $booked_seats_query->get_result();

$booked_seats = [];
while ($row = $booked_seats_result->fetch_assoc()) {
    $booked_seats[] = $row['seat_number'];
}

function renderSeat($rowChar, $num, $booked_seats) {
    $seat_label = $rowChar . $num;
    $seat_class = in_array($seat_label, $booked_seats) ? "booked" : "available";
    $disabled = in_array($seat_label, $booked_seats);

    echo "<div class='seat $seat_class' title='Seat $seat_label' data-seat='$seat_label'>";
    if (!$disabled) {
        echo "<input type='checkbox' name='selected_seats[]' value='$seat_label' id='$seat_label'>";
        echo "<label for='$seat_label'>$seat_label</label>";
    } else {
        echo "<span>$seat_label</span>";
    }
    echo "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seat Selection - <?php echo htmlspecialchars($bus['name']); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #121212;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        h2 {
            margin-bottom: 10px;
            color: #00ffc8;
        }

        .bus-layout {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
            margin-top: 20px;
        }

        .row {
            display: grid;
            grid-template-columns: repeat(2, 60px) 30px repeat(3, 60px);
            gap: 8px;
            align-items: center;
        }

        .seat {
            width: 60px;
            height: 55px;
            border-radius: 10px;
            border: 2px solid #444;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            font-weight: bold;
            font-size: 12px;
            color: white;
            transition: all 0.3s ease-in-out;
        }

        .seat input {
            display: none;
        }

        .seat.available {
            background: linear-gradient(to top, #1e8e3e, #4CAF50);
            cursor: pointer;
            box-shadow: 0 0 8px rgba(0, 255, 100, 0.4);
        }

        .seat.available label {
            cursor: pointer;
            width: 100%;
            height: 100%;
            text-align: center;
            line-height: 55px;
            display: block;
        }

        .seat.booked {
            background: linear-gradient(to top, #7b1c1c, #e53935);
            cursor: not-allowed;
            box-shadow: 0 0 6px rgba(255, 50, 50, 0.4);
        }

        .seat.booked span {
            width: 100%;
            line-height: 55px;
        }

        .seat.selected {
            background: #FFA500 !important;
            box-shadow: 0 0 10px orange;
        }

        .aisle {
            background: transparent;
        }

        button {
            padding: 12px 24px;
            font-size: 16px;
            margin-top: 30px;
            border: none;
            border-radius: 6px;
            background-color: #00ffc8;
            color: #000;
            font-weight: bold;
            opacity: 0.5;
            cursor: not-allowed;
            transition: 0.3s ease;
        }

        button.active {
            opacity: 1;
            cursor: pointer;
        }

        p {
            font-size: 16px;
            color: #cccccc;
        }
    </style>
</head>
<body>

<h2>Seat Selection</h2>
<p><strong>Bus:</strong> <?php echo htmlspecialchars($bus['name']) . " (" . htmlspecialchars($bus['type']) . ")" ?></p>
<p><strong>Total Seats:</strong> <?php echo htmlspecialchars($total_seats); ?></p>

<form id="seatForm" action="payment.php" method="POST">
    <input type="hidden" name="bus_id" value="<?php echo htmlspecialchars($bus_id); ?>">

    <div class="bus-layout">
        <?php
        $seats_per_row = 5;
        $row_count = ceil($total_seats / $seats_per_row);
        $seat_number = 1;

        for ($r = 0; $r < $row_count; $r++) {
            $rowChar = chr(65 + $r);
            echo "<div class='row'>";
            for ($i = 0; $i < 2; $i++) {
                if ($seat_number <= $total_seats) {
                    renderSeat($rowChar, $i + 1, $booked_seats);
                    $seat_number++;
                } else {
                    echo "<div></div>";
                }
            }
            echo "<div class='aisle'></div>";
            for ($i = 2; $i < 5; $i++) {
                if ($seat_number <= $total_seats) {
                    renderSeat($rowChar, $i + 1, $booked_seats);
                    $seat_number++;
                } else {
                    echo "<div></div>";
                }
            }
            echo "</div>";
        }
        ?>
    </div>

    <button type="submit" id="confirm-btn" disabled>Proceed to Payment</button>
</form>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const seats = document.querySelectorAll(".seat.available");
        const confirmBtn = document.getElementById("confirm-btn");

        seats.forEach(seat => {
            seat.addEventListener("click", function () {
                const checkbox = this.querySelector("input[type='checkbox']");
                if (!checkbox) return;

                checkbox.checked = !checkbox.checked;
                this.classList.toggle("selected", checkbox.checked);

                const selected = document.querySelectorAll(".seat.selected").length;
                confirmBtn.disabled = selected === 0;
                confirmBtn.classList.toggle("active", selected > 0);
            });
        });
    });
</script>

</body>
</html>
