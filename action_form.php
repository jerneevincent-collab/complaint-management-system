<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$investigations = $conn->query(
    "SELECT i.investigation_id, c.complaint_id, c.complaint_number, c.subject
     FROM investigations i
     JOIN complaints c ON i.complaint_id = c.complaint_id
     WHERE i.status = 'Completed'
     ORDER BY i.investigation_id DESC"
);
$personnel = $conn->query("SELECT user_id, full_name FROM users WHERE status = 'active' ORDER BY full_name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Action</title>
</head>
<body>
    <h2>Corrective/Administrative Action</h2>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success') echo "<p style='color:green;'>Action created successfully!</p>"; ?>

    <form action="save_action.php" method="POST">
        <label>Investigation (Completed only):</label><br>
        <select name="investigation_data" required onchange="
            var parts = this.value.split('|');
            document.getElementById('complaint_id').value = parts[0];
            document.getElementById('investigation_id').value = parts[1];
        ">
            <option value="">-- Select Investigation --</option>
            <?php while ($i = $investigations->fetch_assoc()): ?>
                <option value="<?php echo $i['complaint_id']; ?>|<?php echo $i['investigation_id']; ?>">
                    <?php echo htmlspecialchars($i['complaint_number'] . " - " . $i['subject']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <input type="hidden" name="complaint_id" id="complaint_id">
        <input type="hidden" name="investigation_id" id="investigation_id"><br><br>

        <label>Action Type:</label><br>
        <select name="action_type" required>
            <option value="Corrective Action">Corrective Action</option>
            <option value="Preventive Action">Preventive Action</option>
            <option value="Employee Action">Employee Action</option>
            <option value="Process Improvement">Process Improvement</option>
            <option value="Service Recovery">Service Recovery</option>
            <option value="Policy Recommendation">Policy Recommendation</option>
            <option value="No Action Required">No Action Required</option>
        </select><br><br>

        <label>Action Description:</label><br>
        <textarea name="action_description" rows="4" required></textarea><br><br>

        <label>Responsible Person:</label><br>
        <select name="responsible_person" required>
            <option value="">-- Select Personnel --</option>
            <?php while ($p = $personnel->fetch_assoc()): ?>
                <option value="<?php echo $p['user_id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Target Date:</label><br>
        <input type="date" name="target_date"><br><br>

        <button type="submit">Create Action</button>
    </form>
</body>
</html>