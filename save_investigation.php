<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complaint_id = $_POST['complaint_id'];
$investigator_id = $_POST['investigator_id'];
$start_date = $_POST['start_date'];
$target_completion_date = $_POST['target_completion_date'];

if (empty($complaint_id) || empty($investigator_id) || empty($start_date)) {
    header("Location: investigation_form.php?status=error");
    exit();
}

$stmt = $conn->prepare(
    "INSERT INTO investigations (complaint_id, investigator_id, start_date, target_completion_date, status)
     VALUES (?, ?, ?, ?, 'Assigned')"
);
$stmt->bind_param("iiss", $complaint_id, $investigator_id, $start_date, $target_completion_date);
$stmt->execute();

// Update complaint status
$update = $conn->prepare("UPDATE complaints SET status = 'Under Investigation' WHERE complaint_id = ?");
$update->bind_param("i", $complaint_id);
$update->execute();

header("Location: investigation_form.php?status=success");
exit();
?>