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

if (isset($_POST['update_status'])) {
    $action_id = $_POST['action_id'];
    $new_status = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE actions SET status = ? WHERE action_id = ?");
    $stmt->bind_param("si", $new_status, $action_id);
    $stmt->execute();
    header("Location: action_list.php");
    exit();
}

$result = $conn->query(
    "SELECT a.*, c.complaint_number, u.full_name AS responsible_name
     FROM actions a
     JOIN complaints c ON a.complaint_id = c.complaint_id
     JOIN users u ON a.responsible_person = u.user_id
     ORDER BY a.action_id DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Action Monitoring</title>
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

        .page-wrap { max-width: 1100px; margin: 0 auto; padding: 30px 20px; }

        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .page-header h2 { margin: 0; color: var(--navy); }
        .btn-primary {
            background: var(--accent); color: #fff; padding: 10px 18px;
            border-radius: 8px; font-weight: 600; font-size: 14px; transition: 0.2s;
        }
        .btn-primary:hover { background: var(--navy); text-decoration: none; }

        .table-card {
            background: #fff; border-radius: 14px; box-shadow: 0 6px 20px rgba(10,37,64,0.06);
            overflow: hidden; opacity: 0; animation: fadeIn 0.4s ease forwards;
        }
        @keyframes fadeIn { to { opacity: 1; } }
        table { margin: 0; box-shadow: none; }
        th { background: var(--navy); }

        .status-select {
            padding: 6px 10px; border-radius: 6px; border: 1px solid #e2e8f0;
            font-family: 'Poppins', sans-serif; font-size: 13px;
        }

        .overdue-row td { background-color: #fef2f2 !important; }
        .overdue-tag {
            color: #dc2626; font-weight: 700; font-size: 11px; text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="index.php">&larr; Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="page-header">
            <h2>Action Monitoring</h2>
            <a href="action_form.php" class="btn-primary">+ New Action</a>
        </div>

        <div class="table-card">
            <table>
                <tr>
                    <th>Complaint #</th>
                    <th>Assigned Date</th>
                    <th>Action Type</th>
                    <th>Responsible</th>
                    <th>Target Date</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()):
                    $isOverdue = ($row['target_date'] && $row['target_date'] < date("Y-m-d") && $row['status'] != 'Completed' && $row['status'] != 'Verified');
                ?>
                <tr <?php if ($isOverdue) echo 'class="overdue-row"'; ?>>
                    <td><?php echo htmlspecialchars($row['complaint_number']); ?></td>
                    <td><?php echo date("Y-m-d", strtotime($row['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($row['action_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['responsible_name']); ?></td>
                    <td><?php echo $row['target_date'] ?: '-'; ?></td>
                    <td>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="action_id" value="<?php echo $row['action_id']; ?>">
                            <select class="status-select" name="new_status" onchange="this.form.submit()">
                                <option value="Pending" <?php if ($row['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                <option value="In Progress" <?php if ($row['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                                <option value="Completed" <?php if ($row['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                                <option value="Verified" <?php if ($row['status'] == 'Verified') echo 'selected'; ?>>Verified</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </td>
                    <td><?php echo $isOverdue ? "<span class='overdue-tag'>Overdue</span>" : "-"; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>