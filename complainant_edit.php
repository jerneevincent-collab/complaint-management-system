<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM complainants WHERE complainant_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$complainant = $result->fetch_assoc();

if (!$complainant) {
    die("Complainant not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Complainant</title>
</head>
<body>
    <h2>Edit Complainant</h2>
    <form action="update_complainant.php" method="POST">
        <input type="hidden" name="complainant_id" value="<?php echo $complainant['complainant_id']; ?>">

        <label>Full Name:</label><br>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($complainant['full_name']); ?>" required><br><br>

        <label>Contact Number:</label><br>
        <input type="text" name="contact_number" value="<?php echo htmlspecialchars($complainant['contact_number']); ?>" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($complainant['email']); ?>" required><br><br>

        <label>Address:</label><br>
        <input type="text" name="address" value="<?php echo htmlspecialchars($complainant['address']); ?>" required><br><br>

        <label>Organization/Department:</label><br>
        <input type="text" name="organization_department" value="<?php echo htmlspecialchars($complainant['organization_department']); ?>" required><br><br>

        <label>Status:</label><br>
        <select name="status">
            <option value="active" <?php if ($complainant['status'] == 'active') echo 'selected'; ?>>Active</option>
            <option value="inactive" <?php if ($complainant['status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
        </select><br><br>

        <button type="submit">Update</button>
    </form>
</body>
</html>