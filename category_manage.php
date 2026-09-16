<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add new category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    if (!empty($category_name)) {
        $stmt = $conn->prepare("INSERT INTO complaint_categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
    }
    header("Location: category_manage.php");
    exit();
}

// Toggle active/inactive
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $conn->prepare("UPDATE complaint_categories SET is_active = NOT is_active WHERE category_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: category_manage.php");
    exit();
}

// Delete category
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM complaint_categories WHERE category_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: category_manage.php");
    exit();
}

$result = $conn->query("SELECT * FROM complaint_categories ORDER BY category_id");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Complaint Categories</title>
</head>
<body>
    <h2>Complaint Categories</h2>

    <form method="POST">
        <input type="text" name="category_name" placeholder="New category name" required>
        <button type="submit" name="add_category">Add Category</button>
    </form>

    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Category Name</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['category_id']; ?></td>
            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
            <td><?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?></td>
            <td>
                <a href="?toggle=<?php echo $row['category_id']; ?>">
                    <?php echo $row['is_active'] ? 'Deactivate' : 'Activate'; ?>
                </a> |
                <a href="?delete=<?php echo $row['category_id']; ?>" onclick="return confirm('Delete this category?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>