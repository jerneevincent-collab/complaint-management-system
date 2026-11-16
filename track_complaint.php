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
if ($currentIndex === false) {
    $currentIndex = -1;
}
$totalSteps = count($statusSteps);
$progressPct = $currentIndex >= 0 ? round((($currentIndex + 1) / $totalSteps) * 100) : 0;
$statusClass = $complaint ? strtolower(str_replace(' ', '-', $complaint['status'])) : '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Track My Complaint</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/enhance.css">
    <style>
        :root {
            --navy: #0a2540;
            --accent: #2f6fed;
        }
        * { box-sizing: border-box; }
        body.track-page { font-family: 'Poppins', sans-serif; margin: 0; }

        /* ---------- animated background ---------- */
        .bg {
            position: fixed; inset: 0; z-index: 0; overflow: hidden;
            background: linear-gradient(135deg, #eef2f9 0%, #dbe6f5 100%);
        }
        .blob { position: absolute; border-radius: 50%; filter: blur(70px); opacity: 0.55; }
        .blob.b1 { width: 380px; height: 380px; background: #9bbcff; top: -120px; left: -100px; animation: drift1 14s ease-in-out infinite; }
        .blob.b2 { width: 320px; height: 320px; background: #b7d4ff; bottom: -110px; right: -80px; animation: drift2 17s ease-in-out infinite; }
        .blob.b3 { width: 240px; height: 240px; background: #cfc6ff; top: 45%; right: 18%; opacity: 0.35; animation: drift1 20s ease-in-out infinite reverse; }
        @keyframes drift1 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(60px, 40px); } }
        @keyframes drift2 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-50px, -40px); } }

        .page {
            position: relative; z-index: 1;
            min-height: 100vh; width: 100%;
            display: flex; align-items: center; justify-content: center;
            padding: 40px 20px;
        }
        .wrap { width: 100%; max-width: 600px; margin: 0 auto; }

        /* ---------- back pill ---------- */
        .back-pill {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 16px; margin-bottom: 16px;
            background: rgba(255,255,255,0.75); backdrop-filter: blur(6px);
            border: 1px solid #dbe3ef; border-radius: 999px;
            color: var(--navy); font-size: 13px; font-weight: 600; text-decoration: none;
            transition: background 0.2s ease, box-shadow 0.2s ease;
            opacity: 0; animation: fadeIn 0.5s ease 0.1s forwards;
        }
        .back-pill svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; transition: transform 0.2s ease; }
        .back-pill:hover { background: #fff; box-shadow: 0 6px 16px rgba(10,37,64,0.1); text-decoration: none; color: var(--navy); }
        .back-pill:hover svg { transform: translateX(-4px); }
        @keyframes fadeIn { to { opacity: 1; } }

        /* ---------- card ---------- */
        .track-card {
            background: #fff;
            border-radius: 26px;
            box-shadow: 0 30px 60px rgba(10, 37, 64, 0.15);
            border: 1px solid #e2e8f0;
            padding: 40px;
            width: 100%;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeUp 0.6s ease forwards;
        }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        .track-card .logo {
            display: block; width: 60px; height: 60px; margin: 0 auto 10px; border-radius: 50%;
            animation: logoPulse 3.4s ease-out 1s infinite;
        }
        @keyframes logoPulse {
            0%   { box-shadow: 0 0 0 0 rgba(47,111,237,0.35); }
            70%  { box-shadow: 0 0 0 14px rgba(47,111,237,0); }
            100% { box-shadow: 0 0 0 0 rgba(47,111,237,0); }
        }
        .track-card h2 { text-align: center; color: var(--navy); margin: 0 0 4px; font-size: 24px; letter-spacing: -0.5px; }
        .track-card .subtitle { text-align: center; color: #64748b; font-size: 13px; margin: 0 0 24px; }

        /* ---------- search ---------- */
        .track-form {
            display: flex; gap: 10px; margin: 0 0 8px;
            background: none; box-shadow: none; padding: 0; max-width: 100%;
        }
        .search-field { position: relative; flex: 1; }
        .search-field svg {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            width: 18px; height: 18px; stroke: #94a3b8; fill: none; stroke-width: 2; stroke-linecap: round;
            pointer-events: none; transition: stroke 0.25s ease;
        }
        .search-field:focus-within svg { stroke: var(--accent); }
        .track-form input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f4f7fb;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            color: var(--navy);
            transition: 0.3s;
        }
        .track-form input:focus {
            outline: none; border-color: var(--accent); background: #fff;
            box-shadow: 0 0 0 4px rgba(47, 111, 237, 0.12);
        }
        .track-form button {
            margin: 0;
            background: var(--navy); color: #fff; border: none; border-radius: 12px;
            padding: 0 24px; font-weight: 600; font-size: 14px; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: transform 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
        }
        .track-form button:hover { background: var(--accent); transform: translateY(-2px); box-shadow: 0 10px 22px rgba(47,111,237,0.3); }
        .track-form button:disabled { opacity: 0.85; cursor: wait; transform: none; }
        .spinner {
            display: none; width: 14px; height: 14px; border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.35); border-top-color: #fff;
            animation: spin 0.7s linear infinite;
        }
        .track-form button.loading .spinner { display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .hint-text { text-align: center; color: #94a3b8; font-size: 12px; margin: 10px 0 0; }

        /* ---------- not found ---------- */
        .not-found {
            display: flex; align-items: center; gap: 12px; margin-top: 18px;
            background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c;
            border-radius: 14px; padding: 14px 16px; font-size: 13px; line-height: 1.5;
            animation: shake 0.45s ease;
        }
        .not-found svg { width: 22px; height: 22px; flex: 0 0 22px; stroke: #dc2626; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        @keyframes shake {
            0%,100% { transform: translateX(0); } 20% { transform: translateX(-6px); }
            40% { transform: translateX(6px); } 60% { transform: translateX(-4px); } 80% { transform: translateX(4px); }
        }

        /* ---------- result ---------- */
        .result { margin-top: 22px; animation: fadeUp 0.6s ease both; }
        .complaint-summary {
            background: #f4f7fb; border: 1px solid #e8eef7; border-radius: 16px;
            padding: 18px 20px; margin-bottom: 20px;
        }
        .summary-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; margin-bottom: 12px; }
        .c-number { font-size: 12px; font-weight: 600; letter-spacing: 0.06em; color: #64748b; text-transform: uppercase; }
        .status-badge { display: inline-block; padding: 4px 14px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap; }
        .status-submitted { background: #dbeafe; color: #1e40af; }
        .status-for-assignment, .status-assigned { background: #fef3c7; color: #92400e; }
        .status-under-investigation, .status-action-required, .status-for-resolution { background: #fed7aa; color: #9a3412; }
        .status-resolved { background: #d1fae5; color: #065f46; }
        .status-closed { background: #e2e8f0; color: #475569; }
        .summary-row { display: flex; gap: 8px; font-size: 13px; color: #475569; padding: 4px 0; }
        .summary-row strong { color: var(--navy); min-width: 92px; font-weight: 600; }

        .progress-head { display: flex; justify-content: space-between; font-size: 12px; color: #64748b; margin-bottom: 8px; font-weight: 500; }
        .progress-head strong { color: var(--navy); }
        .progress-bar { height: 8px; background: #e8eef7; border-radius: 999px; overflow: hidden; margin-bottom: 24px; }
        .progress-fill { height: 100%; width: 0; border-radius: 999px; background: linear-gradient(90deg, #2f6fed, #6aa0ff); animation: grow 1.2s cubic-bezier(0.4, 0, 0.2, 1) 0.3s forwards; }
        @keyframes grow { to { width: <?php echo (int) $progressPct; ?>%; } }

        /* ---------- stepper ---------- */
        .steps { list-style: none; padding: 0; margin: 0; background: none; }
        .steps li {
            position: relative; display: flex; align-items: center; gap: 16px;
            padding: 0 0 22px 0; margin: 0;
            background: none; border-left: none; box-shadow: none; border-radius: 0;
            font-size: 14px; color: #94a3b8;
            opacity: 0; transform: translateX(-10px);
            animation: stepIn 0.45s ease forwards;
        }
        .steps li:last-child { padding-bottom: 0; }
        @keyframes stepIn { to { opacity: 1; transform: translateX(0); } }

        /* connecting line */
        .steps li::before {
            content: ""; position: absolute; left: 13px; top: 28px; bottom: -2px; width: 2px;
            background: #e2e8f0;
        }
        .steps li:last-child::before { display: none; }
        .steps li.done::before { background: var(--accent); }

        .node {
            position: relative; z-index: 1; flex: 0 0 28px; width: 28px; height: 28px; border-radius: 50%;
            background: #fff; border: 2px solid #e2e8f0;
            display: flex; align-items: center; justify-content: center;
        }
        .node svg { width: 14px; height: 14px; stroke: #fff; fill: none; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; }
        .steps li.done .node { background: var(--accent); border-color: var(--accent); }
        .steps li.current .node { border-color: var(--accent); background: #fff; }
        .steps li.current .node::after {
            content: ""; width: 10px; height: 10px; border-radius: 50%; background: var(--accent);
        }
        .steps li.current .node::before {
            content: ""; position: absolute; inset: -2px; border-radius: 50%;
            border: 2px solid var(--accent); animation: ping 1.8s ease-out infinite;
        }
        @keyframes ping { 0% { transform: scale(1); opacity: 0.8; } 100% { transform: scale(1.9); opacity: 0; } }

        .steps li.done { color: var(--navy); font-weight: 600; }
        .steps li.current { color: var(--accent); font-weight: 700; }
        .now-tag {
            margin-left: 6px; font-size: 10px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase;
            background: #eaf0ff; color: var(--accent); padding: 2px 8px; border-radius: 999px;
        }

        @media (max-width: 520px) {
            .track-card { padding: 30px 22px; }
            .track-form { flex-direction: column; }
            .track-form button { padding: 13px 24px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .blob, .track-card .logo, .steps li.current .node::before { animation: none; }
        }
    </style>
</head>
<body class="track-page">
    <div class="bg"><span class="blob b1"></span><span class="blob b2"></span><span class="blob b3"></span></div>

    <div class="page">
        <div class="wrap">
            <a class="back-pill" href="login.php">
                <svg viewBox="0 0 24 24"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                Back to Login
            </a>

            <div class="track-card">
                <img src="assets/cms-logo.png" alt="CMS Logo" class="logo">
                <h2>Track My Complaint</h2>
                <p class="subtitle">Enter your complaint number to check its status</p>

                <form method="GET" class="track-form" id="trackForm">
                    <div class="search-field">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        <input type="text" name="complaint_number" placeholder="e.g. CMS-2026-0009" value="<?php echo isset($_GET['complaint_number']) ? htmlspecialchars($_GET['complaint_number']) : ''; ?>" required>
                    </div>
                    <button type="submit" id="trackBtn"><span class="spinner"></span><span class="btn-text">Track</span></button>
                </form>
                <?php if (!$complaint && !$notFound): ?>
                    <p class="hint-text">You can find your complaint number in the confirmation you received when you filed.</p>
                <?php endif; ?>

                <?php if ($notFound): ?>
                    <div class="not-found">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16.5v.01"/></svg>
                        <span>No complaint found with that number. Please check and try again.</span>
                    </div>
                <?php endif; ?>

                <?php if ($complaint): ?>
                <div class="result">
                    <div class="complaint-summary">
                        <div class="summary-top">
                            <span class="c-number"><?php echo htmlspecialchars($complaint['complaint_number']); ?></span>
                            <span class="status-badge status-<?php echo $statusClass; ?>"><?php echo htmlspecialchars($complaint['status']); ?></span>
                        </div>
                        <div class="summary-row"><strong>Subject:</strong> <span><?php echo htmlspecialchars($complaint['subject']); ?></span></div>
                        <div class="summary-row"><strong>Category:</strong> <span><?php echo htmlspecialchars($complaint['category_name']); ?></span></div>
                        <div class="summary-row"><strong>Date Filed:</strong> <span><?php echo $complaint['date_filed']; ?></span></div>
                    </div>

                    <div class="progress-head">
                        <span>Progress</span>
                        <span><strong>Step <?php echo $currentIndex + 1; ?> of <?php echo $totalSteps; ?></strong> &middot; <?php echo (int) $progressPct; ?>%</span>
                    </div>
                    <div class="progress-bar"><div class="progress-fill"></div></div>

                    <ul class="steps">
                        <?php foreach ($statusSteps as $i => $step): ?>
                            <li class="<?php echo $i < $currentIndex ? 'done' : ($i == $currentIndex ? 'current' : ''); ?>" style="animation-delay: <?php echo 0.2 + $i * 0.09; ?>s;">
                                <span class="node">
                                    <?php if ($i < $currentIndex): ?>
                                    <svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                                    <?php endif; ?>
                                </span>
                                <span><?php echo $step; ?><?php if ($i == $currentIndex): ?><span class="now-tag">Current</span><?php endif; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('trackForm').addEventListener('submit', function () {
            const btn = document.getElementById('trackBtn');
            btn.classList.add('loading');
            btn.querySelector('.btn-text').textContent = 'Searching...';
            setTimeout(() => { btn.disabled = true; }, 0);
        });
    </script>
    <script src="assets/enhance.js"></script>
</body>
</html>