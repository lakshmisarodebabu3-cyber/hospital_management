<?php
// Show errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Get username
$username = $_SESSION['user'];

require 'db_connect.php';

// Fetch dashboard stats
$patient_count = $conn->query("SELECT COUNT(*) AS total FROM patients")->fetch_assoc()['total'] ?? 0;
$doctor_count = $conn->query("SELECT COUNT(*) AS total FROM doctors")->fetch_assoc()['total'] ?? 0;
$today = date('Y-m-d');
$appt_count = $conn->query("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = '$today'")->fetch_assoc()['total'] ?? 0;
$unpaid_count = $conn->query("SELECT COUNT(*) AS total FROM billing WHERE status = 'Unpaid'")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Serenity Springs Hospital</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f9f9;
        }
        header {
            background-color: #004d40;
            color: white;
            text-align: center;
            padding: 30px 0;
        }
        header h1 {
            margin: 0;
            font-size: 36px;
        }
        header p {
            margin-top: 10px;
            font-size: 16px;
        }
        .container {
            max-width: 960px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .hospital-img {
            width: 100%;
            border-radius: 10px;
            height: auto;
            max-height: 300px;
            object-fit: cover;
        }
        .about {
            background: white;
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .about h2 {
            color: #00695c;
            margin-bottom: 10px;
        }
        .about p {
            color: #444;
            line-height: 1.6;
        }
        .menu {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 30px;
        }
        .menu a {
            background: #ffffff;
            border: 2px solid #00796b;
            color: #00796b;
            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
            padding: 14px;
            text-align: center;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .menu a:hover {
            background: #00796b;
            color: #ffffff;
        }
        .stats {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 40px;
        }
        .stat {
            flex: 1 1 220px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .stat h3 {
            margin: 0;
            font-size: 28px;
            color: #004d40;
        }
        .stat p {
            margin-top: 8px;
            color: #555;
            font-size: 16px;
        }
        .welcome {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<header>
    <h1>Serenity Springs Hospital</h1>
    <p>Advanced Healthcare with Compassion and Trust</p>
</header>

<div class="container">

    <!-- ✅ Hospital Image (Update the src URL as needed) -->
    <img src="https://www.panchkulahelp.com/wp-content/uploads/2023/09/hospitals.jpg" 
     alt="Hospital" class="hospital-img">

    <div class="about">
        <h2>About Serenity Springs</h2>
        <p>
            Serenity Springs Hospital is dedicated to providing high-quality, affordable medical care
            with a personal touch. We are equipped with state-of-the-art technology and a team of experienced professionals
            to ensure your health and wellbeing are in good hands.
        </p>
    </div>

    <div class="welcome">
        <h2>Welcome, <?= htmlspecialchars($username) ?> 👋</h2>
    </div>

    <div class="menu">
        <a href="dashboard.php">🏠 Home</a>
        <a href="patients.php">🧍‍♂️ Patients</a>
        <a href="doctors.php">🩺 Doctors</a>
        <a href="appointments.php">📅 Appointments</a>
        <a href="billing.php">💳 Billing</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="stats">
        <div class="stat">
            <h3><?= $patient_count ?></h3>
            <p>Patients</p>
        </div>
        <div class="stat">
            <h3><?= $doctor_count ?></h3>
            <p>Doctors</p>
        </div>
        <div class="stat">
            <h3><?= $appt_count ?></h3>
            <p>Appointments Today</p>
        </div>
        <div class="stat">
            <h3><?= $unpaid_count ?></h3>
            <p>Unpaid Bills</p>
        </div>
    </div>

</div>

</body>
</html>