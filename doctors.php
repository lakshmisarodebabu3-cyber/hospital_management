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
$name = $email = $phone = $speciality = $gender = "";
$editMode = false;

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM doctors WHERE id = $id");
    header("Location: doctors.php");
    exit;
}

if (isset($_GET['edit'])) {
    $editMode = true;
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM doctors WHERE id = $id");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $name = $row['name'];
        $email = $row['email'];
        $phone = $row['phone'];
        $speciality = $row['speciality'];
        $gender = $row['gender'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $speciality = trim($_POST['speciality']);
    $gender = $_POST['gender'];

    if ($name && $email && preg_match('/^\d{10}$/', $phone) && $speciality && $gender) {
        if (isset($_POST['edit_id'])) {
            $edit_id = intval($_POST['edit_id']);
            $stmt = $conn->prepare("UPDATE doctors SET name=?, email=?, phone=?, speciality=?, gender=? WHERE id=?");
            $stmt->bind_param("sssssi", $name, $email, $phone, $speciality, $gender, $edit_id);
            $msg = $stmt->execute() ? "Doctor updated successfully!" : "Error updating: " . $stmt->error;
        } else {
            $stmt = $conn->prepare("INSERT INTO doctors (name, email, phone, speciality, gender) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $speciality, $gender);
            $msg = $stmt->execute() ? "Doctor added successfully!" : "Error: " . $stmt->error;
        }
        header("Location: doctors.php");
        exit;
    } else {
        $msg = "All fields are required, and phone must be 10 digits.";
    }
}

$doctors = $conn->query("SELECT * FROM doctors");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctors</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            margin: 0;
            padding: 0;
        }

        nav {
            background-color: #2c3e50;
            padding: 12px 20px;
            text-align: center;
        }

        nav a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #18bc9c;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        form {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 20px auto;
        }

        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        form input, form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        form input[type="submit"] {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        form input[type="submit"]:hover {
            background-color: #2980b9;
        }

        .msg {
            text-align: center;
            font-weight: bold;
            color: green;
        }

        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        th {
            background-color: #2c3e50;
            color: white;
        }

        .btn-edit, .btn-delete {
            text-decoration: none;
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 5px;
        }

        .btn-edit {
            background-color: #f39c12;
            color: white;
        }

        .btn-edit:hover {
            background-color: #e67e22;
        }

        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }

        .btn-delete:hover {
            background-color: #c0392b;
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

<h2><?= $editMode ? "Edit Doctor" : "Add New Doctor" ?></h2>

<form method="post">
    <?php if ($editMode): ?>
        <input type="hidden" name="edit_id" value="<?= htmlspecialchars($id) ?>">
    <?php endif; ?>

    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>

    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

    <label>Phone:</label>
    <input type="text" name="phone" pattern="\d{10}" maxlength="10" value="<?= htmlspecialchars($phone) ?>" required>

    <label>Speciality:</label>
    <input type="text" name="speciality" value="<?= htmlspecialchars($speciality) ?>" required>

    <label>Gender:</label>
    <select name="gender" required>
        <option value="">Select</option>
        <option value="Male" <?= $gender == "Male" ? "selected" : "" ?>>Male</option>
        <option value="Female" <?= $gender == "Female" ? "selected" : "" ?>>Female</option>
        <option value="Other" <?= $gender == "Other" ? "selected" : "" ?>>Other</option>
    </select>

    <input type="submit" value="<?= $editMode ? "Update Doctor" : "Add Doctor" ?>">
    <p class="msg"><?= $msg ?></p>
</form>

<h2>Doctor List</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Speciality</th>
        <th>Gender</th>
        <th>Edit</th>
        <th>Delete</th>
    </tr>
    <?php while ($row = $doctors->fetch_assoc()) : ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= htmlspecialchars($row['speciality']) ?></td>
        <td><?= htmlspecialchars($row['gender']) ?></td>
        <td><a class="btn-edit" href="?edit=<?= $row['id'] ?>">Edit</a></td>
        <td><a class="btn-delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a></td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
