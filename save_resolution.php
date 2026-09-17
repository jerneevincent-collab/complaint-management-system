<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complaint_id = $_POST['complaint_id'];
$resolution_type = trim($_POST['resolution_type']);
$resolution_description = trim($_POST['resolution_description']);
$resolution_date = $_POST['resolution_date'];
$resolved_by = $_POST['resolved_by'];
$remarks = trim($_POST['remarks']);

if (empty($complaint_id) || empty($resolution_description) || empty($resolution_date) || empty($resolved_by)) {
    header("Location: resolution_form.php?status=error&msg=" . urlencode("All required fields must be filled out."));
    exit();
}

// Rule: complaint must have an investigation on record
$checkInvestigation = $conn->prepare("SELECT investigation_id FROM investigations WHERE complaint_id = ?");
$checkInvestigation->bind_param("i", $complaint_id);
$checkInvestigation->execute();
$hasInvestigation = $checkInvestigation->get_result()->num_rows > 0;

if (!$hasInvestigation) {
    header("Location: resolution_form.php?status=error&msg=" . urlencode("This complaint has not been investigated yet."));
    exit();
}

// Rule: required action must be completed before resolution
$checkAction = $conn->prepare("SELECT COUNT(*) as pending FROM actions WHERE complaint_id = ? AND status NOT IN ('Completed', 'Verified')");
$checkAction->bind_param("i", $complaint_id);
$checkAction->execute();
$pendingActions = $checkAction->get_result()->fetch_assoc()['pending'];

if ($pendingActions > 0) {
    header("Location: resolution_form.php?status=error&msg=" . urlencode("Required action has not been completed yet."));
    exit();
}

// Insert resolution
$stmt = $conn->prepare(
    "INSERT INTO resolutions (complaint_id, resolution_type, resolution_description, resolution_date, resolved_by, remarks)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("isssis", $complaint_id, $resolution_type, $resolution_description, $resolution_date, $resolved_by, $remarks);
$stmt->execute();

// Update complaint status
$update = $conn->prepare("UPDATE complaints SET status = 'Resolved' WHERE complaint_id = ?");
$update->bind_param("i", $complaint_id);
$update->execute();

header("Location: resolution_form.php?status=success");
exit();
?>