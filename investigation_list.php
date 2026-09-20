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

if (isset($_POST['update_investigation_status'])) {
    $investigation_id = $_POST['investigation_id'];
    $new_status = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE investigations SET status = ? WHERE investigation_id = ?");
    $stmt->bind_param("si", $new_status, $investigation_id);
    $stmt->execute();
    header("Location: investigation_list.php");
    exit();
}

$result = $conn->query(
    "SELECT i.*, c.complaint_number, c.subject, u.full_name AS investigator_name
     FROM investigations i
     JOIN complaints c ON i.complaint_id = c.complaint_id
     JOIN users u ON i.investigator_id = u.user_id
     ORDER BY i.investigation_id DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Investigation List</title>
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

        .action-links a { margin-right: 10px; font-size: 13px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="index.php">&larr; Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="page-header">
            <h2>Investigations</h2>
            <a href="investigation_form.php" class="btn-primary">+ New Investigation</a>
        </div>

        <div class="table-card">
            <table>
                <tr>
                    <th>Complaint #</th>
                    <th>Subject</th>
                    <th>Investigator</th>
                    <th>Start Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['complaint_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['subject']); ?></td>
                    <td><?php echo htmlspecialchars($row['investigator_name']); ?></td>
                    <td><?php echo $row['start_date']; ?></td>
                    <td>
                        <form action="update_investigation_status.php" method="POST" style="margin:0;">
                            <input type="hidden" name="investigation_id" value="<?php echo $row['investigation_id']; ?>">
                            <select class="status-select" name="new_status" onchange="this.form.submit()">
                                <option value="Assigned" <?php if ($row['status'] == 'Assigned') echo 'selected'; ?>>Assigned</option>
                                <option value="In Progress" <?php if ($row['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                                <option value="Evidence Gathering" <?php if ($row['status'] == 'Evidence Gathering') echo 'selected'; ?>>Evidence Gathering</option>
                                <option value="Findings Prepared" <?php if ($row['status'] == 'Findings Prepared') echo 'selected'; ?>>Findings Prepared</option>
                                <option value="Completed" <?php if ($row['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                            </select>
                        </form>
                    </td>
                    <td class="action-links">
                        <a href="investigation_findings_form.php?investigation_id=<?php echo $row['investigation_id']; ?>">Add Findings</a>
                        <a href="investigation_evidence_form.php?investigation_id=<?php echo $row['investigation_id']; ?>">Manage Evidence</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>