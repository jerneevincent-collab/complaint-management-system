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
    <title>Bug Tracking Sheet</title>
</head>
<body>
    <h2>Bug Tracking Sheet</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Bug ID</th>
            <th>Module</th>
            <th>Problem</th>
            <th>Severity</th>
            <th>Action Taken</th>
            <th>Status</th>
        </tr>
        <tr>
            <td>B001</td>
            <td>Complaint Registration</td>
            <td>File upload validation was not triggering because the form was missing the enctype="multipart/form-data" attribute, so $_FILES was always empty.</td>
            <td>High</td>
            <td>Added enctype="multipart/form-data" to the complaint registration form.</td>
            <td>Closed</td>
        </tr>
        <tr>
            <td>B002</td>
            <td>Project Foundation</td>
            <td>date_filed value was saved with a typo (0026 instead of 2026) due to manual test data entry.</td>
            <td>Low</td>
            <td>Corrected the value directly in phpMyAdmin.</td>
            <td>Closed</td>
        </tr>
        <tr>
            <td>B003</td>
            <td>Complaint View</td>
            <td>A duplicate/stray character appeared on the page after editing the status display logic.</td>
            <td>Low</td>
            <td>Replaced the full file content to remove the stray character.</td>
            <td>Closed</td>
        </tr>
    </table>
</body>
</html>