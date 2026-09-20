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
    <title>Personnel Management</title>
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

        .page-wrap h2 { color: var(--navy); margin-top: 0; }

        .content-grid {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 20px;
            align-items: start;
        }

        .form-card {
            background: #fff;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 6px 20px rgba(10,37,64,0.06);
            opacity: 0;
            transform: translateY(16px);
            animation: fadeUp 0.5s ease forwards;
        }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        .form-card h3 { margin-top: 0; color: var(--navy); font-size: 15px; }
        .form-card form {
            box-shadow: none; padding: 0; max-width: 100%; margin: 0; background: none;
        }
        .form-card label {
            font-size: 12px; font-weight: 600; color: var(--navy);
            text-transform: uppercase; letter-spacing: 0.03em;
        }
        .form-card input, .form-card select {
            background: #f4f7fb;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
        }
        .form-card input:focus, .form-card select:focus {
            outline: none; border-color: var(--accent); background: #fff;
        }
        .form-card button {
            background: var(--accent);
            width: 100%;
            border: none;
            padding: 11px;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .form-card button:hover { background: var(--navy); }

        .table-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(10,37,64,0.06);
            overflow: hidden;
            opacity: 0;
            animation: fadeUp 0.5s ease 0.1s forwards;
        }
        table { margin: 0; box-shadow: none; }
        th { background: var(--navy); }

        .status-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #fee2e2; color: #991b1b; }

        .toggle-link {
            font-size: 13px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="index.php">&larr; Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <h2>Personnel Management</h2>

        <div class="content-grid">
            <div class="form-card">
                <h3>Add New Personnel</h3>
                <form method="POST">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required>

                    <label>Email</label>
                    <input type="email" name="email" required>

                    <label>Contact Number</label>
                    <input type="text" name="contact_number">

                    <label>Role</label>
                    <select name="role_id" required>
                        <?php $roles->data_seek(0); while ($r = $roles->fetch_assoc()): ?>
                            <option value="<?php echo $r['role_id']; ?>"><?php echo htmlspecialchars($r['role_name']); ?></option>
                        <?php endwhile; ?>
                    </select>

                    <label>Department</label>
                    <select name="department_id" required>
                        <?php $departments->data_seek(0); while ($d = $departments->fetch_assoc()): ?>
                            <option value="<?php echo $d['department_id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option>
                        <?php endwhile; ?>
                    </select>

                    <label>Specialization</label>
                    <input type="text" name="specialization">

                    <button type="submit" name="add_personnel">Add Personnel</button>
                </form>
            </div>

            <div class="table-card">
                <table>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['role_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['department_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><span class="status-pill status-<?php echo $row['status']; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                        <td><a class="toggle-link" href="?toggle=<?php echo $row['user_id']; ?>"><?php echo $row['status'] == 'active' ? 'Deactivate' : 'Activate'; ?></a></td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>