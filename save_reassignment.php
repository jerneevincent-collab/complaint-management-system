<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_id = $_POST['assignment_id'];
$complaint_id = $_POST['complaint_id'];
$previous_assignee = $_POST['previous_assignee'];
$new_assignee = $_POST['new_assignee'];
$reason = trim($_POST['reason']);

// Record in assignment_history
$stmt = $conn->prepare(
    "INSERT INTO assignment_history (complaint_id, previous_assignee, new_assignee, reason_for_reassignment)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("iiis", $complaint_id, $previous_assignee, $new_assignee, $reason);
$stmt->execute();

// Update the assignment itself
$update = $conn->prepare("UPDATE assignments SET assigned_to = ?, status = 'reassigned' WHERE assignment_id = ?");
$update->bind_param("ii", $new_assignee, $assignment_id);
$update->execute();

header("Location: assignment_list.php");
exit();
?>
