<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($username && $password && $confirm_password) {
        if ($password !== $confirm_password) {
            $message = "Passwords do not match.";
        } else {
            // Check if username exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $message = "Username already exists.";
            } else {
                // Register user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $username, $hashed_password);
                if ($stmt->execute()) {
                    $message = "Registration successful. <a href='login.php'>Login here</a>.";
                } else {
                    $message = "Error registering user.";
                }
            }
        }
    } else {
        $message = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 50px; }
        .register-box {
            width: 300px; margin: auto;
            padding: 20px; background: #fff;
            border: 1px solid #ccc; border-radius: 10px;
        }
        input[type=text], input[type=password] {
            width: 100%; padding: 8px;
            margin: 5px 0 10px; border: 1px solid #aaa;
        }
        input[type=submit] {
            background: #4CAF50; color: white;
            padding: 8px 16px; border: none;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="register-box">
        <h2>Register</h2>
        <form method="post">
            <label>Username:</label><br>
            <input type="text" name="username" required><br>
            <label>Password:</label><br>
            <input type="password" name="password" required><br>
            <label>Confirm Password:</label><br>
            <input type="password" name="confirm_password" required><br>
            <input type="submit" value="Register">
            <p style="color:green;"><?= $message ?></p>
        </form>
        <p><a href="login.php">Already have an account? Login here</a></p>
    </div>
</body>
</html>