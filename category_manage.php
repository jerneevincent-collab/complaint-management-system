<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] == 6) {
    die("Access denied.");
}
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        :root {
            --navy: #0a2540;
            --accent: #2f6fed;
            --bg: #f4f7fb;
        }
        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { margin: 0; background: var(--bg); color: #1b2733; }

        .topbar {
            background: var(--navy);
            color: #fff;
            position: relative;
            display: flex;
            align-items: center;
            padding: 14px 30px;
        }
        .topbar h2 {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            margin: 0;
            color: #fff;
            font-size: 18px;
            font-weight: 600;
            white-space: nowrap;
        }
        .topbar .back-link {
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            z-index: 1;
        }
        .topbar .back-link:hover { color: #fff; }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
        }
        body .page-wrap {
            position: relative;
            width: 100% !important;
            margin: 0 !important;
            padding: 30px 0 60px 0 !important;
        }
        .content-wrap {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            top: 30px;
            width: 860px;
            max-width: calc(100% - 40px);
        }

        .form-card, .table-card {
            margin-bottom: 24px;
            opacity: 0;
            animation: fadeUp 0.5s ease forwards;
        }
        .table-card {
            padding: 28px 30px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(10, 37, 64, 0.07);
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-card form {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            max-width: 100%;
            margin: 0;
        }
        .form-card form input[type="text"] {
            flex: 1;
            margin: 0;
        }
        .form-card form button {
            margin: 0;
            white-space: nowrap;
        }

        .table-card h3 {
            margin: 0 0 16px 0;
            color: var(--navy);
            font-size: 16px;
        }
        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th {
            background: var(--navy);
            color: #fff;
            text-align: left;
            padding: 10px 12px;
            white-space: nowrap;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #eef1f5;
            vertical-align: top;
        }
        tr:last-child td { border-bottom: none; }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge-active { background: #e3f6e8; color: #1c7c3f; }
        .badge-inactive { background: #f1f3f6; color: #6b7787; }

        .action-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
        }
        .action-link:hover { text-decoration: underline; }
        .action-link.danger { color: #c1272d; }
        .action-sep { color: #cbd5e1; margin: 0 6px; }

        .empty-note {
            color: #6b7787;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <a class="back-link" href="dashboard.php">← Dashboard</a>
        <h2>Manage Complaint Categories</h2>
    </div>

    <div class="page-wrap">
      <div class="content-wrap">
        <div class="form-card">
            <form method="POST">
                <input type="text" name="category_name" placeholder="New category name" required>
                <button type="submit" name="add_category">Add Category</button>
            </form>
        </div>

        <div class="table-card">
            <h3>Categories</h3>
            <?php if ($result->num_rows > 0): ?>
            <div class="table-scroll">
                <table>
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
                        <td>
                            <span class="badge <?php echo $row['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td>
                            <a class="action-link" href="?toggle=<?php echo $row['category_id']; ?>">
                                <?php echo $row['is_active'] ? 'Deactivate' : 'Activate'; ?>
                            </a>
                            <span class="action-sep">|</span>
                            <a class="action-link danger" href="?delete=<?php echo $row['category_id']; ?>" onclick="return confirm('Delete this category?');">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            <?php else: ?>
                <p class="empty-note">No categories yet.</p>
            <?php endif; ?>
        </div>
      </div>
    </div>
</body>
</html>