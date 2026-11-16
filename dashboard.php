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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/enhance.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <style>
        :root { --navy:#0a2540; --accent:#2f6fed; --bg:#f4f7fb; }
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { color:#1b2733; }

        .topbar {
            background: var(--navy);
            color:#fff;
            position:relative;
            display:flex;
            align-items:center;
            padding:14px 30px;
        }
        .topbar h2 {
            position:absolute;
            left:50%;
            transform:translateX(-50%);
            margin:0;
            color:#fff;
            font-size:18px;
            font-weight:600;
            white-space:nowrap;
        }
        .topbar .back-link { color:#fff; text-decoration:none; font-size:14px; z-index:1; }
        .topbar .back-link:hover { color:#fff; text-decoration:none; }

        body .page-wrap {
            width:100% !important;
            max-width:1100px !important;
            margin:0 auto !important;
            padding:30px 20px 60px !important;
        }

        .kpi-container {
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(150px, 1fr));
            gap:16px;
            margin-bottom:24px;
        }
        .kpi-card {
            background:#fff;
            border-radius:16px;
            padding:20px 18px;
            text-align:center;
            box-shadow:0 8px 24px rgba(10,37,64,0.07);
            border-top:4px solid var(--accent);
            opacity:0;
            animation:fadeUp 0.5s ease forwards;
        }
        .kpi-card h3 { margin:0; font-size:30px; color:var(--navy); font-weight:700; }
        .kpi-card p { margin:6px 0 0; font-size:13px; color:#6b7787; }
        .kpi-card.warn { border-top-color:#e0a82e; }
        .kpi-card.danger { border-top-color:#d64545; }
        .kpi-card.good { border-top-color:#0f766e; }

        .chart-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));
            gap:24px;
        }
        .chart-card {
            background:#fff;
            border-radius:16px;
            padding:24px 26px;
            box-shadow:0 8px 24px rgba(10,37,64,0.07);
            opacity:0;
            animation:fadeUp 0.5s ease forwards;
        }
        .chart-card h3 { margin:0 0 16px 0; color:var(--navy); font-size:16px; }
        .chart-box { position:relative; height:300px; }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(12px); }
            to { opacity:1; transform:translateY(0); }
        }
        @media (max-width:640px) { .topbar h2 { font-size:15px; } }
    </style>
</head>
<body>
    <div class="topbar">
        <a class="back-link" href="index.php">&larr; Back to Home</a>
        <h2>Complaint Management Dashboard</h2>
    </div>

    <div class="page-wrap">

        <div class="kpi-container">
            <div class="kpi-card"><h3><?php echo $total; ?></h3><p>Total Complaints</p></div>
            <div class="kpi-card warn"><h3><?php echo $pending; ?></h3><p>Pending</p></div>
            <div class="kpi-card"><h3><?php echo $underInvestigation; ?></h3><p>Under Investigation</p></div>
            <div class="kpi-card warn"><h3><?php echo $forAction; ?></h3><p>For Action</p></div>
            <div class="kpi-card good"><h3><?php echo $resolved; ?></h3><p>Resolved</p></div>
            <div class="kpi-card good"><h3><?php echo $closed; ?></h3><p>Closed</p></div>
            <div class="kpi-card danger"><h3><?php echo $overdue; ?></h3><p>Overdue</p></div>
        </div>

        <div class="chart-grid">
            <div class="chart-card">
                <h3>Complaints by Category</h3>
                <div class="chart-box"><canvas id="categoryChart"></canvas></div>
            </div>
            <div class="chart-card">
                <h3>Resolution Status</h3>
                <div class="chart-box"><canvas id="statusChart"></canvas></div>
            </div>
        </div>

    </div>

    <script>
        Chart.defaults.font.family = "'Poppins', sans-serif";

        const palette = ['#0a2540', '#2f6fed', '#0f766e', '#e0a82e', '#d64545', '#7c5cd6', '#6b7787', '#3aa6c9'];

        new Chart(document.getElementById('categoryChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($catLabels); ?>,
                datasets: [{ data: <?php echo json_encode($catValues); ?>, backgroundColor: palette, borderWidth: 2, borderColor: '#ffffff' }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        new Chart(document.getElementById('statusChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($statusLabels); ?>,
                datasets: [{ label: 'Complaints', data: <?php echo json_encode($statusValues); ?>, backgroundColor: '#2f6fed', borderRadius: 6 }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    </script>
    <script src="assets/enhance.js"></script>
</body>
</html>