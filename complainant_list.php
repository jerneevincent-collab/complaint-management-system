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
    <title>Complainant List</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        :root { --navy: #0a2540; --accent: #2f6fed; }
        body { background: #f4f7fb; }

        .topbar {
            background: var(--navy); color: #fff; display: flex;
            align-items: center; justify-content: space-between; padding: 14px 30px;
        }
        .topbar a { color: #cbd5e1; font-weight: 500; }
        .topbar a:hover { color: #fff; }

        .page-wrap { max-width: 1100px; margin: 0 auto; padding: 30px 20px; }

        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .page-header h2 { margin: 0; color: var(--navy); }
        .btn-primary {
            background: var(--accent); color: #fff; padding: 10px 18px;
            border-radius: 8px; font-weight: 600; font-size: 14px; transition: 0.2s;
        }
        .btn-primary:hover { background: var(--navy); text-decoration: none; }

        .filter-card {
            background: #fff; border-radius: 14px; padding: 18px 20px;
            box-shadow: 0 6px 20px rgba(10,37,64,0.06); margin-bottom: 20px;
            opacity: 0; animation: fadeIn 0.4s ease forwards;
        }
        @keyframes fadeIn { to { opacity: 1; } }
        .filter-card form {
            display: flex; flex-wrap: wrap; gap: 10px; background: none;
            box-shadow: none; padding: 0; max-width: 100%; margin: 0;
        }
        .filter-card input, .filter-card select {
            flex: 1; min-width: 150px; padding: 9px 12px; border: 1px solid #e2e8f0;
            border-radius: 8px; background: #f8fafc; font-family: 'Poppins', sans-serif; font-size: 13px;
        }
        .filter-card input:focus, .filter-card select:focus { outline: none; border-color: var(--accent); }
        .filter-card button {
            background: var(--navy); color: #fff; border: none; padding: 9px 20px;
            border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 13px; margin: 0; transition: 0.2s;
        }
        .filter-card button:hover { background: var(--accent); }

        .table-card {
            background: #fff; border-radius: 14px; box-shadow: 0 6px 20px rgba(10,37,64,0.06);
            overflow: hidden; opacity: 0; animation: fadeIn 0.4s ease 0.1s forwards;
        }
        table { margin: 0; box-shadow: none; }
        th { background: var(--navy); }

        .status-pill {
            display: inline-block; padding: 4px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 600; text-transform: uppercase;
        }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #fee2e2; color: #991b1b; }

        .action-links a { margin-right: 8px; font-size: 13px; font-weight: 600; }
        .action-links a.delete-link { color: #dc2626; }

        .pagination { display: flex; gap: 6px; margin-top: 16px; }
        .pagination a {
            background: #fff; padding: 6px 12px; border-radius: 6px;
            font-size: 13px; font-weight: 600; box-shadow: 0 2px 6px rgba(10,37,64,0.06);
        }
        .pagination a:hover { background: var(--accent); color: #fff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="index.php">&larr; Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="page-header">
            <h2>Registered Complainants</h2>
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
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="table-card">
            <table>
                <tr>
                    <th>Full Name</th>
                    <th>Contact Number</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['organization_department']); ?></td>
                    <td><span class="status-pill status-<?php echo $row['status']; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td class="action-links">
                        <a href="complainant_view.php?id=<?php echo $row['complainant_id']; ?>">View</a>
                        <a href="complainant_edit.php?id=<?php echo $row['complainant_id']; ?>">Edit</a>
                        <a class="delete-link" href="complainant_delete.php?id=<?php echo $row['complainant_id']; ?>" onclick="return confirm('Are you sure you want to delete this complainant?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&department=<?php echo urlencode($department_filter); ?>&status=<?php echo urlencode($status_filter); ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html>