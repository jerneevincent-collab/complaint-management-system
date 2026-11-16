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
    <title>User Manual</title>
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
            max-width:860px !important;
            margin:0 auto !important;
            padding:30px 20px 60px !important;
        }

        .step-card {
            display:flex;
            gap:18px;
            align-items:flex-start;
            background:#fff;
            border-radius:16px;
            padding:22px 26px;
            margin-bottom:18px;
            box-shadow:0 8px 24px rgba(10,37,64,0.07);
            opacity:0;
            animation:fadeUp 0.5s ease forwards;
        }
        .step-num {
            flex:0 0 40px;
            width:40px;
            height:40px;
            border-radius:50%;
            background:var(--navy);
            color:#fff;
            font-weight:600;
            font-size:16px;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .step-body { flex:1; min-width:0; }
        .step-body h3 { margin:0 0 6px 0; color:var(--navy); font-size:16px; }
        .step-body p { margin:0; font-size:13px; line-height:1.7; color:#475569; }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(12px); }
            to { opacity:1; transform:translateY(0); }
        }
        @media (max-width:640px) {
            .topbar h2 { font-size:15px; }
            .step-card { padding:18px; gap:14px; }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <a class="back-link" href="index.php">&larr; Back to Home</a>
        <h2>User Manual - Complaint Management System</h2>
    </div>

    <div class="page-wrap">

        <div class="step-card" style="animation-delay:0.04s;">
            <div class="step-num">1</div>
            <div class="step-body">
                <h3>Login</h3>
                <p>Go to the login page. Enter your registered email and password, then click Login. If your credentials are incorrect, an error message will appear.</p>
            </div>
        </div>

        <div class="step-card" style="animation-delay:0.08s;">
            <div class="step-num">2</div>
            <div class="step-body">
                <h3>Registering a Complainant</h3>
                <p>From the Home page, click "Register Complainant." Fill in the full name, contact number, email, address, and organization/department, then click Register.</p>
            </div>
        </div>

        <div class="step-card" style="animation-delay:0.12s;">
            <div class="step-num">3</div>
            <div class="step-body">
                <h3>Filing a Complaint</h3>
                <p>Click "File a Complaint." Select the complainant and category, enter the subject and description, choose the date filed and priority, optionally attach a supporting document (JPG, PNG, or PDF only), then click Submit Complaint.</p>
            </div>
        </div>

        <div class="step-card" style="animation-delay:0.16s;">
            <div class="step-num">4</div>
            <div class="step-body">
                <h3>Assigning a Complaint</h3>
                <p>Click "Assign Complaint." Choose the complaint, select the personnel to assign it to, choose the department, set the assignment and due dates, then click Assign Complaint.</p>
            </div>
        </div>

        <div class="step-card" style="animation-delay:0.20s;">
            <div class="step-num">5</div>
            <div class="step-body">
                <h3>Conducting an Investigation</h3>
                <p>Click "Create Investigation." Select an assigned complaint and an investigator, then set the start date. To record findings, go to the Investigation List and click "Add Findings."</p>
            </div>
        </div>

        <div class="step-card" style="animation-delay:0.24s;">
            <div class="step-num">6</div>
            <div class="step-body">
                <h3>Recording an Action</h3>
                <p>Click "Create Action." Select a completed investigation, choose the action type, enter a description, select the responsible person, and set a target date.</p>
            </div>
        </div>

        <div class="step-card" style="animation-delay:0.28s;">
            <div class="step-num">7</div>
            <div class="step-body">
                <h3>Resolving a Complaint</h3>
                <p>Click "Record Resolution." Select the complaint (the required action must already be completed), enter the resolution description and date, then select who resolved it.</p>
            </div>
        </div>

        <div class="step-card" style="animation-delay:0.32s;">
            <div class="step-num">8</div>
            <div class="step-body">
                <h3>Closing a Complaint</h3>
                <p>Only Administrators and Supervisors can close complaints. Go to "Complaint Closure," select who is closing it, and click Confirm Closure.</p>
            </div>
        </div>

        <div class="step-card" style="animation-delay:0.36s;">
            <div class="step-num">9</div>
            <div class="step-body">
                <h3>Generating Reports</h3>
                <p>From the Home page, access the various reports: Complaint Summary, Category Report, Resolution Report, Investigator Performance, Action Monitoring, and Trend Analysis.</p>
            </div>
        </div>

        <div class="step-card" style="animation-delay:0.40s;">
            <div class="step-num">10</div>
            <div class="step-body">
                <h3>Using the Dashboard</h3>
                <p>Click "Dashboard" to view KPI cards (total, pending, resolved, overdue) and charts showing complaints by category and by status.</p>
            </div>
        </div>

    </div>
    <script src="assets/enhance.js"></script>
</body>
</html>