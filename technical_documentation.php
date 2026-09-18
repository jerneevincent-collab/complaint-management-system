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
</head>
<body>
    <h2>Technical Documentation - Complaint Management System</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <h3>1. System Architecture</h3>
    <p>Client-server architecture. The browser (client) sends requests to Apache, which runs PHP scripts that communicate with a MySQL database. Sessions manage user authentication and role-based access.</p>

    <h3>2. Database Design</h3>
    <p>14 core tables: users, roles, departments, complainants, complaints, complaint_categories, assignments, assignment_history, investigations, investigation_findings, investigation_evidence, actions, resolutions, notifications, and audit_logs.</p>

    <h3>3. Entity Relationship Diagram (ERD)</h3>
    <p>See the ERD produced in Lab 2, showing relationships such as one complainant to many complaints, one complaint to one assignment/investigation/resolution, and one investigation to many findings.</p>

    <h3>4. Data Dictionary (Sample)</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr><th>Table</th><th>Key Fields</th><th>Description</th></tr>
        <tr><td>complaints</td><td>complaint_id (PK), complainant_id (FK), category_id (FK), status</td><td>Stores each filed complaint and its current workflow status</td></tr>
        <tr><td>assignments</td><td>assignment_id (PK), complaint_id (FK), assigned_to (FK)</td><td>Links complaints to responsible personnel</td></tr>
        <tr><td>investigations</td><td>investigation_id (PK), complaint_id (FK), investigator_id (FK)</td><td>Tracks the investigation process for a complaint</td></tr>
        <tr><td>resolutions</td><td>resolution_id (PK), complaint_id (FK), resolved_by (FK)</td><td>Stores how and when a complaint was resolved</td></tr>
    </table>

    <h3>5. Installation Procedure</h3>
    <p>1. Install XAMPP. 2. Place project folder in htdocs. 3. Create database "complaint_management_db" in phpMyAdmin. 4. Import the provided SQL backup file. 5. Start Apache and MySQL.</p>

    <h3>6. Configuration</h3>
    <p>Database connection settings are in each PHP file (host: localhost, user: root, no password, database: complaint_management_db). No external dependencies required except an internet connection for Chart.js (dashboard).</p>

    <h3>7. Source Code Structure</h3>
    <p>Flat file structure — each module has its own form (e.g. complaint_register.php) and its own save/process handler (e.g. save_complaint.php). Reports and testing documentation are separate standalone pages.</p>

    <h3>8. Security Mechanisms</h3>
    <p>Session-based login, password hashing (password_hash/password_verify), role-based access control, prepared statements (SQL injection protection), file upload type/size validation.</p>

    <h3>9. Backup/Recovery Procedures</h3>
    <p>Database is backed up via phpMyAdmin's Export function (SQL format) and stored in the project's GitHub repository. Recovery is done via Import in phpMyAdmin using the latest backup file.</p>

    <h3>10. Maintenance Procedures</h3>
    <p>Regular database backups after major updates. Personnel accounts can be deactivated (not deleted) to preserve historical records. Audit logs track all closures and logins for accountability.</p>
</body>
</html>