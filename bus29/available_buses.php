<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if source, destination, and timing are selected
if (!isset($_POST['source']) || !isset($_POST['destination']) || !isset($_POST['timing'])) {
    die("<p style='color:red;'>Error: Please select Source, Destination, and Timing! <a href='home.php'>Go back</a></p>");
}

$source = $_POST['source'];
$destination = $_POST['destination'];
$timing = $_POST['timing'];

// Fetch available buses that are not deleted, canceled, or completed
$query = $conn->prepare("SELECT id, name, type, total_seats, timing FROM buses WHERE source = ? AND destination = ? AND timing = ? AND is_deleted = 0 AND is_canceled = 0 AND is_completed = 0");
$query->bind_param("sss", $source, $destination, $timing);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Buses</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            padding: 30px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            margin-top: 30px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ccc;
        }
        th {
            background-color: #00cccc;
            color: #000;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        button {
            padding: 8px 16px;
            background-color: #00aaff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        button:hover {
            background-color: #007acc;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #007acc;
            font-weight: bold;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2>Available Buses</h2>

<?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Bus Name</th>
            <th>Type</th>
            <th>Available Seats</th>
            <th>Timing</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['type']); ?></td>
                <td><?php echo htmlspecialchars($row['total_seats']); ?></td>
                <td><?php echo htmlspecialchars($row['timing']); ?></td>
                <td>
                    <a href="seat_selection.php?bus_id=<?php echo urlencode($row['id']); ?>">
                        <button>Book</button>
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p style="color:red; text-align: center;">No buses available for the selected route and timing.</p>
<?php endif; ?>

<a class="back-link" href="home.php">⬅ Back to Home</a>

</body>
</html>
