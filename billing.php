<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$msg = "";
$editMode = false;
$patient_id = $amount = $billing_date = $status = "";

ini_set('display_errors', 1);
error_reporting(E_ALL);

// DELETE
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM billing WHERE id = $id");
    header("Location: billing.php");
    exit;
}

// EDIT
if (isset($_GET['edit'])) {
    $editMode = true;
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM billing WHERE id = $id");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $patient_id = $row['patient_id'];
        $amount = $row['amount'];
        $billing_date = $row['billing_date'];
        $status = $row['status'];
    }
}

// ADD / UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_id = trim($_POST['patient_id']);
    $amount = trim($_POST['amount']);
    $billing_date = $_POST['billing_date'];
    $status = $_POST['status'];

    $today = date('Y-m-d');

    if ($patient_id && $amount && $billing_date && $status) {
        if ($billing_date < $today) {
            $msg = "Billing date cannot be in the past.";
        } else {
            if (isset($_POST['edit_id'])) {
                $edit_id = intval($_POST['edit_id']);
                $stmt = $conn->prepare("UPDATE billing SET patient_id=?, amount=?, billing_date=?, status=? WHERE id=?");
                $stmt->bind_param("idssi", $patient_id, $amount, $billing_date, $status, $edit_id);
                $msg = $stmt->execute() ? "Billing updated successfully!" : "Error updating: " . $stmt->error;
            } else {
                $stmt = $conn->prepare("INSERT INTO billing (patient_id, amount, billing_date, status) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("idss", $patient_id, $amount, $billing_date, $status);
                $msg = $stmt->execute() ? "Billing entry added!" : "Error adding: " . $stmt->error;
            }
            header("Location: billing.php");
            exit;
        }
    } else {
        $msg = "All fields are required.";
    }
}

$patients = $conn->query("SELECT id, name FROM patients");

$bills = $conn->query("
    SELECT b.id, p.name AS patient, b.amount, b.billing_date, b.status
    FROM billing b
    JOIN patients p ON b.patient_id = p.id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Billing</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f5f7fa;
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
            max-width: 700px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        select,
        input[type="text"],
        input[type="number"],
        input[type="date"] {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #fefefe;
            margin-bottom: 15px;
        }

        input[type="submit"] {
            background-color: #3498db;
            color: white;
            padding: 12px 25px;
            border: none;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
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
            background-color: #34495e;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #e8f6ff;
        }

        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 14px;
            color: white;
            text-decoration: none;
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

<h2><?= $editMode ? "Edit Billing" : "Add Billing" ?></h2>
<form method="post">
    <?php if ($editMode): ?>
        <input type="hidden" name="edit_id" value="<?= htmlspecialchars($id) ?>">
    <?php endif; ?>

    <label>Patient:</label>
    <select name="patient_id" required>
        <option value="">Select Patient</option>
        <?php $patients->data_seek(0);
        while ($p = $patients->fetch_assoc()) : ?>
            <option value="<?= $p['id'] ?>" <?= $p['id'] == $patient_id ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Amount (₹):</label>
    <input type="number" name="amount" required step="0.01" value="<?= htmlspecialchars($amount) ?>">

    <label>Date:</label>
    <input type="date" name="billing_date" required min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($billing_date) ?>">

    <label>Status:</label>
    <select name="status" required>
        <option value="">Select</option>
        <option value="Unpaid" <?= $status == "Unpaid" ? "selected" : "" ?>>Unpaid</option>
        <option value="Paid" <?= $status == "Paid" ? "selected" : "" ?>>Paid</option>
    </select>

    <input type="submit" value="<?= $editMode ? "Update Billing" : "Add Billing" ?>">
    <p class="msg"><?= $msg ?></p>
</form>

<h2>Billing History</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Patient</th>
        <th>Amount</th>
        <th>Date</th>
        <th>Status</th>
        <th>Edit</th>
        <th>Delete</th>
    </tr>
    <?php while ($row = $bills->fetch_assoc()) : ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['patient']) ?></td>
            <td>₹<?= number_format($row['amount'], 2) ?></td>
            <td><?= $row['billing_date'] ?></td>
            <td><?= $row['status'] ?></td>
            <td><a class="btn-edit" href="?edit=<?= $row['id'] ?>">Edit</a></td>
            <td><a class="btn-delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a></td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>