<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query(
    "SELECT a.*, c.complaint_number, c.subject, u.full_name AS assignee_name, d.department_name
     FROM assignments a
     JOIN complaints c ON a.complaint_id = c.complaint_id
     JOIN users u ON a.assigned_to = u.user_id
     LEFT JOIN departments d ON a.department_id = d.department_id
     ORDER BY a.assignment_id DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Assignment List</title>
</head>
<body>
    <h2>Assignment List</h2>
    <p><a href="assignment_form.php">+ New Assignment</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Complaint #</th>
            <th>Subject</th>
            <th>Assigned To</th>
            <th>Department</th>
            <th>Assignment Date</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['complaint_number']); ?></td>
            <td><?php echo htmlspecialchars($row['subject']); ?></td>
            <td><?php echo htmlspecialchars($row['assignee_name']); ?></td>
            <td><?php echo htmlspecialchars($row['department_name']); ?></td>
            <td><?php echo $row['assignment_date']; ?></td>
            <td><?php echo $row['due_date']; ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td><a href="reassign_form.php?assignment_id=<?php echo $row['assignment_id']; ?>">Reassign</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>