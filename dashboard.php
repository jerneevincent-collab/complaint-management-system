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

$total = $conn->query("SELECT COUNT(*) AS c FROM complaints")->fetch_assoc()['c'];
$pending = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status IN ('Submitted','For Assignment','Assigned')")->fetch_assoc()['c'];
$underInvestigation = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status = 'Under Investigation'")->fetch_assoc()['c'];
$forAction = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status = 'Action Required'")->fetch_assoc()['c'];
$resolved = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status = 'Resolved'")->fetch_assoc()['c'];
$closed = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status = 'Closed'")->fetch_assoc()['c'];
$overdue = $conn->query("SELECT COUNT(*) AS c FROM assignments WHERE due_date < CURDATE() AND status = 'active'")->fetch_assoc()['c'];

$categoryData = $conn->query(
    "SELECT cc.category_name, COUNT(*) AS total FROM complaints c
     JOIN complaint_categories cc ON c.category_id = cc.category_id
     GROUP BY cc.category_name"
);
$catLabels = [];
$catValues = [];
while ($row = $categoryData->fetch_assoc()) {
    $catLabels[] = $row['category_name'];
    $catValues[] = $row['total'];
}

$statusData = $conn->query("SELECT status, COUNT(*) AS total FROM complaints GROUP BY status");
$statusLabels = [];
$statusValues = [];
while ($row = $statusData->fetch_assoc()) {
    $statusLabels[] = $row['status'];
    $statusValues[] = $row['total'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <style>
        .kpi-container { display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 30px; }
        .kpi-card { border: 1px solid #ccc; padding: 15px; min-width: 140px; text-align: center; }
        .kpi-card h3 { margin: 0; font-size: 28px; }
        .kpi-card p { margin: 5px 0 0; }
        .chart-container { width: 400px; display: inline-block; margin-right: 30px; }
    </style>
</head>
<body>
    <h2>Complaint Management Dashboard</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <div class="kpi-container">
        <div class="kpi-card"><h3><?php echo $total; ?></h3><p>Total Complaints</p></div>
        <div class="kpi-card"><h3><?php echo $pending; ?></h3><p>Pending</p></div>
        <div class="kpi-card"><h3><?php echo $underInvestigation; ?></h3><p>Under Investigation</p></div>
        <div class="kpi-card"><h3><?php echo $forAction; ?></h3><p>For Action</p></div>
        <div class="kpi-card"><h3><?php echo $resolved; ?></h3><p>Resolved</p></div>
        <div class="kpi-card"><h3><?php echo $closed; ?></h3><p>Closed</p></div>
        <div class="kpi-card"><h3><?php echo $overdue; ?></h3><p>Overdue</p></div>
    </div>

    <div class="chart-container">
        <canvas id="categoryChart"></canvas>
    </div>
    <div class="chart-container">
        <canvas id="statusChart"></canvas>
    </div>

    <script>
        new Chart(document.getElementById('categoryChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($catLabels); ?>,
                datasets: [{ data: <?php echo json_encode($catValues); ?> }]
            },
            options: { plugins: { title: { display: true, text: 'Complaints by Category' } } }
        });

        new Chart(document.getElementById('statusChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($statusLabels); ?>,
                datasets: [{ label: 'Complaints', data: <?php echo json_encode($statusValues); ?> }]
            },
            options: { plugins: { title: { display: true, text: 'Resolution Status' } } }
        });
    </script>
</body>
</html>