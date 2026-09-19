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

$total = $conn->query("SELECT COUNT(*) AS c FROM complaints")->fetch_assoc()['c'];
$open = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status NOT IN ('Resolved','Closed')")->fetch_assoc()['c'];
$closed = $conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status = 'Closed'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Complaint Management System</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        :root {
            --navy: #0a2540;
            --navy-light: #0f3d5c;
            --accent: #2f6fed;
            --teal: #0f766e;
        }

        body {
            background: #f4f7fb;
        }

        .topbar {
            background: var(--navy);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .topbar .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
               .topbar .brand img {
            width: 52px;
            height: 52px;
            border-radius: 50%;
        }
        .topbar .brand span {
            font-weight: 600;
            font-size: 15px;
        }
        .topbar .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 14px;
        }
        .topbar .user-info a {
            color: #cbd5e1;
        }
        .topbar .user-info a:hover { color: #fff; }

        .dashboard-wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .kpi-row {
            display: flex;
            gap: 16px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .kpi-card {
            flex: 1;
            min-width: 160px;
            background: #fff;
            border-radius: 14px;
            padding: 20px 22px;
            box-shadow: 0 6px 20px rgba(10,37,64,0.06);
            border-top: 3px solid var(--accent);
            opacity: 0;
            transform: translateY(16px);
            animation: fadeUp 0.5s ease forwards;
        }
        .kpi-card:nth-child(2) { animation-delay: 0.08s; border-top-color: #f59e0b; }
        .kpi-card:nth-child(3) { animation-delay: 0.16s; border-top-color: #16a34a; }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        .kpi-card .num { font-size: 32px; font-weight: 700; color: var(--navy); }
        .kpi-card .label { font-size: 13px; color: #64748b; margin-top: 4px; }

        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }
        .module-card {
            background: #fff;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 6px 20px rgba(10,37,64,0.06);
            opacity: 0;
            transform: translateY(16px);
            animation: fadeUp 0.5s ease forwards;
        }
        .module-grid .module-card:nth-child(1) { animation-delay: 0.1s; }
        .module-grid .module-card:nth-child(2) { animation-delay: 0.15s; }
        .module-grid .module-card:nth-child(3) { animation-delay: 0.2s; }
        .module-grid .module-card:nth-child(4) { animation-delay: 0.25s; }
        .module-grid .module-card:nth-child(5) { animation-delay: 0.3s; }
        .module-grid .module-card:nth-child(6) { animation-delay: 0.35s; }
        .module-grid .module-card:nth-child(7) { animation-delay: 0.4s; }
        .module-grid .module-card:nth-child(8) { animation-delay: 0.45s; }

        .module-card h3 {
            color: var(--navy);
            margin: 0 0 12px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .module-card ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .module-card ul li {
            background: none;
            border: none;
            box-shadow: none;
            padding: 6px 0;
            margin: 0;
        }
        .module-card ul li a {
            color: #334155;
            font-weight: 500;
            font-size: 14px;
            transition: color 0.2s, padding-left 0.2s;
            display: inline-block;
        }
        .module-card ul li a:hover {
            color: var(--accent);
            padding-left: 4px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="brand">
            <img src="assets/cms-logo.png" alt="CMS Logo">
            <span>Complaint Management System</span>
        </div>
        <div class="user-info">
            <span> <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="dashboard-wrap">
        <div class="kpi-row">
            <div class="kpi-card">
                <div class="num"><?php echo $total; ?></div>
                <div class="label">Total Complaints</div>
            </div>
            <div class="kpi-card">
                <div class="num"><?php echo $open; ?></div>
                <div class="label">Open</div>
            </div>
            <div class="kpi-card">
                <div class="num"><?php echo $closed; ?></div>
                <div class="label">Closed</div>
            </div>
        </div>

        <div class="module-grid">
            <div class="module-card">
                <h3>👤 Complainant Management</h3>
                <ul>
                    <li><a href="complainant_register.php">Register Complainant</a></li>
                    <li><a href="complainant_list.php">Complainant List / Search</a></li>
                </ul>
            </div>

            <div class="module-card">
                <h3>📋 Complaint Management</h3>
                <ul>
                    <li><a href="category_manage.php">Manage Categories</a></li>
                    <li><a href="complaint_register.php">File a Complaint</a></li>
                    <li><a href="complaint_list.php">Complaint List / Search</a></li>
                    <li><a href="complaint_close.php">Complaint Closure</a></li>
                </ul>
            </div>

            <div class="module-card">
                <h3>📌 Assignment</h3>
                <ul>
                    <li><a href="personnel_manage.php">Personnel Management</a></li>
                    <li><a href="assignment_form.php">Assign Complaint</a></li>
                    <li><a href="assignment_list.php">Assignment List</a></li>
                </ul>
            </div>

            <div class="module-card">
                <h3>🔍 Investigation</h3>
                <ul>
                    <li><a href="investigation_form.php">Create Investigation</a></li>
                    <li><a href="investigation_list.php">Investigation List</a></li>
                </ul>
            </div>

            <div class="module-card">
                <h3>⚙️ Action</h3>
                <ul>
                    <li><a href="action_form.php">Create Action</a></li>
                    <li><a href="action_list.php">Action List / Monitoring</a></li>
                </ul>
            </div>

            <div class="module-card">
                <h3>✅ Resolution</h3>
                <ul>
                    <li><a href="resolution_form.php">Record Resolution</a></li>
                </ul>
            </div>

            <div class="module-card">
                <h3>📊 Reports and Dashboard</h3>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="report_summary.php">Summary Report</a></li>
                    <li><a href="report_category.php">Category Report</a></li>
                    <li><a href="report_resolution.php">Resolution Report</a></li>
                    <li><a href="report_investigator.php">Investigator Report</a></li>
                    <li><a href="report_action.php">Action Report</a></li>
                    <li><a href="report_trend.php">Trend Analysis</a></li>
                </ul>
            </div>

            <div class="module-card">
                <h3>📄 Documentation</h3>
                <ul>
                    <li><a href="business_rules.php">Business Rules</a></li>
                    <li><a href="bug_tracking.php">Bug Tracking Sheet</a></li>
                    <li><a href="uat_checklist.php">UAT Checklist</a></li>
                    <li><a href="security_testing.php">Security Testing</a></li>
                    <li><a href="performance_testing.php">Performance Testing</a></li>
                    <li><a href="deployment_checklist.php">Deployment Checklist</a></li>
                    <li><a href="user_manual.php">User Manual</a></li>
                    <li><a href="technical_documentation.php">Technical Documentation</a></li>
                    <li><a href="final_demo.php">Final Demo Script</a></li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>