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
    <title>Security Testing</title>
</head>
<body>
    <h2>Security Testing Results</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Test</th><th>Method</th><th>Result</th></tr>
        <tr>
            <td>Authentication</td>
            <td>Attempted login with wrong password/email</td>
            <td>Pass — "Invalid email or password" shown, no access granted</td>
        </tr>
        <tr>
            <td>Authorization</td>
            <td>Logged in as Action Officer, attempted to access Personnel Management and Complaint Closure</td>
            <td>Pass — "Access denied" shown for both restricted pages</td>
        </tr>
        <tr>
            <td>SQL Injection Protection</td>
            <td>All database queries use prepared statements (mysqli bind_param) instead of raw string concatenation</td>
            <td>Pass — no raw user input is directly inserted into SQL queries</td>
        </tr>
        <tr>
            <td>Input Validation</td>
            <td>Submitted forms with empty required fields and invalid dates</td>
            <td>Pass — client-side and server-side validation both blocked invalid submissions</td>
        </tr>
        <tr>
            <td>File Upload Security</td>
            <td>Attempted to upload a .txt file as supporting document</td>
            <td>Pass — rejected with "Only JPG, PNG, or PDF files are allowed"</td>
        </tr>
        <tr>
            <td>Session Security</td>
            <td>Attempted to access index.php and personnel_manage.php without logging in</td>
            <td>Pass — redirected to login.php</td>
        </tr>
        <tr>
            <td>Password Security</td>
            <td>Checked stored passwords in the users table</td>
            <td>Pass — passwords are hashed using PHP's password_hash(), not stored as plain text</td>
        </tr>
    </table>
</body>
</html>