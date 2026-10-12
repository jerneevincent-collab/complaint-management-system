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

$result = $conn->query(
    "SELECT u.full_name,
        COUNT(i.investigation_id) AS total_assigned,
        SUM(CASE WHEN i.status = 'Completed' THEN 1 ELSE 0 END) AS total_completed,
        SUM(CASE WHEN i.status != 'Completed' THEN 1 ELSE 0 END) AS total_pending,
        SUM(CASE WHEN i.status != 'Completed' AND i.target_completion_date < CURDATE() THEN 1 ELSE 0 END) AS total_overdue,
        AVG(CASE WHEN i.status = 'Completed' THEN DATEDIFF(i.target_completion_date, i.start_date) ELSE NULL END) AS avg_days
     FROM users u
     LEFT JOIN investigations i ON u.user_id = i.investigator_id
     WHERE u.role_id = 3
     GROUP BY u.user_id, u.full_name"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Investigator Performance Report</title>
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
        }
        .table-card td { padding:10px 12px; border-bottom:1px solid #eef1f5; font-size:13px; }
        .table-card tr:last-child td { border-bottom:none; }
        .table-card th:not(:first-child), .table-card td:not(:first-child) { text-align:center; }

        .empty-note { color:#6b7787; font-size:13px; margin:0; }

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
        <h2>Investigator Performance Report</h2>
    </div>

    <div class="page-wrap">
        <div class="table-card">
            <h3>Investigator Workload</h3>
            <?php if ($result->num_rows > 0): ?>
            <div class="table-scroll">
                <table>
                    <tr>
                        <th>Investigator</th>
                        <th>Assigned</th>
                        <th>Completed</th>
                        <th>Pending</th>
                        <th>Overdue</th>
                        <th>Avg. Time (days)</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo $row['total_assigned']; ?></td>
                        <td><?php echo $row['total_completed']; ?></td>
                        <td><?php echo $row['total_pending']; ?></td>
                        <td><?php echo $row['total_overdue']; ?></td>
                        <td><?php echo $row['avg_days'] !== null ? round($row['avg_days'], 1) : 'N/A'; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <?php else: ?><p class="empty-note">No investigators yet.</p><?php endif; ?>
        </div>
    </div>
</body>
</html>