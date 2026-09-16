<?php
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
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}
if (!empty($department_filter)) {
    $where[] = "organization_department = ?";
    $params[] = $department_filter;
    $types .= "s";
}
if (!empty($status_filter)) {
    $where[] = "status = ?";
    $params[] = $status_filter;
    $types .= "s";
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
</head>
<body>
    <h2>Registered Complainants</h2>
    <p><a href="complainant_register.php">+ Register New Complainant</a></p>

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

    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Contact Number</th>
            <th>Email</th>
            <th>Department</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['complainant_id']; ?></td>
            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
            <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['organization_department']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td>
                <a href="complainant_view.php?id=<?php echo $row['complainant_id']; ?>">View</a> |
                <a href="complainant_edit.php?id=<?php echo $row['complainant_id']; ?>">Edit</a> |
                <a href="complainant_delete.php?id=<?php echo $row['complainant_id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <div>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&department=<?php echo urlencode($department_filter); ?>&status=<?php echo urlencode($status_filter); ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
</body>
</html>