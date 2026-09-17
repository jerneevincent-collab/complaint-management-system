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

$monthly = $conn->query(
    "SELECT DATE_FORMAT(date_filed, '%Y-%m') AS month, COUNT(*) AS total
     FROM complaints GROUP BY month ORDER BY month DESC"
);

$byCategory = $conn->query(
    "SELECT cc.category_name, COUNT(*) AS total
     FROM complaints c
     JOIN complaint_categories cc ON c.category_id = cc.category_id
     GROUP BY cc.category_name ORDER BY total DESC"
);

$totalAll = $conn->query("SELECT COUNT(*) AS c FROM complaints")->fetch_assoc()['c'];
$totalResolved = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status IN ('Resolved','Closed')")->fetch_assoc()['c'];
$resolutionRate = $totalAll > 0 ? round(($totalResolved / $totalAll) * 100, 1) : 0;

$recurring = $conn->query(
    "SELECT complainant_id, COUNT(*) AS total FROM complaints GROUP BY complainant_id HAVING total > 1"
);

$byDepartment = $conn->query(
    "SELECT d.department_name, COUNT(a.assignment_id) AS total
     FROM departments d
     LEFT JOIN assignments a ON d.department_id = a.department_id
     GROUP BY d.department_id, d.department_name"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Trend Analysis</title>
</head>
<body>
    <h2>Complaint Trend Analysis</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <h3>Monthly Trend</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Month</th><th>Total Complaints</th></tr>
        <?php while ($row = $monthly->fetch_assoc()): ?>
        <tr><td><?php echo $row['month']; ?></td><td><?php echo $row['total']; ?></td></tr>
        <?php endwhile; ?>
    </table>

    <h3>By Category</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Category</th><th>Total</th></tr>
        <?php while ($row = $byCategory->fetch_assoc()): ?>
        <tr><td><?php echo htmlspecialchars($row['category_name']); ?></td><td><?php echo $row['total']; ?></td></tr>
        <?php endwhile; ?>
    </table>

    <h3>Resolution Rate</h3>
    <p><?php echo $resolutionRate; ?>% (<?php echo $totalResolved; ?> out of <?php echo $totalAll; ?> resolved or closed)</p>

    <h3>Recurring Complainants (more than 1 complaint)</h3>
    <?php if ($recurring->num_rows > 0): ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Complainant ID</th><th>Total Complaints</th></tr>
        <?php while ($row = $recurring->fetch_assoc()): ?>
        <tr><td><?php echo $row['complainant_id']; ?></td><td><?php echo $row['total']; ?></td></tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
    <p>No recurring complainants found.</p>
    <?php endif; ?>

    <h3>By Department</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Department</th><th>Total Assignments</th></tr>
        <?php while ($row = $byDepartment->fetch_assoc()): ?>
        <tr><td><?php echo htmlspecialchars($row['department_name']); ?></td><td><?php echo $row['total']; ?></td></tr>
        <?php endwhile; ?>
    </table>
</body>
</html>