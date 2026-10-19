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
    <title>Security Testing</title>
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
        .table-card td:first-child { font-weight:600; color:var(--navy); white-space:nowrap; }

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
        <h2>Security Testing Results</h2>
    </div>

    <div class="page-wrap">

        <div class="table-card">
            <h3>Security Test Results</h3>
            <div class="table-scroll">
                <table>
                    <tr><th>Test</th><th>Method</th><th>Result</th></tr>
                    <tr>
                        <td>Authentication</td>
                        <td>Attempted login with wrong password/email</td>
                        <td><span class="badge pass">Pass</span> "Invalid email or password" shown, no access granted</td>
                    </tr>
                    <tr>
                        <td>Authorization</td>
                        <td>Logged in as Action Officer, attempted to access Personnel Management and Complaint Closure</td>
                        <td><span class="badge pass">Pass</span> "Access denied" shown for both restricted pages</td>
                    </tr>
                    <tr>
                        <td>SQL Injection Protection</td>
                        <td>All database queries use prepared statements (mysqli bind_param) instead of raw string concatenation</td>
                        <td><span class="badge pass">Pass</span> no raw user input is directly inserted into SQL queries</td>
                    </tr>
                    <tr>
                        <td>Input Validation</td>
                        <td>Submitted forms with empty required fields and invalid dates</td>
                        <td><span class="badge pass">Pass</span> client-side and server-side validation both blocked invalid submissions</td>
                    </tr>
                    <tr>
                        <td>File Upload Security</td>
                        <td>Attempted to upload a .txt file as supporting document</td>
                        <td><span class="badge pass">Pass</span> rejected with "Only JPG, PNG, or PDF files are allowed"</td>
                    </tr>
                    <tr>
                        <td>Session Security</td>
                        <td>Attempted to access index.php and personnel_manage.php without logging in</td>
                        <td><span class="badge pass">Pass</span> redirected to login.php</td>
                    </tr>
                    <tr>
                        <td>Password Security</td>
                        <td>Checked stored passwords in the users table</td>
                        <td><span class="badge pass">Pass</span> passwords are hashed using PHP's password_hash(), not stored as plain text</td>
                    </tr>
                </table>
            </div>
        </div>

    </div>
</body>
</html>