<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: splash.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];

$check = $conn->prepare("SELECT COUNT(*) as total FROM complaints WHERE complainant_id = ?");
$check->bind_param("i", $id);
$check->execute();
$hasComplaints = $check->get_result()->fetch_assoc()['total'] > 0;

if ($hasComplaints) {
    $stmt = $conn->prepare("UPDATE complainants SET status = 'inactive' WHERE complainant_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: complainant_list.php?msg=" . urlencode("This complainant has existing complaints, so it was deactivated instead of deleted."));
    exit();
} else {
    $stmt = $conn->prepare("DELETE FROM complainants WHERE complainant_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: complainant_list.php?msg=" . urlencode("Complainant deleted successfully."));
    exit();
}
?>