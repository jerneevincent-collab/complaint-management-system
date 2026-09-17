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

// Get existing evidence
$evidenceList = $conn->prepare("SELECT * FROM investigation_evidence WHERE investigation_id = ? ORDER BY evidence_id DESC");
$evidenceList->bind_param("i", $investigation_id);
$evidenceList->execute();
$evidenceResult = $evidenceList->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Investigation Evidence</title>
</head>
<body>
    <h2>Evidence for: <?php echo htmlspecialchars($investigation['complaint_number']); ?></h2>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success') echo "<p style='color:green;'>Evidence uploaded successfully!</p>"; ?>

    <form action="save_investigation_evidence.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="investigation_id" value="<?php echo $investigation['investigation_id']; ?>">

        <label>Upload Evidence File:</label><br>
        <input type="file" name="evidence_file" required><br><br>

        <button type="submit">Upload Evidence</button>
    </form>

    <h3>Uploaded Evidence</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>File Name</th>
            <th>Uploaded At</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $evidenceResult->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['file_name']); ?></td>
            <td><?php echo $row['uploaded_at']; ?></td>
            <td><a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">View/Download</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
