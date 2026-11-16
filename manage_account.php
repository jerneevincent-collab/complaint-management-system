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

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $specialization = trim($_POST['specialization']);

    $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, contact_number=?, specialization=? WHERE user_id=?");
    $stmt->bind_param("ssssi", $full_name, $email, $contact_number, $specialization, $user_id);
    $stmt->execute();

    $_SESSION['full_name'] = $full_name;

    if (!empty($_POST['new_password'])) {
        $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $pwStmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
        $pwStmt->bind_param("si", $hashed, $user_id);
        $pwStmt->execute();
    }

    header("Location: manage_account.php?status=success");
    exit();
}

$stmt = $conn->prepare("SELECT u.*, r.role_name, d.department_name FROM users u LEFT JOIN roles r ON u.role_id = r.role_id LEFT JOIN departments d ON u.department_id = d.department_id WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Account activity (display only)
$loginStmt = $conn->prepare("SELECT COUNT(*) AS total, MAX(created_at) AS last_login FROM audit_logs WHERE user_id = ? AND activity = 'User logged in'");
$loginStmt->bind_param("i", $user_id);
$loginStmt->execute();
$loginInfo = $loginStmt->get_result()->fetch_assoc();
$totalLogins = (int) $loginInfo['total'];
$lastLogin = $loginInfo['last_login'] ? date('M j, Y g:i A', strtotime($loginInfo['last_login'])) : '-';

$asgStmt = $conn->prepare("SELECT COUNT(*) AS c FROM assignments WHERE assigned_to = ? AND status = 'active'");
$asgStmt->bind_param("i", $user_id);
$asgStmt->execute();
$activeAssignments = (int) $asgStmt->get_result()->fetch_assoc()['c'];

$memberSince = date('F j, Y', strtotime($user['created_at']));
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Account</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/enhance.css">
    <style>
        :root { --navy:#0a2540; --accent:#2f6fed; --bg:#f4f7fb; }
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { color:#1b2733; }

        .topbar {
            background: var(--navy);
            color:#fff;
            position:relative;
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:14px 30px;
        }
        .topbar h2 {
            position:absolute;
            left:50%;
            transform:translateX(-50%);
            margin:0;
            color:#fff;
            font-size:18px;
            font-weight:600;
            white-space:nowrap;
        }
        .topbar a { color:#fff; text-decoration:none; font-size:14px; z-index:1; font-weight:500; }
        .topbar a:hover { color:#fff; text-decoration:none; }
        .topbar a.logout { color:#fca5a5; }

        body .page-wrap {
            width:100% !important;
            max-width:1000px !important;
            margin:0 auto !important;
            padding:30px 20px 60px !important;
        }

        /* ----- Toast ----- */
        .toast {
            background:#ecfdf5; color:#065f46; border-left:4px solid #10b981;
            padding:12px 16px; border-radius:10px; font-size:14px; font-weight:500;
            margin-bottom:20px; box-shadow:0 6px 20px rgba(16,185,129,0.12);
            animation: slideDown 0.4s ease, fadeOut 0.5s ease 4s forwards;
        }
        @keyframes slideDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
        @keyframes fadeOut { to { opacity:0; visibility:hidden; height:0; padding:0; margin:0; } }

        /* ----- Profile hero ----- */
        .profile-hero {
            position:relative; overflow:hidden;
            background:#fff; border-radius:18px; margin-bottom:24px;
            box-shadow:0 8px 24px rgba(10,37,64,0.07);
            opacity:0; animation:fadeUp 0.5s ease forwards;
        }
        .hero-banner {
            height:130px; position:relative; overflow:hidden;
            background:linear-gradient(120deg, #0a2540 0%, #12467f 55%, #2f6fed 100%);
        }
        .hero-banner::before, .hero-banner::after {
            content:""; position:absolute; border-radius:50%; background:rgba(255,255,255,0.08);
        }
        .hero-banner::before { width:240px; height:240px; right:-50px; top:-110px; animation:floaty 7s ease-in-out infinite; }
        .hero-banner::after { width:150px; height:150px; right:180px; bottom:-90px; animation:floaty 9s ease-in-out infinite reverse; }
        @keyframes floaty { 0%,100% { transform:translateY(0); } 50% { transform:translateY(14px); } }

        .hero-body { padding:0 30px 26px; display:flex; align-items:flex-end; gap:20px; flex-wrap:wrap; }
        .avatar-ring {
            width:124px; height:124px; border-radius:50%; background:#fff; padding:5px;
            margin-top:-62px; box-shadow:0 8px 20px rgba(10,37,64,0.18); flex:0 0 124px; overflow:hidden; position:relative; z-index:2;
        }
        .avatar-ring img {
            width:100% !important; height:100% !important; border-radius:50%; object-fit:cover; display:block;
            transform:scale(1.15); /* PNG has empty space around the icon, so zoom in a little */
        }
        .hero-info { flex:1; min-width:200px; padding-top:12px; }
        .hero-info h1 { margin:0; font-size:22px; color:var(--navy); }
        .hero-info .sub { margin:2px 0 0; font-size:13px; color:#64748b; }
        .badge-row { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
        .pill { display:inline-block; padding:4px 14px; border-radius:999px; font-size:12px; font-weight:600; }
        .pill.role { background:#eef2ff; color:#2f4fb0; }
        .pill.dept { background:#f1f5f9; color:#475569; }
        .pill.active { background:#ecfdf5; color:#065f46; }
        .pill.inactive { background:#fef2f2; color:#991b1b; }

        /* ----- Stats ----- */
        .stat-row { display:grid; grid-template-columns:repeat(auto-fit, minmax(190px, 1fr)); gap:16px; margin-bottom:24px; }
        .stat-card {
            background:#fff; border-radius:14px; padding:18px 20px;
            box-shadow:0 6px 20px rgba(10,37,64,0.06); border-top:3px solid var(--accent);
            opacity:0; animation:fadeUp 0.5s ease forwards;
            transition:transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover { transform:translateY(-4px); box-shadow:0 12px 26px rgba(10,37,64,0.12); }
        .stat-card:nth-child(1) { animation-delay:0.08s; }
        .stat-card:nth-child(2) { animation-delay:0.16s; border-top-color:#f59e0b; }
        .stat-card:nth-child(3) { animation-delay:0.24s; border-top-color:#16a34a; }
        .stat-card .val { font-size:20px; font-weight:700; color:var(--navy); line-height:1.3; }
        .stat-card .val.big { font-size:30px; }
        .stat-card .lbl { font-size:12px; color:#64748b; margin-top:4px; }

        /* ----- Cards ----- */
        .two-col { display:grid; grid-template-columns:1fr 1.4fr; gap:24px; align-items:start; }
        .card {
            background:#fff; border-radius:16px; padding:26px 28px;
            box-shadow:0 8px 24px rgba(10,37,64,0.07);
            opacity:0; animation:fadeUp 0.5s ease forwards;
        }
        .card h3 { margin:0 0 16px; color:var(--navy); font-size:16px; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }

        .info-list { list-style:none; margin:0; padding:0; }
        .info-list li {
            background:transparent; border-left:none; border-radius:0; box-shadow:none;
            padding:11px 0; margin:0; border-bottom:1px solid #eef1f5;
            display:flex; flex-direction:column; gap:2px;
        }
        .info-list li:last-child { border-bottom:none; }
        .info-list .k { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; color:#94a3b8; }
        .info-list .v { font-size:14px; color:var(--navy); font-weight:500; word-break:break-word; }

        /* ----- Form ----- */
        .card form {
            background:none !important; box-shadow:none !important;
            padding:0 !important; max-width:100% !important; margin:0 !important;
        }
        .card label {
            font-size:12px; font-weight:600; color:var(--navy);
            text-transform:uppercase; letter-spacing:0.03em; margin-top:14px; display:block;
        }
        .card input {
            background:#f4f7fb; border:1px solid #e2e8f0; border-radius:8px;
            font-family:'Poppins', sans-serif; padding:10px 12px; width:100%;
            transition:border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }
        .card input:focus { outline:none; border-color:var(--accent); background:#fff; box-shadow:0 0 0 3px rgba(47,111,237,0.12); }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:0 16px; }

        .password-section { margin-top:22px; padding-top:18px; border-top:1px solid #e2e8f0; }
        .password-section .hint { font-size:12px; color:#94a3b8; margin:0 0 4px; }
        .pw-wrap { position:relative; }
        .pw-wrap input { padding-right:56px; }
        .pw-toggle {
            position:absolute; right:8px; top:50%; transform:translateY(-50%);
            background:none !important; border:none; color:var(--accent) !important;
            font-size:12px; font-weight:600; padding:4px 6px !important; margin:0 !important;
            width:auto !important; cursor:pointer;
        }
        .pw-toggle:hover { background:none !important; text-decoration:underline; }
        .strength { height:6px; background:#eef1f5; border-radius:999px; margin-top:8px; overflow:hidden; }
        .strength-fill { height:100%; width:0; border-radius:999px; transition:width 0.3s ease, background 0.3s ease; }
        .strength-text { font-size:12px; color:#64748b; margin-top:4px; min-height:18px; }
        .pw-error { font-size:12px; color:#991b1b; margin-top:6px; display:none; }

        .card button[type="submit"] {
            background:var(--accent); width:100%; border:none; padding:13px; border-radius:8px;
            color:#fff; font-weight:700; cursor:pointer; transition:0.2s; margin-top:22px; font-size:14px;
        }
        .card button[type="submit"]:hover { background:var(--navy); transform:translateY(-1px); }

        @media (max-width:860px) { .two-col { grid-template-columns:1fr; } }
        @media (max-width:560px) {
            .form-grid { grid-template-columns:1fr; }
            .topbar h2 { font-size:15px; }
            .hero-body { padding:0 20px 22px; }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="index.php">&larr; Back to Home</a>
        <h2>My Account</h2>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="page-wrap">

        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="toast">Account updated successfully!</div>
        <?php endif; ?>

        <div class="profile-hero">
            <div class="hero-banner"></div>
            <div class="hero-body">
                <div class="avatar-ring"><img src="assets/account.png?v=<?php echo @filemtime(__DIR__ . '/assets/account.png'); ?>" alt="Account"></div>
                <div class="hero-info">
                    <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                    <p class="sub"><?php echo htmlspecialchars($user['email']); ?></p>
                    <div class="badge-row">
                        <span class="pill role"><?php echo htmlspecialchars($user['role_name']); ?></span>
                        <?php if (!empty($user['department_name'])): ?>
                        <span class="pill dept"><?php echo htmlspecialchars($user['department_name']); ?></span>
                        <?php endif; ?>
                        <span class="pill <?php echo $user['status'] == 'active' ? 'active' : 'inactive'; ?>"><?php echo ucfirst($user['status']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="stat-row">
            <div class="stat-card">
                <div class="val big" data-count="<?php echo $totalLogins; ?>">0</div>
                <div class="lbl">Total Logins</div>
            </div>
            <div class="stat-card">
                <div class="val big" data-count="<?php echo $activeAssignments; ?>">0</div>
                <div class="lbl">Active Assignments</div>
            </div>
            <div class="stat-card">
                <div class="val"><?php echo htmlspecialchars($lastLogin); ?></div>
                <div class="lbl">Last Login</div>
            </div>
        </div>

        <div class="two-col">
            <div class="card" style="animation-delay:0.3s;">
                <h3>Account Details</h3>
                <ul class="info-list">
                    <li><span class="k">Member Since</span><span class="v"><?php echo htmlspecialchars($memberSince); ?></span></li>
                    <li><span class="k">Role</span><span class="v"><?php echo htmlspecialchars($user['role_name']); ?></span></li>
                    <li><span class="k">Department</span><span class="v"><?php echo htmlspecialchars($user['department_name'] ?? '-'); ?></span></li>
                    <li><span class="k">Contact Number</span><span class="v"><?php echo htmlspecialchars($user['contact_number'] ?: '-'); ?></span></li>
                    <li><span class="k">Specialization</span><span class="v"><?php echo htmlspecialchars($user['specialization'] ?: '-'); ?></span></li>
                </ul>
            </div>

            <div class="card" style="animation-delay:0.38s;">
                <h3>Edit Profile</h3>
                <form method="POST" id="accountForm">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                    <div class="form-grid">
                        <div>
                            <label>Contact Number</label>
                            <input type="text" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>">
                        </div>
                        <div>
                            <label>Specialization</label>
                            <input type="text" name="specialization" value="<?php echo htmlspecialchars($user['specialization']); ?>">
                        </div>
                    </div>

                    <div class="password-section">
                        <p class="hint">Leave blank to keep your current password</p>

                        <label>New Password</label>
                        <div class="pw-wrap">
                            <input type="password" name="new_password" id="newPw" placeholder="••••••••" autocomplete="new-password">
                            <button type="button" class="pw-toggle" id="togglePw">Show</button>
                        </div>
                        <div class="strength"><div class="strength-fill" id="strengthFill"></div></div>
                        <div class="strength-text" id="strengthText"></div>

                        <label>Confirm New Password</label>
                        <input type="password" id="confirmPw" placeholder="••••••••" autocomplete="new-password">
                        <div class="pw-error" id="pwError">Passwords do not match.</div>
                    </div>

                    <button type="submit">Save Changes</button>
                </form>
            </div>
        </div>

    </div>

    <script>
        // Count-up numbers
        document.querySelectorAll('[data-count]').forEach(el => {
            const target = parseInt(el.dataset.count, 10) || 0;
            const t0 = performance.now();
            function step(t) {
                const p = Math.min((t - t0) / 900, 1);
                el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
                if (p < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        });

        // Show / hide password
        const newPw = document.getElementById('newPw');
        const confirmPw = document.getElementById('confirmPw');
        document.getElementById('togglePw').addEventListener('click', function () {
            const show = newPw.type === 'password';
            newPw.type = show ? 'text' : 'password';
            confirmPw.type = show ? 'text' : 'password';
            this.textContent = show ? 'Hide' : 'Show';
        });

        // Password strength meter
        const fill = document.getElementById('strengthFill');
        const text = document.getElementById('strengthText');
        newPw.addEventListener('input', () => {
            const v = newPw.value;
            let score = 0;
            if (v.length >= 8) score++;
            if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
            if (/\d/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            const levels = [
                { w: '0%',   c: '#e2e8f0', t: '' },
                { w: '25%',  c: '#dc2626', t: 'Weak' },
                { w: '50%',  c: '#f59e0b', t: 'Fair' },
                { w: '75%',  c: '#2f6fed', t: 'Good' },
                { w: '100%', c: '#16a34a', t: 'Strong' }
            ];
            const lv = v.length === 0 ? levels[0] : levels[Math.max(score, 1)];
            fill.style.width = lv.w;
            fill.style.background = lv.c;
            text.textContent = lv.t;
        });

        // Confirm password check (only when a new password is typed)
        document.getElementById('accountForm').addEventListener('submit', function (e) {
            const err = document.getElementById('pwError');
            if (newPw.value !== '' && newPw.value !== confirmPw.value) {
                e.preventDefault();
                err.style.display = 'block';
                confirmPw.focus();
            } else {
                err.style.display = 'none';
            }
        });
    </script>
    <script src="assets/enhance.js"></script>
</body>
</html>