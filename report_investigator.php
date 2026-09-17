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

$result = $conn->query(
    "SELECT u.full_name,
        COUNT(i.investigation_id) AS total_assigned,
        SUM(CASE WHEN i.status = 'Completed' THEN 1 ELSE 0 END) AS total_completed,
        SUM(CASE WHEN i.status != 'Completed' THEN 1 ELSE 0 END) AS total_pending,
        SUM(CASE WHEN i.status != 'Completed' AND i.target_completion_date < CURDATE() THEN 1 ELSE 0 END) AS total_overdue,
        AVG(CASE WHEN i.status = 'Completed' THEN DATEDIFF(i.target_completion_date, i.start_date) ELSE NULL END) AS avg_days
     FROM users u
     LEFT JOIN investigations i ON u.user_id = i.investigator_id
     WHERE u.role_id = 3
     GROUP BY u.user_id, u.full_name"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Investigator Performance Report</title>
</head>
<body>
    <h2>Investigator Performance Report</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Investigator</th>
            <th>Assigned</th>
            <th>Completed</th>
            <th>Pending</th>
            <th>Overdue</th>
            <th>Avg. Investigation Time (days)</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
            <td><?php echo $row['total_assigned']; ?></td>
            <td><?php echo $row['total_completed']; ?></td>
            <td><?php echo $row['total_pending']; ?></td>
            <td><?php echo $row['total_overdue']; ?></td>
            <td><?php echo $row['avg_days'] !== null ? round($row['avg_days'], 1) : 'N/A'; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>