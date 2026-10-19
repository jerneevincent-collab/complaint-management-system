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
    <title>Technical Documentation</title>
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
            max-width:860px !important;
            margin:0 auto !important;
            padding:30px 20px 60px !important;
        }

        .doc-card {
            background:#fff;
            border-radius:16px;
            padding:24px 28px;
            margin-bottom:20px;
            box-shadow:0 8px 24px rgba(10,37,64,0.07);
            opacity:0;
            animation:fadeUp 0.5s ease forwards;
        }
        .doc-card h3 { margin:0 0 10px 0; color:var(--navy); font-size:16px; }
        .doc-card p { margin:0; font-size:13px; line-height:1.7; color:#475569; }

        .table-scroll { overflow-x:auto; }
        .doc-card table { width:100%; margin:0; box-shadow:none; border-radius:0; border-collapse:collapse; font-size:13px; }
        .doc-card th {
            background:var(--navy);
            color:#fff;
            text-align:left;
            padding:10px 12px;
            text-transform:none;
            font-size:13px;
            letter-spacing:0;
            white-space:nowrap;
        }
        .doc-card td { padding:10px 12px; border-bottom:1px solid #eef1f5; font-size:13px; vertical-align:top; line-height:1.5; }
        .doc-card tr:last-child td { border-bottom:none; }
        .doc-card td:first-child { font-weight:600; color:var(--navy); }

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
        <h2>Technical Documentation - Complaint Management System</h2>
    </div>

    <div class="page-wrap">

        <div class="doc-card" style="animation-delay:0.04s;">
            <h3>1. System Architecture</h3>
            <p>Client-server architecture. The browser (client) sends requests to Apache, which runs PHP scripts that communicate with a MySQL database. Sessions manage user authentication and role-based access.</p>
        </div>

        <div class="doc-card" style="animation-delay:0.08s;">
            <h3>2. Database Design</h3>
            <p>14 core tables: users, roles, departments, complainants, complaints, complaint_categories, assignments, assignment_history, investigations, investigation_findings, investigation_evidence, actions, resolutions, notifications, and audit_logs.</p>
        </div>

        <div class="doc-card" style="animation-delay:0.12s;">
            <h3>3. Entity Relationship Diagram (ERD)</h3>
            <p>See the ERD produced in Lab 2, showing relationships such as one complainant to many complaints, one complaint to one assignment/investigation/resolution, and one investigation to many findings.</p>
        </div>

        <div class="doc-card" style="animation-delay:0.16s;">
            <h3>4. Data Dictionary (Sample)</h3>
            <div class="table-scroll">
                <table>
                    <tr><th>Table</th><th>Key Fields</th><th>Description</th></tr>
                    <tr><td>complaints</td><td>complaint_id (PK), complainant_id (FK), category_id (FK), status</td><td>Stores each filed complaint and its current workflow status</td></tr>
                    <tr><td>assignments</td><td>assignment_id (PK), complaint_id (FK), assigned_to (FK)</td><td>Links complaints to responsible personnel</td></tr>
                    <tr><td>investigations</td><td>investigation_id (PK), complaint_id (FK), investigator_id (FK)</td><td>Tracks the investigation process for a complaint</td></tr>
                    <tr><td>resolutions</td><td>resolution_id (PK), complaint_id (FK), resolved_by (FK)</td><td>Stores how and when a complaint was resolved</td></tr>
                </table>
            </div>
        </div>

        <div class="doc-card" style="animation-delay:0.20s;">
            <h3>5. Installation Procedure</h3>
            <p>1. Install XAMPP. 2. Place project folder in htdocs. 3. Create database "complaint_management_db" in phpMyAdmin. 4. Import the provided SQL backup file. 5. Start Apache and MySQL.</p>
        </div>

        <div class="doc-card" style="animation-delay:0.24s;">
            <h3>6. Configuration</h3>
            <p>Database connection settings are in each PHP file (host: localhost, user: root, no password, database: complaint_management_db). No external dependencies required except an internet connection for Chart.js (dashboard).</p>
        </div>

        <div class="doc-card" style="animation-delay:0.28s;">
            <h3>7. Source Code Structure</h3>
            <p>Flat file structure — each module has its own form (e.g. complaint_register.php) and its own save/process handler (e.g. save_complaint.php). Reports and testing documentation are separate standalone pages.</p>
        </div>

        <div class="doc-card" style="animation-delay:0.32s;">
            <h3>8. Security Mechanisms</h3>
            <p>Session-based login, password hashing (password_hash/password_verify), role-based access control, prepared statements (SQL injection protection), file upload type/size validation.</p>
        </div>

        <div class="doc-card" style="animation-delay:0.36s;">
            <h3>9. Backup/Recovery Procedures</h3>
            <p>Database is backed up via phpMyAdmin's Export function (SQL format) and stored in the project's GitHub repository. Recovery is done via Import in phpMyAdmin using the latest backup file.</p>
        </div>

        <div class="doc-card" style="animation-delay:0.40s;">
            <h3>10. Maintenance Procedures</h3>
            <p>Regular database backups after major updates. Personnel accounts can be deactivated (not deleted) to preserve historical records. Audit logs track all closures and logins for accountability.</p>
        </div>

    </div>
</body>
</html>