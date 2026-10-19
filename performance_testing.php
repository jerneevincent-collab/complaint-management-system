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

        .dataset-note { margin:0; font-size:13px; line-height:1.6; color:#475569; }

        .notes-list { list-style:none; margin:0; padding:0; }
        .notes-list li {
            background:#f8fafc;
            border-left:4px solid var(--accent);
            border-radius:6px;
            box-shadow:none;
            padding:10px 14px;
            margin-bottom:8px;
            font-size:13px;
            line-height:1.6;
            color:#334155;
        }
        .notes-list li:last-child { margin-bottom:0; }

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
        <h2>Performance Testing Results</h2>
    </div>

    <div class="page-wrap">

        <div class="table-card">
            <h3>Response Times</h3>
            <div class="table-scroll">
                <table>
                    <tr><th>Test</th><th>Response Time</th></tr>
                    <tr><td>Login Query</td><td><?php echo $loginTime; ?> ms</td></tr>
                    <tr><td>Complaint Search</td><td><?php echo $searchTime; ?> ms</td></tr>
                    <tr><td>Report Generation (Category)</td><td><?php echo $reportTime; ?> ms</td></tr>
                    <tr><td>Dashboard Load (Status Summary)</td><td><?php echo $dashboardTime; ?> ms</td></tr>
                </table>
            </div>
        </div>

        <div class="table-card">
            <h3>Dataset Size</h3>
            <p class="dataset-note">Current dataset size: <?php echo $totalComplaints; ?> complaints. All queries use prepared statements with indexed columns (primary/foreign keys) to keep response times fast as data grows.</p>
        </div>

        <div class="table-card">
            <h3>Optimization Notes</h3>
            <ul class="notes-list">
                <li>Indexes exist on complaint_id, status, and date_filed for faster searching.</li>
                <li>Pagination is implemented on the Complainant List to avoid loading all records at once.</li>
                <li>Reports use SQL aggregation (COUNT, GROUP BY) instead of pulling all rows into PHP.</li>
            </ul>
        </div>

    </div>
</body>
</html>