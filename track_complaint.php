<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complaint = null;
$notFound = false;

if (isset($_GET['complaint_number']) && !empty($_GET['complaint_number'])) {
    $number = trim($_GET['complaint_number']);
    $stmt = $conn->prepare(
        "SELECT c.*, cc.category_name FROM complaints c
         JOIN complaint_categories cc ON c.category_id = cc.category_id
         WHERE c.complaint_number = ?"
    );
    $stmt->bind_param("s", $number);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $complaint = $result->fetch_assoc();
    } else {
        $notFound = true;
    }
}

$statusSteps = ['Submitted', 'For Assignment', 'Assigned', 'Under Investigation', 'Action Required', 'For Resolution', 'Resolved', 'Closed'];
$currentIndex = $complaint ? array_search($complaint['status'], $statusSteps) : -1;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Track My Complaint</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        :root {
            --navy: #0a2540;
            --accent: #2f6fed;
        }
        body.track-page {
            background: linear-gradient(135deg, #eef2f9 0%, #dbe6f5 100%);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            font-family: 'Poppins', sans-serif;
        }
        .track-card {
            background: #fff;
            border-radius: 26px;
            box-shadow: 0 30px 60px rgba(10, 37, 64, 0.15);
            padding: 40px;
            max-width: 560px;
            width: 100%;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeUp 0.6s ease forwards;
        }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        .track-card .logo { display: block; width: 60px; height: 60px; margin: 0 auto 10px; border-radius: 50%; }
        .track-card h2 { text-align: center; color: var(--navy); margin: 0 0 4px; }
        .track-card .subtitle { text-align: center; color: #64748b; font-size: 13px; margin-bottom: 24px; }

        .track-form { display: flex; gap: 10px; margin-bottom: 24px; }
        .track-form input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #f4f7fb;
            font-family: 'Poppins', sans-serif;
        }
        .track-form input:focus { outline: none; border-color: var(--accent); }
        .track-form button {
            background: var(--navy);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.25s;
        }
        .track-form button:hover { background: var(--accent); }

        .steps { list-style: none; padding: 0; margin: 0; }
        .steps li {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            font-size: 14px;
            color: #94a3b8;
        }
        .steps li.done { color: var(--navy); font-weight: 600; }
        .steps li.current { color: var(--accent); font-weight: 700; }
        .dot { width: 10px; height: 10px; border-radius: 50%; background: #e2e8f0; flex-shrink: 0; }
        .steps li.done .dot, .steps li.current .dot { background: var(--accent); }

        .complaint-summary {
            background: #f4f7fb;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #475569;
        }
        .complaint-summary strong { color: var(--navy); }

        .not-found { color: #dc2626; text-align: center; font-size: 14px; }
    </style>
</head>
<body class="track-page">
    <div class="track-card">
        <img src="assets/cms-logo.png" alt="CMS Logo" class="logo">
        <h2>Track My Complaint</h2>
        <p class="subtitle">Enter your complaint number to check its status</p>

        <form method="GET" class="track-form">
            <input type="text" name="complaint_number" placeholder="e.g. CMS-2026-0009" value="<?php echo isset($_GET['complaint_number']) ? htmlspecialchars($_GET['complaint_number']) : ''; ?>" required>
            <button type="submit">Track</button>
        </form>

        <?php if ($notFound): ?>
            <p class="not-found">No complaint found with that number. Please check and try again.</p>
        <?php endif; ?>

        <?php if ($complaint): ?>
            <div class="complaint-summary">
                <strong>Subject:</strong> <?php echo htmlspecialchars($complaint['subject']); ?><br>
                <strong>Category:</strong> <?php echo htmlspecialchars($complaint['category_name']); ?><br>
                <strong>Date Filed:</strong> <?php echo $complaint['date_filed']; ?>
            </div>

            <ul class="steps">
                <?php foreach ($statusSteps as $i => $step): ?>
                    <li class="<?php echo $i < $currentIndex ? 'done' : ($i == $currentIndex ? 'current' : ''); ?>">
                        <span class="dot"></span> <?php echo $step; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>