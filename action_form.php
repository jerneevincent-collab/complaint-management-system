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

$investigations = $conn->query(
    "SELECT i.investigation_id, c.complaint_id, c.complaint_number, c.subject
     FROM investigations i
     JOIN complaints c ON i.complaint_id = c.complaint_id
     WHERE i.status = 'Completed'
     ORDER BY i.investigation_id DESC"
);
$personnel = $conn->query("SELECT user_id, full_name FROM users WHERE status = 'active' ORDER BY full_name");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Corrective/Administrative Action</title>
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
    </style>
</head>
<body>
    <div class="topbar" id="topbar">
        <a class="back-link" href="index.php">&larr; Back to Home</a>
        <h2>Create Action</h2>
        <a class="logout" href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="flow">
            <div class="step done"><div class="dot"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div><span class="t">Assign</span></div>
            <div class="step done"><div class="dot"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></div><span class="t">Investigate</span></div>
            <div class="step current"><div class="dot">3</div><span class="t">Action</span></div>
            <div class="step"><div class="dot">4</div><span class="t">Resolve</span></div>
            <div class="step"><div class="dot">5</div><span class="t">Close</span></div>
        </div>

        <div class="form-card">
            <div class="card-head">
                <div class="card-icon green"><svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
                <div>
                    <h1>Corrective / Administrative Action</h1>
                    <p>Decide what will be done after the investigation.</p>
                </div>
            </div>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="notice ok"><svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg><p class='success-msg'>Action created successfully!</p></div>
            <?php endif; ?>
            <?php if (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
                <div class="notice err"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/></svg><p class='error-msg'>Please fill out all required fields.</p></div>
            <?php endif; ?>

            <form action="save_action.php" method="POST" id="actionForm" novalidate>
                <div class="warn-box" id="noInv">
                    <span>No completed investigations yet.</span> <a href="investigation_list.php">Go to Investigations &rarr;</a>
                </div>

                <div class="field full" style="animation-delay:.2s">
                    <label>Investigation <small>completed only</small></label>
                    <select name="investigation_data" required onchange="
                    var parts = this.value.split('|');
                    document.getElementById('complaint_id').value = parts[0];
                    document.getElementById('investigation_id').value = parts[1];
                ">
                        <option value="">-- Select Investigation --</option>
                        <?php while ($i = $investigations->fetch_assoc()): ?>
                            <option value="<?php echo $i['complaint_id']; ?>|<?php echo $i['investigation_id']; ?>">
                                <?php echo htmlspecialchars($i['complaint_number'] . " - " . $i['subject']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <input type="hidden" name="complaint_id" id="complaint_id">
                    <input type="hidden" name="investigation_id" id="investigation_id">
                    <span class="hint">Please choose an investigation.</span>
                </div>

                <div class="field full" style="animation-delay:.26s">
                    <label>Action Type</label>
                    <select name="action_type" id="actionType" required>
                        <option value="Corrective Action">Corrective Action</option>
                        <option value="Preventive Action">Preventive Action</option>
                        <option value="Employee Action">Employee Action</option>
                        <option value="Process Improvement">Process Improvement</option>
                        <option value="Service Recovery">Service Recovery</option>
                        <option value="Policy Recommendation">Policy Recommendation</option>
                        <option value="No Action Required">No Action Required</option>
                    </select>
                    <div class="type-hint" id="typeHint"></div>
                </div>

                <div class="field full" style="animation-delay:.32s">
                    <label>Action Description <small id="count">0 / 600</small></label>
                    <textarea name="action_description" id="desc" rows="4" maxlength="600" required placeholder="Describe what needs to be done..."></textarea>
                    <span class="hint">Please describe the action.</span>
                </div>

                <div class="field" style="animation-delay:.38s">
                    <label>Responsible Person</label>
                    <select name="responsible_person" required>
                        <option value="">-- Select Personnel --</option>
                        <?php while ($p = $personnel->fetch_assoc()): ?>
                            <option value="<?php echo $p['user_id']; ?>"><?php echo htmlspecialchars($p['full_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <span class="hint">Please choose who is responsible.</span>
                </div>

                <div class="field" style="animation-delay:.43s">
                    <label>Target Date <small>optional</small></label>
                    <input type="date" name="target_date" id="targetDate">
                    <span class="hint">Target date cannot be in the past.</span>
                </div>

                <button type="submit" class="submit" id="submitBtn"><span class="spin"></span><span id="btnText">Create Action</span></button>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const topbar = document.getElementById('topbar');
            window.addEventListener('scroll', () => topbar.classList.toggle('scrolled', window.scrollY > 6), { passive: true });

            const form = document.getElementById('actionForm');
            const invSel = form.querySelector('select[name=investigation_data]');
            const target = document.getElementById('targetDate');
            const desc = document.getElementById('desc');
            const type = document.getElementById('actionType');
            const hint = document.getElementById('typeHint');

            if (invSel.options.length <= 1) document.getElementById('noInv').classList.add('show');

            const t = new Date();
            const today = t.getFullYear() + '-' + String(t.getMonth() + 1).padStart(2, '0') + '-' + String(t.getDate()).padStart(2, '0');
            target.min = today;

            desc.addEventListener('input', () => { document.getElementById('count').textContent = desc.value.length + ' / 600'; });

            const hints = {
                'Corrective Action': 'Fix the problem that was found.',
                'Preventive Action': 'Stop the same problem from happening again.',
                'Employee Action': 'Action related to a staff member.',
                'Process Improvement': 'Change a process so it works better.',
                'Service Recovery': 'Make things right for the complainant.',
                'Policy Recommendation': 'Suggest a new or updated policy.',
                'No Action Required': 'The complaint needs no further action.'
            };
            function showHint() { hint.textContent = hints[type.value] || ''; hint.classList.toggle('show', !!hints[type.value]); }
            type.addEventListener('change', showHint); showHint();

            function check(el) {
                const f = el.closest('.field');
                let ok = true;
                if (el.required) ok = el.value.trim() !== '';
                if (el === target && target.value && target.value < today) ok = false;
                f.classList.toggle('bad', !ok);
                return ok;
            }
            const fields = form.querySelectorAll('select[required], textarea[required], #targetDate');
            fields.forEach(el => el.addEventListener('change', () => check(el)));
            desc.addEventListener('input', () => { if (desc.value.trim()) check(desc); });

            form.addEventListener('submit', function (e) {
                let allOk = true, first = null;
                fields.forEach(el => { if (!check(el)) { allOk = false; if (!first) first = el; } });
                if (!allOk) { e.preventDefault(); first.focus(); return; }
                document.getElementById('submitBtn').classList.add('loading');
                document.getElementById('btnText').textContent = 'Creating...';
            });
        })();
    </script>
    <script src="assets/enhance.js"></script>
</body>
</html>