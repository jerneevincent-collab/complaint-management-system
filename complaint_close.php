<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [1, 5])) {
    die("Access denied. Only Administrators and Supervisors can close complaints.");
}

$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complaints = $conn->query(
    "SELECT c.complaint_id, c.complaint_number, c.subject, r.resolution_description, r.resolution_date
     FROM complaints c
     JOIN resolutions r ON c.complaint_id = r.complaint_id
     WHERE c.status = 'Resolved'
     ORDER BY c.complaint_id DESC"
);
$personnel = $conn->query("SELECT user_id, full_name FROM users WHERE status = 'active' ORDER BY full_name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Closure</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        :root { --navy: #0a2540; --accent: #2f6fed; }
        body { background: #f4f7fb; }

        .topbar {
            background: var(--navy); color: #fff; display: flex;
            align-items: center; justify-content: space-between; padding: 14px 30px;
        }
        .topbar a { color: #cbd5e1; font-weight: 500; }
        .topbar a:hover { color: #fff; }

        .page-wrap { max-width: 700px; margin: 0 auto; padding: 30px 20px; }
        .page-wrap h2 { color: var(--navy); }

        .closure-card {
            background: #fff; border-radius: 14px; padding: 20px 24px;
            box-shadow: 0 6px 20px rgba(10,37,64,0.06); margin-bottom: 16px;
            opacity: 0; transform: translateY(16px);
            animation: fadeUp 0.5s ease forwards;
        }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        .closure-card .complaint-tag {
            display: inline-block; background: #eef2f9; color: var(--navy);
            font-weight: 700; padding: 5px 10px; border-radius: 6px; font-size: 13px; margin-bottom: 8px;
        }
        .closure-card .resolution-text { font-size: 14px; color: #475569; margin-bottom: 16px; }
        .closure-card .resolution-text strong { color: var(--navy); }

        .closure-card form {
            box-shadow: none; padding: 0; max-width: 100%; margin: 0; background: none;
            display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;
        }
        .closure-card label {
            font-size: 12px; font-weight: 600; color: var(--navy);
            text-transform: uppercase; letter-spacing: 0.03em; display: block; margin-bottom: 4px;
        }
        .closure-card select {
            background: #f4f7fb; border: 1px solid #e2e8f0; border-radius: 8px;
            font-family: 'Poppins', sans-serif; padding: 10px 12px; min-width: 200px;
        }
        .closure-card button {
            background: #dc2626; border: none; padding: 11px 20px;
            border-radius: 8px; color: #fff; font-weight: 700; cursor: pointer; transition: 0.2s;
        }
        .closure-card button:hover { background: #b91c1c; }

        .empty-msg { color: #94a3b8; text-align: center; padding: 30px; }
        .success-msg { color: #16a34a !important; }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="index.php">&larr; Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <h2>Complaint Closure</h2>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success') echo "<p class='success-msg'>Complaint closed successfully!</p>"; ?>

        <?php if ($complaints->num_rows == 0): ?>
            <div class="closure-card"><p class="empty-msg">No resolved complaints are waiting to be closed.</p></div>
        <?php endif; ?>

        <?php while ($c = $complaints->fetch_assoc()): ?>
        <div class="closure-card">
            <span class="complaint-tag"><?php echo htmlspecialchars($c['complaint_number']); ?></span>
            <div class="resolution-text">
                <strong><?php echo htmlspecialchars($c['subject']); ?></strong><br>
                Resolution: <?php echo htmlspecialchars($c['resolution_description']); ?> (<?php echo $c['resolution_date']; ?>)
            </div>

            <form action="save_closure.php" method="POST">
                <input type="hidden" name="complaint_id" value="<?php echo $c['complaint_id']; ?>">
                <div>
                    <label>Closed By</label>
                    <select name="closed_by" required>
                        <option value="">-- Select Personnel --</option>
                        <?php $personnel->data_seek(0); while ($p = $personnel->fetch_assoc()): ?>
                            <option value="<?php echo $p['user_id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" onclick="return confirm('Confirm closure? This complaint will no longer be editable.');">Confirm Closure</button>
            </form>
        </div>
        <?php endwhile; ?>
    </div>
</body>
</html>