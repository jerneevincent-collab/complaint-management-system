<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];
$stmt = $conn->prepare(
    "SELECT c.*, co.full_name AS complainant_name, cc.category_name
     FROM complaints c
     JOIN complainants co ON c.complainant_id = co.complainant_id
     JOIN complaint_categories cc ON c.category_id = cc.category_id
     WHERE c.complaint_id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();

if (!$complaint) {
    die("Complaint not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Details</title>
</head>
<body>
    <h2>Complaint Details</h2>
    <p><a href="complaint_list.php">&larr; Back to List</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Complaint Number</th><td><?php echo htmlspecialchars($complaint['complaint_number']); ?></td></tr>
        <tr><th>Complainant</th><td><?php echo htmlspecialchars($complaint['complainant_name']); ?></td></tr>
        <tr><th>Category</th><td><?php echo htmlspecialchars($complaint['category_name']); ?></td></tr>
        <tr><th>Subject</th><td><?php echo htmlspecialchars($complaint['subject']); ?></td></tr>
        <tr><th>Description</th><td><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></td></tr>
        <tr><th>Date Filed</th><td><?php echo $complaint['date_filed']; ?></td></tr>
        <tr><th>Location</th><td><?php echo htmlspecialchars($complaint['location']); ?></td></tr>
        <tr><th>Priority</th><td><?php echo htmlspecialchars($complaint['priority']); ?></td></tr>
        <tr><th>Status</th><td><?php echo htmlspecialchars($complaint['status']); ?></td></tr>
    </table>

    <?php if ($complaint['status'] != 'Closed'): ?>
    <h3>Update Status</h3>
    <form action="update_complaint_status.php" method="POST">
        <input type="hidden" name="complaint_id" value="<?php echo $complaint['complaint_id']; ?>">
        <select name="new_status">
            <option value="Submitted" <?php if ($complaint['status'] == 'Submitted') echo 'selected'; ?>>Submitted</option>
            <option value="For Assignment" <?php if ($complaint['status'] == 'For Assignment') echo 'selected'; ?>>For Assignment</option>
            <option value="Assigned" <?php if ($complaint['status'] == 'Assigned') echo 'selected'; ?>>Assigned</option>
            <option value="Under Investigation" <?php if ($complaint['status'] == 'Under Investigation') echo 'selected'; ?>>Under Investigation</option>
            <option value="Action Required" <?php if ($complaint['status'] == 'Action Required') echo 'selected'; ?>>Action Required</option>
            <option value="For Resolution" <?php if ($complaint['status'] == 'For Resolution') echo 'selected'; ?>>For Resolution</option>
            <option value="Resolved" <?php if ($complaint['status'] == 'Resolved') echo 'selected'; ?>>Resolved</option>
            <option value="Closed" <?php if ($complaint['status'] == 'Closed') echo 'selected'; ?>>Closed</option>
        </select>
        <button type="submit">Update Status</button>
    </form>
    <?php else: ?>
    <p><em>This complaint is closed and can no longer be modified.</em></p>
    <?php endif; ?>
</body>
</html>