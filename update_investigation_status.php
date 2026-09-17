<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$investigation_id = $_POST['investigation_id'];
$new_status = $_POST['new_status'];

$stmt = $conn->prepare("UPDATE investigations SET status = ? WHERE investigation_id = ?");
$stmt->bind_param("si", $new_status, $investigation_id);
$stmt->execute();

header("Location: investigation_list.php");
exit();
?>