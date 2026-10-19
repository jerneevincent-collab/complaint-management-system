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

$monthly = $conn->query(
    "SELECT DATE_FORMAT(date_filed, '%Y-%m') AS month, COUNT(*) AS total
     FROM complaints GROUP BY month ORDER BY month DESC"
);

$byCategory = $conn->query(
    "SELECT cc.category_name, COUNT(*) AS total
     FROM complaints c
     JOIN complaint_categories cc ON c.category_id = cc.category_id
     GROUP BY cc.category_name ORDER BY total DESC"
);

$totalAll = $conn->query("SELECT COUNT(*) AS c FROM complaints")->fetch_assoc()['c'];
$totalResolved = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status IN ('Resolved','Closed')")->fetch_assoc()['c'];
$resolutionRate = $totalAll > 0 ? round(($totalResolved / $totalAll) * 100, 1) : 0;

$recurring = $conn->query(
    "SELECT complainant_id, COUNT(*) AS total FROM complaints GROUP BY complainant_id HAVING total > 1"
);

$byDepartment = $conn->query(
    "SELECT d.department_name, COUNT(a.assignment_id) AS total
     FROM departments d
     LEFT JOIN assignments a ON d.department_id = a.department_id
     GROUP BY d.department_id, d.department_name"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Trend Analysis</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
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
            max-width:860px !important;
            margin:0 auto !important;
            padding:30px 20px 60px !important;
        }

        .table-card {
            background:#fff;
            border-radius:16px;
            padding:28px 30px;
            margin-bottom:24px;
            box-shadow:0 8px 24px rgba(10,37,64,0.07);
            opacity:0;
            animation:fadeUp 0.5s ease forwards;
        }
        .table-card h3 { margin:0 0 16px 0; color:var(--navy); font-size:16px; }
        .table-scroll { overflow-x:auto; }
        .table-card table { width:100%; margin:0; box-shadow:none; border-radius:0; border-collapse:collapse; font-size:13px; }
        .table-card th {
            background:var(--navy);
            color:#fff;
            text-align:left;
            padding:10px 12px;
            text-transform:none;
            font-size:13px;
            letter-spacing:0;
        }
        .table-card td { padding:10px 12px; border-bottom:1px solid #eef1f5; font-size:13px; }
        .table-card tr:last-child td { border-bottom:none; }
        .table-card th:last-child, .table-card td:last-child { text-align:center; width:160px; }

        .rate-box { display:flex; align-items:center; gap:22px; flex-wrap:wrap; }
        .rate-number { font-size:38px; font-weight:700; color:var(--navy); line-height:1; }
        .rate-info { flex:1; min-width:200px; }
        .rate-bar { background:#eef1f5; border-radius:999px; height:12px; overflow:hidden; }
        .rate-fill { background:#0f766e; height:100%; border-radius:999px; }
        .rate-text { margin:8px 0 0; font-size:13px; color:#6b7787; }

        .empty-note { color:#6b7787; font-size:13px; margin:0; }

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
        <h2>Complaint Trend Analysis</h2>
    </div>

    <div class="page-wrap">

        <div class="table-card">
            <h3>Resolution Rate</h3>
            <div class="rate-box">
                <div class="rate-number"><?php echo $resolutionRate; ?>%</div>
                <div class="rate-info">
                    <div class="rate-bar"><div class="rate-fill" style="width:<?php echo min(100, $resolutionRate); ?>%;"></div></div>
                    <p class="rate-text"><?php echo $totalResolved; ?> out of <?php echo $totalAll; ?> resolved or closed</p>
                </div>
            </div>
        </div>

        <div class="table-card">
            <h3>Monthly Trend</h3>
            <?php if ($monthly->num_rows > 0): ?>
            <div class="table-scroll">
                <table>
                    <tr><th>Month</th><th>Total Complaints</th></tr>
                    <?php while ($row = $monthly->fetch_assoc()): ?>
                    <tr><td><?php echo $row['month']; ?></td><td><?php echo $row['total']; ?></td></tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <?php else: ?><p class="empty-note">No data yet.</p><?php endif; ?>
        </div>

        <div class="table-card">
            <h3>By Category</h3>
            <?php if ($byCategory->num_rows > 0): ?>
            <div class="table-scroll">
                <table>
                    <tr><th>Category</th><th>Total</th></tr>
                    <?php while ($row = $byCategory->fetch_assoc()): ?>
                    <tr><td><?php echo htmlspecialchars($row['category_name']); ?></td><td><?php echo $row['total']; ?></td></tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <?php else: ?><p class="empty-note">No data yet.</p><?php endif; ?>
        </div>

        <div class="table-card">
            <h3>Recurring Complainants (more than 1 complaint)</h3>
            <?php if ($recurring->num_rows > 0): ?>
            <div class="table-scroll">
                <table>
                    <tr><th>Complainant ID</th><th>Total Complaints</th></tr>
                    <?php while ($row = $recurring->fetch_assoc()): ?>
                    <tr><td><?php echo $row['complainant_id']; ?></td><td><?php echo $row['total']; ?></td></tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <?php else: ?><p class="empty-note">No recurring complainants found.</p><?php endif; ?>
        </div>

        <div class="table-card">
            <h3>By Department</h3>
            <?php if ($byDepartment->num_rows > 0): ?>
            <div class="table-scroll">
                <table>
                    <tr><th>Department</th><th>Total Assignments</th></tr>
                    <?php while ($row = $byDepartment->fetch_assoc()): ?>
                    <tr><td><?php echo htmlspecialchars($row['department_name']); ?></td><td><?php echo $row['total']; ?></td></tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <?php else: ?><p class="empty-note">No data yet.</p><?php endif; ?>
        </div>

    </div>
</body>
</html>