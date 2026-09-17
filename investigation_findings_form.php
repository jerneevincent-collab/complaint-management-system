<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$investigation_id = $_GET['investigation_id'];
$stmt = $conn->prepare(
    "SELECT i.*, c.complaint_number FROM investigations i JOIN complaints c ON i.complaint_id = c.complaint_id WHERE i.investigation_id = ?"
);
$stmt->bind_param("i", $investigation_id);
$stmt->execute();
$investigation = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Investigation Findings</title>
</head>
<body>
    <h2>Investigation Findings: <?php echo htmlspecialchars($investigation['complaint_number']); ?></h2>

    <form action="save_investigation_findings.php" method="POST">
        <input type="hidden" name="investigation_id" value="<?php echo $investigation['investigation_id']; ?>">

        <label>Date Investigated:</label><br>
        <input type="date" name="date_investigated" required><br><br>

        <label>Evidence Collected:</label><br>
        <textarea name="evidence_collected" rows="3"></textarea><br><br>

        <label>Findings:</label><br>
        <textarea name="findings" rows="4" required></textarea><br><br>

        <label>Witnesses:</label><br>
        <input type="text" name="witnesses"><br><br>

        <label>Investigator's Recommendation:</label><br>
        <textarea name="recommendation" rows="3"></textarea><br><br>

        <label>Classification:</label><br>
        <select name="classification" required>
            <option value="Valid Complaint">Valid Complaint</option>
            <option value="Partially Valid">Partially Valid</option>
            <option value="Unsubstantiated">Unsubstantiated</option>
            <option value="Invalid">Invalid</option>
        </select><br><br>

        <button type="submit">Save Findings</button>
    </form>

    <?php
$findingsCheck = $conn->prepare("SELECT * FROM investigation_findings WHERE investigation_id = ? ORDER BY finding_id DESC");
$findingsCheck->bind_param("i", $investigation_id);
$findingsCheck->execute();
$findingsResult = $findingsCheck->get_result();

if ($findingsResult->num_rows > 0):
?>
<h3>Recorded Findings</h3>
<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>Date Investigated</th>
        <th>Findings</th>
        <th>Classification</th>
        <th>Recommendation</th>
    </tr>
    <?php while ($f = $findingsResult->fetch_assoc()): ?>
    <tr>
        <td><?php echo $f['date_investigated']; ?></td>
        <td><?php echo nl2br(htmlspecialchars($f['findings'])); ?></td>
        <td><?php echo htmlspecialchars($f['classification']); ?></td>
        <td><?php echo nl2br(htmlspecialchars($f['recommendation'])); ?></td>
    </tr>
    <?php endwhile; ?>
</table>
<?php endif; ?>
</body>
</html>