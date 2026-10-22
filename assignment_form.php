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

$complaints = $conn->query(
    "SELECT complaint_id, complaint_number, subject FROM complaints
     WHERE status NOT IN ('Closed') ORDER BY complaint_id DESC"
);
$personnel = $conn->query("SELECT user_id, full_name FROM users WHERE status = 'active' ORDER BY full_name");
$departments = $conn->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Assign Complaint</title>
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
        .notice p { flex:1; }
        .notice-link { margin-left:auto; white-space:nowrap; font-weight:600; font-size:13px; color:#166534 !important; text-decoration:underline !important; text-underline-offset:3px; }
        .notice-link:hover { color:#14532d !important; }
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
    </style>
</head>
<body>
    <div class="topbar" id="topbar">
        <a class="back-link" href="index.php">&larr; Back to Home</a>
        <h2>Assign Complaint</h2>
        <a class="logout" href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="flow">
            <div class="step current"><div class="dot">1</div><span class="t">Assign</span></div>
            <div class="step"><div class="dot">2</div><span class="t">Investigate</span></div>
            <div class="step"><div class="dot">3</div><span class="t">Action</span></div>
            <div class="step"><div class="dot">4</div><span class="t">Resolve</span></div>
            <div class="step"><div class="dot">5</div><span class="t">Close</span></div>
        </div>

        <div class="form-card">
            <div class="card-head">
                <div class="card-icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6M17 8h5M19.5 5.5v5"/></svg></div>
                <div>
                    <h1>Assign Complaint</h1>
                    <p>Pick who will handle it and set the dates.</p>
                </div>
            </div>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="notice ok"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg><p class='success-msg'>Complaint assigned successfully!</p><a class='notice-link' href='assignment_list.php'>View Assignment List &rarr;</a></div>
            <?php endif; ?>
            <?php if (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
                <div class="notice err"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/></svg><p class='error-msg'><?php echo htmlspecialchars($_GET['msg'] ?? 'Assignment failed.'); ?></p></div>
            <?php endif; ?>

            <form action="save_assignment.php" method="POST" id="assignForm" novalidate>
                <div class="field full" style="animation-delay:.2s">
                    <label>Complaint</label>
                    <select name="complaint_id" required>
                        <option value="">-- Select Complaint --</option>
                        <?php while ($c = $complaints->fetch_assoc()): ?>
                            <option value="<?php echo $c['complaint_id']; ?>">
                                <?php echo htmlspecialchars($c['complaint_number'] . " - " . $c['subject']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <span class="hint">Please choose a complaint.</span>
                </div>

                <div class="field" style="animation-delay:.27s">
                    <label>Assign To (Personnel)</label>
                    <select name="assigned_to" required>
                        <option value="">-- Select Personnel --</option>
                        <?php while ($p = $personnel->fetch_assoc()): ?>
                            <option value="<?php echo $p['user_id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <span class="hint">Please choose who will handle it.</span>
                </div>

                <div class="field" style="animation-delay:.32s">
                    <label>Department</label>
                    <select name="department_id" required>
                        <?php while ($d = $departments->fetch_assoc()): ?>
                            <option value="<?php echo $d['department_id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="field" style="animation-delay:.37s">
                    <label>Assignment Date</label>
                    <input type="date" name="assignment_date" id="assignDate" required>
                    <span class="hint">Please pick a date.</span>
                </div>

                <div class="field" style="animation-delay:.42s">
                    <label>Due Date</label>
                    <input type="date" name="due_date" id="dueDate" required>
                    <span class="hint" id="dueHint">Due date cannot be before the assignment date.</span>
                </div>

                <div class="field full" style="animation-delay:.47s">
                    <label>Instructions <small id="count">0 / 500</small></label>
                    <textarea name="instructions" id="instr" rows="3" maxlength="500" placeholder="Anything the assigned person should know? (optional)"></textarea>
                </div>

                <button type="submit" class="submit" id="submitBtn"><span class="spin"></span><span id="btnText">Assign Complaint</span></button>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const topbar = document.getElementById('topbar');
            window.addEventListener('scroll', () => topbar.classList.toggle('scrolled', window.scrollY > 6), { passive: true });

            const form = document.getElementById('assignForm');
            const assign = document.getElementById('assignDate');
            const due = document.getElementById('dueDate');
            const instr = document.getElementById('instr');
            const count = document.getElementById('count');

            // default the assignment date to today (only if empty)
            const t = new Date();
            const today = t.getFullYear() + '-' + String(t.getMonth() + 1).padStart(2, '0') + '-' + String(t.getDate()).padStart(2, '0');
            if (!assign.value) assign.value = today;
            due.min = assign.value;
            assign.addEventListener('change', () => { due.min = assign.value; check(due); });

            instr.addEventListener('input', () => { count.textContent = instr.value.length + ' / 500'; });

            function check(el) {
                const f = el.closest('.field');
                let ok = el.value !== '';
                if (el === due && ok && assign.value && due.value < assign.value) ok = false;
                f.classList.toggle('bad', !ok);
                return ok;
            }
            form.querySelectorAll('select[required], input[required]').forEach(el => {
                el.addEventListener('change', () => check(el));
                el.addEventListener('blur', () => { if (el.value) check(el); });
            });

            form.addEventListener('submit', function (e) {
                let allOk = true, first = null;
                form.querySelectorAll('select[required], input[required]').forEach(el => {
                    if (!check(el)) { allOk = false; if (!first) first = el; }
                });
                if (!allOk) { e.preventDefault(); first.focus(); return; }
                const btn = document.getElementById('submitBtn');
                btn.classList.add('loading');
                document.getElementById('btnText').textContent = 'Assigning...';
            });
        })();
    </script>
</body>
</html>