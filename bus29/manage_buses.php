<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    die("Access Denied. <a href='admin_login.php'>Login as Admin</a>");
}

// Add new bus
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_bus'])) {
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $source = trim($_POST['source']);
    $destination = trim($_POST['destination']);
    $timing = trim($_POST['timing']);
    $date = $_POST['date'];
    
    // Validate positive values for seats and amount
    $total_seats = max(1, intval($_POST['seats']));
    $amount_per_seat = max(0.01, floatval($_POST['amount']));

    if ($source === $destination) {
        echo "<p class='error'>Source and destination cannot be the same.</p>";
    } elseif ($total_seats <= 0 || $total_seats > 50 || $amount_per_seat <= 0) {
        echo "<p class='error'>Invalid number of seats or amount per seat!</p>";
    } else {
        $check_stmt = $conn->prepare("SELECT * FROM buses WHERE name = ? AND type = ? AND source = ? AND destination = ? AND timing = ? AND date = ? AND total_seats = ? AND amount_per_seat = ? AND is_deleted = 0");
        $check_stmt->bind_param("ssssssdi", $name, $type, $source, $destination, $timing, $date, $total_seats, $amount_per_seat);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<p class='error'>Bus with these details already exists!</p>";
        } else {
            $stmt = $conn->prepare("INSERT INTO buses (name, type, source, destination, timing, date, total_seats, amount_per_seat, is_deleted, is_edited, is_completed, is_canceled) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0, 0)");
            $stmt->bind_param("ssssssdi", $name, $type, $source, $destination, $timing, $date, $total_seats, $amount_per_seat);

            if ($stmt->execute()) {
                $bus_id = $conn->insert_id;
                $seat_stmt = $conn->prepare("INSERT INTO seats (bus_id, seat_number) VALUES (?, ?)");
                for ($i = 1; $i <= $total_seats; $i++) {
                    $seat_number = "S" . $i;
                    $seat_stmt->bind_param("is", $bus_id, $seat_number);
                    $seat_stmt->execute();
                }
                echo "<script>alert('Bus added successfully with seats!'); window.location='manage_buses.php';</script>";
            } else {
                echo "<p class='error'>Error adding bus. Please try again.</p>";
            }
        }
    }
}

// Soft-delete bus
if (isset($_GET['delete'])) {
    $bus_id = intval($_GET['delete']);
    $conn->query("UPDATE buses SET is_deleted = 1 WHERE id = $bus_id");
    echo "<script>alert('Bus deleted successfully!'); window.location='manage_buses.php';</script>";
}

// Fetch buses
$buses = $conn->query("SELECT * FROM buses WHERE is_deleted = 0");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Buses</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <style>
        body {
            margin: 0; padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #121212;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h2, h3 { color: #00ffff; margin-top: 30px; }
        a.back-home {
            margin-top: 10px;
            background-color: #00cccc;
            color: #000;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }
        a.back-home:hover { background-color: #00ffaa; }
        form {
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            width: 80%;
            max-width: 800px;
        }
        input, select, button {
            padding: 10px;
            border-radius: 6px;
            border: none;
            font-size: 16px;
        }
        input[type="text"], input[type="number"], input[type="date"], select {
            width: 180px;
            background-color: #2a2a2a;
            color: #fff;
            border: 1px solid #00ffff;
        }
        button {
            background-color: #00cccc;
            color: #000;
            cursor: pointer;
        }
        button:hover { background-color: #00ffaa; }
        table {
            width: 90%;
            margin-top: 30px;
            border-collapse: collapse;
            background-color: #1e1e1e;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            border: 1px solid #2e2e2e;
            text-align: center;
        }
        th {
            background-color: #00cccc;
            color: #000;
        }
        td a {
            color: #00ffff;
            text-decoration: none;
        }
        td a:hover {
            color: #00ffaa;
        }
        .error {
            color: #ff4c4c;
            background-color: #2a2a2a;
            border: 1px solid #ff4c4c;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .time-picker select {
            width: 100px;
            background-color: #2a2a2a;
            color: #fff;
            border: 1px solid #00ffff;
        }
    </style>
</head>
<body>

    <h2>Manage Buses</h2>
    <a href="admin_dashboard.php" class="back-home">⬅ Back to Dashboard</a>

    <form method="post">
        <input type="text" name="name" placeholder="Bus Name" required>
        <select name="type" required>
            <option value="">Select Type</option>
            <option value="AC">AC</option>
            <option value="NON-AC">NON-AC</option>
            <option value="SLEEPER">SLEEPER</option>
            <option value="SITTING">SITTING</option>
        </select>
        <input type="text" name="source" placeholder="Source (e.g., City A)" required>
        <input type="text" name="destination" placeholder="Destination (e.g., City B)" required>

        <!-- Hour and Minute Select -->
        <div class="time-picker">
            <select name="hour" id="hour" required>
                <option value="">Hour</option>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                <?php endfor; ?>
            </select>
            <select name="minute" id="minute" required>
                <option value="">Minute</option>
                <?php for ($i = 0; $i < 60; $i += 5): ?>
                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
                <?php endfor; ?>
            </select>
            <select name="ampm" id="ampm" required>
                <option value="">AM/PM</option>
                <option value="AM">AM</option>
                <option value="PM">PM</option>
            </select>
        </div>
        <input type="hidden" name="timing" id="timing-input">

        <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
        <input type="number" name="seats" placeholder="Total Seats" required max="50" min="1">
        <input type="number" step="0.01" name="amount" placeholder="Amount Per Seat (₹)" required min="0.01">
        <button type="submit" name="add_bus">Add Bus</button>
    </form>

    <h3>Available Buses</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Type</th>
            <th>Route</th>
            <th>Timing</th>
            <th>Date</th>
            <th>Seats</th>
            <th>Amount/Seat</th>
            <th>Action</th>
        </tr>
        <?php while ($bus = $buses->fetch_assoc()): ?>
        <tr>
            <td><?php echo $bus['id']; ?></td>
            <td><?php echo htmlspecialchars($bus['name']); ?></td>
            <td><?php echo htmlspecialchars($bus['type']); ?></td>
            <td><?php echo htmlspecialchars($bus['source'] . " → " . $bus['destination']); ?></td>
            <td><?php echo htmlspecialchars($bus['timing']); ?></td>
            <td><?php echo htmlspecialchars($bus['date']); ?></td>
            <td><?php echo $bus['total_seats']; ?></td>
            <td>₹<?php echo number_format($bus['amount_per_seat'], 2); ?></td>
            <td>
                <a href="edit_bus.php?id=<?php echo $bus['id']; ?>">Edit</a> |
                <a href="?delete=<?php echo $bus['id']; ?>" onclick="return confirm('Are you sure you want to delete this bus?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <script>
        document.querySelector("form").addEventListener("submit", function (e) {
            const hour = document.getElementById("hour").value;
            const minute = document.getElementById("minute").value;
            const ampm = document.getElementById("ampm").value;

            if (hour && minute && ampm) {
                const formattedTime = `${hour}:${minute} ${ampm}`;
                document.getElementById("timing-input").value = formattedTime;
            } else {
                alert("Please select a valid time.");
                e.preventDefault();
            }
        });
    </script>

</body>
</html>
