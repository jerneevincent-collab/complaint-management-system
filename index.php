<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Management System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <h1>Complaint Management System</h1>
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! | <a href="logout.php">Logout</a></p>

    <h2>Quick Summary</h2>
    <?php
        $total = $conn->query("SELECT COUNT(*) AS c FROM complaints")->fetch_assoc()['c'];
        $open = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status NOT IN ('Resolved','Closed')")->fetch_assoc()['c'];
        $closed = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status = 'Closed'")->fetch_assoc()['c'];
    ?>
    <p>Total Complaints: <?php echo $total; ?> | Open: <?php echo $open; ?> | Closed: <?php echo $closed; ?></p>

    <h2>Complainant Management</h2>
    <ul>
        <li><a href="complainant_register.php">Register Complainant</a></li>
        <li><a href="complainant_list.php">Complainant List / Search</a></li>
    </ul>

    <h2>Complaint Management</h2>
    <ul>
        <li><a href="category_manage.php">Manage Complaint Categories</a></li>
        <li><a href="complaint_register.php">File a Complaint</a></li>
        <li><a href="complaint_list.php">Complaint List / Search</a></li>
        <li><a href="complaint_close.php">Complaint Closure</a></li>
    </ul>

    <h2>Assignment</h2>
    <ul>
        <li><a href="personnel_manage.php">Personnel Management</a></li>
        <li><a href="assignment_form.php">Assign Complaint</a></li>
        <li><a href="assignment_list.php">Assignment List</a></li>
    </ul>

    <h2>Investigation</h2>
    <ul>
        <li><a href="investigation_form.php">Create Investigation</a></li>
        <li><a href="investigation_list.php">Investigation List</a></li>
    </ul>

    <h2>Action</h2>
    <ul>
        <li><a href="action_form.php">Create Action</a></li>
        <li><a href="action_list.php">Action List / Monitoring</a></li>
    </ul>

    <h2>Resolution</h2>
    <ul>
        <li><a href="resolution_form.php">Record Resolution</a></li>
    </ul>

    <h2>Reports and Dashboard</h2>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="report_summary.php">Complaint Summary Report</a></li>
        <li><a href="report_category.php">Complaint Category Report</a></li>
        <li><a href="report_resolution.php">Complaint Resolution Report</a></li>
        <li><a href="report_investigator.php">Investigator Performance Report</a></li>
        <li><a href="report_action.php">Action Monitoring Report</a></li>
        <li><a href="report_trend.php">Complaint Trend Analysis</a></li>
    </ul>

    <h2>Documentation</h2>
    <ul>
        <li><a href="business_rules.php">Business Rules</a></li>
        <li><a href="bug_tracking.php">Bug Tracking Sheet</a></li>
        <li><a href="uat_checklist.php">UAT Checklist</a></li>
        <li><a href="security_testing.php">Security Testing</a></li>
        <li><a href="performance_testing.php">Performance Testing</a></li>
        <li><a href="deployment_checklist.php">Deployment Checklist</a></li>
        <li><a href="user_manual.php">User Manual</a></li>
        <li><a href="technical_documentation.php">Technical Documentation</a></li>
        <li><a href="final_demo.php">Final Demo Script</a></li>
    </ul>
</body>
</html>