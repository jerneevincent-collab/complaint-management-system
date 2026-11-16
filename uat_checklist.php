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
    <title>UAT Checklist</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/enhance.css">
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
            max-width:1000px !important;
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
            white-space:nowrap;
        }
        .table-card td { padding:10px 12px; border-bottom:1px solid #eef1f5; font-size:13px; }
        .table-card tr:last-child td { border-bottom:none; }
        .table-card th:not(:first-child), .table-card td:not(:first-child) { text-align:center; }

        .badge {
            display:inline-block;
            padding:3px 12px;
            border-radius:999px;
            font-size:12px;
            font-weight:600;
            white-space:nowrap;
        }
        .badge.pass { background:#ecfdf5; color:#065f46; }
        .badge.accepted { background:#eef2ff; color:#2f4fb0; }

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
        <h2>User Acceptance Testing (UAT) Checklist</h2>
    </div>

    <div class="page-wrap">

        <div class="table-card">
            <h3>UAT Results</h3>
            <div class="table-scroll">
                <table>
                    <tr><th>Scenario</th><th>Functionality</th><th>Usability</th><th>Accuracy</th><th>Reliability</th><th>Security</th><th>Result</th></tr>
                    <tr><td>Complaint Submission</td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge accepted">Accepted</span></td></tr>
                    <tr><td>Assignment</td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge accepted">Accepted</span></td></tr>
                    <tr><td>Investigation</td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge accepted">Accepted</span></td></tr>
                    <tr><td>Action</td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge accepted">Accepted</span></td></tr>
                    <tr><td>Resolution</td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge accepted">Accepted</span></td></tr>
                    <tr><td>Reports</td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge accepted">Accepted</span></td></tr>
                    <tr><td>User Management</td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge pass">Pass</span></td><td><span class="badge accepted">Accepted</span></td></tr>
                </table>
            </div>
        </div>

    </div>
    <script src="assets/enhance.js"></script>
</body>
</html>