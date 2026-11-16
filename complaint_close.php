<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [1, 5])) {
    die("Access denied. Only Administrators and Supervisors can close complaints.");
}

$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complaints = $conn->query(
    "SELECT c.complaint_id, c.complaint_number, c.subject, r.resolution_description, r.resolution_date
     FROM complaints c
     JOIN resolutions r ON c.complaint_id = r.complaint_id
     WHERE c.status = 'Resolved'
     ORDER BY c.complaint_id DESC"
);
$personnel = $conn->query("SELECT user_id, full_name FROM users WHERE status = 'active' ORDER BY full_name");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Complaint Closure</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/enhance.css">
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
    </style>
</head>
<body>
    <div class="topbar" id="topbar">
        <a class="back-link" href="index.php">&larr; Back to Home</a>
        <h2>Complaint Closure</h2>
        <a class="logout" href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="flow">
            <div class="step done"><div class="dot"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div><span class="t">Assign</span></div>
            <div class="step done"><div class="dot"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div><span class="t">Investigate</span></div>
            <div class="step done"><div class="dot"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div><span class="t">Action</span></div>
            <div class="step done"><div class="dot"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div><span class="t">Resolve</span></div>
            <div class="step current"><div class="dot">5</div><span class="t">Close</span></div>
        </div>

        <div class="card-head" style="opacity:0;animation:fadeUp .5s ease .1s forwards">
            <div class="card-icon teal"><svg viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg></div>
            <div>
                <h1>Complaint Closure</h1>
                <p>Close resolved complaints. Once closed, they can no longer be edited.</p>
            </div>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="notice ok"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg><p class='success-msg'>Complaint closed successfully!</p></div>
        <?php endif; ?>

        <?php if ($complaints->num_rows == 0): ?>
            <div class="empty-card">
                <div class="big"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div>
                <h3>All caught up!</h3>
                <p class="empty-msg">No resolved complaints are waiting to be closed.</p>
                <a href="complaint_list.php">View all complaints &rarr;</a>
            </div>
        <?php else: ?>
            <div class="summary"><span><strong><?php echo (int)$complaints->num_rows; ?></strong> resolved complaint<?php echo $complaints->num_rows == 1 ? '' : 's'; ?> waiting to be closed</span></div>
        <?php endif; ?>

        <?php $cardIndex = 0; while ($c = $complaints->fetch_assoc()): $cardIndex++; ?>
        <div class="closure-card" style="animation-delay:<?php echo 0.15 + min($cardIndex, 8) * 0.07; ?>s">
            <div class="top">
                <span class="complaint-tag"><?php echo htmlspecialchars($c['complaint_number']); ?></span>
                <span class="pill-resolved">Resolved</span>
            </div>
            <h3><?php echo htmlspecialchars($c['subject']); ?></h3>
            <div class="res-box">
                <small>Resolution</small>
                <?php echo htmlspecialchars($c['resolution_description']); ?>
                <span class="when"><?php echo $c['resolution_date']; ?></span>
            </div>

            <form action="save_closure.php" method="POST">
                <input type="hidden" name="complaint_id" value="<?php echo $c['complaint_id']; ?>">
                <div class="grow">
                    <label>Closed By</label>
                    <select name="closed_by" required>
                        <option value="">-- Select Personnel --</option>
                        <?php $personnel->data_seek(0); while ($p = $personnel->fetch_assoc()): ?>
                            <option value="<?php echo $p['user_id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn-close" onclick="return confirm('Confirm closure? This complaint will no longer be editable.');"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>Confirm Closure</button>
            </form>
        </div>
        <?php endwhile; ?>
    </div>

    <script>
        window.addEventListener('scroll', () => document.getElementById('topbar').classList.toggle('scrolled', window.scrollY > 6), { passive: true });
    </script>
    <script src="assets/enhance.js"></script>
</body>
</html>