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

if (isset($_POST['update_investigation_status'])) {
    $investigation_id = $_POST['investigation_id'];
    $new_status = $_POST['new_status'];
    $stmt = $conn->prepare("UPDATE investigations SET status = ? WHERE investigation_id = ?");
    $stmt->bind_param("si", $new_status, $investigation_id);
    $stmt->execute();
    header("Location: investigation_list.php");
    exit();
}

$result = $conn->query(
    "SELECT i.*, c.complaint_number, c.subject, u.full_name AS investigator_name
     FROM investigations i
     JOIN complaints c ON i.complaint_id = c.complaint_id
     JOIN users u ON i.investigator_id = u.user_id
     ORDER BY i.investigation_id DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Investigation List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/enhance.css">
    <style>
        :root { --navy:#0a2540; --accent:#2f6fed; --bg:#f4f7fb; --ink:#1b2733; --muted:#6b7787; }
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { color: var(--ink); }

        /* ---------- topbar ---------- */
        .topbar {
            background: linear-gradient(135deg, #0a2540 0%, #12467f 100%);
            color:#fff; position: sticky; top:0; z-index: 50;
            display:flex; align-items:center; justify-content:space-between;
            padding: 14px 30px; transition: box-shadow .25s ease, padding .25s ease;
            animation: topIn .5s ease both;
        }
        .topbar.scrolled { box-shadow: 0 8px 24px rgba(10,37,64,.25); padding-top:10px; padding-bottom:10px; }
        @keyframes topIn { from { transform: translateY(-100%); } to { transform: translateY(0); } }
        .topbar a { color:#fff; text-decoration:none; font-size:14px; font-weight:500; opacity:.9; transition: .2s; z-index:1; }
        .topbar a:hover { opacity:1; color:#fff; text-decoration:none; }
        .topbar .back-link:hover { transform: translateX(-3px); }
        .topbar .logout:hover { color:#ffb4b4; }
        .topbar h2 {
            position:absolute; left:50%; transform:translateX(-50%); margin:0;
            color:#fff; font-size:18px; font-weight:600; white-space:nowrap;
        }

        /* ---------- layout ---------- */
        body .page-wrap {
            width:100% !important; max-width:1200px !important;
            margin:0 auto !important; padding:28px 20px 60px !important;
        }
        .page-header { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px; flex-wrap:wrap; }
        .page-header h1 { margin:0; font-size:22px; color:var(--navy); font-weight:700; }
        .page-header p { margin:2px 0 0; font-size:13px; color:var(--muted); }
        .btn-primary {
            background: var(--accent); color:#fff !important; padding:11px 18px; border-radius:12px;
            font-weight:600; font-size:14px; text-decoration:none !important;
            box-shadow: 0 8px 18px rgba(47,111,237,.28); transition: .2s; display:inline-flex; align-items:center; gap:6px;
        }
        .btn-primary:hover { background: var(--navy); transform: translateY(-2px); }

        @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }

        /* ---------- status chips (quick filter on the rows shown) ---------- */
        .chips { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; opacity:0; animation: fadeUp .5s ease .05s forwards; }
        .chip {
            display:inline-flex; align-items:center; gap:8px; cursor:pointer;
            background:#fff; border:1.5px solid #e2e8f0; color:#475569;
            padding:7px 14px; border-radius:999px; font-size:12.5px; font-weight:600;
            transition:.2s; font-family:'Poppins',sans-serif; margin:0; width:auto;
        }
        .chip b { background:#eef2f8; color:var(--navy); border-radius:999px; padding:1px 8px; font-size:11.5px; transition:.2s; }
        .chip:hover { border-color: var(--accent); color: var(--accent); transform: translateY(-1px); }
        .chip.active { background: var(--navy); border-color: var(--navy); color:#fff; }
        .chip.active b { background: rgba(255,255,255,.2); color:#fff; }

        /* ---------- filter card ---------- */
        .filter-card {
            background:#fff; border-radius:16px; padding:18px 20px; margin-bottom:20px;
            box-shadow:0 8px 24px rgba(10,37,64,.07);
            opacity:0; animation: fadeUp .5s ease .1s forwards;
        }
        .filter-card form {
            display:flex; flex-wrap:wrap; gap:10px; background:none; box-shadow:none;
            padding:0; max-width:100%; margin:0;
        }
        .filter-card input, .filter-card select {
            flex:1; min-width:140px; padding:10px 12px; border:1.5px solid #e2e8f0; border-radius:10px;
            background:#f4f7fb; font-size:13px; color:var(--navy); margin:0; width:auto; outline:none; transition:.25s;
        }
        .filter-card input:focus, .filter-card select:focus {
            border-color: var(--accent); background:#fff; box-shadow:0 0 0 4px rgba(47,111,237,.12);
        }
        .filter-card .btn-search {
            background: var(--navy); color:#fff; border:none; padding:10px 22px; border-radius:10px;
            font-weight:600; cursor:pointer; font-size:13px; margin:0; width:auto; transition:.2s;
        }
        .filter-card .btn-search:hover { background: var(--accent); transform: translateY(-1px); }
        .filter-card .btn-clear {
            display:inline-flex; align-items:center; padding:10px 14px; border-radius:10px; font-size:13px; font-weight:600;
            color:var(--muted); text-decoration:none; transition:.2s;
        }
        .filter-card .btn-clear:hover { color:#dc2626; text-decoration:none; }

        /* ---------- table ---------- */
        .table-card {
            background:#fff; border-radius:16px; overflow:hidden;
            box-shadow:0 8px 24px rgba(10,37,64,.07);
            opacity:0; animation: fadeUp .5s ease .15s forwards;
        }
        .table-meta { display:flex; justify-content:space-between; align-items:center; padding:14px 20px; border-bottom:1px solid #eef1f5; font-size:13px; color:var(--muted); }
        .table-meta strong { color:var(--navy); }
        .table-scroll { overflow:auto; max-height: 68vh; }
        .table-card table { width:100%; margin:0; box-shadow:none; border-radius:0; border-collapse:separate; border-spacing:0; font-size:13px; }
        .table-card th {
            background:var(--navy); color:#fff; text-align:left; padding:13px 14px; font-size:12px;
            text-transform:uppercase; letter-spacing:.06em; font-weight:600; white-space:nowrap;
            position:sticky; top:0; z-index:2;
        }
        .table-card td { padding:13px 14px; border-bottom:1px solid #eef1f5; vertical-align:middle; color:#334155; background:#fff; transition: background .2s; }
        .table-card tbody tr { opacity:0; animation: rowIn .45s ease forwards; }
        @keyframes rowIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .table-card tbody tr:hover td { background:#f3f7ff; }
        .table-card tbody tr:last-child td { border-bottom:none; }
        .table-card tbody tr.hidden { display:none; }

        .c-num { font-weight:700; color:var(--navy); white-space:nowrap; }
        .person { display:flex; align-items:center; gap:10px; min-width:140px; }
        .avatar { width:30px; height:30px; border-radius:50%; flex:0 0 30px; background:linear-gradient(135deg,#2f6fed,#12467f); color:#fff; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; }
        .subject { max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .date { white-space:nowrap; color:var(--muted); }

        .status-badge { display:inline-block; padding:4px 11px; border-radius:20px; font-size:11px; font-weight:600; text-transform:uppercase; white-space:nowrap; letter-spacing:.02em; }
        .status-submitted { background:#dbeafe; color:#1e40af; }
        .status-for-assignment, .status-assigned { background:#fef3c7; color:#92400e; }
        .status-under-investigation, .status-action-required, .status-for-resolution { background:#fed7aa; color:#9a3412; }
        .status-resolved { background:#d1fae5; color:#065f46; }
        .status-closed { background:#e2e8f0; color:#475569; }

        .priority-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:8px; font-size:11.5px; font-weight:600; }
        .priority-badge::before { content:""; width:7px; height:7px; border-radius:50%; background:currentColor; }
        .priority-low { background:#dcfce7; color:#166534; }
        .priority-medium { background:#fef3c7; color:#92400e; }
        .priority-high { background:#fee2e2; color:#991b1b; }
        .priority-high::before { animation: blink 1.4s ease-in-out infinite; }
        @keyframes blink { 50% { opacity:.25; } }

        .view-btn {
            display:inline-block; padding:6px 14px; border-radius:8px; border:1.5px solid var(--accent); color:var(--accent);
            font-weight:600; font-size:12.5px; text-decoration:none !important; transition:.2s; white-space:nowrap;
        }
        .view-btn:hover { background:var(--accent); color:#fff; }

        /* empty state */
        .empty { text-align:center; padding:56px 20px; display:none; }
        .empty.show { display:block; animation: fadeUp .4s ease both; }
        .empty svg { width:64px; height:64px; stroke:#a9b6c8; fill:none; stroke-width:1.5; stroke-linecap:round; stroke-linejoin:round; margin-bottom:10px; }
        .empty h3 { margin:0 0 4px; color:var(--navy); font-size:16px; }
        .empty p { margin:0; color:var(--muted); font-size:13px; }

        @media (max-width:700px) {
            .topbar h2 { display:none; }
            .topbar { padding:12px 16px; }
        }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation-duration:.01ms !important; animation-delay:0s !important; } }
    
        .status-pill { display:inline-block; padding:4px 11px; border-radius:20px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.02em; }
        .status-active { background:#dbeafe; color:#1e40af; }
        .status-reassigned { background:#fef3c7; color:#92400e; }
        .status-completed { background:#d1fae5; color:#065f46; }
        .due { white-space:nowrap; color:var(--muted); }
        .due.overdue { color:#b91c1c; font-weight:600; }
        .due small { display:block; font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
        .reassign-btn { display:inline-block; padding:6px 14px; border-radius:8px; border:1.5px solid #b45309; color:#b45309; font-weight:600; font-size:12.5px; text-decoration:none !important; transition:.2s; white-space:nowrap; }
        .reassign-btn:hover { background:#b45309; color:#fff; }
        .search-row { display:flex; gap:10px; margin-bottom:16px; opacity:0; animation: fadeUp .5s ease .08s forwards; }
        .search-row input { flex:1; padding:11px 14px 11px 40px; border:1.5px solid #e2e8f0; border-radius:12px; background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='M21 21l-4.3-4.3'/%3E%3C/svg%3E") no-repeat 13px center / 18px; font-size:13px; outline:none; transition:.25s; margin:0; }
        .search-row input:focus { border-color:var(--accent); box-shadow:0 0 0 4px rgba(47,111,237,.12); }
        .empty svg { stroke:#a9b6c8; }
    
        .table-card td form { box-shadow:none !important; background:none !important; padding:0 !important; margin:0 !important; max-width:none !important; border-radius:0 !important; }
        .status-select-wrap { position:relative; display:inline-block; }
        .status-select {
            appearance:none; -webkit-appearance:none; margin:0; width:auto; min-width:160px;
            padding:7px 30px 7px 14px; border-radius:999px; border:1.5px solid transparent; cursor:pointer;
            font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.02em; outline:none; transition:.2s;
            background-repeat:no-repeat; background-position:right 11px center; background-size:10px;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 8' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M1 1.5l5 5 5-5'/%3E%3C/svg%3E");
        }
        .status-select:hover { box-shadow:0 0 0 3px rgba(47,111,237,.15); }
        .status-select option { text-transform:none; font-weight:500; color:#1b2733; background:#fff; }
        .st-assigned    { background-color:#dbeafe; color:#1e40af; }
        .st-in-progress { background-color:#fef3c7; color:#92400e; }
        .st-evidence-gathering { background-color:#fed7aa; color:#9a3412; }
        .st-findings-prepared  { background-color:#ede9fe; color:#5b21b6; }
        .st-completed   { background-color:#d1fae5; color:#065f46; }

        .stage { display:flex; gap:3px; margin-top:7px; }
        .stage i { width:100%; max-width:30px; height:4px; border-radius:3px; background:#e2e8f0; transition:.4s; }
        .stage i.on { background:var(--accent); }
        .stage.done i.on { background:#16a34a; }

        .actions { display:flex; gap:6px; flex-wrap:wrap; }
        .act { display:inline-block; padding:6px 12px; border-radius:8px; border:1.5px solid var(--accent); color:var(--accent); font-weight:600; font-size:12.5px; text-decoration:none !important; transition:.2s; white-space:nowrap; }
        .act:hover { background:var(--accent); color:#fff; }
        .act.alt { border-color:#0f766e; color:#0f766e; }
        .act.alt:hover { background:#0f766e; color:#fff; }
    </style>
</head>
<body>
    <div class="topbar" id="topbar">
        <a class="back-link" href="index.php">&larr; Back to Home</a>
        <h2>Investigation List</h2>
        <a class="logout" href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="page-header">
            <div>
                <h1>Investigations</h1>
                <p>Track every investigation from start to finish.</p>
            </div>
            <a href="investigation_form.php" class="btn-primary">+ New Investigation</a>
        </div>

        <div class="chips" id="chips"></div>

        <div class="search-row">
            <input type="text" id="quickSearch" placeholder="Search complaint #, subject or investigator...">
        </div>

        <div class="table-card">
            <div class="table-meta">
                <span>Showing <strong id="shownCount">0</strong> of <strong id="totalCount">0</strong> investigations</span>
                <span id="chipNote"></span>
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Complaint #</th>
                            <th>Subject</th>
                            <th>Investigator</th>
                            <th>Start Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="rows">
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr data-status="<?php echo htmlspecialchars($row['status']); ?>">
                    <td class="c-num"><?php echo htmlspecialchars($row['complaint_number']); ?></td>
                    <td><div class="subject" title="<?php echo htmlspecialchars($row['subject']); ?>"><?php echo htmlspecialchars($row['subject']); ?></div></td>
                    <td><div class="person"><span class="avatar"><?php echo htmlspecialchars(strtoupper(substr(trim($row['investigator_name']), 0, 1))); ?></span><span><?php echo htmlspecialchars($row['investigator_name']); ?></span></div></td>
                    <td class="date"><?php echo $row['start_date']; ?></td>
                    <td>
                        <form action="update_investigation_status.php" method="POST" style="margin:0;">
                            <input type="hidden" name="investigation_id" value="<?php echo $row['investigation_id']; ?>">
                            <select class="status-select" name="new_status" onchange="this.form.submit()">
                                <option value="Assigned" <?php if ($row['status'] == 'Assigned') echo 'selected'; ?>>Assigned</option>
                                <option value="In Progress" <?php if ($row['status'] == 'In Progress') echo 'selected'; ?>>In Progress</option>
                                <option value="Evidence Gathering" <?php if ($row['status'] == 'Evidence Gathering') echo 'selected'; ?>>Evidence Gathering</option>
                                <option value="Findings Prepared" <?php if ($row['status'] == 'Findings Prepared') echo 'selected'; ?>>Findings Prepared</option>
                                <option value="Completed" <?php if ($row['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                            </select>
                        </form>
                        <div class="stage"><i></i><i></i><i></i><i></i><i></i></div>
                    </td>
                    <td>
                        <div class="actions">
                            <a class="act" href="investigation_findings_form.php?investigation_id=<?php echo $row['investigation_id']; ?>">Add Findings</a>
                            <a class="act alt" href="investigation_evidence_form.php?investigation_id=<?php echo $row['investigation_id']; ?>">Manage Evidence</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="empty" id="empty">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <h3>No investigations found</h3>
                <p>Nothing matches your search or filter.</p>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const topbar = document.getElementById('topbar');
            window.addEventListener('scroll', () => topbar.classList.toggle('scrolled', window.scrollY > 6), { passive: true });

            const stages = ['Assigned', 'In Progress', 'Evidence Gathering', 'Findings Prepared', 'Completed'];
            const rows = Array.from(document.querySelectorAll('#rows tr'));
            const shown = document.getElementById('shownCount');
            const total = document.getElementById('totalCount');
            const empty = document.getElementById('empty');
            const chipsBox = document.getElementById('chips');
            const note = document.getElementById('chipNote');
            const search = document.getElementById('quickSearch');
            let current = 'All';

            rows.forEach((r, i) => { r.style.animationDelay = (0.25 + Math.min(i, 14) * 0.04) + 's'; });

            // colour the status dropdown + progress dots (display only; the form still submits as before)
            function paint(r) {
                const sel = r.querySelector('.status-select');
                const idx = stages.indexOf(sel.value);
                sel.className = 'status-select st-' + sel.value.toLowerCase().replace(/ /g, '-');
                const st = r.querySelector('.stage');
                st.classList.toggle('done', idx === stages.length - 1);
                st.querySelectorAll('i').forEach((d, k) => d.classList.toggle('on', k <= idx));
            }
            rows.forEach(paint);

            const counts = {};
            rows.forEach(r => { const s = r.dataset.status; counts[s] = (counts[s] || 0) + 1; });
            ['All'].concat(stages).forEach(name => {
                const n = name === 'All' ? rows.length : (counts[name] || 0);
                if (name !== 'All' && n === 0) return;
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'chip' + (name === 'All' ? ' active' : '');
                b.innerHTML = name + ' <b>' + n + '</b>';
                b.addEventListener('click', () => {
                    current = name;
                    chipsBox.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
                    b.classList.add('active');
                    apply();
                });
                chipsBox.appendChild(b);
            });

            function apply() {
                const q = search.value.trim().toLowerCase();
                let n = 0;
                rows.forEach(r => {
                    const ok = (current === 'All' || r.dataset.status === current) &&
                               (!q || r.textContent.toLowerCase().includes(q));
                    r.classList.toggle('hidden', !ok);
                    if (ok) { n++; r.style.opacity = 1; }
                });
                shown.textContent = n;
                empty.classList.toggle('show', n === 0);
                const bits = [];
                if (current !== 'All') bits.push('Status: ' + current);
                if (q) bits.push('Search: "' + search.value.trim() + '"');
                note.textContent = bits.join(' · ');
            }
            search.addEventListener('input', apply);
            total.textContent = rows.length;
            apply();
            if (rows.length === 0) chipsBox.style.display = 'none';
        })();
    </script>
    <script src="assets/enhance.js"></script>
</body>
</html>