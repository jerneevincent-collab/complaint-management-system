<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: splash.php");
    exit();
}

// Splash animation shows only once per login
$showSplash = empty($_SESSION['splash_done']);
$_SESSION['splash_done'] = true;

$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$total =$conn->query("SELECT COUNT(*) AS c FROM complaints")->fetch_assoc()['c'];
$open =$conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status NOT IN ('Resolved','Closed')")->fetch_assoc()['c'];
$closed =$conn->query("SELECT COUNT(*) AS c FROM complaints WHERE status = 'Closed'")->fetch_assoc()['c'];
$overdue = $conn->query("SELECT COUNT(*) AS c FROM assignments WHERE due_date < CURDATE() AND status = 'active'")->fetch_assoc()['c'];

$roleId = (int) $_SESSION['role_id'];
$roleRow = $conn->query("SELECT role_name FROM roles WHERE role_id = $roleId")->fetch_assoc();
$roleName = $roleRow ? $roleRow['role_name'] : 'User';

$recent = $conn->query(
    "SELECT c.complaint_id, c.complaint_number, c.subject, c.status, c.date_filed, cm.full_name
     FROM complaints c
     JOIN complainants cm ON c.complainant_id = cm.complainant_id
     ORDER BY c.complaint_id DESC LIMIT 5"
);

$statusRes = $conn->query("SELECT status, COUNT(*) AS total FROM complaints GROUP BY status");
$statusLabels = [];
$statusValues = [];
while ($r = $statusRes->fetch_assoc()) {
    $statusLabels[] = $r['status'];
    $statusValues[] = (int) $r['total'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Complaint Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <style>
        :root { --navy: #0a2540; --accent: #2f6fed; }

        /* ===== FULLSCREEN SPLASH OVERLAY ===== */
        #splashOverlay {
            position: fixed !important;
            top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background: var(--navy) !important;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: background 0.6s ease-out, opacity 0.6s ease-out, visibility 0.6s;
        }

        .splash-logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 100000;
        }

        .splash-logo-container img#splashLogo {
            width: 130px !important;
            height: 130px !important;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1), width 0.8s cubic-bezier(0.4, 0, 0.2, 1), height 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: center center;
        }

        .splash-text {
            color: #ffffff;
            font-size: 18px;
            font-weight: 500;
            margin-top: 20px;
            letter-spacing: 0.5px;
            transition: opacity 0.3s ease;
        }

        .splash-loader {
            width: 32px;
            height: 32px;
            margin-top: 16px;
            border: 3px solid rgba(255,255,255,0.2);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 0.8s infinite linear;
            transition: opacity 0.3s ease;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        #splashOverlay.hide-splash {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            background: transparent !important;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            background: var(--navy) !important;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 24px;
            width: 100% !important;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .topbar .left { display: flex; align-items: center; gap: 14px; }
        #menuBtn {
            background: none; border: none; color: #fff; font-size: 22px;
            cursor: pointer; padding: 4px 8px; border-radius: 6px; transition: background 0.2s;
        }
        #menuBtn:hover { background: rgba(255,255,255,0.1); }
        .topbar .brand { display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 15px; }
        
        .topbar .brand img#targetLogo { 
            width: 42px !important; 
            height: 42px !important; 
            border-radius: 50%; 
            object-fit: cover;
            opacity: 0; 
            transition: opacity 0.2s ease;
        }

        .user-badge {
            display: flex; align-items: center; gap: 10px;
            background: rgba(255,255,255,0.08); padding: 6px 14px 6px 6px; border-radius: 30px;
            font-size: 14px; color: #fff;
        }
        .user-badge img { width: 26px !important; height: 26px !important; border-radius: 50%; object-fit: cover; }
        .user-badge a { color: #fff; text-decoration: none; }
        .user-badge .divider { color: #64748b; }
        .user-badge .logout-link { color: #fca5a5; font-weight: 500; }

        /* ===== SIDE PANEL ===== */
        .side-panel {
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            width: 300px;
            max-width: 85vw;
            background: #fff;
            z-index: 200;
            transform: translateX(-100%);
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
            box-shadow: 4px 0 24px rgba(0,0,0,0.15);
        }
        .side-panel.open { transform: translateX(0); }

        .panel-header {
            display: flex; align-items: center; gap: 12px;
            padding: 18px 20px; border-bottom: 1px solid #e2e8f0;
        }
        .panel-header img { width: 36px !important; height: 36px !important; border-radius: 50%; object-fit: cover; }
        .panel-header span { font-weight: 700; color: var(--navy); font-size: 15px; }
        #closeBtn {
            margin-left: auto; background: none; border: none; font-size: 22px;
            color: #94a3b8; cursor: pointer;
        }
        #closeBtn:hover { color: var(--navy); }

        .panel-nav { padding: 10px; }
        .panel-group { margin-bottom: 4px; }

        .panel-group-title {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 14px;
            cursor: pointer;
            border-radius: 8px;
            color: var(--navy);
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s ease;
        }
        .panel-group-title:hover { background: #f1f5f9; }
        .panel-group-title .arrow { transition: transform 0.25s ease; color: #94a3b8; font-size: 12px; }
        .panel-group.expanded .panel-group-title .arrow { transform: rotate(90deg); }

        .panel-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .panel-group.expanded .panel-submenu { max-height: 700px; }

        .panel-submenu a {
            display: block;
            padding: 9px 14px 9px 28px;
            color: #475569;
            font-size: 13.5px;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .panel-submenu a:hover { background: #eef2ff; color: var(--accent); }

        .panel-footer {
            border-top: 1px solid #e2e8f0;
            padding: 10px;
        }
        .panel-footer a {
            display: block;
            padding: 12px 14px;
            color: var(--navy);
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            border-radius: 8px;
        }
        .panel-footer a:hover { background: #f1f5f9; }
        .panel-footer a.logout { color: #dc2626; }

        /* ===== MAIN CONTENT ===== */
        .main-content { padding: 30px 20px; max-width: 1200px; margin: 0 auto; width: 100%; }

        /* ----- Hero / greeting ----- */
        .hero-card {
            position: relative; overflow: hidden;
            background: linear-gradient(120deg, #0a2540 0%, #12467f 55%, #2f6fed 100%);
            border-radius: 18px; padding: 30px 34px; margin-bottom: 24px; color: #fff;
            display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;
            box-shadow: 0 12px 30px rgba(10,37,64,0.18);
            opacity: 0; animation: fadeUp 0.5s ease forwards;
        }
        .hero-card::before, .hero-card::after {
            content: ""; position: absolute; border-radius: 50%;
            background: rgba(255,255,255,0.07); pointer-events: none;
        }
        .hero-card::before { width: 260px; height: 260px; right: -60px; top: -110px; animation: floaty 7s ease-in-out infinite; }
        .hero-card::after { width: 160px; height: 160px; right: 160px; bottom: -90px; animation: floaty 9s ease-in-out infinite reverse; }
        @keyframes floaty { 0%,100% { transform: translateY(0); } 50% { transform: translateY(14px); } }
        .hero-text { position: relative; z-index: 1; }
        .hero-text h2 { color: #fff; margin: 0 0 6px; font-size: 26px; }
        .hero-text p { margin: 0; font-size: 14px; color: #cbd9f0; max-width: 560px; line-height: 1.6; }
        .role-badge {
            display: inline-block; margin-top: 12px; padding: 4px 14px; border-radius: 999px;
            background: rgba(255,255,255,0.15); font-size: 12px; font-weight: 600; letter-spacing: 0.03em;
        }
        .hero-clock { position: relative; z-index: 1; text-align: right; }
        .hero-clock .time { font-size: 34px; font-weight: 700; line-height: 1.1; }
        .hero-clock .date { font-size: 13px; color: #cbd9f0; margin-top: 4px; }

        /* ----- KPI cards ----- */
        .kpi-row { display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap; }
        .kpi-card {
            flex: 1; min-width: 160px; background: #fff; border-radius: 14px;
            padding: 20px 22px; box-shadow: 0 6px 20px rgba(10,37,64,0.06);
            border-top: 3px solid var(--accent);
            opacity: 0; transform: translateY(16px);
            animation: fadeUp 0.5s ease forwards;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .kpi-card:hover { transform: translateY(-4px) !important; box-shadow: 0 12px 26px rgba(10,37,64,0.12); }
        .kpi-card:nth-child(1) { animation-delay: 0.10s; }
        .kpi-card:nth-child(2) { animation-delay: 0.18s; border-top-color: #f59e0b; }
        .kpi-card:nth-child(3) { animation-delay: 0.26s; border-top-color: #16a34a; }
        .kpi-card:nth-child(4) { animation-delay: 0.34s; border-top-color: #dc2626; }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
        .kpi-card .num { font-size: 32px; font-weight: 700; color: var(--navy); }
        .kpi-card .label { font-size: 13px; color: #64748b; margin-top: 4px; }

        /* ----- Section cards ----- */
        .section-title { margin: 0 0 14px; font-size: 16px; color: var(--navy); font-weight: 600; }
        .panel-card {
            background: #fff; border-radius: 14px; padding: 22px 24px; margin-bottom: 24px;
            box-shadow: 0 6px 20px rgba(10,37,64,0.06);
            opacity: 0; animation: fadeUp 0.5s ease forwards;
        }

        /* ----- Quick actions ----- */
        .quick-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px; }
        .quick-btn {
            display: flex; flex-direction: column; align-items: center; gap: 10px;
            padding: 18px 10px; border-radius: 14px; background: #f4f7fb;
            color: var(--navy); font-size: 13px; font-weight: 600; text-align: center; text-decoration: none;
            transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }
        .quick-btn:hover { transform: translateY(-4px); background: #eaf0ff; box-shadow: 0 8px 18px rgba(47,111,237,0.15); text-decoration: none; }
        .quick-btn img { width: 44px; height: 44px; object-fit: contain; border-radius: 50%; }

        /* ----- Recent + chart ----- */
        .two-col { display: grid; grid-template-columns: 1.6fr 1fr; gap: 24px; }
        .two-col .panel-card { margin-bottom: 24px; }
        .recent-list { list-style: none; margin: 0; padding: 0; }
        .recent-list li {
            background: transparent; border-left: none; border-radius: 0; box-shadow: none;
            display: flex; justify-content: space-between; align-items: center; gap: 12px;
            padding: 12px 0; margin: 0; border-bottom: 1px solid #eef1f5;
        }
        .recent-list li:last-child { border-bottom: none; }
        .recent-list a.item { color: inherit; text-decoration: none; flex: 1; min-width: 0; display: block; }
        .recent-list a.item:hover .r-subject { color: var(--accent); }
        .r-subject { font-size: 14px; font-weight: 600; color: var(--navy); transition: color 0.2s; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .r-meta { font-size: 12px; color: #64748b; margin-top: 2px; }
        .status-badge { display: inline-block; padding: 3px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap; }
        .status-submitted { background: #dbeafe; color: #1e40af; }
        .status-for-assignment, .status-assigned { background: #fef3c7; color: #92400e; }
        .status-under-investigation, .status-action-required, .status-for-resolution { background: #fed7aa; color: #9a3412; }
        .status-resolved { background: #d1fae5; color: #065f46; }
        .status-closed { background: #e2e8f0; color: #475569; }
        .view-all { display: inline-block; margin-top: 12px; font-size: 13px; font-weight: 600; color: var(--accent); }
        .chart-box { position: relative; height: 260px; }
        .empty-note { color: #64748b; font-size: 13px; margin: 0; }

        @media (max-width: 900px) { .two-col { grid-template-columns: 1fr; } .hero-clock { text-align: left; } }

        @media (max-width: 600px) {
            .user-badge span { display: none; }
            .topbar .brand span { font-size: 13px; }
        }
    </style>
</head>
<body>

    <?php if ($showSplash): ?>
    <!-- FULLSCREEN SPLASH OVERLAY -->
    <div id="splashOverlay">
        <div class="splash-logo-container" id="splashLogoContainer">
            <img src="assets/cms-logo.png" alt="CMS Logo" id="splashLogo">
            <div class="splash-text" id="splashText">Loading your dashboard...</div>
            <div class="splash-loader" id="splashLoader"></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- FULL WIDTH TOPBAR -->
    <div class="topbar">
        <div class="left">
            <button id="menuBtn">&#9776;</button>
            <div class="brand">
                <img src="assets/cms-logo.png" alt="CMS" id="targetLogo"<?php if (!$showSplash) echo ' style="opacity:1;"'; ?>>
                <span>Complaint Management System</span>
            </div>
        </div>
        <div class="user-badge">
            <a href="manage_account.php" style="display:flex; align-items:center; gap:8px;">
                <img src="assets/account.png" alt="">
                <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            </a>
            <span class="divider">|</span>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>
    </div>

    <div class="overlay" id="overlay"></div>

    <nav class="side-panel" id="sidePanel">
        <div class="panel-header">
            <img src="assets/cms-logo.png" alt="CMS">
            <span>Navigation</span>
            <button id="closeBtn">&times;</button>
        </div>

        <div class="panel-nav">
            <div class="panel-group">
                <div class="panel-group-title" data-toggle>
                    <span>Complainant Management</span>
                    <span class="arrow">&#9656;</span>
                </div>
                <div class="panel-submenu">
                    <a href="complainant_register.php">Register Complainant</a>
                    <a href="complainant_list.php">Complainant List / Search</a>
                </div>
            </div>

            <div class="panel-group">
                <div class="panel-group-title" data-toggle>
                    <span>Complaint Management</span>
                    <span class="arrow">&#9656;</span>
                </div>
                <div class="panel-submenu">
                    <a href="category_manage.php">Manage Categories</a>
                    <a href="complaint_register.php">File a Complaint</a>
                    <a href="complaint_list.php">Complaint List / Search</a>
                    <a href="complaint_close.php">Complaint Closure</a>
                </div>
            </div>

            <div class="panel-group">
                <div class="panel-group-title" data-toggle>
                    <span>Assignment</span>
                    <span class="arrow">&#9656;</span>
                </div>
                <div class="panel-submenu">
                    <a href="personnel_manage.php">Personnel Management</a>
                    <a href="assignment_form.php">Assign Complaint</a>
                    <a href="assignment_list.php">Assignment List</a>
                </div>
            </div>

            <div class="panel-group">
                <div class="panel-group-title" data-toggle>
                    <span>Investigation</span>
                    <span class="arrow">&#9656;</span>
                </div>
                <div class="panel-submenu">
                    <a href="investigation_form.php">Create Investigation</a>
                    <a href="investigation_list.php">Investigation List</a>
                </div>
            </div>

            <div class="panel-group">
                <div class="panel-group-title" data-toggle>
                    <span>Action</span>
                    <span class="arrow">&#9656;</span>
                </div>
                <div class="panel-submenu">
                    <a href="action_form.php">Create Action</a>
                    <a href="action_list.php">Action List / Monitoring</a>
                </div>
            </div>

            <div class="panel-group">
                <div class="panel-group-title" data-toggle>
                    <span>Resolution</span>
                    <span class="arrow">&#9656;</span>
                </div>
                <div class="panel-submenu">
                    <a href="resolution_form.php">Record Resolution</a>
                </div>
            </div>

            <div class="panel-group">
                <div class="panel-group-title" data-toggle>
                    <span>Reports and Dashboard</span>
                    <span class="arrow">&#9656;</span>
                </div>
                <div class="panel-submenu">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="report_summary.php">Summary Report</a>
                    <a href="report_category.php">Category Report</a>
                    <a href="report_resolution.php">Resolution Report</a>
                    <a href="report_investigator.php">Investigator Report</a>
                    <a href="report_action.php">Action Report</a>
                    <a href="report_trend.php">Trend Analysis</a>
                    <?php if ($_SESSION['role_id'] == 1): ?>
                    <a href="audit_log_view.php">Audit Trail</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="panel-group">
                <div class="panel-group-title" data-toggle>
                    <span>Documentation</span>
                    <span class="arrow">&#9656;</span>
                </div>
                <div class="panel-submenu">
                    <a href="business_rules.php">Business Rules</a>
                    <a href="bug_tracking.php">Bug Tracking Sheet</a>
                    <a href="uat_checklist.php">UAT Checklist</a>
                    <a href="security_testing.php">Security Testing</a>
                    <a href="performance_testing.php">Performance Testing</a>
                    <a href="deployment_checklist.php">Deployment Checklist</a>
                    <a href="user_manual.php">User Manual</a>
                    <a href="technical_documentation.php">Technical Documentation</a>
                    <a href="final_demo.php">Final Demo Script</a>
                </div>
            </div>
        </div>

        <div class="panel-footer">
            <a href="manage_account.php">My Account</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </nav>

    <div class="main-content">

        <div class="hero-card">
            <div class="hero-text">
                <h2><span id="greetText">Welcome Back</span>, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h2>
                <p>Click the menu icon in the top-left to navigate through Complainant Management, Complaint Management, Assignment, Investigation, Action, Resolution, Reports, and Documentation.</p>
                <span class="role-badge"><?php echo htmlspecialchars($roleName); ?></span>
            </div>
            <div class="hero-clock">
                <div class="time" id="liveTime">--:--</div>
                <div class="date" id="liveDate"></div>
            </div>
        </div>

        <div class="kpi-row">
            <div class="kpi-card">
                <div class="num" data-count="<?php echo (int) $total; ?>">0</div>
                <div class="label">Total Complaints</div>
            </div>
            <div class="kpi-card">
                <div class="num" data-count="<?php echo (int) $open; ?>">0</div>
                <div class="label">Open</div>
            </div>
            <div class="kpi-card">
                <div class="num" data-count="<?php echo (int) $closed; ?>">0</div>
                <div class="label">Closed</div>
            </div>
            <div class="kpi-card">
                <div class="num" data-count="<?php echo (int) $overdue; ?>">0</div>
                <div class="label">Overdue</div>
            </div>
        </div>

        <div class="panel-card" style="animation-delay:0.3s;">
            <h3 class="section-title">Quick Actions</h3>
            <div class="quick-grid">
                <a class="quick-btn" href="complaint_register.php"><img src="assets/complaint-logo.png" alt="">File a Complaint</a>
                <a class="quick-btn" href="assignment_form.php"><img src="assets/assignment-logo.png" alt="">Assign Complaint</a>
                <a class="quick-btn" href="investigation_form.php"><img src="assets/investigation-logo.png" alt="">Investigation</a>
                <a class="quick-btn" href="action_form.php"><img src="assets/action-logo.png" alt="">Create Action</a>
                <a class="quick-btn" href="resolution_form.php"><img src="assets/resolution-logo.png" alt="">Resolution</a>
                <a class="quick-btn" href="dashboard.php"><img src="assets/reports-logo.png" alt="">Reports</a>
            </div>
        </div>

        <div class="two-col">
            <div class="panel-card" style="animation-delay:0.4s;">
                <h3 class="section-title">Recent Complaints</h3>
                <?php if ($recent->num_rows > 0): ?>
                <ul class="recent-list">
                    <?php while ($row = $recent->fetch_assoc()):
                        $statusClass = strtolower(str_replace(' ', '-', $row['status'])); ?>
                    <li>
                        <a class="item" href="complaint_view.php?id=<?php echo $row['complaint_id']; ?>">
                            <div class="r-subject"><?php echo htmlspecialchars($row['subject']); ?></div>
                            <div class="r-meta"><?php echo htmlspecialchars($row['complaint_number']); ?> &middot; <?php echo htmlspecialchars($row['full_name']); ?> &middot; <?php echo $row['date_filed']; ?></div>
                        </a>
                        <span class="status-badge status-<?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                    </li>
                    <?php endwhile; ?>
                </ul>
                <a class="view-all" href="complaint_list.php">View all complaints &rarr;</a>
                <?php else: ?>
                <p class="empty-note">No complaints filed yet.</p>
                <?php endif; ?>
            </div>

            <div class="panel-card" style="animation-delay:0.5s;">
                <h3 class="section-title">Complaints by Status</h3>
                <?php if (count($statusValues) > 0): ?>
                <div class="chart-box"><canvas id="statusDonut"></canvas></div>
                <?php else: ?>
                <p class="empty-note">No data yet.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const splashText = document.getElementById('splashText');
            const splashLoader = document.getElementById('splashLoader');
            const splashLogo = document.getElementById('splashLogo');
            const targetLogo = document.getElementById('targetLogo');
            const splashOverlay = document.getElementById('splashOverlay');

            if (!splashOverlay) return;

            setTimeout(() => {
                if (splashText) splashText.style.opacity = '0';
                if (splashLoader) splashLoader.style.opacity = '0';

                const startRect = splashLogo.getBoundingClientRect();
                const targetRect = targetLogo.getBoundingClientRect();

                const deltaX = targetRect.left + (targetRect.width / 2) - (startRect.left + (startRect.width / 2));
                const deltaY = targetRect.top + (targetRect.height / 2) - (startRect.top + (startRect.height / 2));

                splashLogo.style.transform = `translate(${deltaX}px, ${deltaY}px)`;
                splashLogo.style.width = `${targetRect.width}px`;
                splashLogo.style.height = `${targetRect.height}px`;

                if (splashOverlay) splashOverlay.style.background = 'transparent';

                setTimeout(() => {
                    if (targetLogo) targetLogo.style.opacity = '1';
                    if (splashOverlay) splashOverlay.classList.add('hide-splash');
                }, 800);

            }, 600);
        });


        // ----- Greeting + live clock -----
        (function () {
            const greet = document.getElementById('greetText');
            const timeEl = document.getElementById('liveTime');
            const dateEl = document.getElementById('liveDate');
            function tick() {
                const now = new Date();
                const h = now.getHours();
                greet.textContent = h < 12 ? 'Good Morning' : (h < 18 ? 'Good Afternoon' : 'Good Evening');
                timeEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                dateEl.textContent = now.toLocaleDateString([], { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            }
            tick();
            setInterval(tick, 1000);
        })();

        // ----- Count-up numbers (starts after the splash, if any) -----
        (function () {
            const startDelay = document.getElementById('splashOverlay') ? 1500 : 300;
            setTimeout(() => {
                document.querySelectorAll('[data-count]').forEach(el => {
                    const target = parseInt(el.dataset.count, 10) || 0;
                    const duration = 900;
                    const t0 = performance.now();
                    function step(t) {
                        const p = Math.min((t - t0) / duration, 1);
                        el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
                        if (p < 1) requestAnimationFrame(step);
                    }
                    requestAnimationFrame(step);
                });
            }, startDelay);
        })();

        // ----- Status donut chart -----
        (function () {
            const canvas = document.getElementById('statusDonut');
            if (!canvas || typeof Chart === 'undefined') return;
            Chart.defaults.font.family = "'Poppins', sans-serif";
            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($statusLabels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($statusValues); ?>,
                        backgroundColor: ['#2f6fed', '#f59e0b', '#7c5cd6', '#e0702e', '#3aa6c9', '#16a34a', '#0f766e', '#6b7787'],
                        borderWidth: 2, borderColor: '#ffffff'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
                    animation: { animateRotate: true, duration: 1200 }
                }
            });
        })();

        const panel = document.getElementById('sidePanel');
        const overlay = document.getElementById('overlay');
        const menuBtn = document.getElementById('menuBtn');
        const closeBtn = document.getElementById('closeBtn');

        function openPanel() {
            panel.classList.add('open');
            overlay.classList.add('show');
        }
        function closePanel() {
            panel.classList.remove('open');
            overlay.classList.remove('show');
        }

        menuBtn.addEventListener('click', openPanel);
        closeBtn.addEventListener('click', closePanel);
        overlay.addEventListener('click', closePanel);

        document.querySelectorAll('.panel-group-title').forEach(title => {
            title.addEventListener('click', () => {
                const group = title.closest('.panel-group');
                const wasExpanded = group.classList.contains('expanded');
                document.querySelectorAll('.panel-group.expanded').forEach(g => {
                    if (g !== group) g.classList.remove('expanded');
                });
                group.classList.toggle('expanded', !wasExpanded);
            });
        });
    </script>
</body>
</html>