<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

// Initialize user name, use session variable if available, otherwise set a default value
$user_name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : "User";
$user_id = $_SESSION['user_id'];

include 'db.php';

// Fetch user booking history (not shown directly on this page, but could be used elsewhere)
$stmt = $conn->prepare("SELECT b.name AS bus_name, GROUP_CONCAT(s.seat_number ORDER BY s.seat_number SEPARATOR ', ') AS seats, p.amount, p.payment_method, p.payment_date
FROM payments p
JOIN bookings s ON p.id = s.payment_id
JOIN buses b ON s.bus_id = b.id
WHERE p.user_id = ?
GROUP BY p.id ORDER BY p.payment_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home - Bus Booking</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;600&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    html, body {
      height: 100%;
      font-family: 'Poppins', sans-serif;
      color: white;
      overflow: hidden;
    }

    .carousel-background {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      z-index: -2;
    }
    .logo {
  width: 100px;     /* Adjust width as needed */
  height: 38px;     /* Adjust height for rectangle shape */
  object-fit: cover;  /* Ensures image fits within the rectangle */
  border-radius: 5px; /* Optional: slightly rounded corners */
}


    .carousel-image {
      position: absolute;
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0;
      transition: opacity 1s ease-in-out;
    }

    .carousel-image.active {
      opacity: 1;
    }

    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0, 0, 0, 0.6);
      z-index: -1;
    }

    nav {
      padding: 20px;
      text-align: center;
      background-color: rgba(0, 0, 0, 0.4);
    }

    nav a {
      margin: 0 15px;
      text-decoration: none;
      color: #fff;
      font-weight: 600;
      font-size: 16px;
      transition: color 0.3s;
    }

    nav a:hover {
      color: #00ffff;
    }

    .content {
      max-width: 500px;
      margin: 120px auto;
      background: rgba(255, 255, 255, 0.1);
      padding: 40px;
      border-radius: 16px;
      text-align: center;
      box-shadow: 0 8px 32px rgba(0,0,0,0.3);
      backdrop-filter: blur(10px);
    }

    .content h2 {
      font-size: 32px;
      margin-bottom: 10px;
      text-shadow: 2px 2px 4px black;
    }

    .content p {
      font-size: 18px;
      margin-bottom: 30px;
      color: #e6e6e6;
    }

    .content button {
      padding: 12px 30px;
      background: #00d1ff;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
      margin: 10px 0;
    }

    .content button:hover {
      background: #00b5e6;
    }

    .user-name {
      position: fixed;
      top: 20px;
      right: 20px;
      font-size: 16px;
      font-weight: 600;
      color: #ffffff;
      background: rgba(0,0,0,0.4);
      padding: 6px 12px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
    }

    .user-name i {
      font-size: 18px;
    }

    .profile-dropdown {
      position: absolute;
      top: 40px;
      right: 0;
      background-color: #333;
      padding: 10px;
      border-radius: 8px;
      display: none;
      flex-direction: column;
      width: 200px;
    }

    .profile-dropdown a {
      color: white;
      padding: 10px;
      text-decoration: none;
      font-size: 14px;
      transition: background-color 0.3s;
    }

    .profile-dropdown a:hover {
      background-color: #555;
    }

    .user-name:hover .profile-dropdown {
      display: flex;
    }

    /* Logo positioning */
    .logo {
      position: absolute;
      top: 20px;
      left: 20px;
      width: 100px; /* Adjust logo size */
    }

    @media (max-width: 600px) {
      .content { margin: 80px 20px; padding: 30px; }
      .content h2 { font-size: 24px; }
      .content p { font-size: 16px; }
      .user-name { font-size: 14px; padding: 4px 10px; top: 15px; right: 15px; }
      .user-name i { font-size: 16px; }
      .logo { width: 80px; /* Adjust size for smaller screens */ }
    }
  </style>
</head>
<body>

<!-- Logo in Top Left Corner -->
<img src="images/b29.jpg" alt="Logo" class="logo"> <!-- Replace with your logo path -->

<!-- Carousel Background -->
<div class="carousel-background">
  <img src="images/b1.jpg" class="carousel-image active" alt="Bus 1">
  <img src="images/b2.jpg" class="carousel-image" alt="Bus 2">
  <img src="images/b3.jpg" class="carousel-image" alt="Bus 3">
</div>

<!-- Dark Overlay -->
<div class="overlay"></div>

<!-- Navbar -->
<nav>
  <a href="home.php">Home</a>
  <a href="available_buses_user.php">Available Buses</a>
  <a href="my_booking.php">My Booking</a>
  <a href="cancel_booking.php">Cancel Booking</a>
  <a href="booking_history.php">My Account</a>
</nav>

<!-- User Name & Profile Dropdown -->
<div class="user-name">
  <i class="fas fa-user-circle"></i> <?php echo $user_name; ?>
  <!-- Profile Dropdown -->
  <div class="profile-dropdown">
    <a href="user_logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>
  </div>
</div>

<!-- Main Content -->
<div class="content">
  <h2 class="animate__animated animate__fadeIn">Welcome, <?php echo $user_name; ?>!</h2>
  <p class="animate__animated animate__fadeIn">Book your bus tickets easily and travel comfortably.</p>
  <a href="available_buses_user.php"><button>View Available Buses</button></a>
  <a href="cancel_booking.php"><button>Cancel My Booking</button></a>
</div>

<!-- JavaScript for Carousel -->
<script>
  let currentIndex = 0;
  const images = document.querySelectorAll('.carousel-image');
  const totalImages = images.length;

  function showNextImage() {
    images.forEach(img => img.classList.remove('active'));
    currentIndex = (currentIndex + 1) % totalImages;
    images[currentIndex].classList.add('active');
  }

  setInterval(showNextImage, 4000); // Change every 4 seconds
</script>

</body>
</html>
