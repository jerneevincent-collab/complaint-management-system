<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complaint_id = $_POST['complaint_id'];
$investigation_id = $_POST['investigation_id'];
$action_type = $_POST['action_type'];
$action_description = trim($_POST['action_description']);
$responsible_person = $_POST['responsible_person'];
$target_date = $_POST['target_date'];

if (empty($complaint_id) || empty($action_type) || empty($action_description) || empty($responsible_person)) {
    header("Location: action_form.php?status=error");
    exit();
}

$stmt = $conn->prepare(
    "INSERT INTO actions (complaint_id, investigation_id, action_type, action_description, responsible_person, target_date, status)
     VALUES (?, ?, ?, ?, ?, ?, 'Pending')"
);
$stmt->bind_param("iissis", $complaint_id, $investigation_id, $action_type, $action_description, $responsible_person, $target_date);
$stmt->execute();

// Update complaint status
$update = $conn->prepare("UPDATE complaints SET status = 'Action Required' WHERE complaint_id = ?");
$update->bind_param("i", $complaint_id);
$update->execute();

header("Location: action_form.php?status=success");
exit();
?>
