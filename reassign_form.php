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

$assignment_id = $_GET['assignment_id'];
$stmt = $conn->prepare("SELECT a.*, c.complaint_number FROM assignments a JOIN complaints c ON a.complaint_id = c.complaint_id WHERE a.assignment_id = ?");
$stmt->bind_param("i", $assignment_id);
$stmt->execute();
$assignment = $stmt->get_result()->fetch_assoc();

$personnel = $conn->query("SELECT user_id, full_name FROM users WHERE status = 'active' ORDER BY full_name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reassign Complaint</title>
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

        .page-wrap { max-width: 560px; margin: 0 auto; padding: 30px 20px; }

        .form-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(10,37,64,0.08);
            padding: 30px;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp 0.5s ease forwards;
        }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        .form-card h2 { color: var(--navy); margin-top: 0; font-size: 20px; }
        .form-card .complaint-tag {
            display: inline-block;
            background: #eef2f9;
            color: var(--navy);
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .form-card form { box-shadow: none; padding: 0; max-width: 100%; margin: 0; background: none; }

        .form-card label {
            font-size: 12px; font-weight: 600; color: var(--navy);
            text-transform: uppercase; letter-spacing: 0.03em;
        }
        .form-card select, .form-card textarea {
            background: #f4f7fb;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }
        .form-card select:focus, .form-card textarea:focus {
            outline: none; border-color: var(--accent); background: #fff;
        }
        .form-card button {
            background: var(--accent);
            width: 100%;
            border: none;
            padding: 13px;
            border-radius: 8px;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
        }
        .form-card button:hover { background: var(--navy); }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="assignment_list.php">&larr; Assignment List</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="form-card">
            <span class="complaint-tag"><?php echo htmlspecialchars($assignment['complaint_number']); ?></span>
            <h2>Reassign Complaint</h2>

            <form action="save_reassignment.php" method="POST">
                <input type="hidden" name="assignment_id" value="<?php echo $assignment['assignment_id']; ?>">
                <input type="hidden" name="complaint_id" value="<?php echo $assignment['complaint_id']; ?>">
                <input type="hidden" name="previous_assignee" value="<?php echo $assignment['assigned_to']; ?>">

                <label>New Assignee</label>
                <select name="new_assignee" required>
                    <?php while ($p = $personnel->fetch_assoc()): ?>
                        <option value="<?php echo $p['user_id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
                    <?php endwhile; ?>
                </select>

                <label>Reason for Reassignment</label>
                <textarea name="reason" rows="3" required></textarea>

                <button type="submit">Reassign</button>
            </form>
        </div>
    </div>
</body>
</html>