<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complaints = $conn->query(
    "SELECT complaint_id, complaint_number, subject FROM complaints
     WHERE status NOT IN ('Closed') ORDER BY complaint_id DESC"
);
$personnel = $conn->query("SELECT user_id, full_name FROM users WHERE status = 'active' ORDER BY full_name");
$departments = $conn->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Assign Complaint</title>
</head>
<body>
    <h2>Complaint Assignment</h2>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success') echo "<p style='color:green;'>Complaint assigned successfully!</p>"; ?>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'error') echo "<p style='color:red;'>" . htmlspecialchars($_GET['msg'] ?? 'Assignment failed.') . "</p>"; ?>

    <form action="save_assignment.php" method="POST">
        <label>Complaint:</label><br>
        <select name="complaint_id" required>
            <option value="">-- Select Complaint --</option>
            <?php while ($c = $complaints->fetch_assoc()): ?>
                <option value="<?php echo $c['complaint_id']; ?>">
                    <?php echo htmlspecialchars($c['complaint_number'] . " - " . $c['subject']); ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Assign To (Personnel):</label><br>
        <select name="assigned_to" required>
            <option value="">-- Select Personnel --</option>
            <?php while ($p = $personnel->fetch_assoc()): ?>
                <option value="<?php echo $p['user_id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Department:</label><br>
        <select name="department_id" required>
            <?php while ($d = $departments->fetch_assoc()): ?>
                <option value="<?php echo $d['department_id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Assignment Date:</label><br>
        <input type="date" name="assignment_date" required><br><br>

        <label>Due Date:</label><br>
        <input type="date" name="due_date" required><br><br>

        <label>Instructions:</label><br>
        <textarea name="instructions" rows="3"></textarea><br><br>

        <button type="submit">Assign Complaint</button>
    </form>
</body>
</html>