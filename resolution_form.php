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

$complaints = $conn->query(
    "SELECT complaint_id, complaint_number, subject FROM complaints
     WHERE status IN ('For Resolution', 'Action Required') ORDER BY complaint_id DESC"
);
$personnel = $conn->query("SELECT user_id, full_name FROM users WHERE status = 'active' ORDER BY full_name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint Resolution</title>
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

        .page-wrap { max-width: 560px; margin: 0 auto; padding: 30px 20px; }

        .form-card {
            background: #fff; border-radius: 16px;
            box-shadow: 0 10px 30px rgba(10,37,64,0.08); padding: 30px;
            opacity: 0; transform: translateY(20px);
            animation: fadeUp 0.5s ease forwards;
        }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        .form-card h2 { color: var(--navy); margin-top: 0; }
        .form-card form { box-shadow: none; padding: 0; max-width: 100%; margin: 0; background: none; }

        .form-card label {
            font-size: 12px; font-weight: 600; color: var(--navy);
            text-transform: uppercase; letter-spacing: 0.03em;
        }
        .form-card input, .form-card select, .form-card textarea {
            background: #f4f7fb; border: 1px solid #e2e8f0; border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }
        .form-card input:focus, .form-card select:focus, .form-card textarea:focus {
            outline: none; border-color: var(--accent); background: #fff;
        }
        .form-card button {
            background: var(--accent); width: 100%; border: none; padding: 13px;
            border-radius: 8px; color: #fff; font-weight: 700; cursor: pointer; transition: 0.2s;
        }
        .form-card button:hover { background: var(--navy); }

        .success-msg { color: #16a34a !important; }
        .error-msg { color: #dc2626 !important; }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="index.php">&larr; Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="form-card">
            <h2>Complaint Resolution</h2>
            <?php if (isset($_GET['status']) && $_GET['status'] == 'success') echo "<p class='success-msg'>Resolution recorded successfully!</p>"; ?>
            <?php if (isset($_GET['status']) && $_GET['status'] == 'error') echo "<p class='error-msg'>" . htmlspecialchars($_GET['msg'] ?? 'Resolution failed.') . "</p>"; ?>

            <form action="save_resolution.php" method="POST">
                <label>Complaint</label>
                <select name="complaint_id" required>
                    <option value="">-- Select Complaint --</option>
                    <?php while ($c = $complaints->fetch_assoc()): ?>
                        <option value="<?php echo $c['complaint_id']; ?>">
                            <?php echo htmlspecialchars($c['complaint_number'] . " - " . $c['subject']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Resolution Type</label>
                <input type="text" name="resolution_type" placeholder="e.g. Refund, Policy Change, Apology">

                <label>Resolution Description</label>
                <textarea name="resolution_description" rows="4" required></textarea>

                <label>Resolution Date</label>
                <input type="date" name="resolution_date" required>

                <label>Resolved By</label>
                <select name="resolved_by" required>
                    <option value="">-- Select Personnel --</option>
                    <?php while ($p = $personnel->fetch_assoc()): ?>
                        <option value="<?php echo $p['user_id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
                    <?php endwhile; ?>
                </select>

                <label>Remarks</label>
                <textarea name="remarks" rows="2"></textarea>

                <button type="submit">Save Resolution</button>
            </form>
        </div>
    </div>
</body>
</html>