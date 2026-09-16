<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query(
    "SELECT i.*, c.complaint_number, c.subject, u.full_name AS investigator_name
     FROM investigations i
     JOIN complaints c ON i.complaint_id = c.complaint_id
     JOIN users u ON i.investigator_id = u.user_id
     ORDER BY i.investigation_id DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Investigation List</title>
</head>
<body>
    <h2>Investigations</h2>
    <p><a href="investigation_form.php">+ New Investigation</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Complaint #</th>
            <th>Subject</th>
            <th>Investigator</th>
            <th>Start Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['complaint_number']); ?></td>
            <td><?php echo htmlspecialchars($row['subject']); ?></td>
            <td><?php echo htmlspecialchars($row['investigator_name']); ?></td>
            <td><?php echo $row['start_date']; ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td><a href="investigation_findings_form.php?investigation_id=<?php echo $row['investigation_id']; ?>">Add Findings</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>