<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$msg = "";
$patient_id = $doctor_id = $date = $time = $purpose = "";
$editMode = false;

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM appointments WHERE id = $id");
    header("Location: appointments.php");
    exit;
}

if (isset($_GET['edit'])) {
    $editMode = true;
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM appointments WHERE id = $id");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $patient_id = $row['patient_id'];
        $doctor_id = $row['doctor_id'];
        $date = $row['appointment_date'];
        $time = $row['appointment_time'];
        $purpose = $row['purpose'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $purpose = $_POST['purpose'];

    $today = date('Y-m-d');

    if ($patient_id && $doctor_id && $date && $time && $purpose) {
        if ($date < $today) {
            $msg = "Cannot book an appointment in the past.";
        } else {
            if (isset($_POST['edit_id'])) {
                $edit_id = intval($_POST['edit_id']);
                $stmt = $conn->prepare("UPDATE appointments SET patient_id=?, doctor_id=?, appointment_date=?, appointment_time=?, purpose=? WHERE id=?");
                $stmt->bind_param("iisssi", $patient_id, $doctor_id, $date, $time, $purpose, $edit_id);
                $stmt->execute() ? $msg = "Appointment updated!" : $msg = "Error updating: " . $stmt->error;
            } else {
                $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, purpose) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisss", $patient_id, $doctor_id, $date, $time, $purpose);
                $stmt->execute() ? $msg = "Appointment booked!" : $msg = "Error: " . $stmt->error;
            }
            header("Location: appointments.php");
            exit;
        }
    } else {
        $msg = "All fields are required.";
    }
}

$patients = $conn->query("SELECT id, name FROM patients");
$doctors = $conn->query("SELECT id, name FROM doctors");
$appointments = $conn->query("
    SELECT a.id, p.name AS patient, d.name AS doctor, a.appointment_date, a.appointment_time, a.purpose, a.status
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN doctors d ON a.doctor_id = d.id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Appointments</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            margin: 0;
            background-color: #f4f7fa;
        }

        nav {
            background-color: #2c3e50;
            padding: 15px;
            text-align: center;
        }

        nav a {
            color: #ecf0f1;
            margin: 0 15px;
            font-size: 16px;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #1abc9c;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-top: 25px;
        }

        form {
            background: #ffffff;
            padding: 30px;
            margin: 25px auto;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        select,
        input[type="text"],
        input[type="date"],
        input[type="time"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #fefefe;
        }

        textarea {
            resize: vertical;
            min-height: 70px;
        }

        input[type="submit"] {
            background-color: #3498db;
            color: #fff;
            padding: 12px 25px;
            border: none;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        input[type="submit"]:hover {
            background-color: #2980b9;
        }

        .msg {
            text-align: center;
            color: green;
            font-weight: 600;
            margin-top: 10px;
        }

        table {
            width: 95%;
            margin: 30px auto;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #2980b9;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #eaf6ff;
        }

        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 14px;
            color: white;
            text-decoration: none;
            display: inline-block;
        }

        .btn-edit {
            background-color: #f39c12;
        }

        .btn-edit:hover {
            background-color: #e67e22;
        }

        .btn-delete {
            background-color: #e74c3c;
        }

        .btn-delete:hover {
            background-color: #c0392b;
        }

        @media (max-width: 768px) {
            form, table {
                width: 95%;
            }

            th, td {
                font-size: 13px;
            }

            input[type="submit"] {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="patients.php">Patients</a>
    <a href="doctors.php">Doctors</a>
    <a href="appointments.php">Appointments</a>
    <a href="billing.php">Billing</a>
    <a href="logout.php">Logout</a>
</nav>

<h2><?= $editMode ? "Edit Appointment" : "Book Appointment" ?></h2>

<form method="post">
    <?php if ($editMode): ?>
        <input type="hidden" name="edit_id" value="<?= htmlspecialchars($id) ?>">
    <?php endif; ?>

    <label>Patient:</label>
    <select name="patient_id" required>
        <option value="">Select Patient</option>
        <?php
        $patients->data_seek(0);
        while ($p = $patients->fetch_assoc()) : ?>
            <option value="<?= $p['id'] ?>" <?= $p['id'] == $patient_id ? "selected" : "" ?>>
                <?= htmlspecialchars($p['name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Doctor:</label>
    <select name="doctor_id" required>
        <option value="">Select Doctor</option>
        <?php
        $doctors->data_seek(0);
        while ($d = $doctors->fetch_assoc()) : ?>
            <option value="<?= $d['id'] ?>" <?= $d['id'] == $doctor_id ? "selected" : "" ?>>
                <?= htmlspecialchars($d['name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Date:</label>
    <input type="date" name="appointment_date" value="<?= htmlspecialchars($date) ?>" min="<?= date('Y-m-d') ?>" required>

    <label>Time:</label>
    <input type="time" name="appointment_time" value="<?= htmlspecialchars($time) ?>" required>

    <label>Purpose:</label>
    <textarea name="purpose" required><?= htmlspecialchars($purpose) ?></textarea>

    <input type="submit" value="<?= $editMode ? "Update Appointment" : "Book Appointment" ?>">
    <p class="msg"><?= $msg ?></p>
</form>

<h2>Appointments List</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Patient</th>
        <th>Doctor</th>
        <th>Date</th>
        <th>Time</th>
        <th>Purpose</th>
        <th>Status</th>
        <th>Edit</th>
        <th>Delete</th>
    </tr>
    <?php while ($row = $appointments->fetch_assoc()) : ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['patient']) ?></td>
        <td><?= htmlspecialchars($row['doctor']) ?></td>
        <td><?= $row['appointment_date'] ?></td>
        <td><?= $row['appointment_time'] ?></td>
        <td><?= htmlspecialchars($row['purpose']) ?></td>
        <td><?= htmlspecialchars($row['status'] ?? '-') ?></td>
        <td><a class="btn-edit" href="?edit=<?= $row['id'] ?>">Edit</a></td>
        <td><a class="btn-delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this appointment?')">Delete</a></td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>