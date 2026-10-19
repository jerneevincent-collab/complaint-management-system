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
    <title>Bug Tracking Sheet</title>
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
            max-width:1100px !important;
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
        .table-card td.nowrap { white-space:nowrap; }

        .badge {
            display:inline-block;
            padding:3px 12px;
            border-radius:999px;
            font-size:12px;
            font-weight:600;
            white-space:nowrap;
        }
        .badge.high { background:#fef2f2; color:#991b1b; }
        .badge.medium { background:#fff7e0; color:#8a6100; }
        .badge.low { background:#eef2ff; color:#2f4fb0; }
        .badge.closed { background:#ecfdf5; color:#065f46; }
        .badge.open { background:#fff7e0; color:#8a6100; }

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
        <h2>Bug Tracking Sheet</h2>
    </div>

    <div class="page-wrap">

        <div class="table-card">
            <h3>Reported Bugs and Fixes</h3>
            <div class="table-scroll">
                <table>
                    <tr>
                        <th>Bug ID</th>
                        <th>Module</th>
                        <th>Problem</th>
                        <th>Severity</th>
                        <th>Action Taken</th>
                        <th>Status</th>
                    </tr>
                    <tr>
                        <td class="nowrap">B001</td>
                        <td>Complaint Registration</td>
                        <td>File upload validation was not triggering because the form was missing the enctype="multipart/form-data" attribute, so $_FILES was always empty.</td>
                        <td><span class="badge high">High</span></td>
                        <td>Added enctype="multipart/form-data" to the complaint registration form.</td>
                        <td><span class="badge closed">Closed</span></td>
                    </tr>
                    <tr>
                        <td class="nowrap">B002</td>
                        <td>Project Foundation</td>
                        <td>date_filed value was saved with a typo (0026 instead of 2026) due to manual test data entry.</td>
                        <td><span class="badge low">Low</span></td>
                        <td>Corrected the value directly in phpMyAdmin.</td>
                        <td><span class="badge closed">Closed</span></td>
                    </tr>
                    <tr>
                        <td class="nowrap">B003</td>
                        <td>Complaint View</td>
                        <td>A duplicate/stray character appeared on the page after editing the status display logic.</td>
                        <td><span class="badge low">Low</span></td>
                        <td>Replaced the full file content to remove the stray character.</td>
                        <td><span class="badge closed">Closed</span></td>
                    </tr>
                </table>
            </div>
        </div>

    </div>
</body>
</html>