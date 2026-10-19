<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    die("Access denied. Administrator access only.");
}

$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query(
    "SELECT a.*, u.full_name FROM audit_logs a
     LEFT JOIN users u ON a.user_id = u.user_id
     ORDER BY a.log_id DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Audit Trail</title>
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
        .table-card td { padding:10px 12px; border-bottom:1px solid #eef1f5; font-size:13px; }
        .table-card tr:last-child td { border-bottom:none; }
        .table-card td.nowrap { white-space:nowrap; }

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
        <h2>Audit Trail</h2>
    </div>

    <div class="page-wrap">

        <div class="table-card">
            <h3>System Activity Log</h3>
            <?php if ($result->num_rows > 0): ?>
            <div class="table-scroll">
                <table>
                    <tr>
                        <th>User</th>
                        <th>Activity</th>
                        <th>Record Affected</th>
                        <th>Date/Time</th>
                        <th>IP Address</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['full_name'] ?? 'Unknown'); ?></td>
                        <td><?php echo htmlspecialchars($row['activity']); ?></td>
                        <td><?php echo htmlspecialchars($row['record_affected'] ?? '-'); ?></td>
                        <td class="nowrap"><?php echo $row['created_at']; ?></td>
                        <td><?php echo htmlspecialchars($row['ip_address'] ?? '-'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <?php else: ?><p class="empty-note">No activity logged yet.</p><?php endif; ?>
        </div>

    </div>
</body>
</html>