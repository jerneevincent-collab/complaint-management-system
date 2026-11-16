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
    <title>Business Rules</title>
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
        .table-card td { padding:12px; border-bottom:1px solid #eef1f5; font-size:13px; vertical-align:top; line-height:1.5; }
        .table-card tr:last-child td { border-bottom:none; }
        .table-card th:first-child, .table-card td:first-child { width:50px; text-align:center; }

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
        <h2>Implemented Business Rules</h2>
    </div>

    <div class="page-wrap">

        <div class="table-card">
            <h3>Business Rules</h3>
            <div class="table-scroll">
                <table>
                    <tr><th>#</th><th>Rule</th><th>Where Implemented</th></tr>
                    <tr><td>1</td><td>A complaint must have a valid complainant.</td><td>Complaint Registration form (required dropdown)</td></tr>
                    <tr><td>2</td><td>A complaint must have a category.</td><td>Complaint Registration form (required dropdown)</td></tr>
                    <tr><td>3</td><td>Only submitted complaints can be assigned.</td><td>Assignment form (validated in save_assignment.php)</td></tr>
                    <tr><td>4</td><td>Only assigned complaints can proceed to investigation.</td><td>Investigation form (only shows "Assigned" complaints)</td></tr>
                    <tr><td>5</td><td>Investigation findings must be recorded before resolution.</td><td>Resolution form (checked in save_resolution.php)</td></tr>
                    <tr><td>6</td><td>Required actions must be completed before closure.</td><td>Resolution form (checked in save_resolution.php)</td></tr>
                    <tr><td>7</td><td>Closed complaints cannot be modified without authorization.</td><td>Complaint View (status check) + Closure page (Admin/Supervisor only)</td></tr>
                    <tr><td>8</td><td>Only authorized users can assign, resolve, or close complaints.</td><td>Login required on all pages; role-based access control on sensitive pages</td></tr>
                </table>
            </div>
        </div>

    </div>
    <script src="assets/enhance.js"></script>
</body>
</html>