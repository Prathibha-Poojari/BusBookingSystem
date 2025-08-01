<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    die("Access Denied. <a href='admin_login.php'>Login as Admin</a>");
}

// Fetch buses based on flags
$deleted_buses = $conn->query("SELECT * FROM buses WHERE is_deleted = 1");
$edited_buses = $conn->query("SELECT * FROM buses WHERE is_edited = 1 AND is_deleted = 0");
$available_buses = $conn->query("SELECT * FROM buses WHERE is_deleted = 0 AND is_canceled = 0 AND is_completed = 0");

// Debugging
if ($deleted_buses === false || $edited_buses === false || $available_buses === false) {
    die("Error fetching bus data: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bus History</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #121212;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h2, h3 {
            color: #00ffff;
            margin-top: 30px;
        }

        table {
            width: 80%;
            margin-top: 20px;
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

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            background-color: #00bcd4;
            color: #000;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            align-self: flex-start;
            margin-left: 40px;
        }

        .back-btn:hover {
            background-color: #00ffff;
        }

        .empty-msg {
            padding: 20px;
            text-align: center;
            font-style: italic;
            color: #999;
        }
    </style>
</head>
<body>

<a href="admin_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

<h2>Bus History</h2>

<!-- Deleted Buses -->
<h3>Deleted Buses</h3>
<table>
    <tr>
        <th>Bus ID</th>
        <th>Bus Name</th>
        <th>Bus Type</th>
        <th>Route</th>
        <th>Date</th>
        <th>Timing</th>
        <th>Total Seats</th>
        <th>Status</th>
    </tr>
    <?php if ($deleted_buses->num_rows > 0): ?>
        <?php while ($bus = $deleted_buses->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($bus['id']); ?></td>
                <td><?php echo htmlspecialchars($bus['name']); ?></td>
                <td><?php echo htmlspecialchars($bus['type']); ?></td>
                <td><?php echo htmlspecialchars($bus['source'] . ' → ' . $bus['destination']); ?></td>
                <td><?php echo htmlspecialchars($bus['date']); ?></td>
                <td><?php echo htmlspecialchars($bus['timing']); ?></td>
                <td><?php echo htmlspecialchars($bus['total_seats']); ?></td>
                <td><strong>Deleted</strong></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="7" class="empty-msg">No deleted buses found.</td></tr>
    <?php endif; ?>
</table>

<!-- Edited Buses -->
<h3>Edited Buses</h3>
<table>
    <tr>
        <th>Bus ID</th>
        <th>Bus Name</th>
        <th>Bus Type</th>
        <th>Route</th>
        <th>Date</th>
        <th>Timing</th>
        <th>Total Seats</th>
        <th>Status</th>
    </tr>
    <?php if ($edited_buses->num_rows > 0): ?>
        <?php while ($bus = $edited_buses->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($bus['id']); ?></td>
                <td><?php echo htmlspecialchars($bus['name']); ?></td>
                <td><?php echo htmlspecialchars($bus['type']); ?></td>
                <td><?php echo htmlspecialchars($bus['source'] . ' → ' . $bus['destination']); ?></td>
                <td><?php echo htmlspecialchars($bus['date']); ?></td>
                <td><?php echo htmlspecialchars($bus['timing']); ?></td>
                <td><?php echo htmlspecialchars($bus['total_seats']); ?></td>
                <td><strong>Edited</strong></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="8" class="empty-msg">No edited buses found.</td></tr>
    <?php endif; ?>
</table>

<!-- Available Buses -->
<h3>Available Buses</h3>
<table>
    <tr>
        <th>Bus ID</th>
        <th>Bus Name</th>
        <th>Bus Type</th>
        <th>Route</th>
        <th>Date</th>
        <th>Timing</th>
        <th>Total Seats</th>
        <th>Status</th>
    </tr>
    <?php if ($available_buses->num_rows > 0): ?>
        <?php while ($bus = $available_buses->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($bus['id']); ?></td>
                <td><?php echo htmlspecialchars($bus['name']); ?></td>
                <td><?php echo htmlspecialchars($bus['type']); ?></td>
                <td><?php echo htmlspecialchars($bus['source'] . ' → ' . $bus['destination']); ?></td>
                <td><?php echo htmlspecialchars($bus['date']); ?></td>
                <td><?php echo htmlspecialchars($bus['timing']); ?></td>
                <td><?php echo htmlspecialchars($bus['total_seats']); ?></td>
                <td><strong>Available</strong></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="8" class="empty-msg">No available buses found.</td></tr>
    <?php endif; ?>
</table>

</body>
</html>
