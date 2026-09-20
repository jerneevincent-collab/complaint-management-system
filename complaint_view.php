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

$id = $_GET['id'];
$stmt = $conn->prepare(
    "SELECT c.*, co.full_name AS complainant_name, cc.category_name
     FROM complaints c
     JOIN complainants co ON c.complainant_id = co.complainant_id
     JOIN complaint_categories cc ON c.category_id = cc.category_id
     WHERE c.complaint_id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();

if (!$complaint) {
    die("Complaint not found.");
}

$statusClass = strtolower(str_replace(' ', '-', $complaint['status']));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Details</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        :root { --navy: #0a2540; --accent: #2f6fed; }
        body { background: #f4f7fb; }

        .topbar {
            background: var(--navy);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 30px;
        }
        .topbar a { color: #cbd5e1; font-weight: 500; }
        .topbar a:hover { color: #fff; }

        .page-wrap {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .detail-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(10,37,64,0.08);
            padding: 30px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp 0.5s ease forwards;
        }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        .detail-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        .detail-header h2 { margin: 0; color: var(--navy); font-size: 22px; }

        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-submitted { background: #dbeafe; color: #1e40af; }
        .status-for-assignment, .status-assigned { background: #fef3c7; color: #92400e; }
        .status-under-investigation, .status-action-required, .status-for-resolution { background: #fed7aa; color: #9a3412; }
        .status-resolved { background: #d1fae5; color: #065f46; }
        .status-closed { background: #e2e8f0; color: #475569; }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }
        .detail-item label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .detail-item .value {
            color: var(--navy);
            font-size: 15px;
        }
        .detail-item.full { grid-column: 1 / -1; }

        .status-update-box {
            background: #f4f7fb;
            border-radius: 12px;
            padding: 18px;
            margin-top: 10px;
        }
        .status-update-box h3 { margin: 0 0 10px; font-size: 14px; color: var(--navy); }
        .status-update-box form {
            display: flex;
            gap: 10px;
            box-shadow: none;
            padding: 0;
            max-width: 100%;
            margin: 0;
            background: none;
        }
        .status-update-box select {
            flex: 1;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
        }
        .status-update-box button {
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            margin: 0;
            transition: 0.2s;
        }
        .status-update-box button:hover { background: var(--navy); }

        .closed-notice {
            background: #f1f5f9;
            border-left: 4px solid #64748b;
            padding: 12px 16px;
            border-radius: 8px;
            color: #475569;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="complaint_list.php">&larr; Complaint List</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="detail-card">
            <div class="detail-header">
                <h2><?php echo htmlspecialchars($complaint['complaint_number']); ?></h2>
                <span class="status-badge status-<?php echo $statusClass; ?>"><?php echo htmlspecialchars($complaint['status']); ?></span>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <label>Complainant</label>
                    <div class="value"><?php echo htmlspecialchars($complaint['complainant_name']); ?></div>
                </div>
                <div class="detail-item">
                    <label>Category</label>
                    <div class="value"><?php echo htmlspecialchars($complaint['category_name']); ?></div>
                </div>
                <div class="detail-item full">
                    <label>Subject</label>
                    <div class="value"><?php echo htmlspecialchars($complaint['subject']); ?></div>
                </div>
                <div class="detail-item full">
                    <label>Description</label>
                    <div class="value"><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></div>
                </div>
                <div class="detail-item">
                    <label>Date Filed</label>
                    <div class="value"><?php echo $complaint['date_filed']; ?></div>
                </div>
                <div class="detail-item">
                    <label>Location</label>
                    <div class="value"><?php echo htmlspecialchars($complaint['location'] ?: '—'); ?></div>
                </div>
                <div class="detail-item">
                    <label>Priority</label>
                    <div class="value"><?php echo ucfirst($complaint['priority']); ?></div>
                </div>
            </div>

            <?php if ($complaint['status'] != 'Closed'): ?>
            <div class="status-update-box">
                <h3>Update Status</h3>
                <form action="update_complaint_status.php" method="POST">
                    <input type="hidden" name="complaint_id" value="<?php echo $complaint['complaint_id']; ?>">
                    <select name="new_status">
                        <option value="Submitted" <?php if ($complaint['status'] == 'Submitted') echo 'selected'; ?>>Submitted</option>
                        <option value="For Assignment" <?php if ($complaint['status'] == 'For Assignment') echo 'selected'; ?>>For Assignment</option>
                        <option value="Assigned" <?php if ($complaint['status'] == 'Assigned') echo 'selected'; ?>>Assigned</option>
                        <option value="Under Investigation" <?php if ($complaint['status'] == 'Under Investigation') echo 'selected'; ?>>Under Investigation</option>
                        <option value="Action Required" <?php if ($complaint['status'] == 'Action Required') echo 'selected'; ?>>Action Required</option>
                        <option value="For Resolution" <?php if ($complaint['status'] == 'For Resolution') echo 'selected'; ?>>For Resolution</option>
                        <option value="Resolved" <?php if ($complaint['status'] == 'Resolved') echo 'selected'; ?>>Resolved</option>
                        <option value="Closed" <?php if ($complaint['status'] == 'Closed') echo 'selected'; ?>>Closed</option>
                    </select>
                    <button type="submit">Update</button>
                </form>
            </div>
            <?php else: ?>
            <div class="closed-notice">This complaint is closed and can no longer be modified.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>