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
$name = $email = $phone = $dob = $gender = $age = $blood_group = $address = $street = $city = $state = $pincode = "";
$editMode = false;

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM patients WHERE id = $id");
    header("Location: patients.php");
    exit;
}

if (isset($_GET['edit'])) {
    $editMode = true;
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM patients WHERE id = $id");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $name = $row['name'];
        $email = $row['email'];
        $phone = $row['phone'];
        $dob = $row['dob'];
        $gender = $row['gender'];
        $age = $row['age'];
        $blood_group = $row['blood_group'];
        $address = $row['address'];
        $street = $row['street'];
        $city = $row['city'];
        $state = $row['state'];
        $pincode = $row['pincode'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $age = intval($_POST['age']);
    $blood_group = trim($_POST['blood_group']);
    $address = trim($_POST['address']);
    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $pincode = trim($_POST['pincode']);

    if ($name && $email && preg_match('/^\d{10}$/', $phone) && $dob && $gender && $age && $blood_group && $address && $street && $city && $state && $pincode) {
        if (isset($_POST['edit_id'])) {
            $edit_id = intval($_POST['edit_id']);
            $stmt = $conn->prepare("UPDATE patients SET name=?, email=?, phone=?, dob=?, gender=?, age=?, blood_group=?, address=?, street=?, city=?, state=?, pincode=? WHERE id=?");
            $stmt->bind_param("sssssissssssi", $name, $email, $phone, $dob, $gender, $age, $blood_group, $address, $street, $city, $state, $pincode, $edit_id);
            if ($stmt->execute()) {
                $msg = "Patient updated successfully!";
                header("Location: patients.php");
                exit;
            } else {
                $msg = "Error updating: " . $stmt->error;
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO patients (name, email, phone, dob, gender, age, blood_group, address, street, city, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssissssss", $name, $email, $phone, $dob, $gender, $age, $blood_group, $address, $street, $city, $state, $pincode);
            if ($stmt->execute()) {
                $msg = "Patient added successfully!";
                header("Location: patients.php");
                exit;
            } else {
                $msg = "Error: " . $stmt->error;
            }
        }
    } else {
        $msg = "All fields are required, and phone must be 10 digits.";
    }
}

$patients = $conn->query("SELECT * FROM patients");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patients Management</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: #f0f2f5;
        }

        nav {
            background: #34495e;
            padding: 15px;
            text-align: center;
        }

        nav a {
            color: #ecf0f1;
            margin: 0 15px;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #1abc9c;
        }

        h2 {
            text-align: center;
            margin: 20px 0;
            color: #2c3e50;
        }

        form {
            background: #fff;
            padding: 30px;
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
        }

        textarea {
            resize: vertical;
        }

        input[type="submit"] {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        input[type="submit"]:hover {
            background: #2980b9;
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
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background: #2980b9;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #eaf6ff;
        }

        .btn-edit, .btn-delete {
            padding: 6px 12px;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
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

        @media screen and (max-width: 768px) {
            form, table {
                width: 95%;
            }

            th, td {
                font-size: 13px;
                padding: 8px;
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

<h2><?= $editMode ? "Edit Patient" : "Add New Patient" ?></h2>

<form method="post">
    <?php if ($editMode): ?>
        <input type="hidden" name="edit_id" value="<?= htmlspecialchars($id) ?>">
    <?php endif; ?>
    
    <label>Name:</label><input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
    <label>Email:</label><input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
    <label>Phone:</label><input type="text" name="phone" pattern="\d{10}" maxlength="10" value="<?= htmlspecialchars($phone) ?>" required>
    <label>Date of Birth:</label><input type="date" name="dob" value="<?= htmlspecialchars($dob) ?>" required>
    <label>Gender:</label>
    <select name="gender" required>
        <option value="">Select</option>
        <option value="Male" <?= $gender == "Male" ? "selected" : "" ?>>Male</option>
        <option value="Female" <?= $gender == "Female" ? "selected" : "" ?>>Female</option>
        <option value="Other" <?= $gender == "Other" ? "selected" : "" ?>>Other</option>
    </select>
    <label>Age:</label><input type="number" name="age" min="0" value="<?= htmlspecialchars($age) ?>" required>
    <label>Blood Group:</label><input type="text" name="blood_group" value="<?= htmlspecialchars($blood_group) ?>" required>
    <label>Address:</label><textarea name="address" required><?= htmlspecialchars($address) ?></textarea>
    <label>Street:</label><input type="text" name="street" value="<?= htmlspecialchars($street) ?>" required>
    <label>City:</label><input type="text" name="city" value="<?= htmlspecialchars($city) ?>" required>
    <label>State:</label><input type="text" name="state" value="<?= htmlspecialchars($state) ?>" required>
    <label>Pincode:</label><input type="text" name="pincode" value="<?= htmlspecialchars($pincode) ?>" required>

    <input type="submit" value="<?= $editMode ? "Update Patient" : "Add Patient" ?>">
    <p class="msg"><?= $msg ?></p>
</form>

<h2>Patient List</h2>
<table>
    <tr>
        <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>DOB</th><th>Gender</th><th>Age</th><th>Blood Group</th>
        <th>Address</th><th>Street</th><th>City</th><th>State</th><th>Pincode</th><th>Edit</th><th>Delete</th>
    </tr>
    <?php while ($row = $patients->fetch_assoc()) : ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= $row['dob'] ?></td>
        <td><?= htmlspecialchars($row['gender']) ?></td>
        <td><?= $row['age'] ?></td>
        <td><?= htmlspecialchars($row['blood_group']) ?></td>
        <td><?= htmlspecialchars($row['address']) ?></td>
        <td><?= htmlspecialchars($row['street']) ?></td>
        <td><?= htmlspecialchars($row['city']) ?></td>
        <td><?= htmlspecialchars($row['state']) ?></td>
        <td><?= htmlspecialchars($row['pincode']) ?></td>
        <td><a class="btn-edit" href="?edit=<?= $row['id'] ?>">Edit</a></td>
        <td><a class="btn-delete" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a></td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
    