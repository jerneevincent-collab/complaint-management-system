<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complaints = $conn->query(
    "SELECT complaint_id, complaint_number, subject FROM complaints
     WHERE status IN ('For Resolution', 'Action Required') ORDER BY complaint_id DESC"
);
$personnel = $conn->query("SELECT user_id, full_name FROM users WHERE status = 'active' ORDER BY full_name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Resolution</title>
</head>
<body>
    <h2>Complaint Resolution</h2>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success') echo "<p style='color:green;'>Resolution recorded successfully!</p>"; ?>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'error') echo "<p style='color:red;'>" . htmlspecialchars($_GET['msg'] ?? 'Resolution failed.') . "</p>"; ?>

    <form action="save_resolution.php" method="POST">
        <label>Complaint:</label><br>
        <select name="complaint_id" required>
            <option value="">-- Select Complaint --</option>
            <?php while ($c = $complaints->fetch_assoc()): ?>
                <option value="<?php echo $c['complaint_id']; ?>">
                    <?php echo htmlspecialchars($c['complaint_number'] . " - " . $c['subject']); ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Resolution Type:</label><br>
        <input type="text" name="resolution_type" placeholder="e.g. Refund, Policy Change, Apology"><br><br>

        <label>Resolution Description:</label><br>
        <textarea name="resolution_description" rows="4" required></textarea><br><br>

        <label>Resolution Date:</label><br>
        <input type="date" name="resolution_date" required><br><br>

        <label>Resolved By:</label><br>
        <select name="resolved_by" required>
            <option value="">-- Select Personnel --</option>
            <?php while ($p = $personnel->fetch_assoc()): ?>
                <option value="<?php echo $p['user_id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Remarks:</label><br>
        <textarea name="remarks" rows="2"></textarea><br><br>

        <button type="submit">Save Resolution</button>
    </form>
</body>
</html>