<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Management System</title>
</head>
<body>
    <h1>Complaint Management System</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! | <a href="logout.php">Logout</a></p>

    <h2>Quick Summary</h2>
    <?php
        $total = $conn->query("SELECT COUNT(*) AS c FROM complaints")->fetch_assoc()['c'];
        $open = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status NOT IN ('Resolved','Closed')")->fetch_assoc()['c'];
        $closed = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status = 'Closed'")->fetch_assoc()['c'];
    ?>
    <p>Total Complaints: <?php echo $total; ?> | Open: <?php echo $open; ?> | Closed: <?php echo $closed; ?></p>

    <h2>Complainant Management</h2>
    <ul>
        <li><a href="complainant_register.php">Register Complainant</a></li>
        <li><a href="complainant_list.php">Complainant List / Search</a></li>
    </ul>

    <h2>Complaint Management</h2>
    <ul>
        <li><a href="category_manage.php">Manage Complaint Categories</a></li>
        <li><a href="complaint_register.php">File a Complaint</a></li>
        <li><a href="complaint_list.php">Complaint List / Search</a></li>
        <li><a href="complaint_close.php">Complaint Closure</a></li>
    </ul>

    <h2>Assignment</h2>
    <ul>
        <li><a href="personnel_manage.php">Personnel Management</a></li>
        <li><a href="assignment_form.php">Assign Complaint</a></li>
        <li><a href="assignment_list.php">Assignment List</a></li>
    </ul>

    <h2>Investigation</h2>
    <ul>
        <li><a href="investigation_form.php">Create Investigation</a></li>
        <li><a href="investigation_list.php">Investigation List</a></li>
    </ul>

    <h2>Action</h2>