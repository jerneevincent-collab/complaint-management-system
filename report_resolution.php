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

$totalResolved = $conn->query(
    "SELECT COUNT(*) AS c FROM complaints WHERE status IN ('Resolved', 'Closed')"
)->fetch_assoc()['c'];

$avgResolution = $conn->query(
    "SELECT AVG(DATEDIFF(r.resolution_date, c.date_filed)) AS avg_days
     FROM resolutions r
     JOIN complaints c ON r.complaint_id = c.complaint_id"
)->fetch_assoc()['avg_days'];

$resolvedWithinDeadline = $conn->query(
    "SELECT COUNT(*) AS c
     FROM resolutions r
     JOIN complaints c ON r.complaint_id = c.complaint_id
     WHERE DATEDIFF(r.resolution_date, c.date_filed) <= 7"
)->fetch_assoc()['c'];

$overdueComplaints = $conn->query(
    "SELECT COUNT(*) AS c FROM assignments WHERE due_date < CURDATE() AND status = 'active'"
)->fetch_assoc()['c'];

$cards = [
    ['Total Complaints Resolved', $totalResolved, 'c-green'],
    ['Average Resolution Time (days)', $avgResolution !== null ? round($avgResolution, 1) : 'N/A', 'c-blue'],
    ['Resolved Within 7-Day Deadline', $resolvedWithinDeadline, 'c-navy'],
    ['Overdue Complaints (Assignment)', $overdueComplaints, 'c-orange'],
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Resolution Report</title>
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

        .card-grid {
            display:grid;
            grid-template-columns:repeat(2, 1fr);
            gap:18px;
        }
        .stat-card {
            background:#fff;
            border-radius:16px;
            padding:24px 20px;
            text-align:center;
            box-shadow:0 8px 24px rgba(10,37,64,0.07);
            border-top:5px solid var(--accent);
            opacity:0;
            animation:fadeUp 0.5s ease forwards;
        }
        .stat-card .num { font-size:36px; font-weight:700; color:var(--navy); line-height:1.1; }
        .stat-card .label { margin-top:8px; font-size:13px; font-weight:500; color:#6b7787; }

        .c-blue { border-top-color:#2f6fed; }
        .c-navy { border-top-color:#0a2540; }
        .c-orange { border-top-color:#f08a24; }
        .c-yellow { border-top-color:#e6b800; }
        .c-green { border-top-color:#1c9c5a; }
        .c-gray { border-top-color:#8793a3; }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(12px); }
            to { opacity:1; transform:translateY(0); }
        }
        @media (max-width:640px) {
            .card-grid { grid-template-columns:repeat(2, 1fr); }
            .topbar h2 { font-size:15px; }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <a class="back-link" href="index.php">&larr; Back to Home</a>
        <h2>Complaint Resolution Report</h2>
    </div>

    <div class="page-wrap">
        <div class="card-grid">
            <?php foreach ($cards as $c): ?>
            <div class="stat-card <?php echo $c[2]; ?>">
                <div class="num"><?php echo $c[1]; ?></div>
                <div class="label"><?php echo $c[0]; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>