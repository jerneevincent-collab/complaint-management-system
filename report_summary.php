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

$total = $conn->query("SELECT COUNT(*) AS c FROM complaints")->fetch_assoc()['c'];
$submitted = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status = 'Submitted'")->fetch_assoc()['c'];
$open = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status NOT IN ('Resolved','Closed')")->fetch_assoc()['c'];
$resolved = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status = 'Resolved'")->fetch_assoc()['c'];
$closed = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status = 'Closed'")->fetch_assoc()['c'];
$pending = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status IN ('Submitted','For Assignment','Assigned')")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Summary Report</title>
</head>
<body>
    <h2>Complaint Summary Report</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Total Complaints</th><td><?php echo $total; ?></td></tr>
        <tr><th>Submitted</th><td><?php echo $submitted; ?></td></tr>
        <tr><th>Open (Not Resolved/Closed)</th><td><?php echo $open; ?></td></tr>
        <tr><th>Pending</th><td><?php echo $pending; ?></td></tr>
        <tr><th>Resolved</th><td><?php echo $resolved; ?></td></tr>
        <tr><th>Closed</th><td><?php echo $closed; ?></td></tr>
    </table>
</body>
</html>