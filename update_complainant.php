<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['complainant_id'];
$full_name = trim($_POST['full_name']);
$contact_number = trim($_POST['contact_number']);
$email = trim($_POST['email']);
$address = trim($_POST['address']);
$organization_department = trim($_POST['organization_department']);
$status = $_POST['status'];

$stmt = $conn->prepare(
    "UPDATE complainants SET full_name=?, contact_number=?, email=?, address=?, organization_department=?, status=? WHERE complainant_id=?"
);
$stmt->bind_param("ssssssi", $full_name, $contact_number, $email, $address, $organization_department, $status, $id);
$stmt->execute();

header("Location: complainant_list.php");
exit();
?>