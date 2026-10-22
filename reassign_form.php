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
    <meta charset="UTF-8">
    <title>Reassign Complaint</title>
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

        body .page-wrap { width:100% !important; max-width:600px !important; margin:0 auto !important; padding:28px 20px 60px !important; }
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

        .form-card form { box-shadow:none; padding:0; max-width:100%; margin:0; background:none; display:grid; grid-template-columns:1fr; gap:16px 14px; position:relative; }
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
    
        .tag { display:inline-flex; align-items:center; gap:6px; background:#eef2f9; color:var(--navy); font-weight:700; padding:6px 12px; border-radius:999px; font-size:12.5px; margin-bottom:6px; }
        .tag::before { content:""; width:7px; height:7px; border-radius:50%; background:var(--accent); }
        .info { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin:0 0 18px; }
        .info div { background:#f4f7fb; border-radius:12px; padding:10px 14px; }
        .info small { display:block; font-size:10.5px; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; font-weight:600; }
        .info span { font-size:13.5px; font-weight:600; color:var(--navy); }
        .chips-row { display:flex; flex-wrap:wrap; gap:6px; }
        .rchip { background:#fff; border:1.5px solid #e2e8f0; color:#475569; padding:5px 11px; border-radius:999px; font-size:12px; font-weight:500; cursor:pointer; transition:.2s; margin:0; width:auto; font-family:'Poppins',sans-serif; }
        .rchip:hover { background:#eef4ff !important; border-color:var(--accent); color:var(--accent); transform:translateY(-1px); }
        .back-note { text-align:center; margin-top:14px; font-size:13px; }
        .back-note a { color:var(--muted); text-decoration:none; font-weight:500; }
        .back-note a:hover { color:var(--accent); }
        .card-icon.warm { background:linear-gradient(135deg,#f59e0b,#b45309); box-shadow:0 8px 18px rgba(245,158,11,.3); }
    </style>
</head>
<body>
    <div class="topbar" id="topbar">
        <a class="back-link" href="assignment_list.php">&larr; Assignment List</a>
        <h2>Reassign Complaint</h2>
        <a class="logout" href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="form-card">
            <div class="card-head">
                <div class="card-icon warm"><svg viewBox="0 0 24 24"><path d="M17 3l4 4-4 4M21 7H8a4 4 0 0 0-4 4v1M7 21l-4-4 4-4M3 17h13a4 4 0 0 0 4-4v-1"/></svg></div>
                <div>
                    <span class="tag"><?php echo htmlspecialchars($assignment['complaint_number']); ?></span>
                    <h1>Reassign Complaint</h1>
                    <p>Pick a new person and say why.</p>
                </div>
            </div>

            <div class="info">
                <div><small>Assigned on</small><span><?php echo htmlspecialchars($assignment['assignment_date']); ?></span></div>
                <div><small>Due date</small><span><?php echo htmlspecialchars($assignment['due_date']); ?></span></div>
            </div>

            <form action="save_reassignment.php" method="POST" id="reassignForm" novalidate>
                <input type="hidden" name="assignment_id" value="<?php echo $assignment['assignment_id']; ?>">
                <input type="hidden" name="complaint_id" value="<?php echo $assignment['complaint_id']; ?>">
                <input type="hidden" name="previous_assignee" value="<?php echo $assignment['assigned_to']; ?>">

                <div class="field" style="animation-delay:.2s">
                    <label>New Assignee</label>
                    <select name="new_assignee" required>
                        <?php while ($p = $personnel->fetch_assoc()): ?>
                            <option value="<?php echo $p['user_id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?><?php if ($p['user_id'] == $assignment['assigned_to']) echo ' (current)'; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="field" style="animation-delay:.27s">
                    <label>Reason for Reassignment <small id="count">0 / 300</small></label>
                    <textarea name="reason" id="reason" rows="3" maxlength="300" required placeholder="Why is this being reassigned?"></textarea>
                    <div class="chips-row">
                        <button type="button" class="rchip" data-text="Current assignee is unavailable.">Unavailable</button>
                        <button type="button" class="rchip" data-text="Workload is too heavy for the current assignee.">Heavy workload</button>
                        <button type="button" class="rchip" data-text="Needs a different department's expertise.">Needs expertise</button>
                        <button type="button" class="rchip" data-text="Conflict of interest.">Conflict of interest</button>
                    </div>
                    <span class="hint">Please write a reason.</span>
                </div>

                <button type="submit" class="submit" id="submitBtn" style="animation-delay:.4s"><span class="spin"></span><span id="btnText">Reassign</span></button>
            </form>
            <div class="back-note"><a href="assignment_list.php">Cancel and go back</a></div>
        </div>
    </div>

    <script>
        (function () {
            const topbar = document.getElementById('topbar');
            window.addEventListener('scroll', () => topbar.classList.toggle('scrolled', window.scrollY > 6), { passive: true });

            const form = document.getElementById('reassignForm');
            const reason = document.getElementById('reason');
            const count = document.getElementById('count');
            const field = reason.closest('.field');

            // start on the first person who is NOT the current assignee
            const sel = document.querySelector('select[name=new_assignee]');
            for (const o of sel.options) { if (!o.text.includes('(current)')) { sel.value = o.value; break; } }

            reason.addEventListener('input', () => {
                count.textContent = reason.value.length + ' / 300';
                if (reason.value.trim()) field.classList.remove('bad');
            });
            document.querySelectorAll('.rchip').forEach(b => b.addEventListener('click', () => {
                reason.value = b.dataset.text;
                reason.dispatchEvent(new Event('input'));
                reason.focus();
            }));

            form.addEventListener('submit', function (e) {
                if (!reason.value.trim()) {
                    e.preventDefault();
                    field.classList.add('bad');
                    reason.focus();
                    return;
                }
                document.getElementById('submitBtn').classList.add('loading');
                document.getElementById('btnText').textContent = 'Reassigning...';
            });
        })();
    </script>
</body>
</html>