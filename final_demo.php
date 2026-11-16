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
    <title>Final Demonstration Script</title>
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

        .doc-card {
            background:#fff;
            border-radius:16px;
            padding:24px 28px;
            margin-bottom:24px;
            box-shadow:0 8px 24px rgba(10,37,64,0.07);
            opacity:0;
            animation:fadeUp 0.5s ease forwards;
        }
        .doc-card h3 { margin:0 0 10px 0; color:var(--navy); font-size:16px; }
        .doc-card p { margin:0; font-size:13px; line-height:1.7; color:#475569; }

        .demo-steps { list-style:none; counter-reset:step; margin:0; padding:0; }
        .demo-steps li {
            counter-increment:step;
            display:flex;
            align-items:center;
            gap:14px;
            background:transparent;
            border-left:none;
            border-bottom:1px solid #eef1f5;
            border-radius:0;
            box-shadow:none;
            margin:0;
            padding:12px 0;
            font-size:13px;
            line-height:1.6;
            color:#334155;
        }
        .demo-steps li:last-child { border-bottom:none; }
        .demo-steps li::before {
            content:counter(step);
            flex:0 0 30px;
            width:30px;
            height:30px;
            border-radius:50%;
            background:var(--navy);
            color:#fff;
            font-weight:600;
            font-size:13px;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .demo-steps a { color:var(--accent); font-weight:600; }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(12px); }
            to { opacity:1; transform:translateY(0); }
        }
        @media (max-width:640px) {
            .topbar h2 { font-size:15px; }
            .doc-card { padding:20px; }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <a class="back-link" href="index.php">&larr; Back to Home</a>
        <h2>Final System Demonstration Script</h2>
    </div>

    <div class="page-wrap">

        <div class="doc-card">
            <h3>Scenario</h3>
            <p>Adonis Garcesfiles a complaint about delayed processing of a requested document (Service Complaint, High priority).</p>
        </div>

        <div class="doc-card" style="animation-delay:0.08s;">
            <h3>Demonstration Steps</h3>
            <ol class="demo-steps">
                <li><span><a href="complainant_register.php">Register the complainant</a> (Adonis Garces)</span></li>
                <li><span><a href="complaint_register.php">File the complaint</a> (Category: Service Complaint, Priority: High)</span></li>
                <li><span><a href="assignment_form.php">Assign the complaint</a> to a Complaint Officer</span></li>
                <li><span><a href="investigation_form.php">Create an investigation</a>, then <a href="investigation_list.php">record findings</a></span></li>
                <li><span>Mark the investigation as <a href="investigation_list.php">Completed</a></span></li>
                <li><span><a href="action_form.php">Create a corrective action</a></span></li>
                <li><span>Mark the action as <a href="action_list.php">Completed</a></span></li>
                <li><span><a href="resolution_form.php">Record the resolution</a></span></li>
                <li><span><a href="complaint_close.php">Close the complaint</a> (Administrator/Supervisor only)</span></li>
                <li><span><a href="dashboard.php">Generate a report</a> showing the completed transaction</span></li>
            </ol>
        </div>

        <div class="doc-card" style="animation-delay:0.16s;">
            <h3>Summary</h3>
            <p>This demonstrates the complete traceable complaint record, from Complainant Registration through Reports/Dashboard, as required by the final project scenario.</p>
        </div>

    </div>
    <script src="assets/enhance.js"></script>
</body>
</html>