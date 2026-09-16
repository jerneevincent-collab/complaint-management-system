<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_id = $_GET['assignment_id'];
$stmt = $conn->prepare("SELECT a.*, c.complaint_number FROM assignments a JOIN complaints c ON a.complaint_id = c.complaint_id WHERE a.assignment_id = ?");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();

$personnel = $conn->query("SELECT user_id, full_name FROM users WHERE status = 'active' ORDER BY full_name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reassign Complaint</title>
</head>
<body>
    <h2>Reassign Complaint: <?php echo htmlspecialchars($assignment['complaint_number']); ?></h2>

    <form action="save_reassignment.php" method="POST">
        <input type="hidden" name="assignment_id" value="<?php echo $assignment['assignment_id']; ?>">
        <input type="hidden" name="complaint_id" value="<?php echo $assignment['complaint_id']; ?>">
        <input type="hidden" name="previous_assignee" value="<?php echo $assignment['assigned_to']; ?>">

        <label>New Assignee:</label><br>
        <select name="new_assignee" required>
            <?php while ($p = $personnel->fetch_assoc()): ?>
                <option value="<?php echo $p['user_id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Reason for Reassignment:</label><br>
        <textarea name="reason" rows="3" required></textarea><br><br>

        <button type="submit">Reassign</button>
    </form>
</body>
</html>
