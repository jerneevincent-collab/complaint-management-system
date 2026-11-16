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
$stmt = $conn->prepare("SELECT * FROM complainants WHERE complainant_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$complainant = $stmt->get_result()->fetch_assoc();

if (!$complainant) {
    die("Complainant not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Complainant</title>
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

        body .page-wrap { width:100% !important; max-width:680px !important; margin:0 auto !important; padding:28px 20px 60px !important; }
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
    
        .who { display:flex; align-items:center; gap:14px; margin-bottom:22px; position:relative; }
        .who .av { width:54px; height:54px; border-radius:50%; flex:0 0 54px; background:linear-gradient(135deg,#2f6fed,#12467f); color:#fff; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:700; box-shadow:0 8px 18px rgba(47,111,237,.3); }
        .who h1 { margin:0; font-size:20px; color:var(--navy); }
        .who p { margin:2px 0 0; font-size:13px; color:var(--muted); }
        .dirty { margin-left:auto; font-size:11.5px; font-weight:600; padding:4px 10px; border-radius:999px; background:#f1f5f9; color:#64748b; transition:.25s; white-space:nowrap; }
        .dirty.on { background:#fef3c7; color:#92400e; }
        .seg { display:flex; gap:8px; }
        .seg label { flex:1; display:block; cursor:pointer; margin:0; text-transform:none; letter-spacing:0; font-size:14px; }
        .seg input { display:none; }
        .seg span { display:flex; align-items:center; justify-content:center; gap:8px; padding:12px; border:1.5px solid #e2e8f0; border-radius:12px; background:#f4f7fb; font-size:14px; font-weight:600; color:#475569; transition:.2s; }
        .seg span::before { content:""; width:9px; height:9px; border-radius:50%; background:#cbd5e1; }
        .seg input:checked + span.act { background:#ecfdf5; border-color:#86efac; color:#065f46; box-shadow:0 0 0 4px rgba(34,197,94,.12); }
        .seg input:checked + span.act::before { background:#16a34a; }
        .seg input:checked + span.ina { background:#fef2f2; border-color:#fca5a5; color:#991b1b; box-shadow:0 0 0 4px rgba(239,68,68,.1); }
        .seg input:checked + span.ina::before { background:#dc2626; }
        .actions-row { grid-column:1 / -1; display:flex; gap:10px; align-items:center; }
        .actions-row .submit { grid-column:auto; flex:1; margin:0; }
        .cancel { padding:13px 22px; border-radius:12px; border:1.5px solid #dbe3ee; color:var(--navy); font-weight:600; font-size:14px; text-decoration:none !important; transition:.2s; background:#fff; }
        .cancel:hover { border-color:var(--accent); color:var(--accent); }
        @media (max-width:640px) { .form-card form { grid-template-columns:1fr; } }
    </style>
</head>
<body>
    <div class="topbar" id="topbar">
        <a class="back-link" href="complainant_list.php">&larr; Complainant List</a>
        <h2>Edit Complainant</h2>
        <a class="logout" href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="form-card">
            <div class="who">
                <div class="av"><?php echo htmlspecialchars(strtoupper(substr(trim($complainant['full_name']), 0, 1))); ?></div>
                <div>
                    <h1>Edit Complainant</h1>
                    <p><?php echo htmlspecialchars($complainant['full_name']); ?></p>
                </div>
                <span class="dirty" id="dirty">No changes</span>
            </div>

            <form action="update_complainant.php" method="POST" id="editForm" novalidate>
                <input type="hidden" name="complainant_id" value="<?php echo $complainant['complainant_id']; ?>">

                <div class="field full" style="animation-delay:.15s">
                    <label>Full Name</label>
                    <input type="text" name="full_name" data-rule="name" value="<?php echo htmlspecialchars($complainant['full_name']); ?>" required>
                    <span class="hint">Please enter a name (at least 2 letters).</span>
                </div>

                <div class="field" style="animation-delay:.2s">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" data-rule="phone" value="<?php echo htmlspecialchars($complainant['contact_number']); ?>" required>
                    <span class="hint">Enter a valid contact number.</span>
                </div>

                <div class="field" style="animation-delay:.25s">
                    <label>Email</label>
                    <input type="email" name="email" data-rule="email" value="<?php echo htmlspecialchars($complainant['email']); ?>" required>
                    <span class="hint">Enter a valid email address.</span>
                </div>

                <div class="field full" style="animation-delay:.3s">
                    <label>Address</label>
                    <input type="text" name="address" data-rule="text" value="<?php echo htmlspecialchars($complainant['address']); ?>" required>
                    <span class="hint">Please enter the address.</span>
                </div>

                <div class="field full" style="animation-delay:.35s">
                    <label>Organization/Department</label>
                    <input type="text" name="organization_department" data-rule="text" value="<?php echo htmlspecialchars($complainant['organization_department']); ?>" required>
                    <span class="hint">Please enter the organization or department.</span>
                </div>

                <div class="field full" style="animation-delay:.4s">
                    <label>Status</label>
                    <select name="status" id="statusSel" style="display:none">
                        <option value="active" <?php if ($complainant['status'] == 'active') echo 'selected'; ?>>Active</option>
                        <option value="inactive" <?php if ($complainant['status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
                    </select>
                    <div class="seg" id="seg">
                        <label><input type="radio" name="_seg" value="active"><span class="act">Active</span></label>
                        <label><input type="radio" name="_seg" value="inactive"><span class="ina">Inactive</span></label>
                    </div>
                </div>

                <div class="actions-row">
                    <a class="cancel" href="complainant_list.php">Cancel</a>
                    <button type="submit" class="submit" id="submitBtn" style="animation-delay:.45s"><span class="spin"></span><span id="btnText">Update</span></button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            window.addEventListener('scroll', () => document.getElementById('topbar').classList.toggle('scrolled', window.scrollY > 6), { passive: true });

            const form = document.getElementById('editForm');
            const sel = document.getElementById('statusSel');
            const dirty = document.getElementById('dirty');
            const inputs = Array.from(form.querySelectorAll('input[data-rule]'));

            // pretty Active / Inactive toggle that drives the real <select>
            const radios = document.querySelectorAll('#seg input');
            radios.forEach(r => { r.checked = (r.value === sel.value); r.addEventListener('change', () => { sel.value = r.value; refresh(); }); });

            const original = {};
            inputs.forEach(i => original[i.name] = i.value);
            original.status = sel.value;

            function refresh() {
                const changed = inputs.some(i => i.value !== original[i.name]) || sel.value !== original.status;
                dirty.textContent = changed ? 'Unsaved changes' : 'No changes';
                dirty.classList.toggle('on', changed);
            }

            const rules = {
                name:  v => v.trim().length >= 2,
                phone: v => v.replace(/\D/g, '').length >= 7,
                email: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()),
                text:  v => v.trim().length >= 2
            };
            function check(el) {
                const ok = rules[el.dataset.rule](el.value);
                el.closest('.field').classList.toggle('bad', !ok);
                return ok;
            }
            inputs.forEach(el => {
                el.addEventListener('input', () => { refresh(); if (el.closest('.field').classList.contains('bad')) check(el); });
                el.addEventListener('blur', () => check(el));
            });

            form.addEventListener('submit', function (e) {
                let allOk = true, first = null;
                inputs.forEach(el => { if (!check(el)) { allOk = false; if (!first) first = el; } });
                if (!allOk) { e.preventDefault(); first.focus(); return; }
                document.getElementById('submitBtn').classList.add('loading');
                document.getElementById('btnText').textContent = 'Saving...';
            });
        })();
    </script>
    <script src="assets/enhance.js"></script>
</body>
</html>