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
    <title>User Manual</title>
</head>
<body>
    <h2>User Manual - Complaint Management System</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <h3>1. Login</h3>
    <p>Go to the login page. Enter your registered email and password, then click Login. If your credentials are incorrect, an error message will appear.</p>

    <h3>2. Registering a Complainant</h3>
    <p>From the Home page, click "Register Complainant." Fill in the full name, contact number, email, address, and organization/department, then click Register.</p>

    <h3>3. Filing a Complaint</h3>
    <p>Click "File a Complaint." Select the complainant and category, enter the subject and description, choose the date filed and priority, optionally attach a supporting document (JPG, PNG, or PDF only), then click Submit Complaint.</p>

    <h3>4. Assigning a Complaint</h3>
    <p>Click "Assign Complaint." Choose the complaint, select the personnel to assign it to, choose the department, set the assignment and due dates, then click Assign Complaint.</p>

    <h3>5. Conducting an Investigation</h3>
    <p>Click "Create Investigation." Select an assigned complaint and an investigator, then set the start date. To record findings, go to the Investigation List and click "Add Findings."</p>

    <h3>6. Recording an Action</h3>
    <p>Click "Create Action." Select a completed investigation, choose the action type, enter a description, select the responsible person, and set a target date.</p>

    <h3>7. Resolving a Complaint</h3>
    <p>Click "Record Resolution." Select the complaint (the required action must already be completed), enter the resolution description and date, then select who resolved it.</p>

    <h3>8. Closing a Complaint</h3>
    <p>Only Administrators and Supervisors can close complaints. Go to "Complaint Closure," select who is closing it, and click Confirm Closure.</p>

    <h3>9. Generating Reports</h3>
    <p>From the Home page, access the various reports: Complaint Summary, Category Report, Resolution Report, Investigator Performance, Action Monitoring, and Trend Analysis.</p>

    <h3>10. Using the Dashboard</h3>
    <p>Click "Dashboard" to view KPI cards (total, pending, resolved, overdue) and charts showing complaints by category and by status.</p>
</body>
</html>