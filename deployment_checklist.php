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
    <title>Deployment Checklist</title>
</head>
<body>
    <h2>Deployment Checklist</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <h3>Lab 57: Prepare the System for Deployment</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Task</th><th>Status</th></tr>
        <tr><td>Clean source code, remove test/debug scripts</td><td>Done (removed set_password.php and debug print_r after use)</td></tr>
        <tr><td>Configure production database and application settings</td><td>Done (complaint_management_db created and populated)</td></tr>
        <tr><td>Create administrator account</td><td>Done (personnel with Administrator role can be created via Personnel Management)</td></tr>
        <tr><td>Prepare database backup</td><td>Done (see Lab 54 backup file)</td></tr>
        <tr><td>Check all modules</td><td>Done (all 63 labs tested and working)</td></tr>
    </table>

    <h3>Lab 58: Deploy the Complaint Management System</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Component</th><th>Configuration</th></tr>
        <tr><td>Deployment Target</td><td>Local server (XAMPP) — Apache + MySQL</td></tr>
        <tr><td>Web Server</td><td>Apache 2.4.58, running on localhost, Port 80</td></tr>
        <tr><td>Database Server</td><td>MySQL (via XAMPP), database name: complaint_management_db</td></tr>
        <tr><td>Domain/IP</td><td>http://localhost/complaint_management_system/</td></tr>
        <tr><td>Application Settings</td><td>PHP 8.2.12, session-based authentication enabled</td></tr>
        <tr><td>Security Settings</td><td>Role-based access control, hashed passwords, prepared statements</td></tr>
    </table>

    <h3>Lab 59: Post-Deployment Testing</h3>
    <p>See <a href="uat_checklist.php">UAT Checklist</a> and <a href="bug_tracking.php">Bug Tracking Sheet</a> for verification results.</p>
</body>
</html>