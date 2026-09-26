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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Investigation Findings</title>
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
        body { margin: 0; background: var(--bg); color: #1b2733; }

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

        body .page-wrap {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 20px;
        }
        body .form-card, body .table-card { width: 100% !important; max-width: 860px !important; margin: 0 auto !important; }

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
        .form-group { margin-bottom: 18px; }

        input[type="date"], input[type="text"], textarea, select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d7dee8;
            border-radius: 8px;
            font-size: 14px;
            background: #fafcff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(47, 111, 237, 0.15);
        }
        textarea { resize: vertical; }

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

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge-valid { background: #e3f6e8; color: #1c7c3f; }
        .badge-partial { background: #fff4d9; color: #9a6a00; }
        .badge-unsubstantiated { background: #ffe8d9; color: #b3521a; }
        .badge-invalid { background: #fde3e3; color: #c1272d; }
    </style>
</head>
<body>
    <div class="topbar">
        <a class="back-link" href="investigation_list.php">← Back to Investigations</a>
        <h2>Investigation Findings — <?php echo htmlspecialchars($investigation['complaint_number']); ?></h2>
    </div>

    <div class="page-wrap">
        <div class="form-card">
            <form action="save_investigation_findings.php" method="POST">
                <input type="hidden" name="investigation_id" value="<?php echo $investigation['investigation_id']; ?>">

                <div class="form-group">
                    <label>Date Investigated</label>
                    <input type="date" name="date_investigated" required>
                </div>

                <div class="form-group">
                    <label>Evidence Collected</label>
                    <textarea name="evidence_collected" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Findings</label>
                    <textarea name="findings" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label>Witnesses</label>
                    <input type="text" name="witnesses">
                </div>

                <div class="form-group">
                    <label>Investigator's Recommendation</label>
                    <textarea name="recommendation" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Classification</label>
                    <select name="classification" required>
                        <option value="Valid Complaint">Valid Complaint</option>
                        <option value="Partially Valid">Partially Valid</option>
                        <option value="Unsubstantiated">Unsubstantiated</option>
                        <option value="Invalid">Invalid</option>
                    </select>
                </div>

                <button type="submit">Save Findings</button>
            </form>
        </div>

        <?php
        $findingsCheck = $conn->prepare("SELECT * FROM investigation_findings WHERE investigation_id = ? ORDER BY finding_id DESC");
        $findingsCheck->bind_param("i", $investigation_id);
        $findingsCheck->execute();
        $findingsResult = $findingsCheck->get_result();

        if ($findingsResult->num_rows > 0):
            $badgeMap = [
                'Valid Complaint' => 'badge-valid',
                'Partially Valid' => 'badge-partial',
                'Unsubstantiated' => 'badge-unsubstantiated',
                'Invalid' => 'badge-invalid',
            ];
        ?>
        <div class="table-card">
            <h3>Recorded Findings</h3>
            <div class="table-scroll">
                <table>
                    <tr>
                        <th>Date Investigated</th>
                        <th>Findings</th>
                        <th>Classification</th>
                        <th>Recommendation</th>
                    </tr>
                    <?php while ($f = $findingsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $f['date_investigated']; ?></td>
                        <td><?php echo nl2br(htmlspecialchars($f['findings'])); ?></td>
                        <td>
                            <span class="badge <?php echo $badgeMap[$f['classification']] ?? ''; ?>">
                                <?php echo htmlspecialchars($f['classification']); ?>
                            </span>
                        </td>
                        <td><?php echo nl2br(htmlspecialchars($f['recommendation'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>