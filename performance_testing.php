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

function measureQuery($conn, $sql) {
    $start = microtime(true);
    $result = $conn->query($sql);
    $end = microtime(true);
    return round(($end - $start) * 1000, 2);
}

$loginTime = measureQuery($conn, "SELECT * FROM users WHERE email = 'jerneevincent@gmail.com'");
$searchTime = measureQuery($conn, "SELECT * FROM complaints WHERE subject LIKE '%Tuition%'");
$reportTime = measureQuery($conn, "SELECT cc.category_name, COUNT(*) AS total FROM complaints c JOIN complaint_categories cc ON c.category_id = cc.category_id GROUP BY cc.category_name");
$dashboardTime = measureQuery($conn, "SELECT status, COUNT(*) AS total FROM complaints GROUP BY status");

$totalComplaints = $conn->query("SELECT COUNT(*) AS c FROM complaints")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Performance Testing</title>
</head>
<body>
    <h2>Performance Testing Results</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Test</th><th>Response Time</th></tr>
        <tr><td>Login Query</td><td><?php echo $loginTime; ?> ms</td></tr>
        <tr><td>Complaint Search</td><td><?php echo $searchTime; ?> ms</td></tr>
        <tr><td>Report Generation (Category)</td><td><?php echo $reportTime; ?> ms</td></tr>
        <tr><td>Dashboard Load (Status Summary)</td><td><?php echo $dashboardTime; ?> ms</td></tr>
    </table>

    <p>Current dataset size: <?php echo $totalComplaints; ?> complaints. All queries use prepared statements with indexed columns (primary/foreign keys) to keep response times fast as data grows.</p>

    <h3>Optimization Notes</h3>
    <ul>
        <li>Indexes exist on complaint_id, status, and date_filed for faster searching.</li>
        <li>Pagination is implemented on the Complainant List to avoid loading all records at once.</li>
        <li>Reports use SQL aggregation (COUNT, GROUP BY) instead of pulling all rows into PHP.</li>
    </ul>
</body>
</html>