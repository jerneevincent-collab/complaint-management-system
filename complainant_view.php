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
    <title>View Complainant</title>
</head>
<body>
    <h2>Complainant Details</h2>
    <p><a href="complainant_list.php">&larr; Back to List</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Complainant ID</th><td><?php echo $complainant['complainant_id']; ?></td></tr>
        <tr><th>Full Name</th><td><?php echo htmlspecialchars($complainant['full_name']); ?></td></tr>
        <tr><th>Contact Number</th><td><?php echo htmlspecialchars($complainant['contact_number']); ?></td></tr>
        <tr><th>Email</th><td><?php echo htmlspecialchars($complainant['email']); ?></td></tr>
        <tr><th>Address</th><td><?php echo htmlspecialchars($complainant['address']); ?></td></tr>
        <tr><th>Organization/Department</th><td><?php echo htmlspecialchars($complainant['organization_department']); ?></td></tr>
        <tr><th>Preferred Contact Method</th><td><?php echo htmlspecialchars($complainant['preferred_contact_method']); ?></td></tr>
        <tr><th>Date Registered</th><td><?php echo $complainant['date_registered']; ?></td></tr>
        <tr><th>Status</th><td><?php echo htmlspecialchars($complainant['status']); ?></td></tr>
    </table>
</body>
</html>