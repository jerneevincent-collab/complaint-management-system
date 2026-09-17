<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [1, 5])) {
    die("Access denied. Only Administrators and Supervisors can close complaints.");
}
$conn = new mysqli("localhost", "root", "", "complaint_management_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complaints = $conn->query(
    "SELECT c.complaint_id, c.complaint_number, c.subject, r.resolution_description, r.resolution_date
     FROM complaints c
     JOIN resolutions r ON c.complaint_id = r.complaint_id
     WHERE c.status = 'Resolved'
     ORDER BY c.complaint_id DESC"
);
$personnel = $conn->query("SELECT user_id, full_name FROM users WHERE status = 'active' ORDER BY full_name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Closure</title>
</head>
<body>
    <h2>Complaint Closure</h2>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success') echo "<p style='color:green;'>Complaint closed successfully!</p>"; ?>

    <?php while ($c = $complaints->fetch_assoc()): ?>
    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        <p><strong><?php echo htmlspecialchars($c['complaint_number']); ?></strong> - <?php echo htmlspecialchars($c['subject']); ?></p>
        <p>Resolution: <?php echo htmlspecialchars($c['resolution_description']); ?> (<?php echo $c['resolution_date']; ?>)</p>

        <form action="save_closure.php" method="POST">
            <input type="hidden" name="complaint_id" value="<?php echo $c['complaint_id']; ?>">

            <label>Closed By:</label><br>
            <select name="closed_by" required>
                <option value="">-- Select Personnel --</option>
                <?php $personnel->data_seek(0); while ($p = $personnel->fetch_assoc()): ?>
                    <option value="<?php echo $p['user_id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
                <?php endwhile; ?>
            </select><br><br>

            <button type="submit" onclick="return confirm('Confirm closure? This complaint will no longer be editable.');">Confirm Closure</button>
        </form>
    </div>
    <?php endwhile; ?>
</body>
</html>