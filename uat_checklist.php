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
    <title>UAT Checklist</title>
</head>
<body>
    <h2>User Acceptance Testing (UAT) Checklist</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Scenario</th><th>Functionality</th><th>Usability</th><th>Accuracy</th><th>Reliability</th><th>Security</th><th>Result</th></tr>
        <tr><td>Complaint Submission</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Accepted</td></tr>
        <tr><td>Assignment</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Accepted</td></tr>
        <tr><td>Investigation</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Accepted</td></tr>
        <tr><td>Action</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Accepted</td></tr>
        <tr><td>Resolution</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Accepted</td></tr>
        <tr><td>Reports</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Accepted</td></tr>
        <tr><td>User Management</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Pass</td><td>Accepted</td></tr>
    </table>
</body>
</html>