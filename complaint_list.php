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

$search_number = isset($_GET['complaint_number']) ? trim($_GET['complaint_number']) : '';
$search_complainant = isset($_GET['complainant']) ? trim($_GET['complainant']) : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$date_filter = isset($_GET['date_filed']) ? $_GET['date_filed'] : '';
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where = [];
$params = [];
$types = "";

if (!empty($search_number)) { $where[] = "c.complaint_number LIKE ?"; $params[] = "%$search_number%"; $types .= "s"; }
if (!empty($search_complainant)) { $where[] = "co.full_name LIKE ?"; $params[] = "%$search_complainant%"; $types .= "s"; }
if (!empty($category_filter)) { $where[] = "c.category_id = ?"; $params[] = $category_filter; $types .= "i"; }
if (!empty($date_filter)) { $where[] = "c.date_filed = ?"; $params[] = $date_filter; $types .= "s"; }
if (!empty($priority_filter)) { $where[] = "c.priority = ?"; $params[] = $priority_filter; $types .= "s"; }
if (!empty($status_filter)) { $where[] = "c.status = ?"; $params[] = $status_filter; $types .= "s"; }

$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

$sql = "SELECT c.*, co.full_name AS complainant_name, cc.category_name
        FROM complaints c
        JOIN complainants co ON c.complainant_id = co.complainant_id
        JOIN complaint_categories cc ON c.category_id = cc.category_id
        $whereClause
        ORDER BY c.complaint_id DESC";

$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$categories = $conn->query("SELECT category_id, category_name FROM complaint_categories ORDER BY category_name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complaint List</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        :root { --navy: #0a2540; --accent: #2f6fed; }
        body { background: #f4f7fb; }

        .topbar {
            background: var(--navy);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 30px;
        }
        .topbar a { color: #cbd5e1; font-weight: 500; }
        .topbar a:hover { color: #fff; }

        .page-wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .page-header h2 { margin: 0; color: var(--navy); }
        .btn-primary {
            background: var(--accent);
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: 0.2s;
        }
        .btn-primary:hover { background: var(--navy); text-decoration: none; }

        .filter-card {
            background: #fff;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 6px 20px rgba(10,37,64,0.06);
            margin-bottom: 20px;
            opacity: 0;
            animation: fadeIn 0.4s ease forwards;
        }
        @keyframes fadeIn { to { opacity: 1; } }

        .filter-card form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            background: none;
            box-shadow: none;
            padding: 0;
            max-width: 100%;
            margin: 0;
        }
        .filter-card input, .filter-card select {
            flex: 1;
            min-width: 130px;
            padding: 9px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
        }
        .filter-card input:focus, .filter-card select:focus {
            outline: none;
            border-color: var(--accent);
        }
        .filter-card button {
            background: var(--navy);
            color: #fff;
            border: none;
            padding: 9px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
            margin: 0;
            transition: 0.2s;
        }
        .filter-card button:hover { background: var(--accent); }

        .table-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(10,37,64,0.06);
            overflow: hidden;
            opacity: 0;
            animation: fadeIn 0.4s ease 0.1s forwards;
        }
        table { margin: 0; box-shadow: none; border-radius: 0; }
        th { background: var(--navy); }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-submitted { background: #dbeafe; color: #1e40af; }
        .status-for-assignment, .status-assigned { background: #fef3c7; color: #92400e; }
        .status-under-investigation, .status-action-required, .status-for-resolution { background: #fed7aa; color: #9a3412; }
        .status-resolved { background: #d1fae5; color: #065f46; }
        .status-closed { background: #e2e8f0; color: #475569; }

        .priority-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }
        .priority-low { background: #dcfce7; color: #166534; }
        .priority-medium { background: #fef3c7; color: #92400e; }
        .priority-high { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="index.php">&larr; Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="page-header">
            <h2>Complaint Monitoring</h2>
            <a href="complaint_register.php" class="btn-primary">+ File New Complaint</a>
        </div>

        <div class="filter-card">
            <form method="GET">
                <input type="text" name="complaint_number" placeholder="Complaint #" value="<?php echo htmlspecialchars($search_number); ?>">
                <input type="text" name="complainant" placeholder="Complainant name" value="<?php echo htmlspecialchars($search_complainant); ?>">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['category_id']; ?>" <?php if ($category_filter == $cat['category_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <input type="date" name="date_filed" value="<?php echo htmlspecialchars($date_filter); ?>">
                <select name="priority">
                    <option value="">All Priorities</option>
                    <option value="low" <?php if ($priority_filter == 'low') echo 'selected'; ?>>Low</option>
                    <option value="medium" <?php if ($priority_filter == 'medium') echo 'selected'; ?>>Medium</option>
                    <option value="high" <?php if ($priority_filter == 'high') echo 'selected'; ?>>High</option>
                </select>
                <select name="status">
                    <option value="">All Status</option>
                    <option value="Submitted" <?php if ($status_filter == 'Submitted') echo 'selected'; ?>>Submitted</option>
                    <option value="For Assignment" <?php if ($status_filter == 'For Assignment') echo 'selected'; ?>>For Assignment</option>
                    <option value="Assigned" <?php if ($status_filter == 'Assigned') echo 'selected'; ?>>Assigned</option>
                    <option value="Under Investigation" <?php if ($status_filter == 'Under Investigation') echo 'selected'; ?>>Under Investigation</option>
                    <option value="Action Required" <?php if ($status_filter == 'Action Required') echo 'selected'; ?>>Action Required</option>
                    <option value="For Resolution" <?php if ($status_filter == 'For Resolution') echo 'selected'; ?>>For Resolution</option>
                    <option value="Resolved" <?php if ($status_filter == 'Resolved') echo 'selected'; ?>>Resolved</option>
                    <option value="Closed" <?php if ($status_filter == 'Closed') echo 'selected'; ?>>Closed</option>
                </select>
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="table-card">
            <table>
                <tr>
                    <th>Complaint #</th>
                    <th>Complainant</th>
                    <th>Category</th>
                    <th>Subject</th>
                    <th>Date Filed</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()):
                    $statusClass = strtolower(str_replace(' ', '-', $row['status']));
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['complaint_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['complainant_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['subject']); ?></td>
                    <td><?php echo $row['date_filed']; ?></td>
                    <td><span class="priority-badge priority-<?php echo $row['priority']; ?>"><?php echo ucfirst($row['priority']); ?></span></td>
                    <td><span class="status-badge status-<?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td><a href="complaint_view.php?id=<?php echo $row['complaint_id']; ?>">View</a></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>