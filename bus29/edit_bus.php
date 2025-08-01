<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    die("Access Denied. <a href='admin_login.php'>Login as Admin</a>");
}

// Check if bus ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Request. <a href='manage_buses.php'>Go Back</a>");
}

$bus_id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM buses WHERE id = $bus_id");
$bus = $result->fetch_assoc();

if (!$bus) {
    die("Bus not found. <a href='manage_buses.php'>Go Back</a>");
}

// Handle Bus Update
// In the POST handling section
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $source = trim($_POST['source']);
    $destination = trim($_POST['destination']);
    $total_seats = intval($_POST['seats']);
    $date = $_POST['date'];

    // Convert time from 24hr (input) to 12hr format with AM/PM
    $timing = date("h:i A", strtotime($_POST['timing']));

    if ($source === $destination) {
        echo "<p class='error'>Source and destination cannot be the same.</p>";
    } else {
        $stmt = $conn->prepare("UPDATE buses SET name=?, type=?, source=?, destination=?, timing=?, date=?, total_seats=?, is_edited=1 WHERE id=?");
        $stmt->bind_param("sssssssi", $name, $type, $source, $destination, $timing, $date, $total_seats, $bus_id);

        if ($stmt->execute()) {
            echo "<script>alert('Bus updated successfully!'); window.location='manage_buses.php';</script>";
            exit();
        } else {
            echo "<p class='error'>Error updating bus.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Bus</title>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding-top: 50px;
        }

        h2 {
            margin-bottom: 20px;
            color: #00bcd4;
        }

        form {
            background-color: #1e1e1e;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.1);
            width: 100%;
            max-width: 400px;
        }

        input[type="text"],
        input[type="number"],
        input[type="time"],
        input[type="date"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #333;
            border-radius: 5px;
            background-color: #2a2a2a;
            color: #fff;
        }

        input[type="text"]::placeholder,
        input[type="number"]::placeholder,
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }

        input[type="date"]::-webkit-datetime-edit-text,
        input[type="date"]::-webkit-datetime-edit-month-field,
        input[type="date"]::-webkit-datetime-edit-day-field,
        input[type="date"]::-webkit-datetime-edit-year-field {
            color: #fff;
        }

        input[type="date"]::-webkit-calendar-picker-indicator:hover {
            filter: invert(1) brightness(0.7);
        }
        input[type="text"]::placeholder {
            color: #888;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #00bcd4;
            color: #fff;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #0097a7;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            color: #00bcd4;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            color: #ff4c4c;
            background-color: #2a2a2a;
            border: 1px solid #ff4c4c;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <h2>Edit Bus</h2>
    <form method="post">
        <input type="text" name="name" value="<?php echo htmlspecialchars($bus['name']); ?>" required placeholder="Bus Name">
        <input type="text" name="type" value="<?php echo htmlspecialchars($bus['type']); ?>" required placeholder="Bus Type (e.g., Sleeper)">
        <input type="text" name="source" value="<?php echo htmlspecialchars($bus['source']); ?>" required placeholder="Source (e.g., City A)">
        <input type="text" name="destination" value="<?php echo htmlspecialchars($bus['destination']); ?>" required placeholder="Destination (e.g., City B)">
        <input type="date" name="date" value="<?php echo htmlspecialchars($bus['date']); ?>" required min="<?php echo date('Y-m-d'); ?>">
        <input type="time" name="timing" value="<?php echo date('H:i', strtotime($bus['timing'])); ?>" required>
        <input type="number" name="seats" value="<?php echo (int)$bus['total_seats']; ?>" required placeholder="Total Seats">
        <button type="submit">Update Bus</button>
    </form>

    <a href="manage_buses.php">← Back to Manage Buses</a>

</body>
</html>
