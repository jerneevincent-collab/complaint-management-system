<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: splash.php");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        :root {
            --navy: #0a2540;
            --navy-dark: #081b30;
            --accent: #2f6fed;
            --sidebar-collapsed: 78px;
            --sidebar-expanded: 250px;
        }

        * { box-sizing: border-box; }
        body { background: #f4f7fb; margin: 0; }

        /* Reset shared style.css rules that leak into the sidebar */
        .sidebar ul, .sidebar li, .sidebar .nav-list, .sidebar .nav-item, .sidebar .submenu, .sidebar .submenu li {
            background: none !important;
            border: none !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .sidebar .nav-item { margin-bottom: 4px !important; }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-collapsed);
            background: var(--navy);
            z-index: 200;
            transition: width 0.4s ease;
            overflow-x: hidden;
            overflow-y: auto;
            box-shadow: 2px 0 12px rgba(0,0,0,0.15);
        }
        .sidebar.open { width: var(--sidebar-expanded); }

        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }

        .sidebar-header {
            display: flex;
            align-items: center;
            height: 66px;
            padding: 0 14px;
            position: relative;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-header img {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .sidebar-header .brand-name {
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            margin-left: 12px;
            opacity: 0;
            white-space: nowrap;
            transition: opacity 0.3s ease 0.1s;
            pointer-events: none;
        }
        .sidebar.open .sidebar-header .brand-name { opacity: 1; pointer-events: auto; }

        #sidebarToggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #cbd5e1;
            cursor: pointer;
            font-size: 20px;
            background: none;
            border: none;
            padding: 4px;
            transition: transform 0.4s ease, color 0.2s ease;
        }
        #sidebarToggle:hover { color: #fff; }
        .sidebar.open #sidebarToggle { transform: translateY(-50%) rotate(180deg); }

        .nav-list { list-style: none; padding: 10px 8px; margin: 0; }
        .nav-item { margin-bottom: 4px; position: relative; }

        .nav-link {
            display: flex;
            align-items: center;
            height: 50px;
            border-radius: 10px;
            padding: 0 14px;
            cursor: pointer;
            color: #cbd5e1;
            transition: background 0.25s ease, color 0.25s ease;
            user-select: none;
        }
        .nav-link:hover, .nav-item.active > .nav-link { background: rgba(255,255,255,0.08); color: #fff; }

        .nav-link img { width: 22px; height: 22px; flex-shrink: 0; }
        .nav-link .nav-label {
            margin-left: 14px;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s ease 0.1s;
            pointer-events: none;
            flex: 1;
        }
        .sidebar.open .nav-link .nav-label { opacity: 1; pointer-events: auto; }

        .nav-link .chevron {
            font-size: 12px;
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease 0.1s;
        }
        .sidebar.open .nav-link .chevron { opacity: 1; }
        .nav-item.expanded .nav-link .chevron { transform: rotate(90deg); }

        .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease;
            list-style: none;
            padding: 0 0 0 14px;
            margin: 0;
        }
        .nav-item.expanded .submenu { max-height: 300px; }
        .sidebar:not(.open) .submenu { max-height: 0 !important; }

        .submenu li a {
            display: block;
            color: #94a3b8;
            font-size: 13px;
            padding: 9px 12px 9px 40px;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.2s ease, color 0.2s ease, padding-left 0.2s ease;
        }
        .submenu li a:hover { background: rgba(47,111,237,0.15); color: #fff; padding-left: 44px; }

        /* Tooltip for collapsed state (desktop only) */
        .tooltip {
            position: absolute;
            left: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%);
            background: #fff;
            color: var(--navy);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
            z-index: 10;
        }
        .sidebar:not(.open) .nav-item:hover .tooltip { opacity: 1; }
        .sidebar.open .tooltip { display: none; }

        .sidebar-footer {
            padding: 14px 8px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        /* ===== TOP BAR (mobile) ===== */
        .mobile-topbar {
            display: none;
            background: var(--navy);
            color: #fff;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            position: sticky;
            top: 0;
            z-index: 150;
        }
        .mobile-topbar .brand { display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 14px; }
        .mobile-topbar img { width: 30px; height: 30px; border-radius: 50%; }
        #hamburgerBtn { background: none; border: none; color: #fff; font-size: 24px; cursor: pointer; }

        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 190;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .overlay.show { display: block; opacity: 1; }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-collapsed);
            transition: margin-left 0.4s ease;
            padding: 30px;
        }

        .desktop-topbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-bottom: 24px;
        }
        .user-badge {
            display: flex; align-items: center; gap: 8px;
            background: #fff; padding: 8px 16px; border-radius: 30px;
            box-shadow: 0 3px 10px rgba(10,37,64,0.08); font-size: 14px; color: var(--navy); font-weight: 500;
        }
        .user-badge img { width: 26px; height: 26px; }
        .user-badge a { color: #94a3b8; margin-left: 6px; font-size: 13px; }
        .user-badge a:hover { color: #dc2626; }

        .kpi-row { display: flex; gap: 16px; margin-bottom: 30px; flex-wrap: wrap; }
        .kpi-card {
            flex: 1; min-width: 160px; background: #fff; border-radius: 14px;
            padding: 20px 22px; box-shadow: 0 6px 20px rgba(10,37,64,0.06);
            border-top: 3px solid var(--accent);
            opacity: 0; transform: translateY(16px);
            animation: fadeUp 0.5s ease forwards;
        }
        .kpi-card:nth-child(2) { animation-delay: 0.08s; border-top-color: #f59e0b; }
        .kpi-card:nth-child(3) { animation-delay: 0.16s; border-top-color: #16a34a; }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
        .kpi-card .num { font-size: 32px; font-weight: 700; color: var(--navy); }
        .kpi-card .label { font-size: 13px; color: #64748b; margin-top: 4px; }

        .welcome-card {
            background: #fff; border-radius: 14px; padding: 24px;
            box-shadow: 0 6px 20px rgba(10,37,64,0.06);
            opacity: 0; animation: fadeUp 0.5s ease 0.2s forwards;
        }
        .welcome-card h2 { color: var(--navy); margin: 0 0 8px; }
        .welcome-card p { color: #64748b; margin: 0; font-size: 14px; }

        /* ===== RESPONSIVE: MOBILE ===== */
        @media (max-width: 768px) {
            .sidebar {
                width: 260px;
                transform: translateX(-100%);
                transition: transform 0.35s ease;
            }
            .sidebar.mobile-open { transform: translateX(0); }
            .sidebar.mobile-open .nav-label,
            .sidebar.mobile-open .chevron,
            .sidebar.mobile-open .brand-name { opacity: 1; pointer-events: auto; }
            .sidebar.mobile-open .nav-item.expanded .submenu { max-height: 300px; }
            .sidebar .submenu { max-height: 0 !important; }
            .sidebar.mobile-open .submenu { max-height: unset; }
            .sidebar.mobile-open .nav-item.expanded .submenu { max-height: 300px !important; }

            #sidebarToggle { display: none; }
            .mobile-topbar { display: flex; }
            .main-content { margin-left: 0; padding: 20px 16px; }
            .desktop-topbar { display: none; }
            .kpi-row { gap: 10px; }
            .kpi-card { min-width: 100px; padding: 14px 16px; }
            .kpi-card .num { font-size: 24px; }
        }
    </style>
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="assets/cms-logo.png" alt="CMS">
            <span class="brand-name">CMS</span>
            <button id="sidebarToggle">&#9776;</button>
        </div>

        <ul class="nav-list">
            <li class="nav-item">
                <div class="nav-link" data-toggle="complainant">
                    <img src="assets/complaint-logo.png" alt="">
                    <span class="nav-label">Complainant Mgmt</span>
                    <span class="chevron">&#9656;</span>
                </div>
                <span class="tooltip">Complainant Management</span>
                <ul class="submenu">
                    <li><a href="complainant_register.php">Register Complainant</a></li>
                    <li><a href="complainant_list.php">Complainant List / Search</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <div class="nav-link" data-toggle="complaint">
                    <img src="assets/complaint-logo.png" alt="">
                    <span class="nav-label">Complaint Mgmt</span>
                    <span class="chevron">&#9656;</span>
                </div>
                <span class="tooltip">Complaint Management</span>
                <ul class="submenu">
                    <li><a href="category_manage.php">Manage Categories</a></li>
                    <li><a href="complaint_register.php">File a Complaint</a></li>
                    <li><a href="complaint_list.php">Complaint List / Search</a></li>
                    <li><a href="complaint_close.php">Complaint Closure</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <div class="nav-link" data-toggle="assignment">
                    <img src="assets/assignment-logo.png" alt="">
                    <span class="nav-label">Assignment</span>
                    <span class="chevron">&#9656;</span>
                </div>
                <span class="tooltip">Assignment</span>
                <ul class="submenu">
                    <li><a href="personnel_manage.php">Personnel Management</a></li>
                    <li><a href="assignment_form.php">Assign Complaint</a></li>
                    <li><a href="assignment_list.php">Assignment List</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <div class="nav-link" data-toggle="investigation">
                    <img src="assets/investigation-logo.png" alt="">
                    <span class="nav-label">Investigation</span>
                    <span class="chevron">&#9656;</span>
                </div>
                <span class="tooltip">Investigation</span>
                <ul class="submenu">
                    <li><a href="investigation_form.php">Create Investigation</a></li>
                    <li><a href="investigation_list.php">Investigation List</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <div class="nav-link" data-toggle="action">
                    <img src="assets/action-logo.png" alt="">
                    <span class="nav-label">Action</span>
                    <span class="chevron">&#9656;</span>
                </div>
                <span class="tooltip">Action</span>
                <ul class="submenu">
                    <li><a href="action_form.php">Create Action</a></li>
                    <li><a href="action_list.php">Action List / Monitoring</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <div class="nav-link" data-toggle="resolution">
                    <img src="assets/resolution-logo.png" alt="">
                    <span class="nav-label">Resolution</span>
                    <span class="chevron">&#9656;</span>
                </div>
                <span class="tooltip">Resolution</span>
                <ul class="submenu">
                    <li><a href="resolution_form.php">Record Resolution</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <div class="nav-link" data-toggle="reports">
                    <img src="assets/reports-logo.png" alt="">
                    <span class="nav-label">Reports</span>
                    <span class="chevron">&#9656;</span>
                </div>
                <span class="tooltip">Reports and Dashboard</span>
                <ul class="submenu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="report_summary.php">Summary Report</a></li>
                    <li><a href="report_category.php">Category Report</a></li>
                    <li><a href="report_resolution.php">Resolution Report</a></li>
                    <li><a href="report_investigator.php">Investigator Report</a></li>
                    <li><a href="report_action.php">Action Report</a></li>
                    <li><a href="report_trend.php">Trend Analysis</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <div class="nav-link" data-toggle="documentation">
                    <img src="assets/documentation-logo.png" alt="">
                    <span class="nav-label">Documentation</span>
                    <span class="chevron">&#9656;</span>
                </div>
                <span class="tooltip">Documentation</span>
                <ul class="submenu">
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
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="nav-item">
                <a href="logout.php" class="nav-link" style="text-decoration:none;">
                    <img src="assets/account-logo.png" alt="">
                    <span class="nav-label">Logout</span>
                </a>
                <span class="tooltip">Logout</span>
            </div>
        </div>
    </nav>

    <div class="mobile-topbar">
        <div class="brand">
            <img src="assets/cms-logo.png" alt="CMS">
            <span>Complaint Management System</span>
        </div>
        <button id="hamburgerBtn">&#9776;</button>
    </div>

    <div class="main-content" id="mainContent">
        <div class="desktop-topbar">
            <div class="user-badge">
                <img src="assets/account-logo.png" alt="">
                <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                <a href="logout.php">Logout</a>
            </div>
        </div>

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

        <div class="welcome-card">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?> 👋</h2>
            <p>Use the sidebar to navigate through Complainant Management, Complaint Management, Assignment, Investigation, Action, Resolution, Reports, and Documentation.</p>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const overlay = document.getElementById('overlay');

        // Desktop expand/collapse
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Mobile off-canvas
        hamburgerBtn.addEventListener('click', () => {
            sidebar.classList.add('mobile-open');
            overlay.classList.add('show');
        });
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('show');
        });

        // Submenu accordion
        document.querySelectorAll('.nav-link[data-toggle]').forEach(link => {
            link.addEventListener('click', () => {
                const isMobile = window.innerWidth <= 768;
                const isOpenState = isMobile ? sidebar.classList.contains('mobile-open') : sidebar.classList.contains('open');

                if (!isOpenState) {
                    // Auto-expand sidebar first when clicking an icon while collapsed
                    if (isMobile) {
                        sidebar.classList.add('mobile-open');
                        overlay.classList.add('show');
                    } else {
                        sidebar.classList.add('open');
                    }
                    return;
                }

                const parentItem = link.closest('.nav-item');
                const wasExpanded = parentItem.classList.contains('expanded');

                document.querySelectorAll('.nav-item.expanded').forEach(item => {
                    if (item !== parentItem) item.classList.remove('expanded');
                });

                parentItem.classList.toggle('expanded', !wasExpanded);
            });
        });
    </script>
</body>
</html>