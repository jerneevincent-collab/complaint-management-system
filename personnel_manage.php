<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    die("Access denied. Administrator access only.");
}

$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_personnel'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $role_id = $_POST['role_id'];
    $department_id = $_POST['department_id'];
    $specialization = trim($_POST['specialization']);
    $password = password_hash("password123", PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO users (full_name, email, contact_number, role_id, department_id, specialization, password, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'active')"
    );
    $stmt->bind_param("sssiiss", $full_name, $email, $contact_number, $role_id, $department_id, $specialization, $password);
    $stmt->execute();

    header("Location: personnel_manage.php");
    exit();
}

if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $conn->prepare("UPDATE users SET status = IF(status='active','inactive','active') WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: personnel_manage.php");
    exit();
}

$roles = $conn->query("SELECT role_id, role_name FROM roles ORDER BY role_name");
$departments = $conn->query("SELECT department_id, department_name FROM departments ORDER BY department_name");

$result = $conn->query(
    "SELECT u.*, r.role_name, d.department_name
     FROM users u
     LEFT JOIN roles r ON u.role_id = r.role_id
     LEFT JOIN departments d ON u.department_id = d.department_id
     ORDER BY u.user_id DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Personnel Management</title>
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
    
        .content-grid { display:grid; grid-template-columns:340px 1fr; gap:20px; align-items:start; }
        .side { position:sticky; top:76px; }
        .form-card { background:#fff; border-radius:18px; padding:22px; box-shadow:0 8px 24px rgba(10,37,64,.08); position:relative; overflow:hidden; opacity:0; animation:fadeUp .55s ease .1s forwards; }
        .form-card::before { content:""; position:absolute; top:-70px; right:-70px; width:180px; height:180px; background:var(--accent); filter:blur(80px); opacity:.16; pointer-events:none; }
        .card-head { display:flex; align-items:center; gap:12px; margin-bottom:16px; position:relative; }
        .card-icon { width:40px; height:40px; border-radius:12px; flex:0 0 40px; background:linear-gradient(135deg,#2f6fed,#12467f); display:flex; align-items:center; justify-content:center; box-shadow:0 8px 18px rgba(47,111,237,.3); }
        .card-icon svg { width:20px; height:20px; stroke:#fff; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .card-head h3 { margin:0; font-size:16px; color:var(--navy); }
        .card-head p { margin:2px 0 0; font-size:12px; color:var(--muted); }
        .form-card form { box-shadow:none; padding:0; max-width:100%; margin:0; background:none; display:flex; flex-direction:column; gap:13px; position:relative; }
        .field { display:flex; flex-direction:column; gap:5px; }
        .field label { margin:0; font-size:11px; font-weight:600; color:var(--navy); text-transform:uppercase; letter-spacing:.1em; display:flex; justify-content:space-between; }
        .field label small { text-transform:none; letter-spacing:0; font-weight:500; color:#94a3b8; font-size:11px; }
        .field input, .field select { width:100%; margin:0; padding:11px 13px; border:1.5px solid #e2e8f0; border-radius:12px; background:#f4f7fb; font-size:13.5px; color:var(--navy); outline:none; transition:.25s; }
        .field input:focus, .field select:focus { border-color:var(--accent); background:#fff; box-shadow:0 0 0 4px rgba(47,111,237,.12); }
        .field.bad input { border-color:#f08a8a; background:#fff7f7; animation:shake .35s ease; }
        .hint { font-size:11.5px; color:#dc2626; display:none; }
        .field.bad .hint { display:block; }
        @keyframes shake { 20%,60% { transform:translateX(-4px); } 40%,80% { transform:translateX(4px); } }
        .pw-note { display:flex; gap:8px; align-items:flex-start; background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; padding:10px 12px; border-radius:12px; font-size:12px; line-height:1.45; }
        .pw-note svg { width:16px; height:16px; flex:0 0 16px; margin-top:1px; fill:none; stroke:currentColor; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .submit { margin:2px 0 0; width:100%; border:none; border-radius:12px; padding:13px; background:var(--navy); color:#fff; font-weight:700; font-size:14px; cursor:pointer; transition:.25s; display:flex; align-items:center; justify-content:center; gap:10px; }
        .submit:hover { background:var(--accent) !important; transform:translateY(-2px); box-shadow:0 10px 22px rgba(47,111,237,.3); }
        .submit.loading { pointer-events:none; opacity:.85; }
        .submit .spin { width:15px; height:15px; border:2.5px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; display:none; animation:rot .7s linear infinite; }
        .submit.loading .spin { display:block; }
        @keyframes rot { to { transform:rotate(360deg); } }

        .status-pill { display:inline-flex; align-items:center; gap:6px; padding:4px 11px; border-radius:20px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.02em; white-space:nowrap; }
        .status-pill::before { content:""; width:6px; height:6px; border-radius:50%; background:currentColor; }
        .status-active { background:#d1fae5; color:#065f46; }
        .status-inactive { background:#fee2e2; color:#991b1b; }
        .role-tag { display:inline-block; background:#eef2f9; color:var(--navy); padding:3px 10px; border-radius:8px; font-size:12px; font-weight:600; white-space:nowrap; }
        .email { color:var(--muted); word-break:normal; overflow-wrap:anywhere; min-width:170px; }
        .tog { display:inline-block; padding:6px 12px; border-radius:8px; border:1.5px solid; font-weight:600; font-size:12.5px; text-decoration:none !important; transition:.2s; white-space:nowrap; }
        .tog.off { border-color:#dc2626; color:#dc2626; } .tog.off:hover { background:#dc2626; color:#fff; }
        .tog.on { border-color:#16a34a; color:#16a34a; } .tog.on:hover { background:#16a34a; color:#fff; }
        .table-card tbody tr.inactive td { opacity:.65; }
        .table-scroll { max-height:none; }
        body .page-wrap { max-width:1240px !important; }
        @media (max-width:980px) { .content-grid { grid-template-columns:1fr; } .side { position:static; } }
    </style>
</head>
<body>
    <div class="topbar" id="topbar">
        <a class="back-link" href="index.php">&larr; Back to Home</a>
        <h2>Personnel Management</h2>
        <a class="logout" href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="page-header">
            <div>
                <h1>Personnel</h1>
                <p>Add staff and turn their accounts on or off.</p>
            </div>
        </div>

        <div class="content-grid">
            <div class="side">
                <div class="form-card">
                    <div class="card-head">
                        <div class="card-icon"><svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6M17 8h5M19.5 5.5v5"/></svg></div>
                        <div>
                            <h3>Add New Personnel</h3>
                            <p>Creates an active account.</p>
                        </div>
                    </div>
                    <form method="POST" id="addForm" novalidate>
                        <div class="field">
                            <label>Full Name</label>
                            <input type="text" name="full_name" data-rule="name" required>
                            <span class="hint">Please enter a name.</span>
                        </div>

                        <div class="field">
                            <label>Email</label>
                            <input type="email" name="email" data-rule="email" required>
                            <span class="hint">Enter a valid email address.</span>
                        </div>

                        <div class="field">
                            <label>Contact Number <small>optional</small></label>
                            <input type="text" name="contact_number">
                        </div>

                        <div class="field">
                            <label>Role</label>
                            <select name="role_id" required>
                                <?php $roles->data_seek(0); while ($r = $roles->fetch_assoc()): ?>
                                    <option value="<?php echo $r['role_id']; ?>"><?php echo htmlspecialchars($r['role_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label>Department</label>
                            <select name="department_id" required>
                                <?php $departments->data_seek(0); while ($d = $departments->fetch_assoc()): ?>
                                    <option value="<?php echo $d['department_id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label>Specialization <small>optional</small></label>
                            <input type="text" name="specialization">
                        </div>

                        <div class="pw-note">
                            <svg viewBox="0 0 24 24"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                            <span>New accounts start with the default password <strong>password123</strong>. Ask them to change it after logging in.</span>
                        </div>

                        <button type="submit" name="add_personnel" class="submit" id="addBtn"><span class="spin"></span><span id="addText">Add Personnel</span></button>
                    </form>
                </div>
            </div>

            <div>
                <div class="chips" id="chips"></div>

                <div class="search-row">
                    <input type="text" id="quickSearch" placeholder="Search name, role, department or email...">
                </div>

                <div class="table-card">
                    <div class="table-meta">
                        <span>Showing <strong id="shownCount">0</strong> of <strong id="totalCount">0</strong> personnel</span>
                        <span id="chipNote"></span>
                    </div>
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Department</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="rows">
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr data-status="<?php echo htmlspecialchars($row['status']); ?>" class="<?php echo $row['status'] == 'inactive' ? 'inactive' : ''; ?>">
                        <td><div class="person"><span class="avatar"><?php echo htmlspecialchars(strtoupper(substr(trim($row['full_name']), 0, 1))); ?></span><span class="c-num" style="font-weight:600"><?php echo htmlspecialchars($row['full_name']); ?></span></div></td>
                        <td><span class="role-tag"><?php echo htmlspecialchars($row['role_name']); ?></span></td>
                        <td><?php echo htmlspecialchars($row['department_name']); ?></td>
                        <td class="email"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><span class="status-pill status-<?php echo $row['status']; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                        <td><a class="tog <?php echo $row['status'] == 'active' ? 'off' : 'on'; ?>" href="?toggle=<?php echo $row['user_id']; ?>"<?php if ($row['status'] == 'active'): ?> onclick="return confirm('Deactivate this account? They will no longer be assignable.');"<?php endif; ?>><?php echo $row['status'] == 'active' ? 'Deactivate' : 'Activate'; ?></a></td>
                    </tr>
                    <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="empty" id="empty">
                        <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6M17 11l4 4m0-4l-4 4"/></svg>
                        <h3>No personnel found</h3>
                        <p>Nothing matches your search or filter.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const topbar = document.getElementById('topbar');
            window.addEventListener('scroll', () => topbar.classList.toggle('scrolled', window.scrollY > 6), { passive: true });

            // ----- add form validation -----
            const form = document.getElementById('addForm');
            const rules = { name: v => v.trim().length >= 2, email: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) };
            const ins = Array.from(form.querySelectorAll('input[data-rule]'));
            function check(el) { const ok = rules[el.dataset.rule](el.value); el.closest('.field').classList.toggle('bad', !ok); return ok; }
            ins.forEach(el => { el.addEventListener('blur', () => check(el)); el.addEventListener('input', () => { if (el.closest('.field').classList.contains('bad')) check(el); }); });
            form.addEventListener('submit', e => {
                let ok = true, first = null;
                ins.forEach(el => { if (!check(el)) { ok = false; if (!first) first = el; } });
                if (!ok) { e.preventDefault(); first.focus(); return; }
                document.getElementById('addBtn').classList.add('loading');
                document.getElementById('addText').textContent = 'Adding...';
            });

            // ----- table: chips + search -----
            const rows = Array.from(document.querySelectorAll('#rows tr'));
            const shown = document.getElementById('shownCount');
            const total = document.getElementById('totalCount');
            const empty = document.getElementById('empty');
            const chipsBox = document.getElementById('chips');
            const note = document.getElementById('chipNote');
            const search = document.getElementById('quickSearch');
            let current = 'all';
            rows.forEach((r, i) => { r.style.animationDelay = (0.25 + Math.min(i, 14) * 0.04) + 's'; });

            const counts = {};
            rows.forEach(r => { const s = r.dataset.status.toLowerCase(); counts[s] = (counts[s] || 0) + 1; });
            const labels = { all: 'All', active: 'Active', inactive: 'Inactive' };
            Object.keys(labels).forEach(key => {
                const n = key === 'all' ? rows.length : (counts[key] || 0);
                if (key !== 'all' && n === 0) return;
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'chip' + (key === 'all' ? ' active' : '');
                b.innerHTML = labels[key] + ' <b>' + n + '</b>';
                b.addEventListener('click', () => {
                    current = key;
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
                    const ok = (current === 'all' || r.dataset.status.toLowerCase() === current) && (!q || r.textContent.toLowerCase().includes(q));
                    r.classList.toggle('hidden', !ok);
                    if (ok) { n++; r.style.opacity = 1; }
                });
                shown.textContent = n;
                empty.classList.toggle('show', n === 0);
                const bits = [];
                if (current !== 'all') bits.push('Status: ' + labels[current]);
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