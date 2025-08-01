<?php
session_start();
include 'db.php';

$error = "";
$username = ""; // Initialize to keep field filled if login fails

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepared statement to prevent SQL Injection
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['admin_id'] = $username;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid Admin Credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
        body {
            margin: 0;
            background: #121212;
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .login-container {
            background-color: #1e1e1e;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0, 255, 128, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #00ffcc;
        }

        .login-container input {
            width: 100%;
            padding: 12px 14px;
            margin: 12px 0;
            border: 1px solid #444;
            border-radius: 8px;
            background-color: #2a2a2a;
            color: #fff;
            font-size: 15px;
        }

        .login-container input:focus {
            outline: none;
            border-color: #00ffcc;
            background-color: #333;
        }

        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #00cc88;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .login-container button:hover {
            background-color: #00b377;
        }

        .error {
            color: red;
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Admin Login</h2>
    <form method="post" autocomplete="off">
        <input type="text" name="username" placeholder="Admin Username" autocomplete="off" 
               value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
        
        <!-- For security, we usually don't refill password -->
        <input type="password" name="password" placeholder="Password" autocomplete="new-password">
        
        <button type="submit">Login</button>
    </form>
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
</div>

</body>
</html>
