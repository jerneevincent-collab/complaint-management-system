<?php
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

if (!empty($search_number)) {
    $where[] = "c.complaint_number LIKE ?";
    $params[] = "%$search_number%";
    $types .= "s";
}
if (!empty($search_complainant)) {
    $where[] = "co.full_name LIKE ?";
    $params[] = "%$search_complainant%";
    $types .= "s";
}
if (!empty($category_filter)) {
    $where[] = "c.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}
if (!empty($date_filter)) {
    $where[] = "c.date_filed = ?";
    $params[] = $date_filter;
    $types .= "s";
}
if (!empty($priority_filter)) {
    $where[] = "c.priority = ?";
    $params[] = $priority_filter;
    $types .= "s";
}
if (!empty($status_filter)) {
    $where[] = "c.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

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
</head>
<body>
    <h2>Complaint Monitoring</h2>
    <p><a href="complaint_register.php">+ File New Complaint</a></p>

    <form method="GET">
        <input type="text" name="complaint_number" placeholder="Search by complaint number" value="<?php echo htmlspecialchars($search_number); ?>">
        <input type="text" name="complainant" placeholder="Search by complainant" value="<?php echo htmlspecialchars($search_complainant); ?>">
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

    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Complaint #</th>
            <th>Complainant</th>
            <th>Category</th>
            <th>Subject</th>
            <th>Date Filed</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['complaint_number']); ?></td>
            <td><?php echo htmlspecialchars($row['complainant_name']); ?></td>
            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
            <td><?php echo htmlspecialchars($row['subject']); ?></td>
            <td><?php echo $row['date_filed']; ?></td>
            <td><?php echo htmlspecialchars($row['priority']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td><a href="complaint_view.php?id=<?php echo $row['complaint_id']; ?>">View</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>