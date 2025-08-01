<?php
session_start();
include 'db.php';

// Show logout success message if redirected from logout.php
$logoutMessage = "";
if (isset($_SESSION['logout_success'])) {
    $logoutMessage = $_SESSION['logout_success'];
    unset($_SESSION['logout_success']);
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize error message
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token");
    }

    // Sanitize inputs
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            header("Location: home.php");
            exit();
        } else {
            $errorMessage = "Invalid password!";
        }
    } else {
        $errorMessage = "Invalid Username and password, Please enter valid one.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login - Bus Booking System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #121212;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background-color: #1e1e1e;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.6);
            width: 100%;
            max-width: 400px;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #ffffff;
        }

        .logout-message {
            text-align: center;
            color: #03dac6;
            font-size: 14px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .error-message {
            text-align: center;
            color: #ff6b6b;
            font-size: 14px;
            margin-bottom: 15px;
            font-weight: bold;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            color: #bbb;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 12px;
            top: 12px;
            color: #888;
        }

        .input-group input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            margin-bottom: 20px;
            border: none;
            border-radius: 8px;
            background-color: #2a2a2a;
            color: #f0f0f0;
        }

        .input-group input:focus {
            outline: none;
            background-color: #333;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #03dac6;
            border: none;
            border-radius: 8px;
            color: black;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease;
        }

        button:hover {
            background-color: #00bfa5;
        }

        .show-password {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 13px;
            color: #ccc;
        }

        .show-password input {
            margin-right: 6px;
        }

        p {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        a {
            color: #03dac6;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>User Login</h2>

    <?php if (!empty($logoutMessage)): ?>
        <div class="logout-message"><?php echo htmlspecialchars($logoutMessage); ?></div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <label for="email">Email Address</label>
        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" id="email" placeholder="Enter email" required autocomplete="off">
        </div>

        <label for="password">Password</label>
        <div class="input-group">
            <i class="fas fa-lock"></i>
            <input type="password" name="password" id="password" placeholder="Enter password" required autocomplete="new-password">
        </div>

        <div class="show-password">
            <input type="checkbox" id="showPassword"> <label for="showPassword">Show Password</label>
        </div>

        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>

<script>
    const passwordInput = document.getElementById("password");
    const showPasswordCheckbox = document.getElementById("showPassword");

    showPasswordCheckbox.addEventListener('change', function() {
        if (this.checked) {
            passwordInput.type = "text";
        } else {
            passwordInput.type = "password";
        }
    });
</script>

</body>
</html>
