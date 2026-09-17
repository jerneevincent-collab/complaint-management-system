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
    <title>Business Rules</title>
</head>
<body>
    <h2>Implemented Business Rules</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>#</th><th>Rule</th><th>Where Implemented</th></tr>
        <tr><td>1</td><td>A complaint must have a valid complainant.</td><td>Complaint Registration form (required dropdown)</td></tr>
        <tr><td>2</td><td>A complaint must have a category.</td><td>Complaint Registration form (required dropdown)</td></tr>
        <tr><td>3</td><td>Only submitted complaints can be assigned.</td><td>Assignment form (validated in save_assignment.php)</td></tr>
        <tr><td>4</td><td>Only assigned complaints can proceed to investigation.</td><td>Investigation form (only shows "Assigned" complaints)</td></tr>
        <tr><td>5</td><td>Investigation findings must be recorded before resolution.</td><td>Resolution form (checked in save_resolution.php)</td></tr>
        <tr><td>6</td><td>Required actions must be completed before closure.</td><td>Resolution form (checked in save_resolution.php)</td></tr>
        <tr><td>7</td><td>Closed complaints cannot be modified without authorization.</td><td>Complaint View (status check) + Closure page (Admin/Supervisor only)</td></tr>
        <tr><td>8</td><td>Only authorized users can assign, resolve, or close complaints.</td><td>Login required on all pages; role-based access control on sensitive pages</td></tr>
    </table>
</body>
</html>