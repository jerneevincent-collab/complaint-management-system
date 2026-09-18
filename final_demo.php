<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Final Demonstration Script</title>
</head>
<body>
    <h2>Final System Demonstration Script</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <p>Scenario: Juan Dela Cruz files a complaint about delayed processing of a requested document (Service Complaint, High priority).</p>

    <ol>
        <li><a href="complainant_register.php">Register the complainant</a> (Juan Dela Cruz)</li>
        <li><a href="complaint_register.php">File the complaint</a> (Category: Service Complaint, Priority: High)</li>
        <li><a href="assignment_form.php">Assign the complaint</a> to a Complaint Officer</li>
        <li><a href="investigation_form.php">Create an investigation</a>, then <a href="investigation_list.php">record findings</a></li>
        <li>Mark the investigation as <a href="investigation_list.php">Completed</a></li>
        <li><a href="action_form.php">Create a corrective action</a></li>
        <li>Mark the action as <a href="action_list.php">Completed</a></li>
        <li><a href="resolution_form.php">Record the resolution</a></li>
        <li><a href="complaint_close.php">Close the complaint</a> (Administrator/Supervisor only)</li>
        <li><a href="dashboard.php">Generate a report</a> showing the completed transaction</li>
    </ol>

    <p>This demonstrates the complete traceable complaint record, from Complainant Registration through Reports/Dashboard, as required by the final project scenario.</p>
</body>
</html>