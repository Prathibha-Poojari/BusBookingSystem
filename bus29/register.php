<?php
session_start();
include 'db.php';

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$name = '';
$email = '';
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request! Please try again.';
    } else {
        // Fetch and sanitize inputs
        $name = htmlspecialchars(trim($_POST['name']));
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        // Form validations
        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = 'All fields are required!';
        } elseif (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
            $error = 'Name should only contain letters and spaces!';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address!';
        } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
            $error = 'Only @gmail.com addresses are allowed!';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long!';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $error = 'Password must contain at least one uppercase letter!';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $error = 'Password must contain at least one lowercase letter!';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $error = 'Password must contain at least one number!';
        } elseif (!preg_match('/[\W]/', $password)) {
            $error = 'Password must contain at least one special character!';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match!';
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = 'Email already registered! Please login.';
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, is_logged_out) VALUES (?, ?, ?, 0)");
                $stmt->bind_param("sss", $name, $email, $hashed_password);

                if ($stmt->execute()) {
                    $success = 'Registration successful! Redirecting to login...';
                    $name = '';
                    $email = '';
                } else {
                    $error = 'Error registering user. Please try again.';
                }
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration - Bus Booking System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.6);
            width: 100%;
            max-width: 420px;
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #ffffff;
        }
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
        }
        .alert-error {
            background-color: #ff4d4d;
            color: white;
        }
        .alert-success {
            background-color: #00c853;
            color: white;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            color: #bbb;
        }
        .input-group {
            position: relative;
            margin-bottom: 18px;
        }
        .input-group i {
            position: absolute;
            top: 12px;
            left: 12px;
            color: #aaa;
        }
        .input-group input {
            width: 100%;
            padding: 12px 12px 12px 36px;
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
    <h2>User Registration</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <script>
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 2000); // Redirect after 2 seconds
        </script>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <label for="name">Full Name</label>
        <div class="input-group">
            <i class="fa fa-user"></i>
            <input 
                type="text" 
                name="name" 
                id="name" 
                required 
                pattern="^[A-Za-z\s]+$" 
                title="Name must contain only letters and spaces." 
                value="<?= htmlspecialchars($name) ?>">
        </div>

        <label for="email">Gmail Address</label>
        <div class="input-group">
            <i class="fa fa-envelope"></i>
            <input 
                type="email" 
                name="email" 
                id="email" 
                required 
                value="<?= htmlspecialchars($email) ?>" 
                pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$" 
                title="Only @gmail.com emails are allowed. Example: yourname@gmail.com">
        </div>

        <label for="password">Password</label>
        <div class="input-group">
            <i class="fa fa-lock"></i>
            <input 
                type="password" 
                name="password" 
                id="password" 
                required 
                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W]).{8,}" 
                title="Password must contain at least 8 characters, including uppercase, lowercase, number, and special character.">
        </div>

        <label for="confirm_password">Confirm Password</label>
        <div class="input-group">
            <i class="fa fa-lock"></i>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

</body>
</html>
