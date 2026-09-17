<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['update_status'])) {
    $action_id = $_POST['action_id'];
    $new_status = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE actions SET status = ? WHERE action_id = ?");
    $stmt->bind_param("si", $new_status, $action_id);
    $stmt->execute();
    header("Location: action_list.php");
    exit();
}

$result = $conn->query(
    "SELECT a.*, c.complaint_number, u.full_name AS responsible_name
     FROM actions a
     JOIN complaints c ON a.complaint_id = c.complaint_id
     JOIN users u ON a.responsible_person = u.user_id
     ORDER BY a.action_id DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Action Monitoring</title>
</head>
<body>
    <h2>Action Monitoring</h2>
    <p><a href="action_form.php">+ New Action</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Complaint #</th>
            <th>Assigned Date</th>
            <th>Action Type</th>
            <th>Responsible</th>
            <th>Target Date</th>
            <th>Status</th>
            <th>Remarks</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()):
            $isOverdue = ($row['target_date'] && $row['target_date'] < date("Y-m-d") && $row['status'] != 'Completed' && $row['status'] != 'Verified');
        ?>
        <tr <?php if ($isOverdue) echo "style='background-color:#ffdddd;'"; ?>>
            <td><?php echo htmlspecialchars($row['complaint_number']); ?></td>
            <td><?php echo date("Y-m-d", strtotime($row['created_at'])); ?></td>
            <td><?php echo htmlspecialchars($row['action_type']); ?></td>
            <td><?php echo htmlspecialchars($row['responsible_name']); ?></td>
            <td><?php echo $row['target_date'] ?: '-'; ?></td>
            <td>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action_id" value="<?php echo $row['action_id']; ?>">
                    <select name="new_status" onchange="this.form.submit()">
                        <option value="Pending" <?php if ($row['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                        <option value="In Progress" <?php if ($row['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                        <option value="Completed" <?php if ($row['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                        <option value="Verified" <?php if ($row['status'] == 'Verified') echo 'selected'; ?>>Verified</option>
                    </select>
                    <input type="hidden" name="update_status" value="1">
                </form>
            </td>
            <td><?php echo $isOverdue ? "<strong style='color:red;'>OVERDUE</strong>" : "-"; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>