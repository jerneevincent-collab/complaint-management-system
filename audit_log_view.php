<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    die("Access denied. Administrator access only.");
}

$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query(
    "SELECT a.*, u.full_name FROM audit_logs a
     LEFT JOIN users u ON a.user_id = u.user_id
     ORDER BY a.log_id DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Audit Trail</title>
</head>
<body>
    <h2>Audit Trail</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>User</th>
            <th>Activity</th>
            <th>Record Affected</th>
            <th>Date/Time</th>
            <th>IP Address</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['full_name'] ?? 'Unknown'); ?></td>
            <td><?php echo htmlspecialchars($row['activity']); ?></td>
            <td><?php echo htmlspecialchars($row['record_affected'] ?? '-'); ?></td>
            <td><?php echo $row['created_at']; ?></td>
            <td><?php echo htmlspecialchars($row['ip_address'] ?? '-'); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>