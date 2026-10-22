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

$id = $_GET['id'];
$stmt = $conn->prepare(
    "SELECT c.*, co.full_name AS complainant_name, cc.category_name
     FROM complaints c
     JOIN complainants co ON c.complainant_id = co.complainant_id
     JOIN complaint_categories cc ON c.category_id = cc.category_id
     WHERE c.complaint_id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$complaint = $stmt->get_result()->fetch_assoc();

if (!$complaint) {
    die("Complaint not found.");
}

$statusClass = strtolower(str_replace(' ', '-', $complaint['status']));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Complaint Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        :root { --navy:#0a2540; --accent:#2f6fed; --bg:#f4f7fb; --ink:#1b2733; --muted:#6b7787; }
        * { box-sizing: border-box; font-family:'Poppins',sans-serif; }
        body { color:var(--ink); }

        /* ---------- topbar ---------- */
        .topbar {
            background: linear-gradient(135deg,#0a2540 0%,#12467f 100%); color:#fff;
            position:sticky; top:0; z-index:50; display:flex; align-items:center; justify-content:space-between;
            padding:14px 30px; transition: box-shadow .25s ease, padding .25s ease; animation: topIn .5s ease both;
        }
        .topbar.scrolled { box-shadow:0 8px 24px rgba(10,37,64,.25); padding-top:10px; padding-bottom:10px; }
        @keyframes topIn { from { transform:translateY(-100%); } to { transform:translateY(0); } }
        .topbar a { color:#fff; text-decoration:none; font-size:14px; font-weight:500; opacity:.9; transition:.2s; z-index:1; }
        .topbar a:hover { opacity:1; color:#fff; text-decoration:none; }
        .topbar .back-link:hover { transform:translateX(-3px); }
        .topbar .logout:hover { color:#ffb4b4; }
        .topbar h2 { position:absolute; left:50%; transform:translateX(-50%); margin:0; color:#fff; font-size:18px; font-weight:600; white-space:nowrap; }

        body .page-wrap { width:100% !important; max-width:720px !important; margin:0 auto !important; padding:28px 20px 60px !important; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }

        /* ---------- workflow stepper ---------- */
        .flow { display:flex; align-items:flex-start; justify-content:space-between; margin:0 0 22px; opacity:0; animation:fadeUp .5s ease .05s forwards; }
        .flow .step { flex:1; text-align:center; position:relative; font-size:11.5px; font-weight:600; color:#94a3b8; }
        .flow .step::before { content:""; position:absolute; top:13px; left:-50%; width:100%; height:3px; background:#dbe3ee; z-index:0; }
        .flow .step:first-child::before { display:none; }
        .flow .dot { position:relative; z-index:1; width:28px; height:28px; border-radius:50%; background:#dbe3ee; color:#fff; display:flex; align-items:center; justify-content:center; margin:0 auto 6px; font-size:12px; }
        .flow .step.current { color:var(--navy); }
        .flow .step.current .dot { background:var(--accent); box-shadow:0 0 0 5px rgba(47,111,237,.18); animation:ping 2s ease-out infinite; }
        .flow .step.current::before { background:#dbe3ee; }
        @keyframes ping { 0% { box-shadow:0 0 0 0 rgba(47,111,237,.5); } 70% { box-shadow:0 0 0 10px rgba(47,111,237,0); } 100% { box-shadow:0 0 0 0 rgba(47,111,237,0); } }

        /* ---------- form card ---------- */
        .form-card {
            background:#fff; border-radius:20px; padding:30px 32px 32px; position:relative; overflow:hidden;
            box-shadow:0 12px 34px rgba(10,37,64,.09); opacity:0; animation:fadeUp .55s ease .1s forwards;
        }
        .form-card::before { content:""; position:absolute; top:-70px; right:-70px; width:200px; height:200px; background:var(--accent); filter:blur(90px); opacity:.16; pointer-events:none; }
        .card-head { display:flex; align-items:center; gap:14px; margin-bottom:22px; position:relative; }
        .card-icon { width:46px; height:46px; border-radius:14px; background:linear-gradient(135deg,#2f6fed,#12467f); display:flex; align-items:center; justify-content:center; box-shadow:0 8px 18px rgba(47,111,237,.3); flex:0 0 46px; }
        .card-icon svg { width:23px; height:23px; stroke:#fff; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .card-head h1 { margin:0; font-size:21px; color:var(--navy); font-weight:700; }
        .card-head p { margin:2px 0 0; font-size:13px; color:var(--muted); }

        .notice { display:flex; align-items:center; gap:10px; padding:12px 14px; border-radius:12px; font-size:13px; font-weight:500; margin-bottom:18px; animation:fadeUp .4s ease both; }
        .notice svg { width:20px; height:20px; flex:0 0 20px; fill:none; stroke:currentColor; stroke-width:2.2; stroke-linecap:round; stroke-linejoin:round; }
        .notice.ok { background:#ecfdf5; color:#166534; border:1px solid #bbf7d0; }
        .notice.err { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
        .notice p { margin:0 !important; color:inherit !important; }

        .form-card form { box-shadow:none; padding:0; max-width:100%; margin:0; background:none; display:grid; grid-template-columns:1fr 1fr; gap:16px 14px; position:relative; }
        .field { display:flex; flex-direction:column; gap:6px; opacity:0; animation:fadeUp .45s ease forwards; min-width:0; }
        .field.full { grid-column:1 / -1; }
        .field label { margin:0; font-size:11px; font-weight:600; color:var(--navy); text-transform:uppercase; letter-spacing:.1em; display:flex; justify-content:space-between; }
        .field label small { text-transform:none; letter-spacing:0; font-weight:500; color:#94a3b8; font-size:11px; }
        .field input, .field select, .field textarea {
            width:100%; margin:0; padding:12px 14px; border:1.5px solid #e2e8f0; border-radius:12px; background:#f4f7fb;
            font-size:14px; color:var(--navy); outline:none; transition:.25s; resize:vertical;
        }
        .field input:focus, .field select:focus, .field textarea:focus { border-color:var(--accent); background:#fff; box-shadow:0 0 0 4px rgba(47,111,237,.12); }
        .field.bad input, .field.bad select { border-color:#f08a8a; background:#fff7f7; animation:shake .35s ease; }
        .hint { font-size:11.5px; color:#dc2626; min-height:0; display:none; }
        .field.bad .hint { display:block; }
        @keyframes shake { 20%,60% { transform:translateX(-4px); } 40%,80% { transform:translateX(4px); } }

        .submit {
            grid-column:1 / -1; margin:6px 0 0; width:100%; border:none; border-radius:12px; padding:14px;
            background:var(--navy); color:#fff; font-weight:700; font-size:15px; cursor:pointer; transition:.25s;
            display:flex; align-items:center; justify-content:center; gap:10px; opacity:0; animation:fadeUp .45s ease .55s forwards;
        }
        .submit:hover { background:var(--accent); transform:translateY(-2px); box-shadow:0 10px 22px rgba(47,111,237,.3); }
        .submit.loading { pointer-events:none; opacity:.85 !important; }
        .submit .spin { width:16px; height:16px; border:2.5px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; display:none; animation:rot .7s linear infinite; }
        .submit.loading .spin { display:block; }
        @keyframes rot { to { transform:rotate(360deg); } }

        @media (max-width:640px) {
            .topbar h2 { display:none; } .topbar { padding:12px 16px; }
            .form-card { padding:24px 20px; } .form-card form { grid-template-columns:1fr; }
            .flow .step span.t { display:none; }
        }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration:.01ms !important; animation-delay:0s !important; } }
    
        .flow .step.done { color:#16a34a; }
        .flow .step.done .dot { background:#16a34a; }
        .flow .step.done::before, .flow .step.current::before { background:#16a34a; }
        .flow .step.done .dot svg { width:14px; height:14px; stroke:#fff; fill:none; stroke-width:3; stroke-linecap:round; stroke-linejoin:round; }
        .warn-box { grid-column:1 / -1; display:none; align-items:center; gap:10px; background:#fffbeb; border:1px solid #fde68a; color:#92400e; padding:12px 14px; border-radius:12px; font-size:13px; }
        .warn-box.show { display:flex; animation:fadeUp .4s ease both; }
        .warn-box a { color:#b45309; font-weight:700; }
    
        .type-hint { font-size:12px; color:var(--muted); background:#f4f7fb; border-radius:10px; padding:8px 12px; display:none; }
        .type-hint.show { display:block; animation:fadeUp .3s ease both; }
        .card-icon.green { background:linear-gradient(135deg,#10b981,#0f766e); box-shadow:0 8px 18px rgba(16,185,129,.3); }
    
        .card-icon.purple { background:linear-gradient(135deg,#8b5cf6,#5b21b6); box-shadow:0 8px 18px rgba(139,92,246,.3); }
        .rule-box { grid-column:1 / -1; display:flex; gap:10px; background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; padding:12px 14px; border-radius:12px; font-size:12.5px; line-height:1.5; }
        .rule-box svg { width:18px; height:18px; flex:0 0 18px; margin-top:2px; fill:none; stroke:currentColor; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .chips-row { display:flex; flex-wrap:wrap; gap:6px; }
        .rchip { background:#fff; border:1.5px solid #e2e8f0; color:#475569; padding:5px 11px; border-radius:999px; font-size:12px; font-weight:500; cursor:pointer; transition:.2s; margin:0; width:auto; font-family:'Poppins',sans-serif; }
        .rchip:hover { background:#eef4ff !important; border-color:var(--accent); color:var(--accent); transform:translateY(-1px); }
    
        .summary { display:flex; align-items:center; justify-content:space-between; gap:10px; margin:0 0 16px; font-size:13px; color:var(--muted); opacity:0; animation:fadeUp .5s ease .08s forwards; }
        .summary strong { color:var(--navy); }
        .closure-card {
            background:#fff; border-radius:18px; padding:22px 24px; margin-bottom:16px; position:relative; overflow:hidden;
            box-shadow:0 8px 24px rgba(10,37,64,.08); opacity:0; animation:fadeUp .5s ease forwards;
            border-left:5px solid #16a34a; transition:transform .25s ease, box-shadow .25s ease;
        }
        .closure-card:hover { transform:translateY(-3px); box-shadow:0 14px 30px rgba(10,37,64,.12); }
        .closure-card .top { display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; margin-bottom:10px; }
        .complaint-tag { display:inline-flex; align-items:center; gap:6px; background:#eef2f9; color:var(--navy); font-weight:700; padding:6px 12px; border-radius:999px; font-size:12.5px; }
        .complaint-tag::before { content:""; width:7px; height:7px; border-radius:50%; background:#16a34a; }
        .pill-resolved { background:#d1fae5; color:#065f46; padding:4px 11px; border-radius:20px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.02em; }
        .closure-card h3 { margin:0 0 10px; font-size:16px; color:var(--navy); }
        .res-box { background:#f4f7fb; border-radius:12px; padding:12px 14px; font-size:13px; color:#334155; line-height:1.55; margin-bottom:16px; }
        .res-box small { display:block; font-size:10.5px; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; font-weight:600; margin-bottom:4px; }
        .res-box .when { display:block; margin-top:6px; color:var(--muted); font-size:12px; }
        .closure-card form { box-shadow:none; background:none; padding:0; margin:0; max-width:100%; display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; }
        .closure-card .grow { flex:1; min-width:200px; display:flex; flex-direction:column; gap:6px; }
        .closure-card label { margin:0; font-size:11px; font-weight:600; color:var(--navy); text-transform:uppercase; letter-spacing:.1em; }
        .closure-card select { width:100%; margin:0; padding:12px 14px; border:1.5px solid #e2e8f0; border-radius:12px; background:#f4f7fb; font-size:14px; color:var(--navy); outline:none; transition:.25s; }
        .closure-card select:focus { border-color:var(--accent); background:#fff; box-shadow:0 0 0 4px rgba(47,111,237,.12); }
        .btn-close {
            margin:0; width:auto; border:none; border-radius:12px; padding:13px 22px; background:#16a34a; color:#fff;
            font-weight:700; font-size:14px; cursor:pointer; transition:.25s; display:inline-flex; align-items:center; gap:8px;
        }
        .btn-close:hover { background:#15803d !important; transform:translateY(-2px); box-shadow:0 10px 22px rgba(22,163,74,.3); }
        .btn-close svg { width:16px; height:16px; fill:none; stroke:#fff; stroke-width:2.6; stroke-linecap:round; stroke-linejoin:round; }

        .empty-card { background:#fff; border-radius:20px; padding:56px 24px; text-align:center; box-shadow:0 8px 24px rgba(10,37,64,.07); opacity:0; animation:fadeUp .5s ease .1s forwards; }
        .empty-card .big { width:72px; height:72px; border-radius:50%; background:#ecfdf5; display:flex; align-items:center; justify-content:center; margin:0 auto 14px; }
        .empty-card .big svg { width:34px; height:34px; stroke:#16a34a; fill:none; stroke-width:2.4; stroke-linecap:round; stroke-linejoin:round; }
        .empty-card h3 { margin:0 0 4px; color:var(--navy); font-size:17px; }
        .empty-card p { margin:0; color:var(--muted); font-size:13px; }
        .empty-card a { display:inline-block; margin-top:16px; color:var(--accent); font-weight:600; font-size:13px; text-decoration:none; }
        .card-icon.teal { background:linear-gradient(135deg,#10b981,#0f766e); box-shadow:0 8px 18px rgba(16,185,129,.3); }
        body .page-wrap { max-width:760px !important; }
    
        body .page-wrap { max-width:820px !important; }
        .detail-card { background:#fff; border-radius:20px; box-shadow:0 12px 34px rgba(10,37,64,.09); overflow:hidden; opacity:0; animation:fadeUp .55s ease .1s forwards; }
        .hero { background:linear-gradient(135deg,#0a2540 0%,#2f6fed 130%); color:#fff; padding:26px 30px; position:relative; overflow:hidden; }
        .hero::after { content:""; position:absolute; right:-60px; top:-60px; width:200px; height:200px; border-radius:50%; background:rgba(255,255,255,.08); }
        .hero small { font-size:11px; letter-spacing:.14em; text-transform:uppercase; opacity:.75; }
        .hero h1 { margin:4px 0 12px; font-size:26px; font-weight:700; letter-spacing:.5px; color:#fff; }
        .hero .badges { display:flex; gap:8px; flex-wrap:wrap; position:relative; z-index:1; }
        .status-badge { display:inline-block; padding:5px 13px; border-radius:20px; font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; }
        .status-submitted { background:#dbeafe; color:#1e40af; }
        .status-for-assignment, .status-assigned { background:#fef3c7; color:#92400e; }
        .status-under-investigation, .status-action-required, .status-for-resolution { background:#fed7aa; color:#9a3412; }
        .status-resolved { background:#d1fae5; color:#065f46; }
        .status-closed { background:#e2e8f0; color:#475569; }
        .prio { display:inline-flex; align-items:center; gap:6px; padding:5px 13px; border-radius:20px; font-size:11.5px; font-weight:700; background:rgba(255,255,255,.18); color:#fff; }
        .prio::before { content:""; width:7px; height:7px; border-radius:50%; background:#fff; }
        .prio.low::before { background:#4ade80; } .prio.medium::before { background:#fbbf24; } .prio.high::before { background:#f87171; animation:blink 1.4s ease-in-out infinite; }
        @keyframes blink { 50% { opacity:.3; } }

        .body { padding:26px 30px 30px; }
        .progress { display:flex; margin:0 0 26px; }
        .progress .p { flex:1; text-align:center; position:relative; font-size:10px; font-weight:600; color:#94a3b8; line-height:1.25; }
        .progress .p::before { content:""; position:absolute; top:9px; left:-50%; width:100%; height:3px; background:#e2e8f0; }
        .progress .p:first-child::before { display:none; }
        .progress .p i { position:relative; z-index:1; display:block; width:20px; height:20px; border-radius:50%; background:#e2e8f0; margin:0 auto 6px; }
        .progress .p.done { color:#16a34a; } .progress .p.done i { background:#16a34a; } .progress .p.done::before { background:#16a34a; }
        .progress .p.now { color:var(--navy); } .progress .p.now i { background:var(--accent); box-shadow:0 0 0 5px rgba(47,111,237,.18); animation:ping 2s ease-out infinite; } .progress .p.now::before { background:#16a34a; }
        @keyframes ping { 0% { box-shadow:0 0 0 0 rgba(47,111,237,.5); } 70% { box-shadow:0 0 0 10px rgba(47,111,237,0); } 100% { box-shadow:0 0 0 0 rgba(47,111,237,0); } }

        .detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:22px; }
        .detail-item { background:#f4f7fb; border-radius:14px; padding:14px 16px; opacity:0; animation:fadeUp .45s ease forwards; }
        .detail-item.full { grid-column:1 / -1; }
        .detail-item label { display:block; margin:0 0 4px; font-size:10.5px; text-transform:uppercase; letter-spacing:.1em; color:#94a3b8; font-weight:600; }
        .detail-item .value { color:var(--navy); font-size:14.5px; font-weight:500; line-height:1.55; }
        .detail-item.desc .value { font-weight:400; color:#334155; }

        .status-update-box { border:1.5px dashed #cbd5e1; border-radius:14px; padding:18px; }
        .status-update-box h3 { margin:0 0 10px; font-size:13px; color:var(--navy); text-transform:uppercase; letter-spacing:.1em; }
        .status-update-box form { display:flex; gap:10px; box-shadow:none; padding:0; max-width:100%; margin:0; background:none; flex-wrap:wrap; }
        .status-update-box select { flex:1; min-width:200px; margin:0; padding:12px 14px; border:1.5px solid #e2e8f0; border-radius:12px; background:#f4f7fb; font-size:14px; color:var(--navy); outline:none; transition:.25s; }
        .status-update-box select:focus { border-color:var(--accent); background:#fff; box-shadow:0 0 0 4px rgba(47,111,237,.12); }
        .status-update-box button { background:var(--navy); color:#fff; border:none; padding:12px 26px; border-radius:12px; font-weight:700; cursor:pointer; margin:0; width:auto; transition:.25s; }
        .status-update-box button:hover { background:var(--accent); transform:translateY(-2px); box-shadow:0 10px 22px rgba(47,111,237,.3); }
        .status-update-box button.loading { opacity:.7; pointer-events:none; }

        .closed-notice { display:flex; align-items:center; gap:10px; background:#f1f5f9; border:1px solid #e2e8f0; padding:14px 16px; border-radius:14px; color:#475569; font-size:14px; }
        .closed-notice svg { width:20px; height:20px; flex:0 0 20px; fill:none; stroke:#64748b; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        @media (max-width:640px) { .detail-grid { grid-template-columns:1fr; } .hero, .body { padding-left:20px; padding-right:20px; } .progress .p { font-size:9px; } }
    </style>
</head>
<body>
    <div class="topbar" id="topbar">
        <a class="back-link" href="complaint_list.php">&larr; Complaint List</a>
        <h2>Complaint Details</h2>
        <a class="logout" href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="detail-card">
            <div class="hero">
                <small>Complaint number</small>
                <h1><?php echo htmlspecialchars($complaint['complaint_number']); ?></h1>
                <div class="badges">
                    <span class="status-badge status-<?php echo $statusClass; ?>" id="statusBadge"><?php echo htmlspecialchars($complaint['status']); ?></span>
                    <span class="prio <?php echo htmlspecialchars($complaint['priority']); ?>"><?php echo ucfirst($complaint['priority']); ?> priority</span>
                </div>
            </div>

            <div class="body">
                <div class="progress" id="progress"></div>

                <div class="detail-grid">
                    <div class="detail-item" style="animation-delay:.25s">
                        <label>Complainant</label>
                        <div class="value"><?php echo htmlspecialchars($complaint['complainant_name']); ?></div>
                    </div>
                    <div class="detail-item" style="animation-delay:.3s">
                        <label>Category</label>
                        <div class="value"><?php echo htmlspecialchars($complaint['category_name']); ?></div>
                    </div>
                    <div class="detail-item full" style="animation-delay:.35s">
                        <label>Subject</label>
                        <div class="value"><?php echo htmlspecialchars($complaint['subject']); ?></div>
                    </div>
                    <div class="detail-item full desc" style="animation-delay:.4s">
                        <label>Description</label>
                        <div class="value"><?php echo nl2br(htmlspecialchars($complaint['description'])); ?></div>
                    </div>
                    <div class="detail-item" style="animation-delay:.45s">
                        <label>Date Filed</label>
                        <div class="value"><?php echo $complaint['date_filed']; ?></div>
                    </div>
                    <div class="detail-item" style="animation-delay:.5s">
                        <label>Location</label>
                        <div class="value"><?php echo htmlspecialchars($complaint['location'] ?: '—'); ?></div>
                    </div>
                    <div class="detail-item" style="animation-delay:.55s">
                        <label>Priority</label>
                        <div class="value"><?php echo ucfirst($complaint['priority']); ?></div>
                    </div>
                </div>

                <?php if ($complaint['status'] != 'Closed'): ?>
                <div class="status-update-box">
                    <h3>Update Status</h3>
                    <form action="update_complaint_status.php" method="POST" id="statusForm">
                        <input type="hidden" name="complaint_id" value="<?php echo $complaint['complaint_id']; ?>">
                        <select name="new_status">
                            <option value="Submitted" <?php if ($complaint['status'] == 'Submitted') echo 'selected'; ?>>Submitted</option>
                            <option value="For Assignment" <?php if ($complaint['status'] == 'For Assignment') echo 'selected'; ?>>For Assignment</option>
                            <option value="Assigned" <?php if ($complaint['status'] == 'Assigned') echo 'selected'; ?>>Assigned</option>
                            <option value="Under Investigation" <?php if ($complaint['status'] == 'Under Investigation') echo 'selected'; ?>>Under Investigation</option>
                            <option value="Action Required" <?php if ($complaint['status'] == 'Action Required') echo 'selected'; ?>>Action Required</option>
                            <option value="For Resolution" <?php if ($complaint['status'] == 'For Resolution') echo 'selected'; ?>>For Resolution</option>
                            <option value="Resolved" <?php if ($complaint['status'] == 'Resolved') echo 'selected'; ?>>Resolved</option>
                            <option value="Closed" <?php if ($complaint['status'] == 'Closed') echo 'selected'; ?>>Closed</option>
                        </select>
                        <button type="submit" id="updBtn">Update</button>
                    </form>
                </div>
                <?php else: ?>
                <div class="closed-notice"><svg viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>This complaint is closed and can no longer be modified.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        (function () {
            window.addEventListener('scroll', () => document.getElementById('topbar').classList.toggle('scrolled', window.scrollY > 6), { passive: true });

            // progress tracker (display only)
            const steps = ['Submitted', 'For Assignment', 'Assigned', 'Under Investigation', 'Action Required', 'For Resolution', 'Resolved', 'Closed'];
            const current = document.getElementById('statusBadge').textContent.trim();
            const idx = steps.indexOf(current);
            const box = document.getElementById('progress');
            steps.forEach((s, i) => {
                const d = document.createElement('div');
                d.className = 'p' + (i < idx || (idx === steps.length - 1) ? ' done' : (i === idx ? ' now' : ''));
                d.innerHTML = '<i></i>' + s;
                box.appendChild(d);
            });

            const form = document.getElementById('statusForm');
            if (form) form.addEventListener('submit', () => {
                const b = document.getElementById('updBtn');
                b.classList.add('loading'); b.textContent = 'Updating...';
            });
        })();
    </script>
</body>
</html>