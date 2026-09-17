<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complaint_id = $_POST['complaint_id'];
$closed_by = $_POST['closed_by'];
$closure_date = date("Y-m-d H:i:s");

$update = $conn->prepare("UPDATE complaints SET status = 'Closed' WHERE complaint_id = ?");
$update->bind_param("i", $complaint_id);
$update->execute();

$stmt = $conn->prepare(
    "INSERT INTO audit_logs (user_id, activity, record_affected, previous_value, new_value, created_at)
     VALUES (?, 'Closed complaint', ?, 'Resolved', 'Closed', ?)"
);
$recordAffected = "complaint_id: " . $complaint_id;
$stmt->bind_param("iss", $closed_by, $recordAffected, $closure_date);
$stmt->execute();

header("Location: complaint_close.php?status=success");
exit();
?>