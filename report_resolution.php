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

$totalResolved = $conn->query(
    "SELECT COUNT(*) AS c FROM complaints WHERE status IN ('Resolved', 'Closed')"
)->fetch_assoc()['c'];

$avgResolution = $conn->query(
    "SELECT AVG(DATEDIFF(r.resolution_date, c.date_filed)) AS avg_days
     FROM resolutions r
     JOIN complaints c ON r.complaint_id = c.complaint_id"
)->fetch_assoc()['avg_days'];

$resolvedWithinDeadline = $conn->query(
    "SELECT COUNT(*) AS c
     FROM resolutions r
     JOIN complaints c ON r.complaint_id = c.complaint_id
     WHERE DATEDIFF(r.resolution_date, c.date_filed) <= 7"
)->fetch_assoc()['c'];

$overdueComplaints = $conn->query(
    "SELECT COUNT(*) AS c FROM assignments WHERE due_date < CURDATE() AND status = 'active'"
)->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Resolution Report</title>
</head>
<body>
    <h2>Complaint Resolution Report</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Total Complaints Resolved</th><td><?php echo $totalResolved; ?></td></tr>
        <tr><th>Average Resolution Time (days)</th><td><?php echo $avgResolution !== null ? round($avgResolution, 1) : 'N/A'; ?></td></tr>
        <tr><th>Resolved Within 7-Day Deadline</th><td><?php echo $resolvedWithinDeadline; ?></td></tr>
        <tr><th>Overdue Complaints (Assignment)</th><td><?php echo $overdueComplaints; ?></td></tr>
    </table>
</body>
</html>