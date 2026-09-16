<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complaint_id = $_POST['complaint_id'];
$assigned_to = $_POST['assigned_to'];
$department_id = $_POST['department_id'];
$assignment_date = $_POST['assignment_date'];
$due_date = $_POST['due_date'];
$instructions = trim($_POST['instructions']);

// Validate required fields
if (empty($complaint_id) || empty($assigned_to) || empty($assignment_date) || empty($due_date)) {
    header("Location: assignment_form.php?status=error&msg=" . urlencode("All required fields must be filled out."));
    exit();
}

// Rule: due date cannot be earlier than assignment date
if ($due_date < $assignment_date) {
    header("Location: assignment_form.php?status=error&msg=" . urlencode("Due date cannot be earlier than assignment date."));
    exit();
}

// Rule: closed complaints cannot be assigned
$check = $conn->prepare("SELECT status FROM complaints WHERE complaint_id = ?");
$check->bind_param("i", $complaint_id);
$check->execute();
$complaint = $check->get_result()->fetch_assoc();

if ($complaint['status'] == 'Closed') {
    header("Location: assignment_form.php?status=error&msg=" . urlencode("Closed complaints cannot be assigned."));
    exit();
}

// Rule: inactive personnel cannot receive assignment
$checkUser = $conn->prepare("SELECT status FROM users WHERE user_id = ?");
$checkUser->bind_param("i", $assigned_to);
$checkUser->execute();
$user = $checkUser->get_result()->fetch_assoc();

if ($user['status'] == 'inactive') {
    header("Location: assignment_form.php?status=error&msg=" . urlencode("Inactive personnel cannot receive an assignment."));
    exit();
}

// Insert assignment
$stmt = $conn->prepare(
    "INSERT INTO assignments (complaint_id, assigned_to, department_id, assignment_date, due_date, instructions, status)
     VALUES (?, ?, ?, ?, ?, ?, 'active')"
);
$stmt->bind_param("iiisss", $complaint_id, $assigned_to, $department_id, $assignment_date, $due_date, $instructions);
$stmt->execute();

// Update complaint status
$updateStatus = $conn->prepare("UPDATE complaints SET status = 'Assigned' WHERE complaint_id = ?");
$updateStatus->bind_param("i", $complaint_id);
$updateStatus->execute();

header("Location: assignment_form.php?status=success");
exit();
?>