<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM complainants WHERE complainant_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: complainant_list.php");
exit();
?>