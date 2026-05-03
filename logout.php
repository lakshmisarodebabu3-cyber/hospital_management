<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logout Successful</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: #203354;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .logout-box {
            background-color: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 90%;
            max-width: 400px;
        }

        .logout-box h1 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 28px;
        }

        .logout-box p {
            font-size: 16px;
            color: #555;
            margin-bottom: 30px;
        }

        .logout-box a {
            display: inline-block;
            padding: 12px 25px;
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .logout-box a:hover {
            background-color: #2980b9;
        }

        .logout-icon {
            font-size: 50px;
            color: #27ae60;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="logout-box">
    <div class="logout-icon">✅</div>
    <h1>Successfully Logged Out</h1>
    <p>You have been securely logged out. Click below to log in again.</p>
    <a href="login.php">Login Again</a>
</div>

</body>
</html>