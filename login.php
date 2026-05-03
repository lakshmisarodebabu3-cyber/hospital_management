<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user['username'];
                header("Location: dashboard.php");
                exit;
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $message = "User not found.";
        }
    } else {
        $message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Hospital Management</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-box {
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #2c3e50;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            background-color: #fefefe;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            background-color: #3498db;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #2980b9;
        }

        .error {
            color: #e74c3c;
            font-size: 14px;
            text-align: center;
            margin-top: -10px;
            margin-bottom: 10px;
        }

        .login-box p {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }

        .login-box a {
            color: #3498db;
            text-decoration: none;
        }

        .login-box a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-box {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        <form method="post" action="">
            <label>Username:</label>
            <input type="text" name="username" required>
            
            <label>Password:</label>
            <input type="password" name="password" required>
            
            <?php if ($message): ?>
                <p class="error"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <input type="submit" value="Login">
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>