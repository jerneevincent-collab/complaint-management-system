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
    <title>Deployment Checklist</title>
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
            max-width:900px !important;
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
        .table-card td { padding:12px; border-bottom:1px solid #eef1f5; font-size:13px; vertical-align:top; line-height:1.5; }
        .table-card tr:last-child td { border-bottom:none; }
        .table-card td:first-child { font-weight:600; color:var(--navy); }
        .table-card td.nowrap { white-space:nowrap; }
        .note { margin:0; font-size:13px; line-height:1.6; color:#475569; }

        .badge {
            display:inline-block;
            padding:2px 12px;
            border-radius:999px;
            font-size:12px;
            font-weight:600;
            white-space:nowrap;
            margin-right:4px;
        }
        .badge.pass { background:#ecfdf5; color:#065f46; }

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
        <h2>Deployment Checklist</h2>
    </div>

    <div class="page-wrap">

        <div class="table-card">
            <h3>Lab 57: Prepare the System for Deployment</h3>
            <div class="table-scroll">
                <table>
                    <tr><th>Task</th><th>Status</th></tr>
                    <tr><td>Clean source code, remove test/debug scripts</td><td><span class="badge pass">Done</span> (removed set_password.php and debug print_r after use)</td></tr>
                    <tr><td>Configure production database and application settings</td><td><span class="badge pass">Done</span> (complaint_management_db created and populated)</td></tr>
                    <tr><td>Create administrator account</td><td><span class="badge pass">Done</span> (personnel with Administrator role can be created via Personnel Management)</td></tr>
                    <tr><td>Prepare database backup</td><td><span class="badge pass">Done</span> (see Lab 54 backup file)</td></tr>
                    <tr><td>Check all modules</td><td><span class="badge pass">Done</span> (all 63 labs tested and working)</td></tr>
                </table>
            </div>
        </div>

        <div class="table-card">
            <h3>Lab 58: Deploy the Complaint Management System</h3>
            <div class="table-scroll">
                <table>
                    <tr><th>Component</th><th>Configuration</th></tr>
                    <tr><td>Deployment Target</td><td>Local server (XAMPP) — Apache + MySQL</td></tr>
                    <tr><td>Web Server</td><td>Apache 2.4.58, running on localhost, Port 80</td></tr>
                    <tr><td>Database Server</td><td>MySQL (via XAMPP), database name: complaint_management_db</td></tr>
                    <tr><td>Domain/IP</td><td>http://localhost/complaint_management_system/</td></tr>
                    <tr><td>Application Settings</td><td>PHP 8.2.12, session-based authentication enabled</td></tr>
                    <tr><td>Security Settings</td><td>Role-based access control, hashed passwords, prepared statements</td></tr>
                </table>
            </div>
        </div>

        <div class="table-card">
            <h3>Lab 59: Post-Deployment Testing</h3>
            <p class="note">See <a href="uat_checklist.php">UAT Checklist</a> and <a href="bug_tracking.php">Bug Tracking Sheet</a> for verification results.</p>
        </div>

    </div>
</body>
</html>