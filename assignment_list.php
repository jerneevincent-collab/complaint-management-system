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
    "SELECT a.*, c.complaint_number, c.subject, u.full_name AS assignee_name, d.department_name
     FROM assignments a
     JOIN complaints c ON a.complaint_id = c.complaint_id
     JOIN users u ON a.assigned_to = u.user_id
     LEFT JOIN departments d ON a.department_id = d.department_id
     ORDER BY a.assignment_id DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Assignment List</title>
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

        .page-wrap { max-width: 1100px; margin: 0 auto; padding: 30px 20px; }

        .page-header {
            display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;
        }
        .page-header h2 { margin: 0; color: var(--navy); }
        .btn-primary {
            background: var(--accent); color: #fff; padding: 10px 18px;
            border-radius: 8px; font-weight: 600; font-size: 14px; transition: 0.2s;
        }
        .btn-primary:hover { background: var(--navy); text-decoration: none; }

        .table-card {
            background: #fff; border-radius: 14px;
            box-shadow: 0 6px 20px rgba(10,37,64,0.06); overflow: hidden;
            opacity: 0; animation: fadeIn 0.4s ease forwards;
        }
        @keyframes fadeIn { to { opacity: 1; } }
        table { margin: 0; box-shadow: none; }
        th { background: var(--navy); }

        .status-pill {
            display: inline-block; padding: 4px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 600; text-transform: uppercase;
        }
        .status-active { background: #dbeafe; color: #1e40af; }
        .status-reassigned { background: #fef3c7; color: #92400e; }
        .status-completed { background: #d1fae5; color: #065f46; }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="index.php">&larr; Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="page-header">
            <h2>Assignment List</h2>
            <a href="assignment_form.php" class="btn-primary">+ New Assignment</a>
        </div>

        <div class="table-card">
            <table>
                <tr>
                    <th>Complaint #</th>
                    <th>Subject</th>
                    <th>Assigned To</th>
                    <th>Department</th>
                    <th>Assignment Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['complaint_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['subject']); ?></td>
                    <td><?php echo htmlspecialchars($row['assignee_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['department_name']); ?></td>
                    <td><?php echo $row['assignment_date']; ?></td>
                    <td><?php echo $row['due_date']; ?></td>
                    <td><span class="status-pill status-<?php echo $row['status']; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td><a href="reassign_form.php?assignment_id=<?php echo $row['assignment_id']; ?>">Reassign</a></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>