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

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$department_filter = isset($_GET['department']) ? trim($_GET['department']) : '';
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];
$types = "";

if (!empty($search)) {
    $where[] = "(full_name LIKE ? OR contact_number LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $types .= "ss";
}
if (!empty($department_filter)) {
    $where[] = "organization_department = ?"; $params[] = $department_filter; $types .= "s";
}
if (!empty($status_filter)) {
    $where[] = "status = ?"; $params[] = $status_filter; $types .= "s";
}

$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$countSql = "SELECT COUNT(*) as total FROM complainants $whereClause";
$countStmt = $conn->prepare($countSql);
if ($types) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

$sql = "SELECT * FROM complainants $whereClause ORDER BY complainant_id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$paramsWithLimit = $params;
$paramsWithLimit[] = $limit;
$paramsWithLimit[] = $offset;
$typesWithLimit = $types . "ii";
$stmt->bind_param($typesWithLimit, ...$paramsWithLimit);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Complainant List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
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
        .chips-unused { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; opacity:0; animation: fadeUp .5s ease .05s forwards; }
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
    
        .email { color:var(--muted); word-break:break-all; }
        .status-pill { display:inline-block; padding:4px 11px; border-radius:20px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.02em; }
        .status-active { background:#d1fae5; color:#065f46; }
        .status-inactive { background:#fee2e2; color:#991b1b; }
        .actions { display:flex; gap:6px; flex-wrap:nowrap; }
        .act { display:inline-block; padding:6px 12px; border-radius:8px; border:1.5px solid var(--accent); color:var(--accent); font-weight:600; font-size:12.5px; text-decoration:none !important; transition:.2s; white-space:nowrap; background:transparent; }
        .act:hover { background:var(--accent); color:#fff; }
        .act.edit { border-color:#0f766e; color:#0f766e; }
        .act.edit:hover { background:#0f766e; color:#fff; }
        .act.delete { border-color:#dc2626; color:#dc2626; }
        .act.delete:hover { background:#dc2626; color:#fff; }
        .stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:14px; margin-bottom:18px; }
        .stat { background:#fff; border-radius:16px; padding:16px 18px; box-shadow:0 8px 24px rgba(10,37,64,.07); border-top:4px solid var(--accent); opacity:0; animation:fadeUp .5s ease forwards; }
        .stat h3 { margin:0; font-size:26px; color:var(--navy); font-weight:700; }
        .stat p { margin:4px 0 0; font-size:12.5px; color:var(--muted); }

        .pagination { display:flex; gap:6px; margin-top:18px; justify-content:center; flex-wrap:wrap; opacity:0; animation:fadeUp .5s ease .3s forwards; }
        .pagination a {
            min-width:38px; text-align:center; background:#fff; color:var(--navy); padding:8px 13px; border-radius:10px;
            font-size:13px; font-weight:600; box-shadow:0 2px 8px rgba(10,37,64,.08); text-decoration:none !important; transition:.2s;
        }
        .pagination a:hover { background:var(--accent); color:#fff; transform:translateY(-2px); }
        .pagination a.current { background:var(--navy); color:#fff; pointer-events:none; }
        .pagination a.nav { color:var(--accent); }
        .pagination a.nav:hover { color:#fff; }
    </style>
</head>
<body>
    <div class="topbar" id="topbar">
        <a class="back-link" href="index.php">&larr; Back to Home</a>
        <h2>Registered Complainants</h2>
        <a class="logout" href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="page-header">
            <div>
                <h1>Complainants</h1>
                <p>Manage everyone who can file a complaint.</p>
            </div>
            <a href="complainant_register.php" class="btn-primary">+ Register New Complainant</a>
        </div>

        <div class="filter-card">
            <form method="GET">
                <input type="text" name="search" placeholder="Search by name or contact number" value="<?php echo htmlspecialchars($search); ?>">
                <input type="text" name="department" placeholder="Filter by department" value="<?php echo htmlspecialchars($department_filter); ?>">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="active" <?php if ($status_filter == 'active') echo 'selected'; ?>>Active</option>
                    <option value="inactive" <?php if ($status_filter == 'inactive') echo 'selected'; ?>>Inactive</option>
                </select>
                <button type="submit" class="btn-search">Search</button>
                <a class="btn-clear" href="complainant_list.php">Clear</a>
            </form>
        </div>

        <div class="table-card">
            <div class="table-meta">
                <span>
                    <?php if ($totalRows > 0): ?>
                        Showing <strong><?php echo $offset + 1; ?>&ndash;<?php echo min($offset + $limit, $totalRows); ?></strong> of <strong><?php echo $totalRows; ?></strong> complainants
                    <?php else: ?>
                        <strong>0</strong> complainants
                    <?php endif; ?>
                </span>
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Contact Number</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                <?php $rowCount = 0; while ($row = $result->fetch_assoc()): $rowCount++; ?>
                <tr>
                    <td><div class="person"><span class="avatar"><?php echo htmlspecialchars(strtoupper(substr(trim($row['full_name']), 0, 1))); ?></span><span class="c-num" style="font-weight:600"><?php echo htmlspecialchars($row['full_name']); ?></span></div></td>
                    <td class="date"><?php echo htmlspecialchars($row['contact_number']); ?></td>
                    <td class="email"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['organization_department']); ?></td>
                    <td><span class="status-pill status-<?php echo $row['status']; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td>
                        <div class="actions">
                            <a class="act" href="complainant_view.php?id=<?php echo $row['complainant_id']; ?>">View</a>
                            <a class="act edit" href="complainant_edit.php?id=<?php echo $row['complainant_id']; ?>">Edit</a>
                            <a class="act delete" href="complainant_delete.php?id=<?php echo $row['complainant_id']; ?>" onclick="return confirm('Are you sure you want to delete this complainant?');">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="empty <?php if ($rowCount === 0) echo 'show'; ?>" id="empty">
                <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6M17 11l4 4m0-4l-4 4"/></svg>
                <h3>No complainants found</h3>
                <p>Try changing or clearing your filters.</p>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php $qs = "&search=" . urlencode($search) . "&department=" . urlencode($department_filter) . "&status=" . urlencode($status_filter); ?>
            <?php if ($page > 1): ?><a class="nav" href="?page=<?php echo $page - 1; ?><?php echo $qs; ?>">&lsaquo; Prev</a><?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a class="<?php if ($i == $page) echo 'current'; ?>" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&department=<?php echo urlencode($department_filter); ?>&status=<?php echo urlencode($status_filter); ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?><a class="nav" href="?page=<?php echo $page + 1; ?><?php echo $qs; ?>">Next &rsaquo;</a><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
        (function () {
            const topbar = document.getElementById('topbar');
            window.addEventListener('scroll', () => topbar.classList.toggle('scrolled', window.scrollY > 6), { passive: true });
            document.querySelectorAll('tbody tr').forEach((r, i) => { r.style.animationDelay = (0.2 + i * 0.06) + 's'; });
        })();
    </script>
</body>
</html>