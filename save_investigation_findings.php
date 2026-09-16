<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$investigation_id = $_POST['investigation_id'];
$date_investigated = $_POST['date_investigated'];
$evidence_collected = trim($_POST['evidence_collected']);
$findings = trim($_POST['findings']);
$witnesses = trim($_POST['witnesses']);
$recommendation = trim($_POST['recommendation']);
$classification = $_POST['classification'];

$stmt = $conn->prepare(
    "INSERT INTO investigation_findings (investigation_id, date_investigated, evidence_collected, findings, witnesses, recommendation, classification)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("issssss", $investigation_id, $date_investigated, $evidence_collected, $findings, $witnesses, $recommendation, $classification);
$stmt->execute();

header("Location: investigation_list.php");
exit();
?>