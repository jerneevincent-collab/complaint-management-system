<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complaint_id = $_POST['complaint_id'];
$new_status = $_POST['new_status'];

$stmt = $conn->prepare("UPDATE complaints SET status = ? WHERE complaint_id = ?");
$stmt->bind_param("si", $new_status, $complaint_id);
$stmt->execute();

header("Location: complaint_view.php?id=" . $complaint_id);
exit();
?>