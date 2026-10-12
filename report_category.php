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

$byCategory = $conn->query(
    "SELECT cc.category_name, COUNT(*) AS total
     FROM complaints c
     JOIN complaint_categories cc ON c.category_id = cc.category_id
     GROUP BY cc.category_name
     ORDER BY total DESC"
);

$byPriority = $conn->query(
    "SELECT priority, COUNT(*) AS total FROM complaints GROUP BY priority ORDER BY total DESC"
);

$byStatus = $conn->query(
    "SELECT status, COUNT(*) AS total FROM complaints GROUP BY status ORDER BY total DESC"
);

$byDate = $conn->query(
    "SELECT date_filed, COUNT(*) AS total FROM complaints GROUP BY date_filed ORDER BY date_filed DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Category Report</title>
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
        .table-card th:last-child, .table-card td:last-child { text-align:center; width:120px; }

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
        <h2>Complaint Category Report</h2>
    </div>

    <div class="page-wrap">

        <div class="table-card">
            <h3>By Category</h3>
            <?php if ($byCategory->num_rows > 0): ?>
            <div class="table-scroll">
                <table>
                    <tr><th>Category</th><th>Total</th></tr>
                    <?php while ($row = $byCategory->fetch_assoc()): ?>
                    <tr><td><?php echo htmlspecialchars($row['category_name']); ?></td><td><?php echo $row['total']; ?></td></tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <?php else: ?><p class="empty-note">No data yet.</p><?php endif; ?>
        </div>

        <div class="table-card">
            <h3>By Priority</h3>
            <?php if ($byPriority->num_rows > 0): ?>
            <div class="table-scroll">
                <table>
                    <tr><th>Priority</th><th>Total</th></tr>
                    <?php while ($row = $byPriority->fetch_assoc()): ?>
                    <tr><td><?php echo htmlspecialchars($row['priority']); ?></td><td><?php echo $row['total']; ?></td></tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <?php else: ?><p class="empty-note">No data yet.</p><?php endif; ?>
        </div>

        <div class="table-card">
            <h3>By Status</h3>
            <?php if ($byStatus->num_rows > 0): ?>
            <div class="table-scroll">
                <table>
                    <tr><th>Status</th><th>Total</th></tr>
                    <?php while ($row = $byStatus->fetch_assoc()): ?>
                    <tr><td><?php echo htmlspecialchars($row['status']); ?></td><td><?php echo $row['total']; ?></td></tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <?php else: ?><p class="empty-note">No data yet.</p><?php endif; ?>
        </div>

        <div class="table-card">
            <h3>By Date Filed</h3>
            <?php if ($byDate->num_rows > 0): ?>
            <div class="table-scroll">
                <table>
                    <tr><th>Date Filed</th><th>Total</th></tr>
                    <?php while ($row = $byDate->fetch_assoc()): ?>
                    <tr><td><?php echo $row['date_filed']; ?></td><td><?php echo $row['total']; ?></td></tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <?php else: ?><p class="empty-note">No data yet.</p><?php endif; ?>
        </div>

    </div>
</body>
</html>