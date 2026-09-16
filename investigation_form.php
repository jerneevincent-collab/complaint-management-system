<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complaints = $conn->query(
    "SELECT complaint_id, complaint_number, subject FROM complaints
     WHERE status = 'Assigned' ORDER BY complaint_id DESC"
);
$investigators = $conn->query("SELECT user_id, full_name FROM users WHERE status = 'active' ORDER BY full_name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Investigation</title>
</head>
<body>
    <h2>Create Investigation</h2>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success') echo "<p style='color:green;'>Investigation created successfully!</p>"; ?>

    <form action="save_investigation.php" method="POST">
        <label>Complaint:</label><br>
        <select name="complaint_id" required>
            <option value="">-- Select Assigned Complaint --</option>
            <?php while ($c = $complaints->fetch_assoc()): ?>
                <option value="<?php echo $c['complaint_id']; ?>">
                    <?php echo htmlspecialchars($c['complaint_number'] . " - " . $c['subject']); ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Investigator:</label><br>
        <select name="investigator_id" required>
            <option value="">-- Select Investigator --</option>
            <?php while ($i = $investigators->fetch_assoc()): ?>
                <option value="<?php echo $i['user_id']; ?>"><?php echo htmlspecialchars($i['full_name']); ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Start Date:</label><br>
        <input type="date" name="start_date" required><br><br>

        <label>Target Completion Date:</label><br>
        <input type="date" name="target_completion_date"><br><br>

        <button type="submit">Create Investigation</button>
    </form>
</body>
</html>
