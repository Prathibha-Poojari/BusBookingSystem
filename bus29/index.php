<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bus Booking System</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: url('bus_background.jpg') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      flex-direction: column;
      margin: 0;
      color: white;
    }

    h1 {
      font-size: 36px;
      margin-bottom: 30px;
      color: #fff;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.6);
    }

    .button-container {
      display: flex;
      gap: 30px;
    }

    .button-box {
      width: 180px;
      height: 160px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      text-decoration: none;
      color: white;
      font-size: 20px;
      font-weight: bold;
      backdrop-filter: blur(10px);
      transition: transform 0.2s ease-in-out;
    }

    .button-box:hover {
      transform: scale(1.08);
    }

    .button-box img {
      width: 80px;
      height: 80px;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>

  <h1>Welcome to Bus Booking System</h1>

  <div class="button-container">
    <a href="register.php" class="button-box">
      <img src="user_icon.png" alt="User Icon">
      User
    </a>
    <a href="admin_login.php" class="button-box">
      <img src="admin_icon.png" alt="Admin Icon">
      Admin
    </a>
  </div>

</body>
</html>
