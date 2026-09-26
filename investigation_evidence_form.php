<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] == 6) {
    die("Access denied.");
}
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$investigation_id = $_GET['investigation_id'];
$stmt = $conn->prepare(
    "SELECT i.*, c.complaint_number FROM investigations i JOIN complaints c ON i.complaint_id = c.complaint_id WHERE i.investigation_id = ?"
);
$stmt->bind_param("i", $investigation_id);
$stmt->execute();
$investigation = $stmt->get_result()->fetch_assoc();

// Get existing evidence
$evidenceList = $conn->prepare("SELECT * FROM investigation_evidence WHERE investigation_id = ? ORDER BY evidence_id DESC");
$evidenceList->bind_param("i", $investigation_id);
$evidenceList->execute();
$evidenceResult = $evidenceList->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Investigation Evidence</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        :root {
            --navy: #0a2540;
            --accent: #2f6fed;
            --bg: #f4f7fb;
        }
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
body { margin: 0; background: red; color: #1b2733; }

        .topbar {
            background: var(--navy);
            color: #fff;
            position: relative;
            display: flex;
            align-items: center;
            padding: 14px 30px;
        }
        .topbar h2 {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            margin: 0;
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            white-space: nowrap;
        }
        .topbar .back-link {
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            z-index: 1;
        }
        .topbar .back-link:hover { color: #fff; }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
        }
        body .page-wrap {
            width: 100% !important;
            margin: 0 !important;
            text-align: center !important;
            padding: 30px 20px !important;
        }
        body .form-card, body .table-card {
            display: inline-block !important;
            text-align: left !important;
            width: 860px !important;
            max-width: calc(100% - 40px) !important;
            margin: 0 auto 24px auto !important;
        }

        .form-card, .table-card {
            padding: 28px 30px;
            margin-bottom: 24px;
            opacity: 0;
            animation: fadeUp 0.5s ease forwards;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-card label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--navy);
            margin-bottom: 6px;
        }
        .form-card form { margin: 0 auto; }
        .form-group { margin-bottom: 18px; }

        input[type="file"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d7dee8;
            border-radius: 8px;
            font-size: 14px;
            background: #fafcff;
        }

        button[type="submit"] {
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 11px 26px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button[type="submit"]:hover { background: #244fc7; }

        .table-card h3 {
            margin: 0 0 16px 0;
            color: var(--navy);
            font-size: 16px;
        }
        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th {
            background: var(--navy);
            color: #fff;
            text-align: left;
            padding: 10px 12px;
            white-space: nowrap;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #eef1f5;
            vertical-align: top;
        }
        tr:last-child td { border-bottom: none; }

        .empty-note {
            color: #6b7787;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <a class="back-link" href="investigation_list.php">← Back to Investigations</a>
        <h2>Investigation Evidence — <?php echo htmlspecialchars($investigation['complaint_number']); ?></h2>
    </div>

    <div class="page-wrap">
        <div class="form-card">
            <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <p style="color:green;">Evidence uploaded successfully!</p>
            <?php endif; ?>

            <form action="save_investigation_evidence.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="investigation_id" value="<?php echo $investigation['investigation_id']; ?>">

                <div class="form-group">
                    <label>Upload Evidence File</label>
                    <input type="file" name="evidence_file" required>
                </div>

                <button type="submit">Upload Evidence</button>
            </form>
        </div>

        <div class="table-card">
            <h3>Uploaded Evidence</h3>
            <?php if ($evidenceResult->num_rows > 0): ?>
            <div class="table-scroll">
                <table>
                    <tr>
                        <th>File Name</th>
                        <th>Uploaded At</th>
                        <th>Action</th>
                    </tr>
                    <?php while ($row = $evidenceResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['file_name']); ?></td>
                        <td><?php echo $row['uploaded_at']; ?></td>
                        <td><a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank">View/Download</a></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <?php else: ?>
                <p class="empty-note">No evidence uploaded yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>