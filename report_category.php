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

$byCategory = $conn->query(
    "SELECT cc.category_name, COUNT(*) AS total
     FROM complaints c
     JOIN complaint_categories cc ON c.category_id = cc.category_id
     GROUP BY cc.category_name
     ORDER BY total DESC"
);

$byPriority = $conn->query(
    "SELECT priority, COUNT(*) AS total FROM complaints GROUP BY priority ORDER BY total DESC"
);

$byStatus = $conn->query(
    "SELECT status, COUNT(*) AS total FROM complaints GROUP BY status ORDER BY total DESC"
);

$byDate = $conn->query(
    "SELECT date_filed, COUNT(*) AS total FROM complaints GROUP BY date_filed ORDER BY date_filed DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Category Report</title>
</head>
<body>
    <h2>Complaint Category Report</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <h3>By Category</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Category</th><th>Total</th></tr>
        <?php while ($row = $byCategory->fetch_assoc()): ?>
        <tr><td><?php echo htmlspecialchars($row['category_name']); ?></td><td><?php echo $row['total']; ?></td></tr>
        <?php endwhile; ?>
    </table>

    <h3>By Priority</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Priority</th><th>Total</th></tr>
        <?php while ($row = $byPriority->fetch_assoc()): ?>
        <tr><td><?php echo htmlspecialchars($row['priority']); ?></td><td><?php echo $row['total']; ?></td></tr>
        <?php endwhile; ?>
    </table>

    <h3>By Status</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Status</th><th>Total</th></tr>
        <?php while ($row = $byStatus->fetch_assoc()): ?>
        <tr><td><?php echo htmlspecialchars($row['status']); ?></td><td><?php echo $row['total']; ?></td></tr>
        <?php endwhile; ?>
    </table>

    <h3>By Date Filed</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Date Filed</th><th>Total</th></tr>
        <?php while ($row = $byDate->fetch_assoc()): ?>
        <tr><td><?php echo $row['date_filed']; ?></td><td><?php echo $row['total']; ?></td></tr>
        <?php endwhile; ?>
    </table>
</body>
</html>