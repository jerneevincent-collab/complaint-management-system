<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complainants = $conn->query("SELECT complainant_id, full_name FROM complainants ORDER BY full_name");
$categories = $conn->query("SELECT category_id, category_name FROM complaint_categories WHERE is_active = 1 ORDER BY category_name");

$isSuccess = isset($_GET['status']) && $_GET['status'] == 'success';
$errorText = '';
if (isset($_GET['status']) && $_GET['status'] == 'error') {
    $reason = isset($_GET['reason']) ? $_GET['reason'] : '';
    $msg = isset($_GET['msg']) ? $_GET['msg'] : '';
    if ($reason == 'futuredate') {
        $errorText = 'Date filed cannot be a future date.';
    } elseif (!empty($msg)) {
        $errorText = $msg;
    } else {
        $errorText = 'Please fill out all required fields.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>File a Complaint</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/enhance.css">
    <style>
        :root {
            --card-bg: #ffffff;
            --navy: #0a2540;
            --accent: #2f6fed;
            --text: #475569;
            --input-bg: #f4f7fb;
            --elastic: cubic-bezier(0.75, -0.5, 0.27, 1.55);
        }
        * { box-sizing: border-box; }

        body.reg-page { font-family: 'Poppins', sans-serif; margin: 0; }

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
        .wrap { width: 100%; max-width: 680px; margin: 0 auto; }

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

        /* ---------- card ---------- */
        .reg-card {
            position: relative; width: 100%;
            background: var(--card-bg);
            border-radius: 26px;
            box-shadow: 0 30px 60px rgba(10, 37, 64, 0.15);
            border: 1px solid #e2e8f0;
            padding: 40px 38px;
            overflow: hidden;
            opacity: 0; transform: translateY(60px) scale(0.95);
            animation: revealCard 0.9s var(--elastic) 0.1s forwards;
        }
        @keyframes revealCard { to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes fadeIn { to { opacity: 1; } }
        @keyframes fieldIn { to { opacity: 1; transform: translateY(0); } }

        .glow {
            position: absolute; top: -40px; right: -40px; width: 180px; height: 180px;
            background: var(--accent); filter: blur(90px); opacity: 0.18; pointer-events: none;
        }

        .reg-card .logo {
            display: block; width: 64px; height: 64px; margin: 0 auto 10px;
            border-radius: 50%; position: relative; z-index: 2;
            animation: logoPulse 3.4s ease-out 1.2s infinite;
        }
        @keyframes logoPulse {
            0%   { box-shadow: 0 0 0 0 rgba(47,111,237,0.35); }
            70%  { box-shadow: 0 0 0 14px rgba(47,111,237,0); }
            100% { box-shadow: 0 0 0 0 rgba(47,111,237,0); }
        }
        .reg-card h2 {
            color: var(--navy); text-align: center; margin: 0 0 4px;
            font-size: 24px; letter-spacing: -0.5px; position: relative; z-index: 2;
        }
        .reg-card .subtitle {
            color: var(--text); text-align: center; font-size: 13px;
            margin: 0 0 22px; position: relative; z-index: 2;
        }
        .reg-card .subtitle a { color: var(--accent); font-weight: 600; text-decoration: none; }
        .reg-card .subtitle a:hover { text-decoration: underline; }

        /* ---------- banners ---------- */
        .banner {
            position: relative; z-index: 2; display: flex; align-items: center; gap: 10px;
            border-radius: 12px; padding: 12px 14px; margin-bottom: 18px; font-size: 13px; font-weight: 500;
        }
        .banner svg { width: 18px; height: 18px; flex: none; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .banner.ok  { background: #ecfdf5; color: #166534; border: 1px solid #bbf7d0; animation: slideDown 0.5s ease both; }
        .banner.err { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; animation: shake 0.5s ease both; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes shake { 0%,100% { transform: translateX(0); } 20% { transform: translateX(-7px); } 40% { transform: translateX(6px); } 60% { transform: translateX(-4px); } 80% { transform: translateX(3px); } }

        /* ---------- form ---------- */
        .reg-card form {
            box-shadow: none; padding: 0; max-width: 100%; margin: 0;
            background: transparent; position: relative; z-index: 2;
        }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px 16px; }
        .full { grid-column: 1 / -1; }

        .input-group {
            display: flex; flex-direction: column; gap: 6px;
            opacity: 0; transform: translateY(10px);
            animation: fieldIn 0.5s ease forwards;
        }
        .input-group:nth-child(1) { animation-delay: 0.50s; }
        .input-group:nth-child(2) { animation-delay: 0.56s; }
        .input-group:nth-child(3) { animation-delay: 0.62s; }
        .input-group:nth-child(4) { animation-delay: 0.68s; }
        .input-group:nth-child(5) { animation-delay: 0.74s; }
        .input-group:nth-child(6) { animation-delay: 0.80s; }
        .input-group:nth-child(7) { animation-delay: 0.86s; }
        .input-group:nth-child(8) { animation-delay: 0.92s; }

        .input-group > label, .label-row label {
            color: var(--navy); font-size: 11px; text-transform: uppercase;
            letter-spacing: 1.2px; font-weight: 600; margin: 0;
        }
        .label-row { display: flex; justify-content: space-between; align-items: baseline; }
        .counter { font-size: 11px; color: #94a3b8; font-variant-numeric: tabular-nums; transition: color 0.2s ease; }
        .counter.warn { color: #d97706; }

        .field { position: relative; }
        .field .ico {
            position: absolute; left: 14px; top: 14px;
            width: 18px; height: 18px; stroke: #94a3b8; fill: none; stroke-width: 1.8;
            stroke-linecap: round; stroke-linejoin: round; pointer-events: none;
            transition: stroke 0.25s ease;
        }
        .field:focus-within .ico { stroke: var(--accent); }

        .field input, .field select, .field textarea {
            width: 100%; margin: 0;
            background: var(--input-bg);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 13px 14px 13px 42px;
            color: var(--navy); font-size: 14px;
            outline: none; transition: 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        .field textarea { resize: vertical; min-height: 110px; }
        .field input:focus, .field select:focus, .field textarea:focus {
            border-color: var(--accent); background: #ffffff;
            box-shadow: 0 0 0 4px rgba(47, 111, 237, 0.12);
        }
        .field.invalid input, .field.invalid select, .field.invalid textarea { border-color: #f08a8a; background: #fff7f7; }
        .field.valid input, .field.valid select, .field.valid textarea { border-color: #86d9a6; }
        .err-text { color: #dc2626; font-size: 11px; min-height: 0; max-height: 0; overflow: hidden; opacity: 0; transition: max-height 0.25s ease, opacity 0.25s ease; }
        .field.invalid + .err-text { max-height: 20px; opacity: 1; }

        /* ---------- priority pills ---------- */
        .pills { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .pill input { position: absolute; opacity: 0; pointer-events: none; width: 0; height: 0; }
        .pill span {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            padding: 11px 10px; border-radius: 12px; cursor: pointer;
            border: 1.5px solid #e2e8f0; background: var(--input-bg);
            font-size: 13px; font-weight: 600; color: var(--text);
            transition: all 0.25s ease; user-select: none;
        }
        .pill span::before { content: ""; width: 9px; height: 9px; border-radius: 50%; background: var(--dot); transition: transform 0.25s ease; }
        .pill:hover span { transform: translateY(-2px); box-shadow: 0 6px 14px rgba(10,37,64,0.08); }
        .pill input:focus-visible + span { outline: 2px solid var(--accent); outline-offset: 2px; }
        .pill input:checked + span { background: var(--bgc); border-color: var(--dot); color: var(--fg); box-shadow: 0 6px 16px var(--glow); }
        .pill input:checked + span::before { transform: scale(1.4); }
        .pill.low  { --dot: #16a34a; --bgc: #ecfdf5; --fg: #166534; --glow: rgba(22,163,74,0.2); }
        .pill.med  { --dot: #d97706; --bgc: #fffbeb; --fg: #92400e; --glow: rgba(217,119,6,0.2); }
        .pill.high { --dot: #dc2626; --bgc: #fef2f2; --fg: #991b1b; --glow: rgba(220,38,38,0.2); }

        /* ---------- drop zone ---------- */
        .dropzone {
            position: relative; border: 2px dashed #c5d3e8; border-radius: 14px;
            background: var(--input-bg); padding: 22px 16px; text-align: center; cursor: pointer;
            transition: all 0.25s ease;
        }
        .dropzone:hover { border-color: var(--accent); background: #f0f5ff; }
        .dropzone.drag { border-color: var(--accent); background: #e6efff; transform: scale(1.01); box-shadow: 0 0 0 5px rgba(47,111,237,0.12); }
        .dropzone.bad { border-color: #f08a8a; background: #fff7f7; animation: shake 0.4s ease; }
        .dropzone input[type=file] { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; margin: 0; padding: 0; }
        .dz-icon { width: 34px; height: 34px; stroke: var(--accent); fill: none; stroke-width: 1.7; stroke-linecap: round; stroke-linejoin: round; margin-bottom: 6px; animation: bob 2.4s ease-in-out infinite; }
        @keyframes bob { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }
        .dz-title { font-size: 13px; font-weight: 600; color: var(--navy); margin: 0; }
        .dz-title b { color: var(--accent); }
        .dz-hint { font-size: 11px; color: #94a3b8; margin: 4px 0 0; }

        .file-chip {
            display: none; align-items: center; gap: 12px; margin-top: 10px;
            padding: 10px 12px; border: 1px solid #dbe6f5; border-radius: 12px; background: #fff;
            animation: slideDown 0.35s ease both;
        }
        .file-chip.show { display: flex; }
        .thumb { width: 44px; height: 44px; border-radius: 10px; background: #eaf0fb center/cover no-repeat; display: flex; align-items: center; justify-content: center; flex: none; color: var(--accent); font-size: 11px; font-weight: 700; }
        .f-info { flex: 1; min-width: 0; text-align: left; }
        .f-name { font-size: 13px; font-weight: 600; color: var(--navy); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .f-size { font-size: 11px; color: #94a3b8; }
        .f-remove {
            width: 28px; height: 28px; border-radius: 50%; border: none; background: #fee2e2; color: #b91c1c;
            font-size: 16px; line-height: 1; cursor: pointer; padding: 0; margin: 0; flex: none;
            transition: transform 0.2s ease, background 0.2s ease;
        }
        .f-remove:hover { background: #fecaca; transform: rotate(90deg); }
        .dz-error { color: #dc2626; font-size: 12px; margin-top: 8px; display: none; }
        .dz-error.show { display: block; animation: slideDown 0.3s ease both; }

        /* ---------- submit ---------- */
        .reg-card button.submit {
            position: relative; overflow: hidden;
            background: var(--navy); color: #fff; padding: 15px; border: none; border-radius: 12px;
            font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 14px; cursor: pointer; margin: 4px 0 0;
            width: 100%; transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            opacity: 0; animation: fadeIn 0.5s ease 1s forwards;
        }
        .reg-card button.submit:hover { background: var(--accent); transform: translateY(-2px); box-shadow: 0 10px 22px rgba(47,111,237,0.3); }
        .reg-card button.submit .spinner {
            display: none; width: 16px; height: 16px; margin-right: 8px; vertical-align: -3px;
            border: 2px solid rgba(255,255,255,0.35); border-top-color: #fff; border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .reg-card button.submit.loading { pointer-events: none; background: var(--accent); }
        .reg-card button.submit.loading .spinner { display: inline-block; }

        /* ---------- success screen ---------- */
        .success { position: relative; z-index: 2; text-align: center; }
        .check-wrap { width: 92px; height: 92px; margin: 4px auto 14px; position: relative; }
        .check-wrap svg { width: 100%; height: 100%; }
        .check-wrap .ring { fill: none; stroke: #16a34a; stroke-width: 4; stroke-dasharray: 289; stroke-dashoffset: 289; transform: rotate(-90deg); transform-origin: 50% 50%; animation: draw 0.7s ease 0.2s forwards; }
        .check-wrap .tick { fill: none; stroke: #16a34a; stroke-width: 5; stroke-linecap: round; stroke-linejoin: round; stroke-dasharray: 60; stroke-dashoffset: 60; animation: draw 0.45s ease 0.8s forwards; }
        .check-wrap::after { content: ""; position: absolute; inset: 0; border-radius: 50%; background: rgba(22,163,74,0.12); transform: scale(0); animation: pop 0.5s ease 0.2s forwards; z-index: -1; }
        @keyframes draw { to { stroke-dashoffset: 0; } }
        @keyframes pop { to { transform: scale(1); } }
        .success h3 { margin: 0 0 4px; color: var(--navy); font-size: 22px; opacity: 0; animation: fadeIn 0.5s ease 1s forwards; }
        .success p.lead { margin: 0 0 18px; color: var(--text); font-size: 13px; opacity: 0; animation: fadeIn 0.5s ease 1.1s forwards; }
        .ticket {
            background: #ecfdf5; border: 2px dashed #16a34a; border-radius: 14px; padding: 18px;
            opacity: 0; transform: scale(0.92); animation: ticketIn 0.6s var(--elastic) 1.2s forwards;
        }
        @keyframes ticketIn { to { opacity: 1; transform: scale(1); } }
        .ticket .t-label { margin: 0 0 6px; color: #166534; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; }
        .ticket .t-num { margin: 0; font-size: 28px; font-weight: 700; color: #15803d; letter-spacing: 1.5px; }
        .ticket .t-note { margin: 10px 0 0; font-size: 12px; color: #166534; }
        .actions { display: flex; gap: 10px; margin-top: 18px; flex-wrap: wrap; opacity: 0; animation: fadeIn 0.5s ease 1.5s forwards; }
        .actions .btn {
            flex: 1; min-width: 150px; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 13px 14px; border-radius: 12px; font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 700;
            text-decoration: none; cursor: pointer; border: 1.5px solid var(--navy); transition: all 0.25s ease; margin: 0; width: auto;
        }
        .btn.primary { background: var(--navy); color: #fff; }
        .btn.primary:hover { background: var(--accent); border-color: var(--accent); transform: translateY(-2px); text-decoration: none; }
        .btn.ghost { background: #fff; color: var(--navy); }
        .btn.ghost:hover { background: #f0f5ff; transform: translateY(-2px); text-decoration: none; }
        .btn.copied { background: #16a34a !important; border-color: #16a34a !important; color: #fff !important; }
        .again { display: block; margin-top: 14px; font-size: 12px; color: var(--accent); font-weight: 600; text-decoration: none; opacity: 0; animation: fadeIn 0.5s ease 1.7s forwards; }
        .again:hover { text-decoration: underline; }

        /* confetti */
        .confetti { position: absolute; inset: 0; pointer-events: none; overflow: hidden; z-index: 1; }
        .confetti i { position: absolute; top: -12px; width: 8px; height: 14px; border-radius: 2px; opacity: 0; animation: fall linear forwards; }
        @keyframes fall { 0% { opacity: 1; transform: translateY(0) rotate(0); } 100% { opacity: 0; transform: translateY(520px) rotate(540deg); } }

        @media (max-width: 600px) {
            .reg-card { padding: 30px 20px; }
            .form-grid { grid-template-columns: 1fr; }
            .pills { grid-template-columns: 1fr; }
        }
        @media (prefers-reduced-motion: reduce) {
            .blob, .reg-card .logo, .dz-icon { animation: none; }
        }
    </style>
</head>
<body class="reg-page">
    <div class="bg"><div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div></div>

    <div class="page">
        <div class="wrap">
            <a href="login.php" class="back-pill">
                <svg viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6"/></svg>
                Back to Login
            </a>

            <div class="reg-card">
                <div class="glow"></div>
                <img src="assets/cms-logo.png" alt="CMS Logo" class="logo">

<?php if ($isSuccess): ?>
                <div class="confetti" id="confetti"></div>
                <div class="success">
                    <div class="check-wrap">
                        <svg viewBox="0 0 100 100">
                            <circle class="ring" cx="50" cy="50" r="46"/>
                            <path class="tick" d="M30 52l14 14 27-30"/>
                        </svg>
                    </div>
                    <h3>Complaint Filed!</h3>
                    <p class="lead">We received your complaint and will review it soon.</p>

                    <div class="ticket">
                        <p class="t-label">Your Complaint Number</p>
                        <p class="t-num" id="ticketNum"><?php echo htmlspecialchars(isset($_GET['complaint_number']) ? $_GET['complaint_number'] : ''); ?></p>
                        <p class="t-note">Save this number. You'll need it to track your complaint.</p>
                    </div>

                    <div class="actions">
                        <button type="button" class="btn ghost" id="copyBtn">
                            <span id="copyText">Copy Number</span>
                        </button>
                        <a class="btn primary" href="track_complaint.php">Track this Complaint</a>
                    </div>
                    <a class="again" href="complaint_register.php">+ File another complaint</a>
                </div>

<?php else: ?>
                <h2>File a Complaint</h2>
                <p class="subtitle">New here? <a href="complainant_register.php">Register as a complainant first</a></p>

                <?php if (isset($_GET['new_complainant'])): ?>
                    <div class="banner ok">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 12.5l3 3 5-6"/></svg>
                        Registered successfully! Now let's file your complaint.
                    </div>
                <?php endif; ?>
                <?php if ($errorText !== ''): ?>
                    <div class="banner err">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v5M12 16.5v.01"/></svg>
                        <?php echo htmlspecialchars($errorText); ?>
                    </div>
                <?php endif; ?>

                <form id="complaintForm" action="save_complaint.php" method="POST" enctype="multipart/form-data" novalidate>
                    <div class="form-grid">

                        <div class="input-group">
                            <label for="complainant">Complainant</label>
                            <div class="field">
                                <svg class="ico" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>
                                <select name="complainant_id" id="complainant" required>
                                    <option value="">-- Select Complainant --</option>
                                    <?php
                                    $preselected = isset($_GET['new_complainant']) ? $_GET['new_complainant'] : null;
                                    while ($row = $complainants->fetch_assoc()): ?>
                                        <option value="<?php echo $row['complainant_id']; ?>" <?php if ($preselected == $row['complainant_id']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($row['full_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="err-text">Please choose a complainant.</div>
                        </div>

                        <div class="input-group">
                            <label for="category">Category</label>
                            <div class="field">
                                <svg class="ico" viewBox="0 0 24 24"><path d="M20.6 13.4l-7.2 7.2a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.2"/></svg>
                                <select name="category_id" id="category" required>
                                    <option value="">-- Select Category --</option>
                                    <?php while ($row = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $row['category_id']; ?>"><?php echo htmlspecialchars($row['category_name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="err-text">Please choose a category.</div>
                        </div>

                        <div class="input-group full">
                            <label for="subject">Subject</label>
                            <div class="field">
                                <svg class="ico" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                                <input type="text" name="subject" id="subject" placeholder="Short title of your complaint" required>
                            </div>
                            <div class="err-text">Please enter a subject.</div>
                        </div>

                        <div class="input-group full">
                            <div class="label-row">
                                <label for="description">Description</label>
                                <span class="counter" id="descCount">0 / 1000</span>
                            </div>
                            <div class="field">
                                <svg class="ico" viewBox="0 0 24 24"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6M8 13h8M8 17h6"/></svg>
                                <textarea name="description" id="description" rows="4" maxlength="1000" placeholder="Tell us what happened..." required></textarea>
                            </div>
                            <div class="err-text">Please describe your complaint.</div>
                        </div>

                        <div class="input-group">
                            <label for="dateFiled">Date Filed</label>
                            <div class="field">
                                <svg class="ico" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg>
                                <input type="date" name="date_filed" id="dateFiled" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="err-text" id="dateErr">Date filed cannot be a future date.</div>
                        </div>

                        <div class="input-group">
                            <label for="location">Location</label>
                            <div class="field">
                                <svg class="ico" viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                <input type="text" name="location" id="location" placeholder="Where did it happen? (optional)">
                            </div>
                        </div>

                        <div class="input-group full">
                            <label>Priority</label>
                            <div class="pills">
                                <label class="pill low"><input type="radio" name="priority" value="low"><span>Low</span></label>
                                <label class="pill med"><input type="radio" name="priority" value="medium" checked><span>Medium</span></label>
                                <label class="pill high"><input type="radio" name="priority" value="high"><span>High</span></label>
                            </div>
                        </div>

                        <div class="input-group full">
                            <label>Supporting Document <span style="text-transform:none;letter-spacing:0;font-weight:400;color:#94a3b8;">(optional)</span></label>
                            <div class="dropzone" id="dropzone">
                                <input type="file" name="supporting_document" id="fileInput" accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf">
                                <svg class="dz-icon" viewBox="0 0 24 24"><path d="M12 16V4M7 9l5-5 5 5"/><path d="M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3"/></svg>
                                <p class="dz-title">Drag &amp; drop a file here, or <b>browse</b></p>
                                <p class="dz-hint">JPG, PNG or PDF &bull; up to 5MB</p>
                            </div>
                            <div class="file-chip" id="fileChip">
                                <div class="thumb" id="thumb"></div>
                                <div class="f-info"><div class="f-name" id="fName"></div><div class="f-size" id="fSize"></div></div>
                                <button type="button" class="f-remove" id="fRemove" aria-label="Remove file">&times;</button>
                            </div>
                            <div class="dz-error" id="dzError"></div>
                        </div>

                    </div>

                    <button type="submit" class="submit" id="submitBtn" style="margin-top:20px;"><span class="spinner"></span><span id="btnText">Submit Complaint</span></button>
                </form>
<?php endif; ?>
            </div>
        </div>
    </div>

    <script>
<?php if ($isSuccess): ?>
        // confetti burst
        (function () {
            const box = document.getElementById('confetti');
            const colors = ['#2f6fed', '#16a34a', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'];
            for (let i = 0; i < 36; i++) {
                const c = document.createElement('i');
                c.style.left = (Math.random() * 100) + '%';
                c.style.background = colors[i % colors.length];
                c.style.animationDuration = (1.6 + Math.random() * 1.6) + 's';
                c.style.animationDelay = (0.9 + Math.random() * 0.6) + 's';
                c.style.transform = 'rotate(' + (Math.random() * 360) + 'deg)';
                box.appendChild(c);
            }
        })();

        // copy complaint number
        document.getElementById('copyBtn').addEventListener('click', function () {
            const num = document.getElementById('ticketNum').textContent.trim();
            const btn = this, label = document.getElementById('copyText');
            function done() {
                btn.classList.add('copied'); label.textContent = 'Copied!';
                setTimeout(() => { btn.classList.remove('copied'); label.textContent = 'Copy Number'; }, 1800);
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(num).then(done).catch(fallback);
            } else { fallback(); }
            function fallback() {
                const t = document.createElement('textarea');
                t.value = num; document.body.appendChild(t); t.select();
                try { document.execCommand('copy'); done(); } catch (e) {}
                document.body.removeChild(t);
            }
        });
<?php else: ?>
        const form = document.getElementById('complaintForm');
        const desc = document.getElementById('description');
        const descCount = document.getElementById('descCount');
        const dateEl = document.getElementById('dateFiled');

        function todayStr() {
            const d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }

        // ----- character counter -----
        function updateCount() {
            descCount.textContent = desc.value.length + ' / 1000';
            descCount.classList.toggle('warn', desc.value.length > 900);
        }
        desc.addEventListener('input', updateCount);
        updateCount();

        // ----- live validation -----
        const checks = [
            { el: document.getElementById('complainant'), ok: v => v !== '' },
            { el: document.getElementById('category'),    ok: v => v !== '' },
            { el: document.getElementById('subject'),     ok: v => v.trim().length >= 3 },
            { el: desc,                                   ok: v => v.trim().length >= 5 },
            { el: dateEl,                                 ok: v => v !== '' && v <= todayStr() }
        ];
        function setState(c, show) {
            const field = c.el.closest('.field');
            const good = c.ok(c.el.value);
            field.classList.toggle('invalid', show && !good);
            field.classList.toggle('valid', good && c.el.value !== '');
            return good;
        }
        checks.forEach(c => {
            c.el.addEventListener('blur', () => setState(c, true));
            c.el.addEventListener('input', () => setState(c, c.el.closest('.field').classList.contains('invalid')));
            c.el.addEventListener('change', () => setState(c, true));
        });

        // ----- file drop zone -----
        const dz = document.getElementById('dropzone');
        const fileInput = document.getElementById('fileInput');
        const chip = document.getElementById('fileChip');
        const thumb = document.getElementById('thumb');
        const fName = document.getElementById('fName');
        const fSize = document.getElementById('fSize');
        const dzError = document.getElementById('dzError');
        const MAX = 5 * 1024 * 1024;
        const OK_TYPES = ['image/jpeg', 'image/png', 'application/pdf'];
        let thumbUrl = null;

        function prettySize(b) { return b < 1048576 ? (b / 1024).toFixed(0) + ' KB' : (b / 1048576).toFixed(2) + ' MB'; }
        function showError(msg) {
            dzError.textContent = msg; dzError.classList.add('show');
            dz.classList.remove('bad'); void dz.offsetWidth; dz.classList.add('bad');
        }
        function clearFile() {
            fileInput.value = '';
            chip.classList.remove('show');
            if (thumbUrl) { URL.revokeObjectURL(thumbUrl); thumbUrl = null; }
            thumb.style.backgroundImage = ''; thumb.textContent = '';
        }
        function handleFile(file) {
            dzError.classList.remove('show'); dz.classList.remove('bad');
            if (!file) { clearFile(); return false; }
            if (OK_TYPES.indexOf(file.type) === -1) { clearFile(); showError('Only JPG, PNG, or PDF files are allowed.'); return false; }
            if (file.size > MAX) { clearFile(); showError('File size must not exceed 5MB.'); return false; }
            fName.textContent = file.name;
            fSize.textContent = prettySize(file.size);
            if (thumbUrl) URL.revokeObjectURL(thumbUrl);
            if (file.type.indexOf('image/') === 0) {
                thumbUrl = URL.createObjectURL(file);
                thumb.style.backgroundImage = 'url(' + thumbUrl + ')'; thumb.textContent = '';
            } else {
                thumb.style.backgroundImage = ''; thumb.textContent = 'PDF';
            }
            chip.classList.add('show');
            return true;
        }
        fileInput.addEventListener('change', () => handleFile(fileInput.files[0]));
        ['dragenter', 'dragover'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('drag'); }));
        ['dragleave', 'drop'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.remove('drag'); }));
        dz.addEventListener('drop', e => {
            const f = e.dataTransfer.files[0];
            if (!f) return;
            try {
                const dt = new DataTransfer(); dt.items.add(f); fileInput.files = dt.files;
            } catch (err) {}
            handleFile(f);
        });
        document.getElementById('fRemove').addEventListener('click', () => { clearFile(); dzError.classList.remove('show'); });

        // ----- submit -----
        form.addEventListener('submit', function (e) {
            let allGood = true, firstBad = null;
            checks.forEach(c => {
                const good = setState(c, true);
                if (!good) { allGood = false; if (!firstBad) firstBad = c.el; }
            });
            if (fileInput.files[0] && !handleFile(fileInput.files[0])) { allGood = false; }

            if (!allGood) {
                e.preventDefault();
                if (firstBad) firstBad.focus();
                return;
            }
            const btn = document.getElementById('submitBtn');
            btn.classList.add('loading');
            document.getElementById('btnText').textContent = 'Submitting...';
        });
<?php endif; ?>
    </script>
    <script src="assets/enhance.js"></script>
</body>
</html>