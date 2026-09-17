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

$pending = $conn->query("SELECT COUNT(*) AS c FROM actions WHERE status = 'Pending'")->fetch_assoc()['c'];
$completed = $conn->query("SELECT COUNT(*) AS c FROM actions WHERE status IN ('Completed', 'Verified')")->fetch_assoc()['c'];
$overdue = $conn->query("SELECT COUNT(*) AS c FROM actions WHERE target_date < CURDATE() AND status NOT IN ('Completed', 'Verified')")->fetch_assoc()['c'];

$byPersonnel = $conn->query(
    "SELECT u.full_name,
        COUNT(a.action_id) AS total,
        SUM(CASE WHEN a.status IN ('Completed','Verified') THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN a.status NOT IN ('Completed','Verified') THEN 1 ELSE 0 END) AS pending
     FROM users u
     LEFT JOIN actions a ON u.user_id = a.responsible_person
     GROUP BY u.user_id, u.full_name
     HAVING total > 0"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Action Monitoring Report</title>
</head>
<body>
    <h2>Action Monitoring Report</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Pending Actions</th><td><?php echo $pending; ?></td></tr>
        <tr><th>Completed Actions</th><td><?php echo $completed; ?></td></tr>
        <tr><th>Overdue Actions</th><td><?php echo $overdue; ?></td></tr>
    </table>

    <h3>Actions by Responsible Personnel</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Personnel</th>
            <th>Total</th>
            <th>Completed</th>
            <th>Pending</th>
        </tr>
        <?php while ($row = $byPersonnel->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
            <td><?php echo $row['total']; ?></td>
            <td><?php echo $row['completed']; ?></td>
            <td><?php echo $row['pending']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>