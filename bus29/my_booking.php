<?php 
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $source = $_POST['source'];
    $destination = $_POST['destination'];
    $timing = $_POST['timing'];
    $date = $_POST['date'];

    header("Location: my_booking.php?source=" . urlencode($source) . 
           "&destination=" . urlencode($destination) . 
           "&timing=" . urlencode($timing) . 
           "&date=" . urlencode($date));
    exit();
}

$buses = [];
if (isset($_GET['source'], $_GET['destination'], $_GET['timing'], $_GET['date'])) {
    $source = $_GET['source'];
    $destination = $_GET['destination'];
    $timing = $_GET['timing'];
    $date = $_GET['date'];

    $stmt = $conn->prepare("SELECT * FROM buses WHERE source = ? AND destination = ? AND timing = ? AND date = ? AND is_deleted = 0 AND is_canceled = 0 AND is_completed = 0");
    $stmt->bind_param("ssss", $source, $destination, $timing, $date);
    $stmt->execute();
    $buses = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Bookings</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #121212;
      color: #ffffff;
    }

    nav {
      padding: 20px;
      text-align: center;
      background-color: #1f1f1f;
    }

    nav a {
      margin: 0 15px;
      text-decoration: none;
      color: #00d9ff;
      font-weight: bold;
    }

    nav a:hover {
      color: #ffdd57;
    }

    h2, h3 {
      text-align: center;
      margin-top: 30px;
    }

    form {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin: 30px auto;
      max-width: 800px;
      flex-wrap: wrap;
    }

    form input, form select, form button {
      padding: 10px;
      border-radius: 5px;
      border: none;
      font-size: 16px;
    }

    form input, form select {
      width: 200px;
    }

    form button {
      background-color: #28a745;
      color: white;
      cursor: pointer;
    }

    form button:hover {
      background-color: #218838;
    }

    table {
      width: 90%;
      margin: 30px auto;
      border-collapse: collapse;
      background-color: #1e1e1e;
      border-radius: 10px;
      overflow: hidden;
    }

    th, td {
      padding: 15px;
      text-align: center;
      border-bottom: 1px solid #333;
    }

    th {
      background-color: #2c2c2c;
    }

    .available {
      background-color: #28a745;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .booked {
      background-color: #dc3545;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 5px;
      cursor: not-allowed;
    }

    .message {
      text-align: center;
      margin-top: 40px;
      font-size: 18px;
      color: #bbb;
    }
  </style>

  <script>
  function combineTime() {
    const hour = document.getElementById('hour').value;
    const minute = document.getElementById('minute').value;
    const ampm = document.getElementById('ampm').value;
    const timing = `${hour}:${minute} ${ampm}`;
    document.getElementById('timing').value = timing;
    return true;
  }
  </script>
</head>
<body>

<nav>
  <a href="home.php">Home</a>
  <a href="available_buses_user.php">Available Buses</a>
  <a href="my_booking.php">My Booking</a>
  <a href="user_logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
</nav>

<h2>Search Buses</h2>

<form method="post" onsubmit="return combineTime();">
  <input type="text" name="source" placeholder="Source" required>
  <input type="text" name="destination" placeholder="Destination" required>
  <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>">

  <!-- Hour -->
  <select id="hour" required>
    <?php for ($h = 1; $h <= 12; $h++): ?>
      <option value="<?= $h ?>"><?= $h ?></option>
    <?php endfor; ?>
  </select>

  <!-- Minute -->
  <select id="minute" required>
    <?php for ($m = 0; $m < 60; $m += 5): ?>
      <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>"><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
    <?php endfor; ?>
  </select>

  <!-- AM/PM -->
  <select id="ampm" required>
    <option value="AM">AM</option>
    <option value="PM">PM</option>
  </select>

  <!-- Hidden Timing Field -->
  <input type="hidden" name="timing" id="timing">

  <button type="submit">Find Bus</button>
</form>

<?php if (!empty($buses) && $buses->num_rows > 0): ?>
  <h3>Available Buses</h3>
  <table>
    <tr>
      <th>Bus Name</th>
      <th>Type</th>
      <th>Date</th>
      <th>Seats Available</th>
      <th>Action</th>
    </tr>
    <?php while ($bus = $buses->fetch_assoc()): 
      $bus_id = $bus['id'];
      $seat_stmt = $conn->prepare("SELECT COUNT(*) AS booked_seats FROM bookings WHERE bus_id = ?");
      $seat_stmt->bind_param("i", $bus_id);
      $seat_stmt->execute();
      $seat_result = $seat_stmt->get_result();
      $seat_data = $seat_result->fetch_assoc();
      $booked_seats = $seat_data['booked_seats'];
      $available_seats = $bus['total_seats'] - $booked_seats;
    ?>
      <tr>
        <td><?php echo htmlspecialchars($bus['name']); ?></td>
        <td><?php echo htmlspecialchars($bus['type']); ?></td>
        <td><?php echo htmlspecialchars($bus['date']); ?></td>
        <td><?php echo $available_seats > 0 ? $available_seats : "<span style='color: #dc3545;'>Full</span>"; ?></td>
        <td>
          <?php if ($available_seats > 0): ?>
            <a href="seat_selection.php?bus_id=<?php echo urlencode($bus['id']); ?>">
              <button class="available">Book Now</button>
            </a>
          <?php else: ?>
            <button class="booked" disabled>Full</button>
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
<?php else: ?>
  <p class="message">
    <?php if (isset($_GET['source'], $_GET['destination'], $_GET['timing'], $_GET['date'])): ?>
      No buses available yet for route <?php echo htmlspecialchars($_GET['source']); ?> to <?php echo htmlspecialchars($_GET['destination']); ?> on <?php echo htmlspecialchars($_GET['date']); ?> at <?php echo htmlspecialchars($_GET['timing']); ?>. Please try a different date or time.
    <?php else: ?>
      Please search for buses by entering source, destination, date, and time.
    <?php endif; ?>
  </p>
<?php endif; ?>

</body>
</html>
